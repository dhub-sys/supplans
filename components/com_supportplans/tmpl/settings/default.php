<?php
\defined('_JEXEC') or die;
$spItemid=max(1,(int)\Joomla\CMS\Component\ComponentHelper::getParams('com_supportplans')->get('standalone_itemid',785));

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
HTMLHelper::_('stylesheet','com_supportplans/supportplans.css',['version'=>'auto','relative'=>true]);
$p=$this->params;
?>
<?php
$spParams=$this->params ?? \Joomla\CMS\Component\ComponentHelper::getParams('com_supportplans');
$spCompany=trim((string)$spParams->get('brand_name','')) ?: \Joomla\CMS\Factory::getApplication()->get('sitename','Support Plans');
$spLogo=trim((string)$spParams->get('brand_logo',''));
$spAccent=trim((string)$spParams->get('accent_color','#367d22'));
$spSecondary=trim((string)$spParams->get('secondary_color','#245719'));
if(!preg_match('/^#[0-9a-fA-F]{6}$/',$spAccent))$spAccent='#367d22';
if(!preg_match('/^#[0-9a-fA-F]{6}$/',$spSecondary))$spSecondary='#245719';
$spInitials='';
foreach(preg_split('/\s+/u',$spCompany,-1,PREG_SPLIT_NO_EMPTY) as $word){$spInitials.=mb_strtoupper(mb_substr($word,0,1));if(mb_strlen($spInitials)>=2)break;}
if($spInitials==='')$spInitials='SP';
$spLogoUrl='';
if($spLogo!==''){$spLogoUrl=preg_match('#^https?://#i',$spLogo)?$spLogo:\Joomla\CMS\Uri\Uri::root().ltrim($spLogo,'/');}
?>
<div class="sp-saas-root">
<header class="sp-appbar">
 <div class="sp-brand">
  <?php if($spLogoUrl!==''): ?><span class="sp-brandlogo sp-brandlogo-natural"><img src="<?php echo htmlspecialchars($spLogoUrl); ?>" alt="<?php echo htmlspecialchars($spCompany); ?>"></span><?php else: ?><span class="sp-brandmark"><?php echo htmlspecialchars($spInitials); ?></span><?php endif; ?>
  <div class="sp-brand-copy"><strong><?php echo htmlspecialchars($spCompany); ?></strong><small>Service Hub</small></div>
 </div>
 <div class="sp-app-actions"><span class="sp-header-pill"><span class="sp-header-dot"></span>Workspace</span></div>
</header>
<main class="sp-app"><div class="sp-wrap"><div class="sp-app-layout"><aside class="sp-sidebar">
  <div class="sp-sidebar-section-label">Διαχείριση</div>
  <nav class="sp-side-nav"><a class="sp-side-link" href="<?php echo Route::_('index.php?option=com_supportplans&view=plans&tmpl=component&spapp=1&Itemid='.(int)$spItemid.''); ?>"><span class="sp-side-icon"><svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M7 8h10M7 12h4M7 16h6"/></svg></span><span>Πακέτα</span></a><a class="sp-side-link" href="<?php echo Route::_('index.php?option=com_supportplans&view=serviceplans&tmpl=component&spapp=1&Itemid='.(int)$spItemid.''); ?>"><span class="sp-side-icon"><svg viewBox="0 0 24 24"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5z"/><path d="M4 5.5v16M8 7h8M8 11h6"/></svg></span><span>Πλάνα</span></a><a class="sp-side-link is-active" href="<?php echo Route::_('index.php?option=com_supportplans&view=settings&tmpl=component&spapp=1&Itemid='.(int)$spItemid.''); ?>"><span class="sp-side-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.83 2.83-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .6 1.7 1.7 0 0 0-.4 1.1V21h-4v-.1A1.7 1.7 0 0 0 8.6 19.4a1.7 1.7 0 0 0-1.88.34l-.06.06-2.83-2.83.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-.6-1 1.7 1.7 0 0 0-1.1-.4H3v-4h.1A1.7 1.7 0 0 0 4.6 8.6a1.7 1.7 0 0 0-.34-1.88l-.06-.06 2.83-2.83.06.06A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-.6 1.7 1.7 0 0 0 .4-1.1V3h4v.1A1.7 1.7 0 0 0 15.4 4.6a1.7 1.7 0 0 0 1.88-.34l.06-.06 2.83 2.83-.06.06A1.7 1.7 0 0 0 19.4 9c.1.4.3.75.6 1 .3.25.7.4 1.1.4h.1v4h-.1c-.4 0-.8.15-1.1.4-.3.25-.5.6-.6 1z"/></svg></span><span>Ρυθμίσεις</span></a>
<a class="sp-side-link" href="<?php echo Route::_('index.php?option=com_supportplans&view=help&tmpl=component&spapp=1&Itemid='.(int)$spItemid); ?>"><span class="sp-side-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M9.8 9a2.3 2.3 0 1 1 3.7 1.8c-.9.6-1.5 1-1.5 2.2"/><path d="M12 17h.01"/></svg></span><span>Οδηγίες</span></a></nav>
  <div class="sp-sidebar-bottom">
    <div class="sp-sidebar-help"><strong>Service Hub</strong><span>Πακέτα, πλάνα, αναφορές και σύντομα προσφορές σε ένα σημείο.</span></div>
    <a class="sp-side-joomla" href="<?php echo \Joomla\CMS\Uri\Uri::root(); ?>index.php?option=com_supportplans&amp;task=auth.logout&amp;tmpl=component&Itemid='.(int)$spItemid.'"><span>↪</span> Αποσύνδεση</a>
  </div>
</aside>
<section class="sp-workspace">
 <section class="sp-dashboard-head"><div><span class="sp-eyebrow">Εμφάνιση και στοιχεία</span><h1>Στοιχεία επιχείρησης</h1><p>Εταιρικά στοιχεία και χρώματα για την προσωποποίηση των Pdf αναφορών.</p></div></section><form action="<?php echo Route::_('index.php?option=com_supportplans&view=settings&tmpl=component&Itemid='.(int)$spItemid.''); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="adminForm">
  <div class="sp-card">
   <h2>Στοιχεία επιχείρησης και Pdf</h2>
   <p class="text-muted">Τα στοιχεία αυτά χρησιμοποιούνται στο report / Pdf. Αν η επωνυμία μείνει κενή, χρησιμοποιείται το Site Name του Joomla.</p>
   <div class="sp-grid">
    <label>Επωνυμία<input class="form-control" name="brand_name" value="<?php echo htmlspecialchars((string)$p->get('brand_name','')); ?>"></label>
    <label class="sp-wide sp-upload-setting"><span class="sp-label-tooltip" tabindex="0" data-tooltip="Επίλεξε εικόνα λογοτύπου. Μπορείς επίσης να επικολλήσεις URL ή σχετική διαδρομή.">Λογότυπο</span>
     <div class="sp-media-row">
      <input class="form-control" id="brand_logo" name="brand_logo" value="<?php echo htmlspecialchars((string)$p->get('brand_logo','')); ?>" placeholder="images/supportplans/logo.png ή URL">
      <input type="file" id="brand_logo_upload" name="brand_logo_upload" accept="image/png,image/jpeg,image/webp,image/gif,image/svg+xml" hidden>
      <button type="button" class="btn btn-outline-secondary sp-media-button" id="brand_logo_pick">Upload logo</button>
     </div>
     <span class="sp-upload-name" id="brand_logo_filename" aria-live="polite"></span>
    </label>
    <label class="sp-wide">Διεύθυνση<textarea class="form-control" name="brand_address" rows="3"><?php echo htmlspecialchars((string)$p->get('brand_address','')); ?></textarea></label>
    <label>Τηλέφωνο<input class="form-control" name="brand_phone" value="<?php echo htmlspecialchars((string)$p->get('brand_phone','')); ?>"></label>
    <label>Email<input class="form-control" type="email" name="brand_email" value="<?php echo htmlspecialchars((string)$p->get('brand_email','')); ?>"></label>
    <label>Website<input class="form-control" name="brand_website" value="<?php echo htmlspecialchars((string)$p->get('brand_website','')); ?>"></label>
    <label>Χρώμα branding<input class="form-control form-control-color" type="color" name="accent_color" value="<?php echo htmlspecialchars((string)$p->get('accent_color','#367d22')); ?>"></label>
    <label><span class="sp-label-tooltip" tabindex="0" data-tooltip="Ορίζει το χρώμα των γραμμάτων και στοιχείων που εμφανίζονται πάνω στο βασικό χρώμα branding στο Pdf.">Χρώμα κειμένου πάνω στο branding</span><input class="form-control form-control-color" type="color" name="secondary_color" value="<?php echo htmlspecialchars((string)$p->get('secondary_color','#245719')); ?>"></label>
    <label class="sp-wide sp-upload-setting"><span class="sp-label-tooltip" tabindex="0" data-tooltip="Προαιρετική εικόνα που εμφανίζεται διακριτικά στο φόντο κάθε σελίδας του Pdf.">Watermark Pdf</span>
     <div class="sp-media-row">
      <input class="form-control" id="pdf_watermark" name="pdf_watermark" value="<?php echo htmlspecialchars((string)$p->get('pdf_watermark','')); ?>" placeholder="images/supportplans/watermark.png ή URL">
      <input type="file" id="pdf_watermark_upload" name="pdf_watermark_upload" accept="image/png,image/jpeg,image/webp,image/gif,image/svg+xml" hidden>
      <button type="button" class="btn btn-outline-secondary sp-media-button" id="pdf_watermark_pick">Upload watermark</button>
     </div>
     <span class="sp-upload-name" id="pdf_watermark_filename" aria-live="polite"><?php
$savedWatermark=trim((string)$p->get('pdf_watermark',''));
echo $savedWatermark!=='' ? '✓ Αποθηκευμένο: '.htmlspecialchars($savedWatermark,ENT_QUOTES,'UTF-8') : '';
?></span>
    </label>
    <label class="sp-wide sp-opacity-setting">
     <span class="sp-label-tooltip" tabindex="0" data-tooltip="Ένταση του watermark στο PDF. 0% = αόρατο, 100% = πλήρως αδιαφανές.">Watermark opacity</span>
     <div class="sp-opacity-row">
      <input type="range" id="watermark_opacity" name="watermark_opacity" min="0" max="100" step="1" value="<?php echo max(0,min(100,(int)$p->get('watermark_opacity',7))); ?>">
      <output id="watermark_opacity_value"><?php echo max(0,min(100,(int)$p->get('watermark_opacity',7))); ?>%</output>
     </div>
    </label>
    <label class="sp-wide sp-upload-setting"><span class="sp-label-tooltip" tabindex="0" data-tooltip="Εικονίδιο που εμφανίζεται πάνω από τις οθόνες σύνδεσης και επιβεβαίωσης OTP.">Login icon</span>
     <div class="sp-media-row">
      <input class="form-control" id="login_icon" name="login_icon" value="<?php echo htmlspecialchars((string)$p->get('login_icon','')); ?>" placeholder="images/supportplans/login-icon.png ή URL">
      <input type="file" id="login_icon_upload" name="login_icon_upload" accept="image/png,image/jpeg,image/webp,image/gif,image/svg+xml" hidden>
      <button type="button" class="btn btn-outline-secondary sp-media-button" id="login_icon_pick">Upload login icon</button>
     </div>
     <span class="sp-upload-name sp-saved-file" id="login_icon_filename" aria-live="polite"><?php
$savedLoginIcon=trim((string)$p->get('login_icon',''));
echo $savedLoginIcon!=='' ? '✓ Αποθηκευμένο: '.htmlspecialchars($savedLoginIcon,ENT_QUOTES,'UTF-8') : 'Δεν έχει οριστεί login icon';
?></span>
    </label>
            <label class="sp-wide"><span class="sp-label-tooltip" tabindex="0" data-tooltip="Η ονομασία που εμφανίζεται για το πεδίο supported_websites μέσα στα Πακέτα.">Ονομασία πεδίου Υπηρεσιών στα Πακέτα</span>
     <input class="form-control" type="text" name="supported_websites_label" value="<?php echo htmlspecialchars((string)$p->get('supported_websites_label','Υποστηριζόμενα websites'), ENT_QUOTES, 'UTF-8'); ?>" placeholder="Υποστηριζόμενα websites">
    </label>
<label class="sp-wide"><span class="sp-label-tooltip" tabindex="0" data-tooltip="Joomla Menu Item ID για το standalone Support Plans. Διατηρείται σε Login, OTP και εφαρμογή ώστε integrations όπως το Web357 να αναγνωρίζουν πάντα την ίδια σελίδα.">Standalone Menu Item ID</span>
     <input class="form-control" type="number" min="0" step="1" name="standalone_itemid" value="<?php echo (int)$p->get('standalone_itemid',785); ?>" placeholder="785">
    </label>

    <div class="sp-wide sp-settings-subsection">
      <h3>Τράπεζες / Λογαριασμοί</h3>
      <p>Κάθε τράπεζα εμφανίζεται σε μία σειρά. Μπορείς να προσθέσεις όσους λογαριασμούς χρειάζεσαι.</p>
    </div>
    <?php
    $spBanksJson=(string)$p->get('bank_accounts','');
    $spBankRows=[];
    if($spBanksJson!==''){
      $spDecoded=json_decode($spBanksJson,true);
      if(is_array($spDecoded)) $spBankRows=$spDecoded;
    }
    if(!$spBankRows){
      for($spI=1;$spI<=3;$spI++){
        $spN=(string)$p->get('bank_'.$spI.'_name','');
        $spIb=(string)$p->get('bank_'.$spI.'_iban','');
        $spB=(string)$p->get('bank_'.$spI.'_beneficiary','');
        if($spN!==''||$spIb!==''||$spB!=='') $spBankRows[]=['name'=>$spN,'iban'=>$spIb,'beneficiary'=>$spB];
      }
    }
    if(!$spBankRows) $spBankRows[]=['name'=>'','iban'=>'','beneficiary'=>''];
    ?>
    <div class="sp-wide sp-bank-editor" id="sp-bank-editor">
      <div class="sp-bank-editor-head"><span>Τράπεζα</span><span>IBAN</span><span>Όνομα δικαιούχου</span><span></span></div>
      <div id="sp-bank-rows">
      <?php foreach($spBankRows as $spBank): ?>
        <div class="sp-bank-edit-row">
          <input class="form-control" name="bank_name[]" value="<?php echo htmlspecialchars((string)($spBank['name']??''),ENT_QUOTES,'UTF-8'); ?>" placeholder="Τράπεζα">
          <input class="form-control" name="bank_iban[]" value="<?php echo htmlspecialchars((string)($spBank['iban']??''),ENT_QUOTES,'UTF-8'); ?>" placeholder="IBAN">
          <input class="form-control" name="bank_beneficiary[]" value="<?php echo htmlspecialchars((string)($spBank['beneficiary']??''),ENT_QUOTES,'UTF-8'); ?>" placeholder="Όνομα δικαιούχου">
          <button type="button" class="btn btn-outline-danger sp-bank-remove" title="Αφαίρεση">×</button>
        </div>
      <?php endforeach; ?>
      </div>
      <button type="button" class="btn btn-outline-primary" id="sp-bank-add">+ Προσθήκη τράπεζας</button>
    </div>
    <script>
    (function(){
      const rows=document.getElementById('sp-bank-rows'), add=document.getElementById('sp-bank-add');
      if(!rows||!add)return;
      function bindRemove(btn){btn.addEventListener('click',function(){const row=btn.closest('.sp-bank-edit-row'); if(row) row.remove();});}
      rows.querySelectorAll('.sp-bank-remove').forEach(bindRemove);
      add.addEventListener('click',function(){
        const row=document.createElement('div');
        row.className='sp-bank-edit-row';
        row.innerHTML='<input class="form-control" name="bank_name[]" placeholder="Τράπεζα"><input class="form-control" name="bank_iban[]" placeholder="IBAN"><input class="form-control" name="bank_beneficiary[]" placeholder="Όνομα δικαιούχου"><button type="button" class="btn btn-outline-danger sp-bank-remove" title="Αφαίρεση">×</button>';
        rows.appendChild(row); bindRemove(row.querySelector('.sp-bank-remove'));
      });
    })();
    </script>
    <label class="sp-wide">Τίτλος report<input class="form-control" name="report_title" value="<?php echo htmlspecialchars((string)$p->get('report_title','Αναλυτικό δελτίο εργασιών πλάνου υποστήριξης')); ?>"></label>
    <label class="sp-wide">Footer report<input class="form-control" name="report_footer" value="<?php echo htmlspecialchars((string)$p->get('report_footer','Αναλυτικό δελτίο εργασιών πλάνου υποστήριξης')); ?>"></label>
   </div>
  </div>
  <input type="hidden" name="task" value="">
  <?php echo HTMLHelper::_('form.token'); ?>
 
<div class="sp-settings-savebar" id="sp-settings-save-v1044">
    <div class="sp-settings-savecopy">
        <strong>Αποθήκευση ρυθμίσεων</strong>
        <span>Αποθήκευσε τα στοιχεία επιχείρησης, τα αρχεία και τις ρυθμίσεις Pdf.</span>
    </div>
    <button type="submit" class="btn btn-primary sp-settings-savebtn" onclick="this.form.task.value='settings.apply'">
        Αποθήκευση
    </button>
</div>
</form>
</div>
</section></div></div></main>
<script>
document.addEventListener('DOMContentLoaded',function(){
 const pick=document.getElementById('brand_logo_pick');
 const file=document.getElementById('brand_logo_upload');
 const name=document.getElementById('brand_logo_filename');
 const watermarkPick=document.getElementById('pdf_watermark_pick');
 const watermarkFile=document.getElementById('pdf_watermark_upload');
 const watermarkName=document.getElementById('pdf_watermark_filename');
 const loginIconPick=document.getElementById('login_icon_pick');
 const loginIconFile=document.getElementById('login_icon_upload');
 const loginIconName=document.getElementById('login_icon_filename');
 if(pick&&file){pick.addEventListener('click',()=>file.click());file.addEventListener('change',()=>{name.textContent=file.files&&file.files[0]?'Επιλέχθηκε: '+file.files[0].name:'';});}
 if(watermarkPick&&watermarkFile){watermarkPick.addEventListener('click',()=>watermarkFile.click());watermarkFile.addEventListener('change',()=>{watermarkName.textContent=watermarkFile.files&&watermarkFile.files[0]?'Επιλέχθηκε: '+watermarkFile.files[0].name:'';});}
 if(loginIconPick&&loginIconFile){loginIconPick.addEventListener('click',()=>loginIconFile.click());loginIconFile.addEventListener('change',()=>{loginIconName.textContent=loginIconFile.files&&loginIconFile.files[0]?'Επιλέχθηκε: '+loginIconFile.files[0].name:'';});}
 const wmOpacity=document.getElementById('watermark_opacity');
 const wmOpacityValue=document.getElementById('watermark_opacity_value');
 if(wmOpacity&&wmOpacityValue){wmOpacity.addEventListener('input',()=>{wmOpacityValue.textContent=wmOpacity.value+'%';});}

});
</script>
</div>
