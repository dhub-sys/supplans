<?php
namespace Support\Component\Supportplans\Site\Helper;

\defined('_JEXEC') or die;

/**
 * Minimal Excel (.xlsx) reader and writer, without external libraries (ZipArchive + XML).
 *
 * Reader: first worksheet (or the one with a given name), cell values as strings; numeric
 * cells stay numeric strings, so dates come back as Excel serial numbers (see toDate()).
 * Writer: sheets of plain rows with a bold header, frozen first row, column widths, an
 * optional date column and an optional drop-down list validation.
 */
final class Xlsx
{
    private const MAX_XML_BYTES = 20 * 1024 * 1024;
    private const NS = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
    private const NS_REL = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';

    public static function available(): bool
    {
        return class_exists(\ZipArchive::class) && function_exists('simplexml_load_string');
    }

    /* ================================================================ reading */

    /**
     * Rows of a worksheet as arrays of strings (empty trailing cells trimmed).
     * $preferSheet: name of the worksheet to read if it exists (else the first one).
     */
    public static function read(string $path, string $preferSheet = ''): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            throw new \RuntimeException('Το αρχείο δεν είναι έγκυρο Excel (.xlsx).');
        }
        try {
            $workbook = self::xml($zip, 'xl/workbook.xml');
            $rels = self::xml($zip, 'xl/_rels/workbook.xml.rels');
            $targets = [];
            foreach ($rels->Relationship as $r) {
                $targets[(string) $r['Id']] = (string) $r['Target'];
            }
            $sheetPath = '';
            $workbook->registerXPathNamespace('m', self::NS);
            foreach ($workbook->xpath('//m:sheets/m:sheet') ?: [] as $sheet) {
                $rid = (string) $sheet->attributes(self::NS_REL)['id'];
                $target = $targets[$rid] ?? '';
                $target = strpos($target, '/') === 0 ? ltrim($target, '/') : 'xl/' . $target;
                if ($sheetPath === '') {
                    $sheetPath = $target;
                }
                if ($preferSheet !== '' && mb_strtolower((string) $sheet['name']) === mb_strtolower($preferSheet)) {
                    $sheetPath = $target;
                    break;
                }
            }
            if ($sheetPath === '') {
                throw new \RuntimeException('Το αρχείο δεν έχει φύλλα.');
            }
            $shared = [];
            if ($zip->locateName('xl/sharedStrings.xml') !== false) {
                foreach (self::xml($zip, 'xl/sharedStrings.xml')->si as $si) {
                    $shared[] = self::text($si);
                }
            }
            $rows = [];
            foreach (self::xml($zip, $sheetPath)->sheetData->row as $row) {
                $cells = [];
                foreach ($row->c as $c) {
                    $col = self::colIndex((string) $c['r']);
                    $type = (string) $c['t'];
                    if ($type === 's') {
                        $value = $shared[(int) $c->v] ?? '';
                    } elseif ($type === 'inlineStr') {
                        $value = self::text($c->is);
                    } elseif ($type === 'b') {
                        $value = (string) $c->v === '1' ? '1' : '0';
                    } else {
                        $value = (string) $c->v;
                    }
                    $cells[$col] = trim($value);
                }
                if (!$cells) {
                    continue;
                }
                $line = array_fill(0, max(array_keys($cells)) + 1, '');
                foreach ($cells as $i => $v) {
                    $line[$i] = $v;
                }
                $rows[(int) $row['r'] ?: count($rows) + 1] = $line;
            }
            return $rows;
        } finally {
            $zip->close();
        }
    }

    private static function xml(\ZipArchive $zip, string $name): \SimpleXMLElement
    {
        $stat = $zip->statName($name);
        if (!$stat || $stat['size'] > self::MAX_XML_BYTES) {
            throw new \RuntimeException('Το αρχείο Excel δεν διαβάζεται ή είναι πολύ μεγάλο.');
        }
        $data = (string) $zip->getFromName($name);
        // No DTDs: blocks entity tricks (external entities are also disabled by default in PHP 8).
        if (stripos($data, '<!DOCTYPE') !== false) {
            throw new \RuntimeException('Μη αποδεκτό αρχείο Excel.');
        }
        $xml = simplexml_load_string($data, \SimpleXMLElement::class, LIBXML_NONET | LIBXML_COMPACT);
        if ($xml === false) {
            throw new \RuntimeException('Το αρχείο Excel είναι κατεστραμμένο.');
        }
        return $xml;
    }

    /** Text of a shared/inline string, also when it is split in formatted runs. */
    private static function text(\SimpleXMLElement $node): string
    {
        if (isset($node->t)) {
            return (string) $node->t;
        }
        $out = '';
        foreach ($node->r as $run) {
            $out .= (string) $run->t;
        }
        return $out;
    }

    private static function colIndex(string $ref): int
    {
        $letters = preg_replace('/\d+/', '', strtoupper($ref));
        $n = 0;
        for ($i = 0, $len = strlen($letters); $i < $len; $i++) {
            $n = $n * 26 + (ord($letters[$i]) - 64);
        }
        return max(0, $n - 1);
    }

    private static function colName(int $index): string
    {
        $name = '';
        for ($n = $index + 1; $n > 0; $n = intdiv($n - 1, 26)) {
            $name = chr(65 + ($n - 1) % 26) . $name;
        }
        return $name;
    }

    /** Excel serial date (e.g. 46447) or text date to Y-m-d; '' if not a date. */
    public static function toDate(string $value): string
    {
        $value = trim($value);
        if ($value !== '' && preg_match('/^\d+(\.\d+)?$/', $value) && (float) $value > 20000 && (float) $value < 80000) {
            return (new \DateTimeImmutable('1899-12-30'))->modify('+' . (int) floor((float) $value) . ' days')->format('Y-m-d');
        }
        return Notify::parseDate($value);
    }

    /* ================================================================ writing */

    /**
     * Builds an .xlsx file and returns its bytes.
     * $sheets: [['name' => …, 'rows' => [[…], …], 'widths' => [cols…], 'dateCols' => [index…],
     *            'list' => ['col' => index, 'source' => "'Sheet'!\$A\$2:\$A\$9"]], …]
     * The first row of every sheet is the header.
     */
    public static function build(array $sheets): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'spx');
        $zip = new \ZipArchive();
        if ($zip->open($tmp, \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Δεν ήταν δυνατή η δημιουργία του αρχείου Excel.');
        }
        $e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES | ENT_XML1, 'UTF-8');
        $sheetEntries = $relEntries = $overrides = '';
        foreach (array_values($sheets) as $i => $sheet) {
            $n = $i + 1;
            $sheetEntries .= '<sheet name="' . $e(mb_substr($sheet['name'], 0, 31)) . '" sheetId="' . $n . '" r:id="rId' . $n . '"/>';
            $relEntries .= '<Relationship Id="rId' . $n . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . $n . '.xml"/>';
            $overrides .= '<Override PartName="/xl/worksheets/sheet' . $n . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
            $zip->addFromString('xl/worksheets/sheet' . $n . '.xml', self::sheetXml($sheet, $e));
        }
        $count = count($sheets);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>' . $overrides . '</Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="' . self::NS . '" xmlns:r="' . self::NS_REL . '"><sheets>' . $sheetEntries . '</sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . $relEntries
            . '<Relationship Id="rId' . ($count + 1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>');
        // Styles: 0 normal, 1 bold header with fill and border, 2 date dd/mm/yyyy, 3 wrapped text.
        $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="' . self::NS . '"><numFmts count="1"><numFmt numFmtId="164" formatCode="dd/mm/yyyy"/></numFmts>'
            . '<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font></fonts>'
            . '<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF2563EB"/><bgColor indexed="64"/></patternFill></fill></fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="4"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/>'
            . '<xf numFmtId="164" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyAlignment="1"><alignment wrapText="1" vertical="top"/></xf></cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>');
        $zip->close();
        $data = (string) file_get_contents($tmp);
        @unlink($tmp);
        return $data;
    }

    private static function sheetXml(array $sheet, callable $e): string
    {
        $rows = $sheet['rows'];
        $dateCols = array_flip($sheet['dateCols'] ?? []);
        $wrap = !empty($sheet['wrap']);
        $cols = '';
        foreach ($sheet['widths'] ?? [] as $i => $w) {
            $cols .= '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="' . (float) $w . '" customWidth="1"/>';
        }
        $data = '';
        foreach (array_values($rows) as $r => $row) {
            $n = $r + 1;
            $data .= '<row r="' . $n . '">';
            foreach (array_values($row) as $c => $value) {
                $ref = self::colName($c) . $n;
                if ($value === null || $value === '') {
                    continue;
                }
                if ($r === 0) {
                    $data .= '<c r="' . $ref . '" s="1" t="inlineStr"><is><t>' . $e($value) . '</t></is></c>';
                } elseif (isset($dateCols[$c]) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $value)) {
                    $serial = (int) (new \DateTimeImmutable('1899-12-30'))->diff(new \DateTimeImmutable((string) $value))->days;
                    $data .= '<c r="' . $ref . '" s="2"><v>' . $serial . '</v></c>';
                } elseif (is_int($value) || is_float($value)) {
                    $data .= '<c r="' . $ref . '"><v>' . $value . '</v></c>';
                } else {
                    $data .= '<c r="' . $ref . '"' . ($wrap ? ' s="3"' : '') . ' t="inlineStr"><is><t xml:space="preserve">' . $e($value) . '</t></is></c>';
                }
            }
            $data .= '</row>';
        }
        $validation = '';
        if (!empty($sheet['list'])) {
            $col = self::colName((int) $sheet['list']['col']);
            $validation = '<dataValidations count="1"><dataValidation type="list" allowBlank="1" showErrorMessage="1" errorTitle="Υπηρεσία" error="Επιλέξτε υπηρεσία από τον κατάλογο." sqref="'
                . $col . '2:' . $col . '2000"><formula1>' . $e($sheet['list']['source']) . '</formula1></dataValidation></dataValidations>';
        }
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="' . self::NS . '" xmlns:r="' . self::NS_REL . '">'
            . '<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            . ($cols !== '' ? '<cols>' . $cols . '</cols>' : '') . '<sheetData>' . $data . '</sheetData>' . $validation . '</worksheet>';
    }
}
