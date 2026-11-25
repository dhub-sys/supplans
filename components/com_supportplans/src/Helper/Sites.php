<?php
namespace Support\Component\Supportplans\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Συντήρηση sites: the websites of the clients with their CMS and extensions (and versions),
 * update rounds ("Joomla 6.2.0 → 6.2.1" on every site that has it) and the personalised
 * e-mail to every client whose site was updated.
 */
final class Sites
{
    public const T_SITES = '#__supportplans_sites';
    public const T_ITEMS = '#__supportplans_site_items';
    public const T_ROUNDS = '#__supportplans_update_rounds';
    public const T_UPDATES = '#__supportplans_site_updates';

    public const CMS = ['joomla' => 'Joomla', 'wordpress' => 'WordPress', 'other' => 'Άλλο'];
    public const STATUSES = ['active' => 'Ενεργό', 'inactive' => 'Ανενεργό'];
    public const ROUND_STATUSES = ['draft' => 'Πρόχειρο', 'logged' => 'Καταγράφηκε', 'sent' => 'Εστάλη άμεσα', 'applied' => 'Χωρίς ενημέρωση πελάτη'];
    public const MONTHS = [1 => 'Ιανουάριος', 'Φεβρουάριος', 'Μάρτιος', 'Απρίλιος', 'Μάιος', 'Ιούνιος', 'Ιούλιος', 'Αύγουστος', 'Σεπτέμβριος', 'Οκτώβριος', 'Νοέμβριος', 'Δεκέμβριος'];

    private static function db()
    {
        return Factory::getContainer()->get('DatabaseDriver');
    }

    /* ================================================================ values */

    /** "https://www.Example.gr/" -> "www.example.gr" */
    public static function domain(string $v): string
    {
        $v = strtolower(trim($v));
        $v = preg_replace('#^[a-z]+://#', '', $v);
        return rtrim((string) preg_replace('#[/?\#].*$#', '', $v), '.');
    }

    public static function cmsKey(string $v): string
    {
        $v = strtolower(trim($v));
        if ($v === '') return '';
        if (strpos($v, 'joom') === 0) return 'joomla';
        if ($v === 'wp' || strpos($v, 'word') === 0) return 'wordpress';
        return isset(self::CMS[$v]) ? $v : 'other';
    }

    /** Name of the CMS item of a site ("Joomla", "WordPress"), '' for other systems. */
    public static function coreName(string $cms): string
    {
        return in_array($cms, ['joomla', 'wordpress'], true) ? self::CMS[$cms] : '';
    }

    public static function isVersion(string $v): bool
    {
        return (bool) preg_match('/^v?\d+(\.\d+)*([.\-+ ]?[a-z0-9.]+)?$/i', trim($v));
    }

    /** -1 / 0 / 1; version numbers are compared as versions, anything else as text. */
    public static function vcmp(string $a, string $b): int
    {
        $a = ltrim(trim($a), 'vV');
        $b = ltrim(trim($b), 'vV');
        if (self::isVersion($a) && self::isVersion($b)) {
            return version_compare($a, $b);
        }
        return strcasecmp($a, $b) <=> 0;
    }

    /** "Name | 1.2.3" (or tab / ";" separated, or "Name 1.2.3") per line -> [[name, version], …] */
    public static function parseItems(string $text): array
    {
        $out = [];
        foreach (preg_split('/\r\n|\r|\n/', $text) as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0) continue;
            $parts = preg_split('/\s*[|\t;]\s*/u', $line);
            if (count($parts) >= 2) {
                [$name, $version] = [trim($parts[0]), trim($parts[1])];
            } elseif (preg_match('/^(.*\S)\s+v?(\d+(?:\.\d+)+\S*)$/u', $line, $m)) {
                [$name, $version] = [trim($m[1]), $m[2]];
            } else {
                [$name, $version] = [$line, ''];
            }
            if ($name !== '') {
                $out[mb_strtolower($name)] = [mb_substr($name, 0, 190), mb_substr($version, 0, 50)];
            }
        }
        return array_values($out);
    }

    /* ================================================================ sites */

    public static function site(int $id): ?object
    {
        $db = self::db();
        return $db->setQuery($db->getQuery(true)->select('*')->from($db->quoteName(self::T_SITES))->where('id=' . $id))->loadObject() ?: null;
    }

    public static function siteByDomain(string $domain): ?object
    {
        $db = self::db();
        return $db->setQuery($db->getQuery(true)->select('*')->from($db->quoteName(self::T_SITES))->where('domain=' . $db->quote(self::domain($domain))))->loadObject() ?: null;
    }

    /** Sites with their client, CMS version and number of extensions. */
    public static function sites(string $search = '', string $cms = '', int $clientId = 0): array
    {
        $db = self::db();
        $i = $db->quoteName(self::T_ITEMS);
        $q = $db->getQuery(true)
            ->select('s.*, c.name AS client_name, c.company AS client_company, c.email AS client_email, c.status AS client_status')
            ->select("(SELECT version FROM $i WHERE site_id=s.id AND is_core=1 LIMIT 1) AS cms_version")
            ->select("(SELECT COUNT(*) FROM $i WHERE site_id=s.id AND is_core=0) AS items")
            ->from($db->quoteName(self::T_SITES, 's'))
            ->join('LEFT', $db->quoteName(Notify::T_CLIENTS, 'c') . ' ON c.id=s.client_id')
            ->order('s.domain ASC');
        if ($search !== '') {
            $like = $db->quote('%' . $db->escape($search, true) . '%', false);
            $q->where("(s.domain LIKE $like OR c.name LIKE $like OR c.company LIKE $like OR c.email LIKE $like OR EXISTS (SELECT 1 FROM $i x WHERE x.site_id=s.id AND x.name LIKE $like))");
        }
        if (isset(self::CMS[$cms])) $q->where('s.cms=' . $db->quote($cms));
        if ($clientId) $q->where('s.client_id=' . $clientId);
        return $db->setQuery($q)->loadObjectList();
    }

    /** Items of a site: the CMS first, then the extensions by name. */
    public static function items(int $siteId): array
    {
        $db = self::db();
        return $db->setQuery($db->getQuery(true)->select('*')->from($db->quoteName(self::T_ITEMS))
            ->where('site_id=' . $siteId)->order('is_core DESC, name ASC'))->loadObjectList();
    }

    /** Sets the version of one item of a site (adds it when missing). */
    public static function setItem(int $siteId, string $name, string $version, bool $core = false): void
    {
        $db = self::db();
        $name = trim($name);
        if ($name === '') return;
        $id = (int) $db->setQuery($db->getQuery(true)->select('id')->from($db->quoteName(self::T_ITEMS))
            ->where('site_id=' . $siteId)->where('LOWER(name)=' . $db->quote(mb_strtolower($name))))->loadResult();
        $now = Notify::now();
        if ($id) {
            $db->setQuery($db->getQuery(true)->update($db->quoteName(self::T_ITEMS))
                ->set(['version=' . $db->quote(mb_substr(trim($version), 0, 50)), 'is_core=' . ($core ? 1 : 0), 'modified=' . $db->quote($now)])->where('id=' . $id))->execute();
            return;
        }
        $o = (object) ['site_id' => $siteId, 'name' => mb_substr($name, 0, 190), 'version' => mb_substr(trim($version), 0, 50), 'is_core' => $core ? 1 : 0, 'modified' => $now];
        $db->insertObject(self::T_ITEMS, $o);
    }

    /** CMS item of a site: "Joomla 6.2.0"; a change of CMS removes the item of the old one. */
    public static function setCore(int $siteId, string $cms, string $version): void
    {
        $db = self::db();
        $core = self::coreName($cms);
        $where = 'site_id=' . $siteId . ' AND is_core=1' . ($core !== '' ? ' AND LOWER(name)<>' . $db->quote(mb_strtolower($core)) : '');
        $db->setQuery($db->getQuery(true)->delete($db->quoteName(self::T_ITEMS))->where($where))->execute();
        if ($core !== '' && trim($version) !== '') {
            self::setItem($siteId, $core, $version, true);
        }
    }

    /** Replaces the extensions of a site with the given list ([[name, version], …]). */
    public static function replaceItems(int $siteId, array $items): void
    {
        $db = self::db();
        $keep = [];
        foreach ($items as [$name, $version]) {
            self::setItem($siteId, $name, $version);
            $keep[] = $db->quote(mb_strtolower($name));
        }
        $q = $db->getQuery(true)->delete($db->quoteName(self::T_ITEMS))->where('site_id=' . $siteId)->where('is_core=0');
        if ($keep) $q->where('LOWER(name) NOT IN (' . implode(',', $keep) . ')');
        $db->setQuery($q)->execute();
    }

    public static function deleteSite(int $id): void
    {
        $db = self::db();
        foreach ([self::T_ITEMS, self::T_UPDATES] as $t) {
            $db->setQuery($db->getQuery(true)->delete($db->quoteName($t))->where('site_id=' . $id))->execute();
        }
        $db->setQuery($db->getQuery(true)->delete($db->quoteName(self::T_SITES))->where('id=' . $id))->execute();
    }

    /** Updates applied to a site (newest first). */
    public static function history(int $siteId, int $limit = 200): array
    {
        $db = self::db();
        return $db->setQuery($db->getQuery(true)->select('*')->from($db->quoteName(self::T_UPDATES))
            ->where('site_id=' . $siteId)->order('created DESC, id ASC'), 0, $limit)->loadObjectList();
    }

    /* ================================================================ inventory */

    /**
     * Every CMS and extension in use: lower name => [name, core, sites, cms => [cms => n], versions => [v => n]].
     * The CMS first, then the extensions used by most sites.
     */
    public static function inventory(): array
    {
        $db = self::db();
        $rows = $db->setQuery($db->getQuery(true)->select('i.name, i.version, i.is_core, s.cms')
            ->from($db->quoteName(self::T_ITEMS, 'i'))->join('INNER', $db->quoteName(self::T_SITES, 's') . ' ON s.id=i.site_id')
            ->where("s.status='active'"))->loadObjectList();
        $inv = [];
        foreach ($rows as $r) {
            $k = mb_strtolower(trim($r->name));
            if (!isset($inv[$k])) $inv[$k] = ['name' => trim($r->name), 'core' => false, 'sites' => 0, 'cms' => [], 'versions' => []];
            $inv[$k]['core'] = $inv[$k]['core'] || (int) $r->is_core === 1;
            $inv[$k]['sites']++;
            $inv[$k]['cms'][$r->cms] = ($inv[$k]['cms'][$r->cms] ?? 0) + 1;
            $v = trim((string) $r->version) !== '' ? trim((string) $r->version) : '—';
            $inv[$k]['versions'][$v] = ($inv[$k]['versions'][$v] ?? 0) + 1;
        }
        foreach ($inv as &$x) {
            uksort($x['versions'], static fn($a, $b) => self::vcmp((string) $b, (string) $a));
        }
        unset($x);
        uasort($inv, static fn($a, $b) => [$b['core'], $b['sites'], mb_strtolower($a['name'])] <=> [$a['core'], $a['sites'], mb_strtolower($b['name'])]);
        return $inv;
    }

    /* ================================================================ update rounds */

    /**
     * Which site gets which update: $entries [[n => name, o => old version or '', v => new], …].
     * A site is included for an entry when it has the item, its version is older than the new
     * one (or different, for versions that are not numbers) and, when given, equal to the old one.
     * Returns the plan: [[s => site id, m => 1 (send e-mail), u => [[n, o, v, on], …]], …]
     */
    public static function plan(array $entries): array
    {
        $db = self::db();
        $byName = [];
        foreach ($entries as $e) {
            $byName[mb_strtolower($e['n'])] = $e;
        }
        if (!$byName) return [];
        $names = implode(',', array_map([$db, 'quote'], array_keys($byName)));
        $rows = $db->setQuery($db->getQuery(true)->select('i.site_id, i.name, i.version')
            ->from($db->quoteName(self::T_ITEMS, 'i'))->join('INNER', $db->quoteName(self::T_SITES, 's') . ' ON s.id=i.site_id')
            ->where("s.status='active'")->where("LOWER(i.name) IN ($names)")->order('s.domain ASC'))->loadObjectList();
        $plan = [];
        foreach ($rows as $r) {
            $e = $byName[mb_strtolower($r->name)];
            $cur = trim((string) $r->version);
            if ($e['o'] !== '' && strcasecmp($cur, $e['o']) !== 0) continue;
            if ($cur !== '' && self::vcmp($cur, $e['v']) >= 0) continue;
            $sid = (int) $r->site_id;
            $plan[$sid] = $plan[$sid] ?? ['s' => $sid, 'm' => 1, 'u' => []];
            $plan[$sid]['u'][] = ['n' => $e['n'], 'o' => $cur, 'v' => $e['v'], 'on' => 1];
        }
        // In the order of the entries (the CMS first).
        $order = array_flip(array_keys($byName));
        foreach ($plan as &$p) {
            usort($p['u'], static fn($a, $b) => $order[mb_strtolower($a['n'])] <=> $order[mb_strtolower($b['n'])]);
        }
        unset($p);
        return array_values($plan);
    }

    public static function round(int $id): ?object
    {
        $db = self::db();
        $r = $db->setQuery($db->getQuery(true)->select('*')->from($db->quoteName(self::T_ROUNDS))->where('id=' . $id))->loadObject();
        if (!$r) return null;
        $r->entriesList = json_decode((string) $r->entries, true) ?: [];
        $r->planList = json_decode((string) $r->plan, true) ?: [];
        return $r;
    }

    public static function rounds(): array
    {
        $db = self::db();
        return $db->setQuery($db->getQuery(true)->select('*')->from($db->quoteName(self::T_ROUNDS))->order('created DESC'), 0, 300)->loadObjectList();
    }

    /** Plan rows with their site and client objects (rows whose site was deleted are left out). */
    public static function planRows(object $round): array
    {
        $out = [];
        foreach ($round->planList as $i => $p) {
            $site = self::site((int) $p['s']);
            if (!$site) continue;
            $out[] = ['i' => $i, 'p' => $p, 'site' => $site, 'client' => $site->client_id ? Notify::client((int) $site->client_id) : null];
        }
        return $out;
    }

    /**
     * The message a row of a round belongs to: [client or null, groups] — all the sites of the same
     * client in the round with checked updates (sites without a client: that site alone).
     */
    public static function rowMessage(array $planRows, int $index): array
    {
        $row = null;
        foreach ($planRows as $r) {
            if ($r['i'] === $index) $row = $r;
        }
        if (!$row) return [null, []];
        $cid = $row['client'] ? (int) $row['client']->id : 0;
        $groups = [];
        foreach ($planRows as $r) {
            $same = $cid ? ($r['client'] && (int) $r['client']->id === $cid) : $r['i'] === $index;
            $updates = array_values(array_filter($r['p']['u'], static fn($u) => !empty($u['on'])));
            if ($same && $updates && ($r['i'] === $index || !empty($r['p']['m']))) $groups[] = ['site' => $r['site'], 'updates' => $updates];
        }
        return [$row['client'], $groups];
    }

    /** E-mail table of the updates: Ενημέρωση | Προηγούμενη έκδοση | Νέα έκδοση. */
    public static function updatesTable(array $updates): string
    {
        if (!$updates) return '';
        $b = Notify::brandColors();
        $font = Notify::fontStack();
        $cell = 'padding:9px 12px;font-family:' . $font . ';font-size:14px;line-height:20px;mso-line-height-rule:exactly;border-top:1px solid #eef0f4;text-align:left;vertical-align:top;';
        $head = 'padding:9px 12px;font-family:' . $font . ';font-size:13px;line-height:18px;background:' . $b['accent'] . ';color:' . $b['secondary'] . ';font-weight:bold;text-align:left;';
        $html = '<table class="sp-n-data" role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;border-collapse:separate;border-spacing:0;margin:14px 0;border:1px solid #e4e7ec;border-radius:8px;overflow:hidden;">'
            . '<tr><th style="' . $head . '">Ενημέρωση</th><th style="' . $head . '">Προηγούμενη έκδοση</th><th style="' . $head . '">Νέα έκδοση</th></tr>';
        foreach ($updates as $u) {
            $e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
            $html .= '<tr><td style="' . $cell . '"><strong>' . $e($u['n']) . '</strong></td>'
                . '<td style="' . $cell . 'color:#667085;white-space:nowrap;">' . $e(($u['o'] ?? '') !== '' ? $u['n'] . ' ' . $u['o'] : '—') . '</td>'
                . '<td style="' . $cell . 'white-space:nowrap;"><strong style="color:#067647;">' . $e($u['n'] . ' ' . $u['v']) . '</strong></td></tr>';
        }
        return $html . '</table>';
    }

    /** "Joomla 6.2.0 → Joomla 6.2.1" lines (plain text, e.g. for {updates_text}). */
    public static function updatesText(array $updates): string
    {
        return implode("\n", array_map(static fn($u) => ($u['o'] !== '' ? $u['n'] . ' ' . $u['o'] . ' → ' : '') . $u['n'] . ' ' . $u['v'], $updates));
    }

    /** "acme.gr", "acme.gr και shop.acme.gr", "a.gr, b.gr και c.gr", "5 ιστοσελίδες". */
    public static function siteList(array $domains): string
    {
        $domains = array_values($domains);
        if (count($domains) > 3) return count($domains) . ' ιστοσελίδες';
        if (count($domains) < 2) return (string) ($domains[0] ?? '');
        return implode(', ', array_slice($domains, 0, -1)) . ' και ' . end($domains);
    }

    /**
     * Sites with the same updates (same items, same new versions) become one group: one list for
     * 2022.event.gr, 2023.event.gr and 2024.event.gr. Different old versions are joined ("6.1.4 / 6.2.0").
     * Returns [[sites => [site, …], updates => […]], …] in the order of the first site.
     */
    public static function mergeGroups(array $groups): array
    {
        $out = [];
        foreach ($groups as $g) {
            $sig = array_map(static fn($u) => mb_strtolower($u['n']) . '=' . mb_strtolower($u['v']), $g['updates']);
            sort($sig);
            $k = implode('|', $sig);
            if (!isset($out[$k])) {
                $out[$k] = ['sites' => [], 'updates' => array_map(static fn($u) => $u + ['olds' => []], $g['updates'])];
            }
            $out[$k]['sites'][] = $g['site'];
            foreach ($out[$k]['updates'] as &$u) {
                foreach ($g['updates'] as $x) {
                    if (strcasecmp($x['n'], $u['n']) === 0 && (string) $x['o'] !== '' && !in_array((string) $x['o'], $u['olds'], true)) $u['olds'][] = (string) $x['o'];
                }
            }
            unset($u);
        }
        foreach ($out as &$m) {
            foreach ($m['updates'] as &$u) {
                usort($u['olds'], static fn($a, $b) => self::vcmp($a, $b));
                $u['o'] = implode(' / ', $u['olds']);
            }
            unset($u);
        }
        unset($m);
        return array_values($out);
    }

    /**
     * The {updates} block: one table per group of sites with the same updates, with a heading
     * naming its sites (no heading when the message is about one site only).
     */
    public static function updatesBlock(array $groups): string
    {
        $html = '';
        $font = Notify::fontStack();
        $merged = self::mergeGroups($groups);
        foreach ($merged as $m) {
            if (count($groups) > 1) {
                $names = array_map(static fn($s) => htmlspecialchars((string) $s->domain, ENT_QUOTES, 'UTF-8'), $m['sites']);
                $cms = array_unique(array_filter(array_map(static fn($s) => $s->cms !== 'other' ? (self::CMS[$s->cms] ?? '') : '', $m['sites'])));
                $html .= '<p style="margin:18px 0 0;font-family:' . $font . ';font-size:15px;line-height:22px;"><strong>🌐 ' . implode(', ', $names) . '</strong>'
                    . ($cms ? ' <span style="color:#667085;font-size:13px;">· ' . implode(' / ', $cms) . (count($m['sites']) > 1 ? ' · ' . count($m['sites']) . ' ιστοσελίδες με τις ίδιες ενημερώσεις' : '') . '</span>' : '') . '</p>';
            }
            $html .= self::updatesTable($m['updates']);
        }
        return $html;
    }

    /**
     * [subject, html] of the message of a client: $groups [[site => object, updates => […]], …],
     * one section per site. $client->id 0 = preview.
     */
    public static function buildMessage(array $groups, object $client, string $period = ''): array
    {
        $domains = array_map(static fn($g) => (string) $g['site']->domain, $groups);
        $cms = array_unique(array_filter(array_map(static fn($g) => self::CMS[$g['site']->cms] ?? '', $groups)));
        $vars = Notify::clientVars($client) + [
            'site' => self::siteList($domains),
            // "στην ιστοσελίδα σας acme.gr" / "στις ιστοσελίδες σας" (each one has its heading).
            'site_phrase' => count($domains) > 1 ? 'στις ιστοσελίδες σας' : 'στην ιστοσελίδα σας ' . ($domains[0] ?? ''),
            'site_of' => count($domains) > 1 ? 'των ιστοσελίδων σας' : 'της ιστοσελίδας σας',
            'cms' => implode(' / ', $cms),
            'updates_count' => (string) array_sum(array_map(static fn($g) => count($g['updates']), $groups)),
            'period' => $period !== '' ? $period : Notify::dateGr(Notify::today()),
            'date' => Notify::dateGr(Notify::today()),
        ];
        $html = Notify::renderText((string) Notify::setting('notify_siteupdate_body'), $vars, ['updates' => self::updatesBlock($groups), 'banks' => Notify::banksTable()]);
        return [Notify::subject((string) Notify::setting('notify_siteupdate_subject'), $vars), Notify::wrap($html)];
    }

    /**
     * Applies a round: the checked updates become the versions of the sites (with history).
     * $mode 'log': kept for the monthly summary of every site with e-mail on; 'send': also a
     * message now (one per client, with all their sites); 'silent': no message ever.
     * Returns [sites updated, items updated, messages queued].
     */
    public static function applyRound(int $id, string $mode = 'log'): array
    {
        $db = self::db();
        $round = self::round($id);
        if (!$round || $round->status !== 'draft') return [0, 0, 0];
        // Claim it atomically: a double click cannot apply it twice.
        $db->setQuery($db->getQuery(true)->update($db->quoteName(self::T_ROUNDS))->set("status='applying'")->where('id=' . $id)->where("status='draft'"))->execute();
        if ($db->getAffectedRows() < 1) return [0, 0, 0];
        $now = Notify::now();
        [$sites, $items, $messages] = [0, 0, 0];
        $byClient = [];
        foreach (self::planRows($round) as $row) {
            $updates = array_values(array_filter($row['p']['u'], static fn($u) => !empty($u['on'])));
            if (!$updates) continue;
            $site = $row['site'];
            $client = $row['client'];
            $inform = $mode !== 'silent' && !empty($row['p']['m']);
            $ids = [];
            foreach ($updates as $u) {
                $core = self::coreName((string) $site->cms);
                self::setItem((int) $site->id, $u['n'], $u['v'], $core !== '' && strcasecmp($core, $u['n']) === 0);
                $h = (object) ['round_id' => $id, 'site_id' => (int) $site->id, 'name' => $u['n'], 'old_version' => (string) $u['o'], 'new_version' => $u['v'],
                    'notify' => $inform ? 1 : 0, 'created' => $now];
                $db->insertObject(self::T_UPDATES, $h, 'id');
                $ids[] = (int) $h->id;
                $items++;
            }
            $db->setQuery($db->getQuery(true)->update($db->quoteName(self::T_SITES))->set('last_update=' . $db->quote($now))->where('id=' . (int) $site->id))->execute();
            $sites++;
            if ($mode === 'send' && $inform && $client && $client->status === 'active' && $site->status === 'active') {
                $cid = (int) $client->id;
                $byClient[$cid] = $byClient[$cid] ?? ['client' => $client, 'groups' => [], 'ids' => []];
                $byClient[$cid]['groups'][] = ['site' => $site, 'updates' => $updates];
                $byClient[$cid]['ids'] = array_merge($byClient[$cid]['ids'], $ids);
            }
        }
        foreach ($byClient as $cid => $c) {
            [$subject, $body] = self::buildMessage($c['groups'], $c['client'], Notify::dateGr(Notify::today()));
            if (Notify::queue(['client_id' => $cid, 'kind' => 'siteupdate', 'ref' => 'siteupdate:' . $id . ':' . $cid,
                'to_email' => $c['client']->email, 'subject' => $subject, 'body' => $body])) {
                $db->setQuery($db->getQuery(true)->update($db->quoteName(self::T_UPDATES))->set('notified=' . $db->quote($now))->where('id IN (' . implode(',', $c['ids']) . ')'))->execute();
                $messages++;
            }
        }
        $db->setQuery($db->getQuery(true)->update($db->quoteName(self::T_ROUNDS))
            ->set(['status=' . $db->quote($mode === 'send' ? 'sent' : ($mode === 'silent' ? 'applied' : 'logged')), 'applied=' . $db->quote($now), 'sites=' . $sites, 'messages=' . $messages])
            ->where('id=' . $id))->execute();
        return [$sites, $items, $messages];
    }

    /* ================================================================ monthly summary */

    /** "Νοέμβριος 2026" when both dates are in the same month, else "01/10/2026 – 25/11/2026". */
    public static function periodLabel(string $fromYmd, string $toYmd): string
    {
        if (substr($fromYmd, 0, 7) === substr($toYmd, 0, 7)) {
            return self::MONTHS[(int) substr($fromYmd, 5, 2)] . ' ' . substr($fromYmd, 0, 4);
        }
        return Notify::dateGr($fromYmd) . ' – ' . Notify::dateGr($toYmd);
    }

    /** Local date (Y-m-d) of a UTC datetime. */
    private static function localDate(string $utc): string
    {
        return (new \DateTimeImmutable($utc, new \DateTimeZone('UTC')))->setTimezone(Notify::tz())->format('Y-m-d');
    }

    /**
     * Updates not yet sent to the clients (for the monthly summary), by site:
     * site id => [site, client, updates (one per item: first old → last new), ids, from, to].
     * $untilUtc: only updates before this moment.
     */
    public static function pending(string $untilUtc = ''): array
    {
        $db = self::db();
        $q = $db->getQuery(true)->select('*')->from($db->quoteName(self::T_UPDATES))->where('notify=1')->where('notified IS NULL')->order('created ASC, id ASC');
        if ($untilUtc !== '') $q->where('created<' . $db->quote($untilUtc));
        $out = [];
        foreach ($db->setQuery($q)->loadObjectList() as $h) {
            $sid = (int) $h->site_id;
            if (!isset($out[$sid])) {
                $site = self::site($sid);
                if (!$site) continue;
                $out[$sid] = ['site' => $site, 'client' => $site->client_id ? Notify::client((int) $site->client_id) : null, 'items' => [], 'ids' => [], 'from' => '', 'to' => ''];
            }
            $k = mb_strtolower($h->name);
            $d = self::localDate((string) $h->created);
            if (!isset($out[$sid]['items'][$k])) {
                $out[$sid]['items'][$k] = ['n' => $h->name, 'o' => (string) $h->old_version, 'v' => (string) $h->new_version, 'd' => $d, 'times' => 0];
            }
            $out[$sid]['items'][$k]['v'] = (string) $h->new_version;
            $out[$sid]['items'][$k]['d'] = $d;
            $out[$sid]['items'][$k]['times']++;
            $out[$sid]['ids'][] = (int) $h->id;
            $out[$sid]['from'] = $out[$sid]['from'] ?: $d;
            $out[$sid]['to'] = $d;
        }
        foreach ($out as &$p) {
            $p['updates'] = array_values($p['items']);
            unset($p['items']);
        }
        unset($p);
        uasort($out, static fn($a, $b) => strcmp($a['site']->domain, $b['site']->domain));
        return $out;
    }

    /**
     * Pending updates by client (one message each): client id => [client, groups (site, updates),
     * ids, from, to]. Sites without a client are under key 0 (they cannot be sent).
     */
    public static function pendingByClient(string $untilUtc = ''): array
    {
        $out = [];
        foreach (self::pending($untilUtc) as $p) {
            $cid = $p['client'] ? (int) $p['client']->id : 0;
            $out[$cid] = $out[$cid] ?? ['client' => $p['client'], 'groups' => [], 'ids' => [], 'from' => '', 'to' => ''];
            $out[$cid]['groups'][] = ['site' => $p['site'], 'updates' => $p['updates'], 'ids' => $p['ids']];
            $out[$cid]['ids'] = array_merge($out[$cid]['ids'], $p['ids']);
            $out[$cid]['from'] = $out[$cid]['from'] === '' || $p['from'] < $out[$cid]['from'] ? $p['from'] : $out[$cid]['from'];
            $out[$cid]['to'] = $p['to'] > $out[$cid]['to'] ? $p['to'] : $out[$cid]['to'];
        }
        uasort($out, static fn($a, $b) => strcasecmp($a['client'] ? Notify::clientLabel($a['client']) : '~', $b['client'] ? Notify::clientLabel($b['client']) : '~'));
        return $out;
    }

    /**
     * Sends the summary of the pending updates: one message per client with all their sites
     * (only $clientIds when given). Returns the number of queued messages. Clients that are not
     * active, and inactive sites, are left pending.
     */
    public static function sendDigest(?array $clientIds = null, string $untilUtc = ''): int
    {
        $db = self::db();
        $now = Notify::now();
        $n = 0;
        foreach (self::pendingByClient($untilUtc) as $cid => $c) {
            if (!$cid || ($clientIds !== null && !in_array($cid, $clientIds, true))) continue;
            if (!$c['client'] || $c['client']->status !== 'active') continue;
            $groups = array_values(array_filter($c['groups'], static fn($g) => $g['site']->status === 'active' && $g['updates']));
            if (!$groups) continue;
            $ids = [];
            foreach ($groups as $g) {
                $ids = array_merge($ids, $g['ids']);
            }
            [$subject, $body] = self::buildMessage($groups, $c['client'], self::periodLabel($c['from'], $c['to']));
            $id = Notify::queue(['client_id' => $cid, 'kind' => 'siteupdate', 'ref' => 'sitedigest:' . $cid . ':' . gmdate('YmdHis') . ':' . bin2hex(random_bytes(3)),
                'to_email' => $c['client']->email, 'subject' => $subject, 'body' => $body]);
            if ($id && $ids) {
                $db->setQuery($db->getQuery(true)->update($db->quoteName(self::T_UPDATES))->set('notified=' . $db->quote($now))
                    ->where('id IN (' . implode(',', $ids) . ')'))->execute();
                $n++;
            }
        }
        return $n;
    }

    /** Pending updates of these clients kept out of the summary (they stay in the history of the sites). */
    public static function skipPending(array $clientIds): void
    {
        if (!$clientIds) return;
        $db = self::db();
        $sites = $db->setQuery($db->getQuery(true)->select('id')->from($db->quoteName(self::T_SITES))->where('client_id IN (' . implode(',', array_map('intval', $clientIds)) . ')'))->loadColumn();
        if (!$sites) return;
        $db->setQuery($db->getQuery(true)->update($db->quoteName(self::T_UPDATES))->set('notify=0')
            ->where('notified IS NULL')->where('site_id IN (' . implode(',', array_map('intval', $sites)) . ')'))->execute();
    }

    /** First moment (UTC) of the current local month. */
    public static function monthStartUtc(): string
    {
        return (new \DateTimeImmutable('first day of this month 00:00:00', Notify::tz()))->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s');
    }

    /** Day of the automatic summary this month (the setting, or the last day of a shorter month); 0 = off. */
    public static function digestDay(): int
    {
        $day = max(0, min(31, (int) Notify::setting('notify_sitedigest_day')));
        return $day ? min($day, (int) (new \DateTimeImmutable('now', Notify::tz()))->format('t')) : 0;
    }

    /**
     * Automatic monthly summary (from the scheduled run, at the send time): on its day, once a
     * month, every site gets the updates of the previous month(s).
     */
    public static function autoDigest(): int
    {
        $day = self::digestDay();
        if (!$day) return 0;
        $now = new \DateTimeImmutable('now', Notify::tz());
        if ((int) $now->format('j') < $day || (string) Notify::setting('notify_sitedigest_last') === $now->format('Y-m')) return 0;
        try {
            OfferHelper::saveParam('notify_sitedigest_last', $now->format('Y-m'));
        } catch (\Throwable $e) {
            return 0;
        }
        return self::sendDigest(null, self::monthStartUtc());
    }

    /* ================================================================ import / export */

    /** Header names of the Excel / CSV file (normalised, without accents). */
    public const SHEET_COLUMNS = [
        'email'   => ['email', 'e-mail', 'email πελατη'],
        'client'  => ['πελατης', 'ονοματεπωνυμο', 'client', 'name'],
        'site'    => ['site', 'domain', 'ιστοσελιδα', 'project', 'url'],
        'cms'     => ['cms'],
        'cms_ver' => ['εκδοση cms', 'cms version', 'εκδοση cms '],
        'item'    => ['extension', 'plugin', 'extension / plugin', 'στοιχειο', 'επεκταση'],
        'version' => ['εκδοση', 'version', 'εκδοση extension'],
    ];

    public static function normaliseHeader(string $h): string
    {
        $h = mb_strtolower(trim(str_replace(['*', "\xC2\xA0"], ['', ' '], $h)));
        $h = strtr($h, ['ά' => 'α', 'έ' => 'ε', 'ή' => 'η', 'ί' => 'ι', 'ϊ' => 'ι', 'ΐ' => 'ι', 'ό' => 'ο', 'ύ' => 'υ', 'ϋ' => 'υ', 'ΰ' => 'υ', 'ώ' => 'ω']);
        return trim(preg_replace('/\s+/u', ' ', $h));
    }

    /**
     * Imports rows (line => cells, the first is the header): one row per extension of a site.
     * Clients are found (or created) by e-mail, sites by domain; versions are set, nothing is removed.
     */
    public static function import(array $rows): array
    {
        $r = ['sn' => 0, 'su' => 0, 'cn' => 0, 'items' => 0, 'errors' => []];
        if (!$rows) {
            $r['errors'][] = 'Το αρχείο είναι κενό.';
            return $r;
        }
        $headerLine = array_key_first($rows);
        $map = [];
        foreach ($rows[$headerLine] as $i => $h) {
            $h = self::normaliseHeader((string) $h);
            foreach (self::SHEET_COLUMNS as $key => $names) {
                if (in_array($h, $names, true) && !isset($map[$key])) $map[$key] = $i;
            }
        }
        unset($rows[$headerLine]);
        if (!isset($map['site'])) {
            $r['errors'][] = 'Γραμμή ' . $headerLine . ': δεν βρέθηκε η στήλη «Site». Χρησιμοποιήστε το πρότυπο Excel.';
            return $r;
        }
        $db = self::db();
        $seen = [];
        foreach ($rows as $line => $cells) {
            $get = static fn(string $k) => isset($map[$k]) ? trim((string) ($cells[$map[$k]] ?? '')) : '';
            $domain = self::domain($get('site'));
            if ($domain === '' && implode('', array_map('trim', array_map('strval', $cells))) === '') continue;
            if ($domain === '') {
                $r['errors'][] = 'Γραμμή ' . $line . ': λείπει το site.';
                continue;
            }
            $site = self::siteByDomain($domain);
            $email = strtolower($get('email'));
            $clientId = $site ? (int) $site->client_id : 0;
            if ($email !== '') {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $r['errors'][] = 'Γραμμή ' . $line . ': μη έγκυρο email «' . $email . '».';
                    continue;
                }
                $client = Notify::clientByEmail($email);
                if (!$client) {
                    $now = Notify::now();
                    $o = (object) ['name' => mb_substr($get('client'), 0, 190), 'company' => '', 'email' => $email, 'status' => 'active', 'created' => $now, 'modified' => $now];
                    $db->insertObject(Notify::T_CLIENTS, $o, 'id');
                    $client = Notify::client((int) $o->id);
                    $r['cn']++;
                }
                $clientId = (int) $client->id;
            }
            if (!$site && !$clientId) {
                $r['errors'][] = 'Γραμμή ' . $line . ': νέο site χωρίς email πελάτη.';
                continue;
            }
            $cms = self::cmsKey($get('cms'));
            $now = Notify::now();
            if (!$site) {
                $o = (object) ['client_id' => $clientId, 'domain' => $domain, 'cms' => $cms ?: 'other', 'status' => 'active', 'notes' => '', 'created' => $now, 'modified' => $now];
                $db->insertObject(self::T_SITES, $o, 'id');
                $site = self::site((int) $o->id);
                $r['sn']++;
                $seen[$domain] = true;
            } elseif (!isset($seen[$domain])) {
                $seen[$domain] = true;
                $r['su']++;
            }
            $set = [];
            if ($clientId && $clientId !== (int) $site->client_id) $set[] = 'client_id=' . $clientId;
            if ($cms !== '' && $cms !== $site->cms) $set[] = 'cms=' . $db->quote($cms);
            if ($set) {
                $set[] = 'modified=' . $db->quote($now);
                $db->setQuery($db->getQuery(true)->update($db->quoteName(self::T_SITES))->set($set)->where('id=' . (int) $site->id))->execute();
                $site = self::site((int) $site->id);
            }
            if ($get('cms_ver') !== '') {
                self::setCore((int) $site->id, (string) $site->cms, $get('cms_ver'));
            }
            if ($get('item') !== '') {
                self::setItem((int) $site->id, $get('item'), $get('version'));
                $r['items']++;
            }
        }
        return $r;
    }

    /** Rows of the Excel export / template: one row per extension of every site. */
    public static function exportRows(): array
    {
        $rows = [['Email', 'Πελάτης', 'Site', 'CMS', 'Έκδοση CMS', 'Extension', 'Έκδοση']];
        foreach (self::sites() as $s) {
            $client = Notify::clientLabel((object) ['name' => $s->client_name, 'company' => $s->client_company, 'email' => '']);
            $base = [(string) $s->client_email, $client, (string) $s->domain, self::CMS[$s->cms] ?? '', (string) $s->cms_version];
            $ext = array_values(array_filter(self::items((int) $s->id), static fn($i) => !(int) $i->is_core));
            if (!$ext) {
                $rows[] = array_merge($base, ['', '']);
            }
            foreach ($ext as $i) {
                $rows[] = array_merge($base, [(string) $i->name, (string) $i->version]);
            }
        }
        return $rows;
    }
}
