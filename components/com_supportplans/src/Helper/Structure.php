<?php
namespace Support\Component\Supportplans\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Website structure proposals ("Πρόταση δομής"): a sitemap tree of sections and content
 * suggestions per section. The tree is stored as an ordered flat list of nodes with a depth
 * ("level"), which keeps editing simple; the document builds the nested view from it.
 */
final class Structure
{
    public const T = '#__supportplans_structures';
    public const MAX_LEVEL = 4;
    public const DEFAULTS = [
        'doc_title'     => 'Πρόταση δομής ιστοσελίδας',
        'intro_title'   => 'Εισαγωγή',
        'sitemap_title' => 'Δομή ιστοσελίδας (sitemap)',
        'content_title' => 'Προτάσεις περιεχομένου ανά ενότητα',
        'outro_title'   => 'Επόμενα βήματα',
    ];

    public static function find(int $id): ?object
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        return $db->setQuery($db->getQuery(true)->select('*')->from($db->quoteName(self::T))->where('id=' . $id))->loadObject() ?: null;
    }

    public static function all(): array
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        return $db->setQuery($db->getQuery(true)->select('*')->from($db->quoteName(self::T))->order('proposal_date DESC, id DESC'))->loadObjectList();
    }

    /** "26001", "26002"… like the offers, with its own sequence. */
    /** Next document number, shared by all document types (see Documents). */
    public static function nextNumber(): string
    {
        return Documents::nextNumber();
    }

    /**
     * Nodes from JSON: [{title, level, purpose, content}], with valid levels (the first node is
     * level 0; a node is at most one level deeper than the one before it).
     */
    public static function nodes($json): array
    {
        $raw = is_array($json) ? $json : json_decode((string) $json, true);
        $out = [];
        $prev = -1;
        foreach (is_array($raw) ? $raw : [] as $n) {
            if (!is_array($n)) continue;
            $title = trim((string) ($n['title'] ?? ''));
            $purpose = trim((string) ($n['purpose'] ?? ''));
            $content = str_replace("\r\n", "\n", trim((string) ($n['content'] ?? '')));
            if ($title === '' && $purpose === '' && $content === '') continue;
            $level = max(0, min(self::MAX_LEVEL, (int) ($n['level'] ?? 0), $prev + 1));
            // Layout of the section in the content suggestions: new page, or a spacing in mm
            // ("" = automatic spacing by level).
            $space = trim((string) ($n['space'] ?? ''));
            $out[] = ['title' => mb_substr($title, 0, 190), 'level' => $level, 'purpose' => mb_substr($purpose, 0, 1000), 'content' => mb_substr($content, 0, 20000),
                'break' => (int) !empty($n['break']), 'space' => $space === '' || !is_numeric($space) ? '' : max(0, min(80, (int) $space))];
            $prev = $level;
        }
        return $out;
    }

    /** Adds "number" (1, 1.2, 1.2.3) to every node. */
    public static function numbered(array $nodes): array
    {
        $counters = [];
        foreach ($nodes as &$n) {
            $l = $n['level'];
            $counters = array_slice($counters, 0, $l + 1);
            $counters[$l] = ($counters[$l] ?? 0) + 1;
            for ($i = 0; $i < $l; $i++) $counters[$i] = $counters[$i] ?? 1;
            $n['number'] = implode('.', array_slice($counters, 0, $l + 1));
        }
        return $nodes;
    }

    /** Nested tree: each node gets "children". */
    public static function nested(array $nodes): array
    {
        $root = ['children' => []];
        $stack = [&$root];
        foreach ($nodes as $n) {
            $n['children'] = [];
            while (count($stack) > $n['level'] + 1) array_pop($stack);
            $parent = &$stack[count($stack) - 1];
            $parent['children'][] = $n;
            $stack[] = &$parent['children'][count($parent['children']) - 1];
            unset($parent);
        }
        return $root['children'];
    }

    public static function counts(array $nodes): array
    {
        $top = count(array_filter($nodes, static fn($n) => $n['level'] === 0));
        return ['sections' => $top, 'pages' => count($nodes), 'depth' => $nodes ? max(array_column($nodes, 'level')) + 1 : 0];
    }

    /**
     * Rows of the printed sitemap with their connector lines, like the "tree" command:
     * "rails" (one per ancestor level: true = the vertical line continues through this row),
     * "last" (no later sibling: the elbow ends here) and "parent" (has children: a stem goes down).
     */
    public static function treeRows(array $nodes): array
    {
        $count = count($nodes);
        foreach ($nodes as $i => &$n) {
            $l = $n['level'];
            $hasLaterSibling = static function (int $from, int $level) use ($nodes, $count): bool {
                for ($j = $from + 1; $j < $count; $j++) {
                    if ($nodes[$j]['level'] < $level) return false;
                    if ($nodes[$j]['level'] === $level) return true;
                }
                return false;
            };
            // Column c (< level) continues when this node's ancestor at level c has a later sibling.
            $rails = [];
            $anc = $i;
            for ($c = $l - 1; $c >= 0; $c--) {
                while ($anc >= 0 && $nodes[$anc]['level'] !== $c) $anc--;
                $rails[$c] = $anc >= 0 && $hasLaterSibling($anc, $c);
            }
            ksort($rails);
            $n['rails'] = $rails;
            $n['last'] = !$hasLaterSibling($i, $l);
            $n['parent'] = $i + 1 < $count && $nodes[$i + 1]['level'] > $l;
        }
        return $nodes;
    }

    /** "Υπηρεσίες › Web design" for every node (its ancestors' titles). */
    public static function trails(array $nodes): array
    {
        $path = [];
        foreach ($nodes as &$n) {
            $path = array_slice($path, 0, $n['level']);
            $n['trail'] = $path;
            $path[] = $n['title'];
        }
        return $nodes;
    }

    /** Document sections with a page layout (column prefix => label). */
    public const SECTIONS = ['intro' => 'Εισαγωγή', 'sitemap' => 'Sitemap', 'content' => 'Προτάσεις περιεχομένου', 'outro' => 'Κλείσιμο'];

    /** Class and style of a heading: ' sp-o-break' or a margin-top in mm (the first block of a page ignores it). */
    public static function layout($break, $space): array
    {
        if ((int) $break) return [' sp-o-break', ''];
        return $space === '' || $space === null ? ['', ''] : ['', 'margin-top:' . max(0, min(80, (int) $space)) . 'mm'];
    }

    public static function label(object $s, string $field): string
    {
        $v = trim((string) ($s->$field ?? ''));
        return $v !== '' ? $v : (self::DEFAULTS[$field] ?? '');
    }
}
