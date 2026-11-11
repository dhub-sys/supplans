<?php
namespace Support\Component\Supportplans\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Mail\MailHelper;

/**
 * Sending documents by email (offers, structure proposals, packages): a message, a private link
 * to view / save the document, and / or the document Pdf attached. Every send is kept per document.
 */
final class DocMail
{
    public const T_SENDS = '#__supportplans_offer_sends';

    /** Per type: table, number column, date column, project column, wording. */
    private const TYPES = [
        'offer' => ['table' => '#__supportplans_offers', 'number' => 'offer_number', 'date' => 'offer_date', 'project' => 'project_name',
            'name' => 'Προσφορά', 'subject' => 'Οικονομική προσφορά', 'the' => 'την οικονομική προσφορά', 'button' => 'Προβολή της προσφοράς',
            'view' => 'view=offers&id=', 'print' => 'spPrintOffer', 'statuses' => true],
        'structure' => ['table' => '#__supportplans_structures', 'number' => 'number', 'date' => 'proposal_date', 'project' => 'project_name',
            'name' => 'Πρόταση δομής', 'subject' => 'Πρόταση δομής ιστοσελίδας', 'the' => 'την πρόταση δομής', 'button' => 'Προβολή της πρότασης',
            'view' => 'view=structures&id=', 'print' => 'spPrintDoc', 'statuses' => true],
        'plan' => ['table' => '#__supportplans_plans', 'number' => 'form_number', 'date' => 'purchase_date', 'project' => 'supported_websites',
            'name' => 'Πακέτο', 'subject' => 'Αναφορά πακέτου υποστήριξης', 'the' => 'την αναφορά του πακέτου υποστήριξης', 'button' => 'Προβολή της αναφοράς πακέτου',
            'view' => 'view=plans&plan_id=', 'print' => 'supportPlansPrintReport', 'statuses' => false],
    ];

    public static function isType(string $type): bool
    {
        return isset(self::TYPES[$type]);
    }

    public static function find(string $type, int $id): ?object
    {
        if (!self::isType($type) || $id < 1) return null;
        $db = Factory::getContainer()->get('DatabaseDriver');
        return $db->setQuery($db->getQuery(true)->select('*')->from($db->quoteName(self::TYPES[$type]['table']))->where('id=' . $id))->loadObject() ?: null;
    }

    public static function number(string $type, object $doc): string
    {
        return trim((string) ($doc->{self::TYPES[$type]['number']} ?? '')) ?: ($type === 'plan' ? (string) ($doc->sequence_no ?? '') : '');
    }

    public static function label(string $type, object $doc): string
    {
        return trim(self::TYPES[$type]['name'] . ' ' . self::number($type, $doc) . ' – ' . $doc->customer_name, ' –');
    }

    private static function project(string $type, object $doc): string
    {
        return trim((string) ($doc->{self::TYPES[$type]['project']} ?? ''));
    }

    /** {customer}, {project}, {number}, {company}, {date}… for subject and message. */
    public static function vars(string $type, object $doc): array
    {
        $c = self::TYPES[$type];
        return OfferHelper::offerVars((object) [
            'customer_name' => (string) $doc->customer_name, 'project_name' => self::project($type, $doc),
            'offer_number' => self::number($type, $doc), 'offer_date' => (string) ($doc->{$c['date']} ?? ''),
        ], OfferHelper::brand());
    }

    /** Private link of the document (created when missing). */
    public static function link(string $type, object $doc, bool $create): string
    {
        $token = (string) ($doc->share_token ?? '');
        if (!ShareLink::isToken($token)) {
            if (!$create) return '';
            $token = ShareLink::newToken();
            $db = Factory::getContainer()->get('DatabaseDriver');
            $db->setQuery($db->getQuery(true)->update($db->quoteName(self::TYPES[$type]['table']))->set($db->quoteName('share_token') . '=' . $db->quote($token))->where('id=' . (int) $doc->id))->execute();
            $doc->share_token = $token;
        }
        return ['offer' => ShareLink::offerUrl($token), 'structure' => ShareLink::structureUrl($token), 'plan' => ShareLink::url($token)][$type];
    }

    public static function revoke(string $type, int $id): void
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $db->setQuery($db->getQuery(true)->update($db->quoteName(self::TYPES[$type]['table']))->set($db->quoteName('share_token') . "=''")->where('id=' . $id))->execute();
    }

    public static function backUrl(string $type, int $id): string
    {
        return self::TYPES[$type]['view'] . $id . '#sp-sends';
    }

    /** "a@x.gr, b@y.gr" -> list; null when one of them is not valid. */
    public static function emails(string $raw): ?array
    {
        $out = [];
        foreach (preg_split('/[\s,;]+/', trim($raw), -1, PREG_SPLIT_NO_EMPTY) as $addr) {
            if (!MailHelper::isEmailAddress($addr)) return null;
            $out[] = $addr;
        }
        return array_values(array_unique(array_slice($out, 0, 10)));
    }

    /**
     * Sends the document. $in: to, cc, subject, message, with_link; $file: the uploaded Pdf
     * ($_FILES entry) or null. Returns '' on success, else the error for the user.
     */
    public static function send(string $type, object $doc, array $in, ?array $file): string
    {
        $c = self::TYPES[$type];
        $to = self::emails((string) ($in['to'] ?? ''));
        $cc = self::emails((string) ($in['cc'] ?? ''));
        if ($to === null || !$to) return 'Συμπληρώστε έγκυρο email παραλήπτη (για περισσότερους, χωρίστε με κόμμα).';
        if ($cc === null) return 'Ένα από τα email της κοινοποίησης δεν είναι έγκυρο.';
        $number = self::number($type, $doc);
        $project = self::project($type, $doc);
        // Attached Pdf (saved from "Εκτύπωση / Pdf"): only real Pdf files, up to 15 MB.
        $attachment = null;
        if ($file && (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $tmp = (string) ($file['tmp_name'] ?? '');
            if ((int) $file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($tmp)) return 'Το αρχείο Pdf δεν ανέβηκε (ίσως είναι πολύ μεγάλο).';
            if ((int) $file['size'] > 15 * 1024 * 1024 || file_get_contents($tmp, false, null, 0, 5) !== '%PDF-') return 'Το συνημμένο πρέπει να είναι αρχείο Pdf έως 15 MB.';
            $name = trim(preg_replace('/[^\p{L}\p{N} ._()-]+/u', '', (string) pathinfo((string) $file['name'], PATHINFO_FILENAME)));
            if ($name === '') $name = trim($number . ' - ' . ($project ?: $doc->customer_name), ' -');
            $attachment = ['path' => $tmp, 'name' => mb_substr($name, 0, 120) . '.pdf'];
        }
        $withLink = !empty($in['with_link']);
        if (!$withLink && !$attachment) return 'Επιλέξτε σύνδεσμο προβολής ή/και επισυνάψτε το Pdf.';

        $brand = OfferHelper::brand();
        $vars = self::vars($type, $doc);
        $subject = trim(preg_replace('/[\r\n]+/', ' ', OfferHelper::vars((string) ($in['subject'] ?? ''), $vars)));
        if ($subject === '') $subject = trim($c['subject'] . ' ' . $number . ($project !== '' ? ' – ' . $project : ''));
        $message = str_replace("\r\n", "\n", trim((string) ($in['message'] ?? '')));
        $link = $withLink ? self::link($type, $doc, true) : '';
        $otp = $withLink && !empty($in['link_otp']);
        $days = (int) ($in['link_days'] ?? 0);
        $expires = $withLink && $days > 0 ? gmdate('Y-m-d H:i:s', time() + $days * 86400) : null;

        $esc = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
        $inner = OfferHelper::render($message, $vars);
        if ($link !== '') {
            $bg = $esc($brand['accent'] ?: '#367d22');
            $fg = $esc($brand['secondary'] ?: '#ffffff');
            $label = $esc($c['button']);
            $width = max(200, (int) ceil(mb_strlen($c['button']) * 9.5) + 48);
            // Outlook for Windows ignores padding on links: rounded VML button there, a padded
            // table cell everywhere else (the whole cell is clickable through the link).
            // Outlook ignores margins: the spacing comes from empty table rows of a fixed height.
            $spacer = static fn(int $h) => '<tr><td height="' . $h . '" style="height:' . $h . 'px;line-height:' . $h . 'px;font-size:1px;mso-line-height-rule:exactly;">&nbsp;</td></tr>';
            $inner .= '<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">' . $spacer(22) . '<tr><td align="left">'
                . '<!--[if mso]><v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="' . $esc($link) . '" style="height:44px;v-text-anchor:middle;width:' . $width . 'px;" arcsize="18%" stroke="f" fillcolor="' . $bg . '"><w:anchorlock/>'
                . '<center style="color:' . $fg . ';font-family:Arial,sans-serif;font-size:15px;font-weight:bold;">' . $label . '</center></v:roundrect><![endif]-->'
                . '<!--[if !mso]><!--><table role="presentation" cellpadding="0" cellspacing="0" border="0" style="border-collapse:separate;"><tr>'
                . '<td align="center" bgcolor="' . $bg . '" style="background:' . $bg . ';border-radius:8px;padding:12px 24px;">'
                . '<a href="' . $esc($link) . '" style="color:' . $fg . ';text-decoration:none;font-weight:bold;font-size:15px;line-height:20px;display:inline-block;">' . $label . '</a>'
                . '</td></tr></table><!--<![endif]--></td></tr>' . $spacer(14) . '</table>'
                . '<p style="margin:0 0 6px;font-size:13px;color:#6b7280;">Από τη σελίδα μπορείτε να το εκτυπώσετε ή να το αποθηκεύσετε σε Pdf. Ο σύνδεσμος είναι προσωπικός'
                . ($otp ? '· για την προβολή θα σας ζητηθεί κωδικός, που θα σταλεί σε αυτό το email' : '')
                . ($expires ? '· ισχύει έως ' . $esc(Notify::datetimeGr($expires)) : '') . '.</p>';
        }
        if ($attachment) {
            $inner .= '<p style="margin:10px 0 0;font-size:13px;color:#6b7280;">Συνημμένο: ' . $esc($attachment['name']) . '</p>';
        }
        $body = Notify::wrap($inner);
        try {
            $mailer = Notify::senderMailer($type);
            foreach ($to as $addr) $mailer->addRecipient($addr);
            // Copy to the office (Αποστολείς εγγράφων → Κρυφή κοινοποίηση).
            $bcc = trim((string) Notify::setting('doc_' . $type . '_bcc'));
            if ($bcc !== '' && filter_var($bcc, FILTER_VALIDATE_EMAIL) && !in_array($bcc, array_merge($to, $cc), true)) $mailer->addBcc($bcc);
            foreach ($cc as $addr) $mailer->addCc($addr);
            $mailer->setSubject($subject);
            $mailer->isHtml(true);
            $mailer->setBody($body);
            $mailer->AltBody = Notify::toText($body) . ($link !== '' ? "\n\n" . $link : '');
            if ($attachment) $mailer->addAttachment($attachment['path'], $attachment['name'], 'base64', 'application/pdf');
            if ($mailer->Send() !== true) throw new \RuntimeException((string) ($mailer->ErrorInfo ?? '') ?: 'Η αποστολή απέτυχε.');
        } catch (\Throwable $e) {
            return 'Το email δεν στάλθηκε: ' . $e->getMessage();
        }

        $db = Factory::getContainer()->get('DatabaseDriver');
        $app = Factory::getApplication();
        $user = Factory::getContainer()->get(\Joomla\CMS\User\UserFactoryInterface::class)->loadUserById((int) $app->getSession()->get('supportplans.auth.user_id', 0));
        $log = (object) [
            'doc_type' => $type, 'offer_id' => (int) $doc->id, 'to_email' => mb_substr(implode(', ', $to), 0, 255), 'cc' => mb_substr(implode(', ', $cc), 0, 500),
            'subject' => mb_substr($subject, 0, 255), 'attachment' => $attachment ? mb_substr($attachment['name'], 0, 255) : '',
            'with_link' => $link !== '' ? 1 : 0, 'username' => (string) ($user->username ?? ''), 'sent' => Factory::getDate()->toSql(),
        ];
        $log->link_otp = $otp ? 1 : 0;
        $log->link_expires = $expires;
        $db->insertObject(self::T_SENDS, $log);
        if ($link !== '') {
            // Protection of the link: code by email to the recipients, and expiry (this send's settings).
            $allowed = array_values(array_unique(array_merge(DocGate::emails($doc), $to, $cc)));
            $q = $db->getQuery(true)->update($db->quoteName($c['table']))
                ->set($db->quoteName('share_otp') . '=' . ($otp ? 1 : 0))
                ->set($db->quoteName('share_expires') . '=' . ($expires ? $db->quote($expires) : 'NULL'))
                ->set($db->quoteName('share_emails') . '=' . $db->quote(mb_substr(implode(',', $allowed), 0, 1000)))
                ->where('id=' . (int) $doc->id);
            $db->setQuery($q)->execute();
        }
        if ($c['statuses'] && ($doc->status ?? '') === 'draft') {
            $db->setQuery($db->getQuery(true)->update($db->quoteName($c['table']))->set($db->quoteName('status') . '=' . $db->quote('sent'))->where('id=' . (int) $doc->id))->execute();
        }
        Activity::note(self::label($type, $doc) . ' · προς ' . implode(', ', $to) . ($cc ? ' (κοιν. ' . implode(', ', $cc) . ')' : '') . ($attachment ? ' · Pdf' : '') . ($link !== '' ? ' · σύνδεσμος' . ($otp ? ' με κωδικό' : '') . ($expires ? ' έως ' . Notify::datetimeGr($expires) : '') : ''));
        $app->enqueueMessage('Στάλθηκε στο ' . implode(', ', $to) . '.');
        return '';
    }

    public static function sends(string $type, int $id): array
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        return $db->setQuery($db->getQuery(true)->select('*')->from($db->quoteName(self::T_SENDS))
            ->where('offer_id=' . $id)->where($db->quoteName('doc_type') . '=' . $db->quote($type))->order('sent DESC, id DESC'))->loadObjectList();
    }

    /** The "Αποστολή με email" dialog of a document; opened with spOpenSend(). */
    public static function dialog(string $type, object $doc, string $defaultTo = ''): string
    {
        $c = self::TYPES[$type];
        $e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
        $sends = self::sends($type, (int) $doc->id);
        $clientMails = [];
        $to = '';
        $cust = mb_strtolower(trim((string) $doc->customer_name));
        foreach (Notify::clients() as $cl) {
            if (trim((string) $cl->email) === '') continue;
            $clientMails[] = ['email' => (string) $cl->email, 'label' => trim((string) $cl->name . ((string) $cl->company !== '' ? ' · ' . $cl->company : ''))];
            if ($to === '' && $cust !== '' && in_array($cust, [mb_strtolower(trim((string) $cl->name)), mb_strtolower(trim((string) $cl->company))], true)) $to = (string) $cl->email;
        }
        if ($to === '' && $sends) $to = (string) $sends[0]->to_email;
        if ($to === '') $to = $defaultTo;
        $number = self::number($type, $doc);
        $project = self::project($type, $doc);
        $subject = trim($c['subject'] . ' ' . $number . ($project !== '' ? ' – ' . $project : ''));
        $message = "Καλησπέρα σας,\n\nσας αποστέλλουμε " . $c['the'] . ($number !== '' ? ' {number}' : '') . ($project !== '' ? ' για {project}' : '')
            . ".\nΕίμαστε στη διάθεσή σας για οποιαδήποτε διευκρίνιση.\n\nΜε εκτίμηση,\n{company}";
        $statusNote = $c['statuses'] ? 'Αν είναι «Πρόχειρη», γίνεται «Απεσταλμένη».' : '';
        // Defaults: protected, 30 days (packages: no expiry, their link follows the package).
        $otpDefault = true;
        $daysDefault = $type === 'plan' ? 0 : 30;
        ob_start(); ?>
<dialog class="sp-send" id="sp-send" aria-label="Αποστολή με email">
 <form method="post" enctype="multipart/form-data" action="<?php echo $e(OfferHelper::url('task=docmail.send', false)); ?>" id="sp-send-form">
  <div class="sp-send-head"><div><strong>Αποστολή: <?php echo $e(trim($c['name'] . ' ' . $number)); ?></strong><span>Αποστέλλεται η αποθηκευμένη μορφή του εγγράφου.</span></div><button type="button" class="sp-preview-close" onclick="document.getElementById('sp-send').close()" aria-label="Κλείσιμο">×</button></div>
  <div class="sp-send-body">
   <?php [$fromName, $fromMail] = Notify::senderOf($type); ?>
   <div class="sp-send-from">Από: <b><?php echo $e($fromName); ?></b> &lt;<?php echo $e($fromMail); ?>&gt; <a href="<?php echo $e(OfferHelper::url('view=notify&section=settings', false)); ?>#sp-doc-senders" target="_blank" rel="noopener">αλλαγή</a></div>
   <label>Προς<input type="text" name="to" value="<?php echo $e($to); ?>" list="sp-send-clients" required placeholder="pelatis@example.gr — για περισσότερους, χωρίστε με κόμμα" autocomplete="off"></label>
   <datalist id="sp-send-clients"><?php foreach ($clientMails as $cm): ?><option value="<?php echo $e($cm['email']); ?>"><?php echo $e($cm['label']); ?></option><?php endforeach; ?></datalist>
   <label>Κοινοποίηση (cc)<input type="text" name="cc" placeholder="προαιρετικό"></label>
   <label>Θέμα<input name="subject" value="<?php echo $e($subject); ?>" required></label>
   <label>Μήνυμα<textarea name="message" rows="7"><?php echo $e($message); ?></textarea><small>Μεταβλητές: {customer}, {project}, {number}, {company}. **έντονα**, γραμμές με «- » για λίστα.</small></label>
   <div class="sp-send-what">
    <label class="sp-check"><input type="checkbox" name="with_link" value="1" checked data-link-toggle> Σύνδεσμος προβολής <small>— ο πελάτης βλέπει το έγγραφο online και το εκτυπώνει / αποθηκεύει σε Pdf</small></label>
    <div class="sp-send-protect" data-link-opts>
     <label class="sp-check"><input type="checkbox" name="link_otp" value="1"<?php echo $otpDefault ? ' checked' : ''; ?>> Προστασία με κωδικό μίας χρήσης <small>— πριν το δει, ο πελάτης λαμβάνει κωδικό στο email του</small></label>
     <label class="sp-send-days">Λήξη συνδέσμου<select name="link_days"><?php foreach ([0 => 'Χωρίς λήξη', 7 => 'Σε 7 ημέρες', 14 => 'Σε 14 ημέρες', 30 => 'Σε 30 ημέρες', 60 => 'Σε 60 ημέρες', 90 => 'Σε 90 ημέρες'] as $d => $dl): ?><option value="<?php echo $d; ?>"<?php echo $d === $daysDefault ? ' selected' : ''; ?>><?php echo $dl; ?></option><?php endforeach; ?></select></label>
    </div>
    <div class="sp-send-file">
     <span>Συνημμένο Pdf <small>(προαιρετικό)</small></span>
     <div><input type="file" name="attachment" accept="application/pdf,.pdf" id="sp-send-file"><button type="button" class="btn btn-sm btn-outline-secondary" onclick="<?php echo $c['print']; ?>()">Δημιουργία Pdf…</button></div>
     <small>Πατήστε «Δημιουργία Pdf…», επιλέξτε «Αποθήκευση ως PDF» και μετά επιλέξτε εδώ το αρχείο. Έως 15 MB.</small>
    </div>
   </div>
  </div>
  <div class="sp-send-foot">
   <span><?php echo $e($statusNote); ?></span>
   <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('sp-send').close()">Άκυρο</button>
   <button class="btn btn-primary">✉ Αποστολή</button>
  </div>
  <input type="hidden" name="doc_type" value="<?php echo $e($type); ?>"><input type="hidden" name="id" value="<?php echo (int) $doc->id; ?>">
  <?php echo HTMLHelper::_('form.token'); ?>
 </form>
</dialog>
<script>
(function () {
  // window.spDocDirty(): unsaved changes of the page's form (set by the page, optional).
  window.spOpenSend = function () {
    if (typeof window.spDocDirty === 'function' && window.spDocDirty()) {
      if (!confirm('Υπάρχουν μη αποθηκευμένες αλλαγές που δεν θα σταλούν. Συνέχεια χωρίς αποθήκευση;')) return;
    }
    document.getElementById('sp-send').showModal();
  };
  var lt = document.querySelector('#sp-send [data-link-toggle]'), lo = document.querySelector('#sp-send [data-link-opts]');
  var syncLink = function () { lo.hidden = !lt.checked; };
  lt.addEventListener('change', syncLink); syncLink();
  document.getElementById('sp-send-form').addEventListener('submit', function () {
    if (typeof window.spDocClean === 'function') window.spDocClean();
    this.querySelector('button.btn-primary').disabled = true;
  });
  // Opened from the documents list ("Email"): open the dialog directly.
  if (/[?&]send=1(&|$)/.test(location.search)) document.addEventListener('DOMContentLoaded', function () { window.spOpenSend(); });
})();
</script>
<?php return ob_get_clean();
    }

    /** History card of the sends, with the document's private link (if active). */
    public static function history(string $type, object $doc, bool $withLinkManage = true): string
    {
        $e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
        $sends = self::sends($type, (int) $doc->id);
        $link = $withLinkManage ? self::link($type, $doc, false) : '';
        ob_start(); ?>
<div class="sp-card" id="sp-sends">
 <div class="sp-title-row"><h3>Αποστολές με email</h3><button type="button" class="btn btn-sm btn-outline-primary" onclick="spOpenSend()">✉ Νέα αποστολή</button></div>
 <?php if ($sends): ?>
 <div class="table-responsive"><table class="table sp-offer-table">
  <thead><tr><th>Ημερομηνία</th><th>Προς</th><th>Θέμα</th><th>Περιεχόμενο</th><th>Χρήστης</th></tr></thead>
  <tbody><?php foreach ($sends as $sd): ?><tr>
   <td class="sp-n-nowrap"><?php echo $e(Notify::datetimeGr($sd->sent)); ?></td>
   <td><?php echo $e($sd->to_email); ?><?php if ($sd->cc !== ''): ?><br><small class="text-muted">κοιν. <?php echo $e($sd->cc); ?></small><?php endif; ?></td>
   <td><?php echo $e($sd->subject); ?></td>
   <td><?php echo $e(implode(' + ', array_filter([(int) $sd->with_link ? 'Σύνδεσμος' . (!empty($sd->link_otp) ? ' 🔒' : '') : '', $sd->attachment !== '' ? 'Pdf: ' . $sd->attachment : '']))); ?></td>
   <td><?php echo $e($sd->username); ?></td>
  </tr><?php endforeach; ?></tbody>
 </table></div>
 <?php else: ?><p class="sp-foot">Δεν έχει σταλεί ακόμα από το Dashboard.</p><?php endif; ?>
 <?php if ($link !== ''): ?>
 <div class="sp-send-link">
  <span><?php echo DocGate::expired($doc) ? 'Σύνδεσμος προβολής (έληξε)' : 'Σύνδεσμος προβολής (ενεργός)'; ?></span>
  <small class="sp-send-prot"><?php echo $e(implode(' · ', array_filter([DocGate::needsCode($doc) ? '🔒 με κωδικό στο email' : 'χωρίς κωδικό', (string) ($doc->share_expires ?? '') !== '' ? 'λήξη ' . Notify::datetimeGr((string) $doc->share_expires) : 'χωρίς λήξη']))); ?></small>
  <input type="text" readonly value="<?php echo $e($link); ?>" onclick="this.select()" aria-label="Σύνδεσμος προβολής">
  <a class="btn btn-sm btn-outline-secondary" href="<?php echo $e($link); ?>" target="_blank" rel="noopener">Άνοιγμα</a>
  <form method="post" action="<?php echo $e(OfferHelper::url('task=docmail.revoke', false)); ?>" onsubmit="return confirm('Να απενεργοποιηθεί ο σύνδεσμος; Όσοι τον έχουν δεν θα βλέπουν πλέον το έγγραφο.');"><input type="hidden" name="doc_type" value="<?php echo $e($type); ?>"><input type="hidden" name="id" value="<?php echo (int) $doc->id; ?>"><?php echo HTMLHelper::_('form.token'); ?><button class="btn btn-sm btn-outline-danger">Απενεργοποίηση</button></form>
 </div>
 <?php endif; ?>
</div>
<?php return ob_get_clean();
    }
}
