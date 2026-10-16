<?php
\defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
HTMLHelper::_('stylesheet','com_supportplans/supportplans.css',['version'=>'auto','relative'=>true]);
$spParams=ComponentHelper::getParams('com_supportplans');
$spItemid=max(1,(int)$spParams->get('standalone_itemid',785));
$spCompany=trim((string)$spParams->get('brand_name','')) ?: Factory::getApplication()->get('sitename','Support Plans');
$spLogo=trim((string)$spParams->get('brand_logo',''));
$spInitials='';
foreach(preg_split('/\s+/u',$spCompany,-1,PREG_SPLIT_NO_EMPTY) as $word){$spInitials.=mb_strtoupper(mb_substr($word,0,1));if(mb_strlen($spInitials)>=2)break;}
if($spInitials==='')$spInitials='SP';
$spLogoUrl=$spLogo!==''?(preg_match('#^https?://#i',$spLogo)?$spLogo:Uri::root().ltrim($spLogo,'/')):'';
$app=Factory::getApplication();
$section=$app->input->getCmd('section','guide');
$base='index.php?option=com_supportplans&view=help&tmpl=component&spapp=1&Itemid='.(int)$spItemid;
?>
<div class="sp-saas-root">
<header class="sp-appbar"><div class="sp-brand">
<?php if($spLogoUrl!==''): ?><span class="sp-brandlogo sp-brandlogo-natural"><img src="<?php echo htmlspecialchars($spLogoUrl); ?>" alt="<?php echo htmlspecialchars($spCompany); ?>"></span><?php else: ?><span class="sp-brandmark"><?php echo htmlspecialchars($spInitials); ?></span><?php endif; ?>
<div class="sp-brand-copy"><strong><?php echo htmlspecialchars($spCompany); ?></strong><small>Service Hub</small></div>
</div><div class="sp-app-actions"><span class="sp-header-pill"><span class="sp-header-dot"></span>Workspace</span></div></header>
<main class="sp-app"><div class="sp-wrap"><div class="sp-app-layout">
<aside class="sp-sidebar">
  <div class="sp-sidebar-section-label">Διαχείριση</div>
  <nav class="sp-side-nav"><a class="sp-side-link" href="<?php echo Route::_('index.php?option=com_supportplans&view=plans&tmpl=component&spapp=1&Itemid='.(int)$spItemid.''); ?>"><span class="sp-side-icon"><svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M7 8h10M7 12h4M7 16h6"/></svg></span><span>Πακέτα</span></a><a class="sp-side-link" href="<?php echo Route::_('index.php?option=com_supportplans&view=serviceplans&tmpl=component&spapp=1&Itemid='.(int)$spItemid.''); ?>"><span class="sp-side-icon"><svg viewBox="0 0 24 24"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5z"/><path d="M4 5.5v16M8 7h8M8 11h6"/></svg></span><span>Πλάνα</span></a><a class="sp-side-link" href="<?php echo Route::_('index.php?option=com_supportplans&view=offers&tmpl=component&spapp=1&Itemid='.(int)$spItemid); ?>"><span class="sp-side-icon"><svg viewBox="0 0 24 24"><path d="M6 3h8l4 4v14H6z"/><path d="M14 3v4h4"/><path d="M14.2 11.6a2.6 2.6 0 1 0 0 3.8M9.5 12.8h3.2M9.5 14.4h3.2"/></svg></span><span>Προσφορές</span></a><a class="sp-side-link" href="<?php echo Route::_('index.php?option=com_supportplans&view=settings&tmpl=component&spapp=1&Itemid='.(int)$spItemid.''); ?>"><span class="sp-side-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.83 2.83-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .6 1.7 1.7 0 0 0-.4 1.1V21h-4v-.1A1.7 1.7 0 0 0 8.6 19.4a1.7 1.7 0 0 0-1.88.34l-.06.06-2.83-2.83.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-.6-1 1.7 1.7 0 0 0-1.1-.4H3v-4h.1A1.7 1.7 0 0 0 4.6 8.6a1.7 1.7 0 0 0-.34-1.88l-.06-.06 2.83-2.83.06.06A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-.6 1.7 1.7 0 0 0 .4-1.1V3h4v.1A1.7 1.7 0 0 0 15.4 4.6a1.7 1.7 0 0 0 1.88-.34l.06-.06 2.83 2.83-.06.06A1.7 1.7 0 0 0 19.4 9c.1.4.3.75.6 1 .3.25.7.4 1.1.4h.1v4h-.1c-.4 0-.8.15-1.1.4-.3.25-.5.6-.6 1z"/></svg></span><span>Ρυθμίσεις</span></a>
<a class="sp-side-link is-active" href="<?php echo Route::_('index.php?option=com_supportplans&view=help&tmpl=component&spapp=1&Itemid='.(int)$spItemid); ?>"><span class="sp-side-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M9.8 9a2.3 2.3 0 1 1 3.7 1.8c-.9.6-1.5 1-1.5 2.2"/><path d="M12 17h.01"/></svg></span><span>Οδηγίες</span></a></nav>
  <div class="sp-sidebar-bottom">
    <div class="sp-sidebar-help"><strong>Service Hub</strong><span>Πακέτα, πλάνα, αναφορές και προσφορές σε ένα σημείο.</span></div>
    <a class="sp-side-joomla" href="<?php echo \Joomla\CMS\Uri\Uri::root(); ?>index.php?option=com_supportplans&amp;task=auth.logout&amp;tmpl=component&amp;Itemid=<?php echo (int)$spItemid; ?>"><span>↪</span> Αποσύνδεση</a>
  </div>
</aside>
<section class="sp-workspace">
<style>
.sp-help-wrap{max-width:1100px;margin:0 auto;padding:8px 0 40px}
.sp-help-head{display:flex;justify-content:space-between;align-items:flex-end;gap:20px;margin-bottom:22px}
.sp-help-head h1{margin:0;font-size:28px;line-height:1.2;color:#111827}
.sp-help-head p{margin:6px 0 0;color:#6b7280}
.sp-help-tabs{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px}
.sp-help-tab{display:inline-flex;align-items:center;padding:9px 14px;border:1px solid #dbe2ea;border-radius:9px;background:#fff;color:#374151;text-decoration:none;font-weight:700}
.sp-help-tab.is-active{background:#2563eb;border-color:#2563eb;color:#fff}
.sp-help-card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:22px;margin-bottom:16px}
.sp-help-card h2{font-size:18px;margin:0 0 12px;color:#111827}
.sp-help-card h3{font-size:15px;margin:18px 0 8px;color:#111827}
.sp-help-card p,.sp-help-card li{font-size:14px;line-height:1.65;color:#374151}
.sp-help-card ol,.sp-help-card ul{margin:8px 0 0;padding-left:22px}
.sp-help-note{background:#f8fafc;border-left:4px solid #2563eb;padding:12px 14px;border-radius:6px;margin-top:12px}
.sp-change{display:grid;grid-template-columns:100px 1fr;gap:18px;padding:16px 0;border-bottom:1px solid #eef0f3}
.sp-change:last-child{border-bottom:0}
.sp-version{font-weight:800;color:#2563eb}
.sp-change ul{margin:0;padding-left:18px}
@media(max-width:700px){.sp-help-head{display:block}.sp-change{grid-template-columns:1fr;gap:6px}}
</style>

<div class="sp-help-wrap">
 <div class="sp-help-head">
  <div>
   <h1>Οδηγίες &amp; Changelog</h1>
   <p>Οδηγός χρήσης και ιστορικό αλλαγών του Support Plans.</p>
  </div>
 </div>

 <nav class="sp-help-tabs">
  <a class="sp-help-tab <?php echo $section !== 'changelog' ? 'is-active' : ''; ?>" href="<?php echo Route::_($base.'&view=help&section=guide'); ?>">Οδηγίες χρήσης</a>
  <a class="sp-help-tab <?php echo $section === 'changelog' ? 'is-active' : ''; ?>" href="<?php echo Route::_($base.'&view=help&section=changelog'); ?>">Changelog</a>
 </nav>

 <?php if ($section !== 'changelog'): ?>
 <section class="sp-help-card">
  <h2>1. Πρώτη ρύθμιση</h2>
  <ol>
   <li>Ανοίξτε τις <strong>Ρυθμίσεις</strong> και συμπληρώστε τα στοιχεία εταιρείας, το λογότυπο, τα στοιχεία επικοινωνίας και τα χρώματα του PDF.</li>
   <li>Ορίστε την ονομασία του πεδίου υπηρεσιών/υποστηριζόμενων websites όπως θέλετε να εμφανίζεται στα Πακέτα και στα PDF.</li>
   <li>Προσθέστε όσους τραπεζικούς λογαριασμούς χρειάζεστε. Κάθε γραμμή περιλαμβάνει Τράπεζα, IBAN και Όνομα δικαιούχου.</li>
   <li>Ελέγξτε ότι το Itemid Κέντρου Υπηρεσιών αντιστοιχεί στο Joomla menu item που χρησιμοποιείται για το Support Plans.</li>
  </ol>
 </section>

 <section class="sp-help-card">
  <h2>2. Σύνδεση στο Service Hub</h2>
  <p>Η είσοδος γίνεται από το Service Hub του Support Plans χωρίς μετάβαση στο Joomla Administrator.</p>
  <ol>
   <li>Συμπληρώστε Joomla username και password.</li>
   <li>Θα αποσταλεί εξαψήφιος κωδικός OTP στο email του λογαριασμού. Ισχύει 2 λεπτά — η μπάρα δείχνει τον χρόνο που απομένει.</li>
   <li>Πληκτρολογήστε ή επικολλήστε τον κωδικό στα 6 κουτάκια· η σύνδεση γίνεται αυτόματα μόλις συμπληρωθούν.</li>
  </ol>
  <div class="sp-help-note">Το OTP ισχύει για περιορισμένο χρόνο και χρησιμοποιείται μία φορά. Η αποσύνδεση γίνεται από το κάτω μέρος του sidebar.</div>
 </section>

 <section class="sp-help-card">
  <h2>3. Πλάνα υπηρεσιών</h2>
  <p>Τα Πλάνα είναι τα πρότυπα πάνω στα οποία δημιουργούνται τα Πακέτα πελατών. Η εγκατάσταση δεν δημιουργεί προκαθορισμένα πλάνα.</p>
  <h3>Prepaid</h3><p>Προπληρωμένος χρόνος/ποσό με ελάχιστη χρέωση και βήμα χρέωσης.</p>
  <h3>Ongoing</h3><p>Η χρέωση υπολογίζεται από τον χρεώσιμο χρόνο και την ωριαία τιμή. Στο PDF εμφανίζονται Συνολικός χρόνος και Συνολικό κόστος.</p>
  <h3>Subscription</h3><p>Σταθερή συνδρομή για συγκεκριμένη διάρκεια. Οι εργασίες μπορούν να καταγράφονται χωρίς χρέωση χρόνου/κόστους.</p>
  <p>Μπορείτε να επεξεργαστείτε ή να διαγράψετε ένα Πλάνο. Η διαγραφή εμποδίζεται όταν το Πλάνο χρησιμοποιείται ήδη από Πακέτο, ώστε να προστατεύεται το ιστορικό.</p>
 </section>

 <section class="sp-help-card">
  <h2>4. Πακέτα πελατών</h2>
  <ol>
   <li>Επιλέξτε <strong>Νέο πακέτο</strong>.</li>
   <li>Συμπληρώστε πελάτη, ημερομηνία/περίοδο και τις υπηρεσίες που καλύπτονται.</li>
   <li>Επιλέξτε το κατάλληλο Πλάνο. Τα οικονομικά και οι κανόνες χρέωσης αποθηκεύονται στο Πακέτο ως snapshot.</li>
   <li>Αποθηκεύστε το Πακέτο και στη συνέχεια καταχωρίστε τις εργασίες του.</li>
  </ol>
 </section>

 <section class="sp-help-card">
  <h2>5. Καταχώρηση εργασιών</h2>
  <p>Σε κάθε Πακέτο μπορείτε να προσθέτετε ημερομηνία/περίοδο, αίτημα ή εργασία, περιγραφή και διάρκεια. Στα χρεώσιμα πλάνα το Support Plans υπολογίζει τον χρεώσιμο χρόνο και το αντίστοιχο κόστος σύμφωνα με τους κανόνες του Πλάνου.</p>
 </section>

 <section class="sp-help-card">
  <h2>6. Αναφορά / PDF</h2>
  <p>Η αναφορά ανοίγει σε καθαρή εταιρική διάταξη και χρησιμοποιεί το native Print/PDF του browser.</p>
  <ul>
   <li>Η πρώτη σελίδα περιλαμβάνει στοιχεία πελάτη/πακέτου και τη σύνοψη του αντίστοιχου τύπου Πλάνου.</li>
   <li>Η δεύτερη σελίδα περιλαμβάνει το αναλυτικό δελτίο εργασιών. Στα Ongoing και Συνδρομητικά πλάνα ακολουθούν η Οικονομική σύνοψη και οι Τράπεζες / Λογαριασμοί. Στα προπληρωμένα δεν εμφανίζονται, γιατί έχουν ήδη εξοφληθεί.</li>
   <li>Η Οικονομική σύνοψη περιλαμβάνει συνολικό χρεώσιμο χρόνο, καθαρή αξία, Φ.Π.Α. και σύνολο με Φ.Π.Α.</li>
   <li>Το Web357 CPNB δεν εμφανίζεται στο Service Hub ούτε στην εκτύπωση/PDF.</li>
  </ul>
 </section>

 <section class="sp-help-card">
  <h2>7. Προσφορές</h2>
  <p>Οι προσφορές δημιουργούνται από <strong>templates</strong> που συνδυάζουν <strong>υπηρεσίες</strong> από μια κοινή βιβλιοθήκη. Όλα βρίσκονται στο μενού <strong>Προσφορές</strong>, στις καρτέλες Προσφορές / Templates / Υπηρεσίες.</p>
  <h3>Υπηρεσίες</h3>
  <p>Κάθε υπηρεσία έχει τίτλο ενότητας και κείμενο περιγραφής για το έγγραφο, περιγραφή και τιμή για τον πίνακα τιμών. Μπορεί να είναι καθαρά κείμενο (π.χ. «Εισαγωγή», τιμή 0, χωρίς πίνακα) ή καθαρά χρέωση (π.χ. μια άδεια λογισμικού που μπαίνει μόνο στον πίνακα).</p>
  <h3>Templates</h3>
  <p>Ένα template ορίζει τον τίτλο του εξωφύλλου, το footer, τους όρους πληρωμής, τους όρους/παραπομπές, τον Φ.Π.Α. και ποιες υπηρεσίες περιλαμβάνονται, με ποια σειρά, αν περιγράφονται και αν μπαίνουν στον πίνακα τιμών. Με το κουμπί «Δημιουργία παραδείγματος» δημιουργείται ένα έτοιμο template e-shop / φιλοξενίας / συντήρησης για να ξεκινήσετε.</p>
  <h3>Νέα προσφορά</h3>
  <ol>
   <li>Επιλέξτε <strong>Νέα προσφορά</strong>, το template, τον πελάτη και το έργο. Ο αριθμός προτείνεται αυτόματα (έτος + αύξων, π.χ. 26023).</li>
   <li>Στην προσφορά επιλέξτε για κάθε υπηρεσία «Περιγράφεται στο έγγραφο» και/ή «Στον πίνακα τιμών», αλλάξτε τιμές, εκπτώσεις, σειρά ή κείμενα, και προσθέστε ή αφαιρέστε υπηρεσίες. Τα σύνολα υπολογίζονται αμέσως.</li>
   <li>Πατήστε <strong>Αποθήκευση</strong> και μετά <strong>Εκτύπωση / Pdf</strong> → «Αποθήκευση ως PDF». Το αρχείο προτείνεται με όνομα «αριθμός - έργο».</li>
  </ol>
  <h3>Διάταξη στο Pdf</h3>
  <p>Από τη 2η σελίδα και μετά κάθε σελίδα έχει header (λογότυπο και κείμενο) και footer. Το header ενεργοποιείται ή απενεργοποιείται και αλλάζει κείμενο στα «Κείμενα εγγράφου» του template ή της προσφοράς. Σε κάθε υπηρεσία της προσφοράς, καθώς και στις ενότητες τιμών και όρων (Κείμενα εγγράφου), επιλέγετε «Συνέχεια στην ίδια σελίδα» με απόσταση σε mm από την προηγούμενη ενότητα, ή «Ξεκινά σε νέα σελίδα». Μια ενότητα που πέφτει στην αρχή σελίδας δεν παίρνει επιπλέον απόσταση. Για μακριές λίστες, βάλτε τον κέρσορα μέσα στη λίστα και πατήστε το κουμπί «Στήλες» για να εμφανιστεί σε 2 στήλες.</p>
  <h3>Λίστες σε κάρτα</h3>
  <p>Με το «＋ Λίστα» σε μια υπηρεσία προσθέτετε λίστα με τίτλο, περιγραφή, 1 ή 2 στήλες και στοιχεία ένα ανά γραμμή (μπορείτε να επικολλήσετε ολόκληρη λίστα). Στο Pdf εμφανίζεται μετά το κείμενο της υπηρεσίας ως κάρτα, στο στυλ των Τραπεζών. Μια λίστα χωρίς στοιχεία δεν αποθηκεύεται. Τα στοιχεία και η περιγραφή έχουν εργαλειοθήκη (έντονα, παραπομπές κ.λπ.). Με το «＋ Κείμενο» προσθέτετε επιπλέον κείμενο, και αλλάζετε τη σειρά λιστών και κειμένων σύροντας τη λαβή.</p>
  <h3>Μεταβλητές</h3>
  <p>Στην καρτέλα <strong>Μεταβλητές</strong> ορίζετε δικά σας πεδία, π.χ. «Intro Service» με μεταβλητή <code>{intro_service}</code>. Κάθε προσφορά αποκτά πεδίο «Intro Service» δίπλα σε Πελάτη και Έργο. Γράφετε το κείμενο μία φορά στη βιβλιοθήκη, π.χ. «…αφορά στο {intro_service}, για την εταιρεία {client}», και το Pdf κάθε προσφοράς βγαίνει με τις δικές της τιμές. Οι μεταβλητές λειτουργούν σε κείμενα, τίτλους ενοτήτων και περιγραφές του πίνακα τιμών. Οι βασικές <code>{customer}</code> (ή <code>{client}</code>), <code>{project}</code> (ή <code>{project_name}</code>), <code>{company}</code>, <code>{number}</code>, <code>{date}</code> συμπληρώνονται αυτόματα.</p>
  <h3>Προεπισκόπηση</h3>
  <p>Το κουμπί <strong>Προεπισκόπηση</strong> δείχνει το Pdf όπως θα βγει, με τις αλλαγές που δεν έχετε αποθηκεύσει ακόμα. Από εκεί μπορείτε να το ανανεώσετε, να το εκτυπώσετε ή να αποθηκεύσετε.</p>
  <div class="sp-help-note">Κάθε προσφορά κρατά δικό της αντίγραφο των κειμένων και των τιμών. Αλλαγές σε υπηρεσίες ή templates επηρεάζουν μόνο τις νέες προσφορές. Η υπογραφή/σφραγίδα στο τέλος του Pdf ορίζεται στις Ρυθμίσεις.</div>
  <h3>Μορφοποίηση κειμένων</h3>
  <p>Κάθε κείμενο έχει εργαλειοθήκη: στυλ (κείμενο / υπότιτλος), έντονα, πλάγια, υπογράμμιση, λίστες με κουκκίδες ή αρίθμηση, παραπομπή (x¹ ή Ctrl+. — προτείνει τον επόμενο αριθμό), σύνδεσμο, μεταβλητές, καθαρισμό μορφοποίησης και αναίρεση. Το κουμπί &lt;/&gt; δείχνει τον κώδικα μορφοποίησης:</p>
  <ul>
   <li><code>- στοιχείο</code> λίστα με κουκκίδες, <code>1. στοιχείο</code> αριθμημένη λίστα (10+ στοιχεία σε δύο στήλες).</li>
   <li><code>## Τίτλος</code> υπότιτλος, <code>**έντονα**</code>, <code>*πλάγια*</code>, <code>__υπογράμμιση__</code>, <code>^1</code> εκθέτης παραπομπής, <code>[κείμενο](https://…)</code> σύνδεσμος.</li>
   <li>Μεταβλητές: <code>{customer}</code>, <code>{project}</code>, <code>{company}</code>, <code>{number}</code>, <code>{date}</code>.</li>
  </ul>
 </section>

 <?php else: ?>
 <section class="sp-help-card">
  <h2>Changelog</h2>

  <div class="sp-change"><div class="sp-version">v1.6.4</div><div><ul>
<li>Διορθώθηκε η αναβάθμιση από παλιές εκδόσεις (σφάλμα «Unknown column 'billing_mode'»). Όλες οι στήλες που λείπουν προστίθενται πλέον με ασφάλεια, ανεξάρτητα από την έκδοση από την οποία γίνεται η αναβάθμιση.</li>
<li>Τα παλιά πακέτα παίρνουν αύξοντα αριθμό και συνδέονται με το αντίστοιχο πλάνο, και η ημερομηνία εργασιών γίνεται ελεύθερο κείμενο.</li>
</ul></div></div>
  <div class="sp-change"><div class="sp-version">v1.6.3</div><div><ul>
<li>Header Pdf χωρίς κεφαλαία: όταν δεν έχετε ορίσει δικό σας κείμενο, εμφανίζεται «Οικονομική προσφορά + αριθμός · έργο».</li>
<li>Ο τίτλος της προσφοράς στο εξώφυλλο είναι πλέον έντονος.</li>
</ul></div></div>
  <div class="sp-change"><div class="sp-version">v1.6.2</div><div><ul>
<li>Οι λίστες σε κάρτα εμφανίζονται striped, με διακριτικό ανοιχτό γκρι σε κάθε δεύτερη γραμμή.</li>
<li>Λίγο μεγαλύτερο κενό ανάμεσα σε λίστες και επιπλέον κείμενα της ίδιας υπηρεσίας. Δεν προστίθεται κενό στην αρχή σελίδας.</li>
</ul></div></div>
  <div class="sp-change"><div class="sp-version">v1.6.1</div><div><ul>
<li>Νέο κουμπί «＋ Κείμενο» δίπλα στο «＋ Λίστα»: επιπλέον μπλοκ κειμένου με πλήρη εργαλειοθήκη. Λίστες και κείμενα εμφανίζονται στο Pdf με τη σειρά που ορίζετε (drag &amp; drop από τη λαβή).</li>
<li>Νέο πεδίο «Περιγραφή λίστας», που εμφανίζεται μέσα στην κάρτα κάτω από τον τίτλο.</li>
<li>Εργαλειοθήκη στα στοιχεία και στην περιγραφή της λίστας: έντονα, πλάγια, υπογράμμιση, παραπομπή (x¹), σύνδεσμος, μεταβλητές.</li>
</ul></div></div>
  <div class="sp-change"><div class="sp-version">v1.6.0</div><div><ul>
<li>Νέο κουμπί «＋ Λίστα» σε κάθε υπηρεσία (στη βιβλιοθήκη και στην προσφορά): τίτλος, επιλογή 1 ή 2 στηλών και στοιχεία ένα ανά γραμμή.</li>
<li>Στο Pdf η λίστα εμφανίζεται μετά το κείμενο της υπηρεσίας ως κάρτα, όπως οι Τράπεζες / Λογαριασμοί. Στις 2 στήλες τα στοιχεία μοιράζονται κατά στήλη.</li>
<li>Οι λίστες της βιβλιοθήκης αντιγράφονται στις νέες προσφορές. Στα στοιχεία και στον τίτλο ισχύουν **έντονα** και μεταβλητές.</li>
</ul></div></div>
  <div class="sp-change"><div class="sp-version">v1.5.4</div><div><ul>
<li>Header Pdf: ρυθμιζόμενο μέγεθος λογοτύπου (px) και απόσταση του περιεχομένου από το header (mm), στο template και σε κάθε προσφορά. Η σελιδοποίηση υπολογίζει αυτόματα τον διαθέσιμο χώρο.</li>
</ul></div></div>
  <div class="sp-change"><div class="sp-version">v1.5.3</div><div><ul>
<li>Ο τίτλος της «Οικονομικής σύνοψης» εμφανίζεται με το «Χρώμα κειμένου πάνω στο branding» (π.χ. λευκό), όπως ο τίτλος των Τραπεζών, και στις προσφορές και στα Pdf των πακέτων.</li>
<li>Pdf πακέτων: το αναλυτικό δελτίο εργασιών εμφανίζεται ως κάρτα, όπως οι Τράπεζες / Λογαριασμοί.</li>
</ul></div></div>
  <div class="sp-change"><div class="sp-version">v1.5.2</div><div><ul>
<li>Οι ενότητες «Αξία Έργου – Τρόπος Πληρωμής» και «Όροι Προσφοράς» έχουν πλέον κι αυτές επιλογή «Συνέχεια στην ίδια σελίδα» (με απόσταση σε mm) ή «Ξεκινά σε νέα σελίδα», στο template και σε κάθε προσφορά.</li>
</ul></div></div>
  <div class="sp-change"><div class="sp-version">v1.5.1</div><div><ul>
<li>Ο πίνακας τιμών της προσφοράς έχει πλέον τη μορφή της «Οικονομικής σύνοψης» των αναφορών, με στήλες Περιγραφή, Αξία, Φ.Π.Α., Σύνολο (χωρίς στήλη έκπτωσης) και τονισμένη γραμμή συνόλου.</li>
<li>Οι τράπεζες εμφανίζονται στην ίδια μορφή κάρτας «Τράπεζες / Λογαριασμοί».</li>
<li>Γραμματοσειρά Geom σε όλο το Pdf της προσφοράς.</li>
<li>Οι γραμμές Αρ. Προσφοράς / Ημερομηνία / Πελάτης στο εξώφυλλο καλύπτουν όλο το πλάτος.</li>
</ul></div></div>
  <div class="sp-change"><div class="sp-version">v1.5.0</div><div><ul>
<li>Header στο Pdf της προσφοράς σε κάθε σελίδα εκτός από το εξώφυλλο: λογότυπο αριστερά, κείμενο δεξιά και λεπτή γραμμή.</li>
<li>Ρυθμίζεται ανά template και ανά προσφορά (Κείμενα εγγράφου): ενεργό/ανενεργό και δικό σας κείμενο με μεταβλητές. Αν μείνει κενό, εμφανίζεται «Οικονομική προσφορά + αριθμός · έργο».</li>
</ul></div></div>
  <div class="sp-change"><div class="sp-version">v1.4.0</div><div><ul>
<li>Για κάθε υπηρεσία της προσφοράς ορίζετε αν στο Pdf <strong>συνεχίζει στην ίδια σελίδα</strong> (με απόσταση σε mm από την προηγούμενη ενότητα) ή <strong>ξεκινά σε νέα σελίδα</strong>. Οι προεπιλογές ορίζονται στη βιβλιοθήκη Υπηρεσιών.</li>
<li>Νέο κουμπί «Στήλες» στον επεξεργαστή: με τον κέρσορα μέσα σε μια λίστα, τη χωρίζει σε 2 στήλες στο Pdf (ή την επαναφέρει σε 1). Οι λίστες 2 στηλών μπορούν πλέον να συνεχίζουν και στην επόμενη σελίδα.</li>
</ul></div></div>
  <div class="sp-change"><div class="sp-version">v1.3.2</div><div><ul>
<li>Οι οδηγίες της καρτέλας Μεταβλητές μεταφέρθηκαν σε εικονίδιο πληροφοριών (ⓘ) δίπλα στον τίτλο: εμφανίζονται με hover ή με πάτημα και κλείνουν με Esc ή κλικ έξω.</li>
</ul></div></div>
  <div class="sp-change"><div class="sp-version">v1.3.1</div><div><ul>
<li>Αλλαγή σειράς με drag &amp; drop από λαβή ⋮⋮ (ποντίκι, αφή, και βελάκια πληκτρολογίου πάνω στη λαβή) στις Μεταβλητές, στις υπηρεσίες της προσφοράς και στις υπηρεσίες του template. Η σελίδα κυλά αυτόματα όταν σύρετε κοντά στην άκρη.</li>
<li>Μικρό τετράγωνο κουμπί «+» για νέα μεταβλητή, και εικονίδιο κάδου για αφαίρεση.</li>
<li>Πιο συμπαγή κουμπιά σε όλο το Service Hub (μικρότερο ύψος, γράμματα και βάρος).</li>
<li>Ουδέτερα placeholders στη «Νέα προσφορά» (Επωνυμία, Project).</li>
</ul></div></div>
  <div class="sp-change"><div class="sp-version">v1.3.0</div><div><ul>
<li>Νέα καρτέλα <strong>Μεταβλητές</strong> στις Προσφορές: ορίζετε μία φορά μια μεταβλητή (π.χ. «Intro Service» → <code>{intro_service}</code>) και γίνεται πεδίο σε κάθε προσφορά, δίπλα σε Πελάτη και Έργο.</li>
<li>Η καρτέλα εντοπίζει μεταβλητές που έχετε γράψει στα κείμενα αλλά δεν έχετε ορίσει, και τις προσθέτει με ένα κλικ.</li>
<li>Τα πεδία εμφανίζονται ήδη στη «Νέα προσφορά», με προαιρετική προεπιλεγμένη τιμή και τύπο (μία ή πολλές γραμμές).</li>
<li>Νέα συνώνυμα: <code>{client}</code> = <code>{customer}</code> και <code>{project_name}</code> = <code>{project}</code>.</li>
<li>Οι μεταβλητές ανά template της έκδοσης 1.2.0 αντικαταστάθηκαν από τα κοινά πεδία· τιμές που έχουν ήδη αποθηκευτεί σε προσφορές διατηρούνται.</li>
</ul></div></div>
  <div class="sp-change"><div class="sp-version">v1.2.1</div><div><ul>
<li>Επεξεργασία καταχωρημένων εργασιών στα Πακέτα (εικονίδιο μολυβιού). Ο χρεώσιμος χρόνος και το κόστος υπολογίζονται ξανά με τους κανόνες του πλάνου.</li>
<li>Η επεξεργασία και η διαγραφή εργασίας είναι πλέον δύο μικρά εικονίδια, και το κείμενο του αιτήματος είναι μικρότερο.</li>
<li>Το Pdf των προπληρωμένων πακέτων δεν περιλαμβάνει πλέον Οικονομική σύνοψη και Τράπεζες / Λογαριασμούς.</li>
</ul></div></div>
  <div class="sp-change"><div class="sp-version">v1.2.0</div><div><ul>
<li>Νέα οθόνη OTP: 6 κουτάκια (ένα ανά ψηφίο), αυτόματη μετάβαση, επικόλληση/αυτόματη συμπλήρωση κωδικού και αυτόματη υποβολή.</li>
<li>Ο κωδικός OTP ισχύει πλέον 2 λεπτά, με μπάρα αντίστροφης μέτρησης. Το «Αποστολή νέου κωδικού» ενεργοποιείται μετά από 60″.</li>
<li>Δικές σας μεταβλητές σε templates (με προεπιλεγμένη τιμή) και προσφορές, π.χ. <code>{πακέτο}</code>, και στη λίστα «Μεταβλητή…» του επεξεργαστή.</li>
<li>Οι μεταβλητές ισχύουν και σε τίτλους ενοτήτων, περιγραφές του πίνακα τιμών, υπότιτλο και τίτλους ενοτήτων τιμών/όρων.</li>
<li>Προεπισκόπηση του Pdf μέσα στη σελίδα της προσφοράς, με τις αλλαγές πριν την αποθήκευση, και εκτύπωση από την προεπισκόπηση.</li>
</ul></div></div>
  <div class="sp-change"><div class="sp-version">v1.1.2</div><div><ul>
<li>Οπτικός επεξεργαστής κειμένων στις Προσφορές (υπηρεσίες, templates, προσφορές) με εργαλειοθήκη: στυλ/υπότιτλοι, έντονα, πλάγια, υπογράμμιση, λίστες, παραπομπές, σύνδεσμοι, μεταβλητές, αναίρεση και προβολή κώδικα.</li>
<li>Νέα μορφοποίηση στο Pdf: πλάγια (*κείμενο*) και υπογράμμιση (__κείμενο__).</li>
<li>Η επικόλληση γίνεται ως καθαρό κείμενο, ώστε να μη μεταφέρεται μορφοποίηση από Word ή ιστοσελίδες.</li>
</ul></div></div>
  <div class="sp-change"><div class="sp-version">v1.1.1</div><div><ul>
<li>Διορθώθηκε η εγκατάσταση του launcher σε Joomla 6: το «Support Plans» εμφανίζεται ξανά στο αριστερό menu του administrator και ανοίγει το Service Hub σε νέο tab.</li>
<li>Και το «Components → Support Plans» ανοίγει πλέον το Service Hub σε νέο tab.</li>
<li>Ο launcher χρησιμοποιεί το Standalone Menu Item ID των Ρυθμίσεων αντί για σταθερό 785.</li>
<li>Διορθώθηκε το ανέβασμα λογοτύπου / login icon / υπογραφής σε Joomla 6.</li>
</ul></div></div>
  <div class="sp-change"><div class="sp-version">v1.1.0</div><div><ul>
<li>Νέα ενότητα <strong>Προσφορές</strong> με βιβλιοθήκη υπηρεσιών, templates προσφορών και Pdf σε μορφή οικονομικής προσφοράς.</li>
<li>Επιλογή ανά υπηρεσία αν περιγράφεται στο έγγραφο και αν μπαίνει στον πίνακα τιμών, με εκπτώσεις και αυτόματο υπολογισμό Φ.Π.Α./συνόλων.</li>
<li>Αυτόματη σελιδοποίηση A4 με εξώφυλλο, αριθμημένες ενότητες, footer και αρίθμηση σελίδων.</li>
<li>Νέα ρύθμιση: υπογραφή / σφραγίδα προσφορών.</li>
<li>Διορθώθηκε το Itemid στον σύνδεσμο Αποσύνδεσης του sidebar.</li>
</ul></div></div>
  <div class="sp-change"><div class="sp-version">v1.0.85</div><div><ul>
<li>Διορθώθηκε πλήρως το sidebar της σελίδας Οδηγίες.</li>
<li>Οι επιλογές Πακέτα, Πλάνα, Ρυθμίσεις και Οδηγίες παραμένουν όλες ορατές όταν είναι ενεργές οι Οδηγίες.</li>
<li>Η σελίδα Οδηγίες χρησιμοποιεί πλέον αυτούσιο το ίδιο sidebar markup με τη σελίδα Πακέτα.</li>
</ul></div></div>
<div class="sp-change"><div class="sp-version">v1.0.84</div><div><ul>
<li>Η ονομασία του περιβάλλοντος άλλαξε σε «Service Hub».</li>
<li>Διορθώθηκε το icon των Ρυθμίσεων όταν είναι ενεργή η σελίδα Οδηγίες, χρησιμοποιώντας ακριβώς το ίδιο SVG/menu markup με τις υπόλοιπες σελίδες.</li>
</ul></div></div>
<div class="sp-change"><div class="sp-version">v1.0.83</div><div><ul>
<li>Οι Οδηγίες ενσωματώθηκαν πλήρως στο Service Hub με το ίδιο header και αριστερό menu.</li>
<li>Διορθώθηκε το routing ώστε να μην εμφανίζεται το menu του δημόσιου site.</li>
<li>Η ονομασία «standalone» αντικαταστάθηκε στο UI από «Service Hub», ώστε να καλύπτει και τη μελλοντική σύνταξη προσφορών.</li>
</ul></div></div>
<div class="sp-change"><div class="sp-version">v1.0.82</div><div><ul>
   <li>Προστέθηκε ενότητα Οδηγιών χρήσης μέσα στο standalone.</li>
   <li>Προστέθηκε ενότητα Changelog μέσα στο standalone.</li>
  </ul></div></div>

  <div class="sp-change"><div class="sp-version">v1.0.81</div><div><ul>
   <li>Πλήρης απόκρυψη του Web357 CPNB από όλες τις standalone σελίδες και το PDF/Print.</li>
  </ul></div></div>

  <div class="sp-change"><div class="sp-version">v1.0.80</div><div><ul>
   <li>Διορθώθηκε η τυπογραφία της Οικονομικής σύνοψης στο πραγματικό PDF markup.</li>
   <li>Ο τίτλος έγινε 19px, οι οικονομικές γραμμές 16px και το τελικό σύνολο 17px bold.</li>
  </ul></div></div>

  <div class="sp-change"><div class="sp-version">v1.0.76–1.0.79</div><div><ul>
   <li>Βελτιώσεις PDF: δεξιά στοίχιση Διάρκειας/Κόστους, zebra rows και ισχυρότερη οικονομική σύνοψη.</li>
  </ul></div></div>

  <div class="sp-change"><div class="sp-version">v1.0.67–1.0.75</div><div><ul>
   <li>Προστέθηκαν Οικονομική σύνοψη, Τράπεζες / Λογαριασμοί, δυναμικές τραπεζικές γραμμές και βελτιώσεις διάταξης PDF.</li>
   <li>Καταργήθηκε οριστικά η αυτόματη δημιουργία προκαθορισμένων Πλάνων.</li>
   <li>Προστέθηκε ασφαλής διαγραφή Πλάνων όταν δεν χρησιμοποιούνται από Πακέτα.</li>
  </ul></div></div>

  <div class="sp-change"><div class="sp-version">v1.0.51–1.0.66</div><div><ul>
   <li>Πλήρες Service Hub για Πακέτα, Πλάνα και Ρυθμίσεις.</li>
   <li>Βελτιώσεις αναφορών/PDF, Geom typography και διαφορετικές συνόψεις ανά billing mode.</li>
  </ul></div></div>

  <div class="sp-change"><div class="sp-version">v1.0.42–1.0.50</div><div><ul>
   <li>Ανεξάρτητη σύνδεση Support Plans με Joomla username/password και εξαψήφιο Email OTP.</li>
   <li>Βελτιώσεις routing/Itemid του Κέντρου Υπηρεσιών, branding και PDF watermark.</li>
  </ul></div></div>

  <div class="sp-change"><div class="sp-version">v1.0.25–1.0.41</div><div><ul>
   <li>Joomla Administrator launcher, responsive SaaS UI και βασική υποδομή PDF/branding.</li>
  </ul></div></div>
 </section>
 <?php endif; ?>
</div>

</section></div></div></main></div>