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
  <nav class="sp-side-nav"><a class="sp-side-link" href="<?php echo Route::_('index.php?option=com_supportplans&view=plans&tmpl=component&spapp=1&Itemid='.(int)$spItemid.''); ?>"><span class="sp-side-icon"><svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M7 8h10M7 12h4M7 16h6"/></svg></span><span>Πακέτα</span></a><a class="sp-side-link" href="<?php echo Route::_('index.php?option=com_supportplans&view=serviceplans&tmpl=component&spapp=1&Itemid='.(int)$spItemid.''); ?>"><span class="sp-side-icon"><svg viewBox="0 0 24 24"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5z"/><path d="M4 5.5v16M8 7h8M8 11h6"/></svg></span><span>Πλάνα</span></a><a class="sp-side-link" href="<?php echo Route::_('index.php?option=com_supportplans&view=settings&tmpl=component&spapp=1&Itemid='.(int)$spItemid.''); ?>"><span class="sp-side-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.83 2.83-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .6 1.7 1.7 0 0 0-.4 1.1V21h-4v-.1A1.7 1.7 0 0 0 8.6 19.4a1.7 1.7 0 0 0-1.88.34l-.06.06-2.83-2.83.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-.6-1 1.7 1.7 0 0 0-1.1-.4H3v-4h.1A1.7 1.7 0 0 0 4.6 8.6a1.7 1.7 0 0 0-.34-1.88l-.06-.06 2.83-2.83.06.06A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-.6 1.7 1.7 0 0 0 .4-1.1V3h4v.1A1.7 1.7 0 0 0 15.4 4.6a1.7 1.7 0 0 0 1.88-.34l.06-.06 2.83 2.83-.06.06A1.7 1.7 0 0 0 19.4 9c.1.4.3.75.6 1 .3.25.7.4 1.1.4h.1v4h-.1c-.4 0-.8.15-1.1.4-.3.25-.5.6-.6 1z"/></svg></span><span>Ρυθμίσεις</span></a>
<a class="sp-side-link is-active" href="<?php echo Route::_('index.php?option=com_supportplans&view=help&tmpl=component&spapp=1&Itemid='.(int)$spItemid); ?>"><span class="sp-side-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M9.8 9a2.3 2.3 0 1 1 3.7 1.8c-.9.6-1.5 1-1.5 2.2"/><path d="M12 17h.01"/></svg></span><span>Οδηγίες</span></a></nav>
  <div class="sp-sidebar-bottom">
    <div class="sp-sidebar-help"><strong>Service Hub</strong><span>Πακέτα, πλάνα, αναφορές και σύντομα προσφορές σε ένα σημείο.</span></div>
    <a class="sp-side-joomla" href="<?php echo \Joomla\CMS\Uri\Uri::root(); ?>index.php?option=com_supportplans&amp;task=auth.logout&amp;tmpl=component&Itemid='.(int)$spItemid.'"><span>↪</span> Αποσύνδεση</a>
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
   <li>Θα αποσταλεί εξαψήφιος κωδικός OTP στο email του λογαριασμού.</li>
   <li>Εισαγάγετε τον κωδικό OTP για να ανοίξει το Support Plans workspace.</li>
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
   <li>Η δεύτερη σελίδα περιλαμβάνει το αναλυτικό δελτίο εργασιών, την Οικονομική σύνοψη και τις Τράπεζες / Λογαριασμούς.</li>
   <li>Η Οικονομική σύνοψη περιλαμβάνει συνολικό χρεώσιμο χρόνο, καθαρή αξία, Φ.Π.Α. και σύνολο με Φ.Π.Α.</li>
   <li>Το Web357 CPNB δεν εμφανίζεται στο Service Hub ούτε στην εκτύπωση/PDF.</li>
  </ul>
 </section>

 <?php else: ?>
 <section class="sp-help-card">
  <h2>Changelog</h2>

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