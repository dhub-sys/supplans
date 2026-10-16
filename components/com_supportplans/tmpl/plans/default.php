<?php
\defined('_JEXEC') or die;
$spWebsitesLabel=(string)\Joomla\CMS\Component\ComponentHelper::getParams('com_supportplans')->get('supported_websites_label','Υποστηριζόμενα websites');
$spItemid=max(1,(int)\Joomla\CMS\Component\ComponentHelper::getParams('com_supportplans')->get('standalone_itemid',785));

use Joomla\CMS\HTML\HTMLHelper; use Joomla\CMS\Router\Route;
echo \Support\Component\Supportplans\Site\Helper\Assets::css('supportplans.css');HTMLHelper::_('behavior.formvalidator');
$fmt=fn($m)=>sprintf('%02d:%02d',intdiv(max(0,(int)$m),60),max(0,(int)$m)%60);$dateGr=function($d){if(!$d)return '';try{return(new DateTime($d))->format('d/m/Y');}catch(Throwable $e){return $d;}};
$serviceOptions=function($selected=0){$out='';foreach($this->servicePlans as $sp){if(!(int)$sp->published && (int)$sp->id!==(int)$selected)continue;$mode=$sp->billing_mode??'prepaid';$h=rtrim(rtrim(number_format($sp->total_minutes/60,2,',','.'),'0'),',');$rate=$mode==='ongoing'?(float)$sp->hourly_rate:($mode==='prepaid'&&$sp->total_minutes?((float)$sp->total_cost/($sp->total_minutes/60)):0);$label=$mode==='ongoing'?($sp->name.' — Ongoing / '.number_format($rate,2,',','.').' € / ώρα προ ΦΠΑ'):($mode==='subscription'?($sp->name.' — Συνδρομή '.(int)($sp->subscription_months??1).' μήν. / '.number_format((float)$sp->total_cost,2,',','.').' € προ ΦΠΑ'):($sp->name.' — '.$h.' ώρες / '.number_format((float)$sp->total_cost,2,',','.').' € προ ΦΠΑ'));$out.='<option value="'.(int)$sp->id.'" '.((int)$selected===(int)$sp->id?'selected':'').'>'.htmlspecialchars($label).'</option>';}return $out;};
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
  <nav class="sp-side-nav"><a class="sp-side-link is-active" href="<?php echo Route::_('index.php?option=com_supportplans&view=plans&tmpl=component&spapp=1&Itemid='.(int)$spItemid.''); ?>"><span class="sp-side-icon"><svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M7 8h10M7 12h4M7 16h6"/></svg></span><span>Πακέτα</span></a><a class="sp-side-link" href="<?php echo Route::_('index.php?option=com_supportplans&view=serviceplans&tmpl=component&spapp=1&Itemid='.(int)$spItemid.''); ?>"><span class="sp-side-icon"><svg viewBox="0 0 24 24"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5z"/><path d="M4 5.5v16M8 7h8M8 11h6"/></svg></span><span>Πλάνα</span></a><a class="sp-side-link" href="<?php echo Route::_('index.php?option=com_supportplans&view=offers&tmpl=component&spapp=1&Itemid='.(int)$spItemid); ?>"><span class="sp-side-icon"><svg viewBox="0 0 24 24"><path d="M6 3h8l4 4v14H6z"/><path d="M14 3v4h4"/><path d="M14.2 11.6a2.6 2.6 0 1 0 0 3.8M9.5 12.8h3.2M9.5 14.4h3.2"/></svg></span><span>Προσφορές</span></a><a class="sp-side-link" href="<?php echo Route::_('index.php?option=com_supportplans&view=settings&tmpl=component&spapp=1&Itemid='.(int)$spItemid.''); ?>"><span class="sp-side-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.83 2.83-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .6 1.7 1.7 0 0 0-.4 1.1V21h-4v-.1A1.7 1.7 0 0 0 8.6 19.4a1.7 1.7 0 0 0-1.88.34l-.06.06-2.83-2.83.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-.6-1 1.7 1.7 0 0 0-1.1-.4H3v-4h.1A1.7 1.7 0 0 0 4.6 8.6a1.7 1.7 0 0 0-.34-1.88l-.06-.06 2.83-2.83.06.06A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-.6 1.7 1.7 0 0 0 .4-1.1V3h4v.1A1.7 1.7 0 0 0 15.4 4.6a1.7 1.7 0 0 0 1.88-.34l.06-.06 2.83 2.83-.06.06A1.7 1.7 0 0 0 19.4 9c.1.4.3.75.6 1 .3.25.7.4 1.1.4h.1v4h-.1c-.4 0-.8.15-1.1.4-.3.25-.5.6-.6 1z"/></svg></span><span>Ρυθμίσεις</span></a>
<a class="sp-side-link" href="<?php echo Route::_('index.php?option=com_supportplans&view=help&tmpl=component&spapp=1&Itemid='.(int)$spItemid); ?>"><span class="sp-side-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M9.8 9a2.3 2.3 0 1 1 3.7 1.8c-.9.6-1.5 1-1.5 2.2"/><path d="M12 17h.01"/></svg></span><span>Οδηγίες</span></a></nav>
  <div class="sp-sidebar-bottom">
    <div class="sp-sidebar-help"><strong>Service Hub</strong><span>Πακέτα, πλάνα, αναφορές και προσφορές σε ένα σημείο.</span></div>
    <a class="sp-side-joomla" href="<?php echo \Joomla\CMS\Uri\Uri::root(); ?>index.php?option=com_supportplans&amp;task=auth.logout&amp;tmpl=component&amp;Itemid=<?php echo (int)$spItemid; ?>"><span>↪</span> Αποσύνδεση</a>
  </div>
</aside>
<section class="sp-workspace">
<div class="sp-standalone-actions sp-package-edit-actions">
<?php if($this->mode==='new' || $this->selected): ?>
 <button type="button" class="btn btn-primary" onclick="spSubmit('plan.apply')">Αποθήκευση</button>
 <button type="button" class="btn btn-outline-secondary" onclick="spSubmit('plan.save')">Αποθήκευση & κλείσιμο</button>
 <a class="btn btn-outline-secondary" href="<?php echo Route::_('index.php?option=com_supportplans&view=plans&tmpl=component&spapp=1&Itemid='.(int)$spItemid); ?>">Κλείσιμο</a>
<?php else: ?>
 <a class="btn btn-primary" href="<?php echo Route::_('index.php?option=com_supportplans&view=plans&tmpl=component&spapp=1&mode=new&Itemid='.(int)$spItemid); ?>">＋ Νέο πακέτο</a>
<?php endif; ?>
</div>
<script>function spSubmit(task){var f=document.getElementById('adminForm');if(!f)return;var t=f.querySelector('input[name="task"]');if(t)t.value=task;f.submit();}</script>
<?php if($this->mode==='new'): ?>
<form action="<?php echo Route::_('index.php?option=com_supportplans&tmpl=component&spapp=1&view=plans&mode=new&Itemid='.(int)$spItemid.''); ?>" method="post" name="adminForm" id="adminForm" class="form-validate"><div class="sp-card"><h2>Στοιχεία νέου πακέτου</h2><div class="sp-grid">
<label>Πελάτης<input type="text" name="customer_name" required></label><label><?php echo htmlspecialchars($spWebsitesLabel, ENT_QUOTES, 'UTF-8'); ?><input name="supported_websites"></label><label>Αρ. εντύπου<input name="form_number"></label><label>Πλάνο<select name="service_plan_id" required><option value="">— Επιλογή —</option><?php echo $serviceOptions(); ?></select></label><label class="sp-purchase-field">Ημερομηνία αγοράς<input type="date" name="purchase_date" value="<?php echo date('Y-m-d'); ?>" required></label><label>ΦΠΑ %<input type="number" step="0.01" name="vat_rate" value="24"></label><label>Κατάσταση<select name="status"><option value="active">Ενεργό</option><option value="completed">Ολοκληρωμένο</option></select></label>
</div><p class="sp-foot">Οι ώρες, η τιμή, η ελάχιστη χρέωση, το step και η περιγραφή αντιγράφονται από το επιλεγμένο πλάνο τη στιγμή δημιουργίας του πακέτου.</p></div><input type="hidden" name="id" value="0"><input type="hidden" name="task" value=""><?php echo HTMLHelper::_('form.token'); ?></form>
<?php elseif($this->selected): $used=array_sum(array_map(fn($e)=>(int)$e->billable_minutes,$this->entries));$actualUsed=array_sum(array_map(fn($e)=>(int)$e->actual_minutes,$this->entries));$spent=array_sum(array_map(fn($e)=>(float)$e->cost,$this->entries));$mode=$this->selected->billing_mode??'prepaid';$ongoing=$mode==='ongoing';$subscription=$mode==='subscription';$rem=max(0,(int)$this->selected->initial_minutes-$used);$money=max(0,(float)$this->selected->initial_amount-$spent); ?>
<div class="sp-card"><div class="sp-title-row"><div><h2><?php echo htmlspecialchars($this->selected->customer_name); ?></h2><div class="sp-plan-subtitle"><?php echo htmlspecialchars($this->selected->plan_type); ?> · πακέτο <?php echo (int)$this->selected->sequence_no; ?></div></div><button type="button" class="btn btn-secondary" onclick="supportPlansPrintReport()">Εκτύπωση / Pdf</button></div>
<iframe id="supportplans-print-frame" title="Αναφορά πακέτου" src="<?php echo Route::_('index.php?option=com_supportplans&view=report&tmpl=component&spapp=1&plan_id='.(int)$this->selected->id.'&Itemid='.(int)$spItemid); ?>" style="position:fixed;width:1px;height:1px;right:0;bottom:0;border:0;opacity:0;pointer-events:none;" aria-hidden="true"></iframe>
<script>
function supportPlansPrintReport() {
    const frame = document.getElementById('supportplans-print-frame');
    if (!frame || !frame.contentWindow) {
        return;
    }
    frame.contentWindow.focus();
    frame.contentWindow.print();
}
</script><div class="sp-purchase-hero"><span><?php echo $ongoing?'Ημερομηνία έναρξης':'Ημερομηνία αγοράς'; ?></span><strong><?php echo htmlspecialchars($dateGr($this->selected->purchase_date)); ?></strong></div><div class="sp-stats"><?php if($ongoing): ?><div><b>Πραγματικός χρόνος</b><strong><?php echo $fmt($actualUsed); ?></strong></div><div><b>Χρεώσιμος χρόνος</b><strong><?php echo $fmt($used); ?></strong></div><div><b>Συνολικό κόστος</b><strong><?php echo number_format($spent,2,',','.'); ?> €</strong></div><?php else: ?><div><b>Υπόλοιπο χρόνου</b><strong><?php echo $fmt($rem); ?></strong></div><div><b>Διαθέσιμο ποσό</b><strong><?php echo number_format($money,2,',','.'); ?> €</strong></div><div><b>Χρησιμοποιήθηκαν</b><strong><?php echo $fmt($used); ?></strong></div><?php endif; ?></div></div>
<form action="<?php echo Route::_('index.php?option=com_supportplans&tmpl=component&spapp=1&view=plans&plan_id='.(int)$this->selected->id.'&Itemid='.(int)$spItemid); ?>" method="post" name="adminForm" id="adminForm" class="form-validate"><div class="sp-card"><h3>Στοιχεία πακέτου</h3><div class="sp-grid"><label>Πελάτης<input name="customer_name" value="<?php echo htmlspecialchars($this->selected->customer_name); ?>" required></label><label><?php echo htmlspecialchars($spWebsitesLabel, ENT_QUOTES, 'UTF-8'); ?><input name="supported_websites" value="<?php echo htmlspecialchars($this->selected->supported_websites); ?>"></label><label>Αρ. εντύπου<input name="form_number" value="<?php echo htmlspecialchars($this->selected->form_number); ?>"></label><label>Πλάνο<select name="service_plan_id" required><?php echo $serviceOptions((int)$this->selected->service_plan_id); ?></select></label><label><?php echo $ongoing?'Ημερομηνία έναρξης':'Ημερομηνία αγοράς'; ?><input type="date" name="purchase_date" value="<?php echo htmlspecialchars($this->selected->purchase_date); ?>" required></label><label>ΦΠΑ %<input type="number" step="0.01" name="vat_rate" value="<?php echo htmlspecialchars($this->selected->vat_rate); ?>"></label><label>Κατάσταση<select name="status"><option value="active" <?php echo $this->selected->status==='active'?'selected':''; ?>>Ενεργό</option><option value="completed" <?php echo $this->selected->status==='completed'?'selected':''; ?>>Ολοκληρωμένο</option></select></label></div><p class="sp-foot">Οι εμπορικοί όροι αυτού του πακέτου παραμένουν όπως ήταν κατά την αγορά. Αλλάζουν μόνο αν επιλέξετε διαφορετικό πλάνο.</p></div><input type="hidden" name="id" value="<?php echo (int)$this->selected->id; ?>"><input type="hidden" name="task" value=""><?php echo HTMLHelper::_('form.token'); ?></form>
<div class="sp-card"><div class="sp-title-row"><h3>Εργασίες</h3><form method="post" action="<?php echo Route::_('index.php?option=com_supportplans&tmpl=component&task=plan.delete&Itemid='.(int)$spItemid.''); ?>" onsubmit="return confirm('Να διαγραφεί οριστικά το πακέτο και όλες οι εργασίες του;');"><input type="hidden" name="id" value="<?php echo (int)$this->selected->id; ?>"><button class="btn btn-outline-danger">Διαγραφή πακέτου</button><?php echo HTMLHelper::_('form.token'); ?></form></div><h4 id="sp-entry-heading">Νέα εργασία</h4><form method="post" id="sp-entry-form" action="<?php echo Route::_('index.php?option=com_supportplans&tmpl=component&task=entry.save&Itemid='.(int)$spItemid.''); ?>" class="sp-grid"><input type="hidden" name="plan_id" value="<?php echo (int)$this->selected->id; ?>"><input type="hidden" name="entry_id" value="0"><label>Ημερομηνία / περίοδος<input name="request_date" value="<?php echo date('d/m/Y'); ?>" required></label><label>Αίτημα / εργασία<input name="work_type" required></label><?php if(!$subscription): ?><label>Διάρκεια<input type="number" min="<?php echo (int)$this->selected->min_minutes; ?>" step="<?php echo (int)$this->selected->step_minutes; ?>" value="<?php echo (int)$this->selected->min_minutes; ?>" name="actual_minutes" required></label><?php else: ?><input type="hidden" name="actual_minutes" value="0"><?php endif; ?><label class="sp-wide">Πρόσθετη περιγραφή<textarea name="description" rows="2"></textarea></label><div class="sp-entry-buttons"><button class="btn btn-primary" id="sp-entry-submit">Καταχώριση εργασίας</button><button type="button" class="btn btn-outline-secondary" id="sp-entry-cancel" hidden>Ακύρωση</button></div><?php echo HTMLHelper::_('form.token'); ?></form><?php if($subscription): ?><p class="sp-foot">Συνδρομητικό πλάνο: οι εργασίες καταγράφονται χωρίς ωριαία χρέωση. Η σταθερή αξία της συνδρομής είναι προ ΦΠΑ.</p><?php else: ?><p class="sp-foot">Ελάχιστη διάρκεια: <?php echo (int)$this->selected->min_minutes; ?>′ · step: <?php echo (int)$this->selected->step_minutes; ?>′</p><?php endif; ?><h4>Αναλυτικό δελτίο εργασιών</h4><div class="table-responsive"><table class="table"><thead><tr><th>Ημ/νία</th><th>Αίτημα</th><th>Διάρκεια</th><th>Χρεώσιμο</th><th>Κόστος</th><th></th></tr></thead><tbody><?php foreach($this->entries as $e): ?><tr data-entry="<?php echo htmlspecialchars(json_encode(['id'=>(int)$e->id,'request_date'=>(string)$e->request_date,'work_type'=>(string)$e->work_type,'description'=>(string)$e->description,'actual_minutes'=>(int)$e->actual_minutes],JSON_UNESCAPED_UNICODE),ENT_QUOTES,'UTF-8'); ?>"><td><?php echo htmlspecialchars($e->request_date); ?></td><td class="sp-entry-request"><b><?php echo htmlspecialchars($e->work_type); ?></b><?php if(trim((string)$e->description)!==''): ?><br><small><?php echo nl2br(htmlspecialchars($e->description)); ?></small><?php endif; ?></td><td><?php echo $subscription?'—':((int)$e->actual_minutes.'′'); ?></td><td><?php echo $subscription?'—':((int)$e->billable_minutes.'′'); ?></td><td><?php echo $subscription?'Περιλαμβάνεται στη συνδρομή':(number_format((float)$e->cost,2,',','.').' €'); ?></td><td class="sp-entry-actions"><button type="button" class="sp-icon-btn" data-entry-edit title="Επεξεργασία εργασίας" aria-label="Επεξεργασία εργασίας"><svg viewBox="0 0 24 24"><path d="M4 20h4L18.5 9.5a2.1 2.1 0 0 0-3-3L5 17v3z"/><path d="M13.5 8.5l3 3"/></svg></button><form method="post" action="<?php echo Route::_('index.php?option=com_supportplans&tmpl=component&task=entry.delete&Itemid='.(int)$spItemid.''); ?>" onsubmit="return confirm('Διαγραφή εργασίας;');"><input type="hidden" name="id" value="<?php echo (int)$e->id; ?>"><input type="hidden" name="plan_id" value="<?php echo (int)$this->selected->id; ?>"><button type="submit" class="sp-icon-btn is-danger" title="Διαγραφή εργασίας" aria-label="Διαγραφή εργασίας"><svg viewBox="0 0 24 24"><path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/></svg></button><?php echo HTMLHelper::_('form.token'); ?></form></td></tr><?php endforeach; ?></tbody></table></div></div>
<style>
.sp-entry-request{font-size:13px;line-height:1.4}
.sp-entry-request b{font-weight:600}
.sp-entry-request small{font-size:12px;color:#667085}
.sp-entry-actions{white-space:nowrap;text-align:right;width:1%}
.sp-entry-actions form{display:inline}
tr.is-editing td{background:#fffbeb!important}
.sp-entry-buttons{display:flex;gap:8px;align-items:end}
#sp-entry-form.is-editing{padding:12px;border-radius:10px;background:#fffbeb;outline:1px solid #fde68a}
</style>
<script>
(function(){
  var form=document.getElementById('sp-entry-form'); if(!form)return;
  var heading=document.getElementById('sp-entry-heading'), submit=document.getElementById('sp-entry-submit'), cancel=document.getElementById('sp-entry-cancel');
  var defaults={}; ['request_date','work_type','description','actual_minutes'].forEach(function(n){var f=form.elements[n]; if(f)defaults[n]=f.value;});
  function reset(){
    form.elements.entry_id.value='0';
    Object.keys(defaults).forEach(function(n){form.elements[n].value=defaults[n];});
    heading.textContent='Νέα εργασία'; submit.textContent='Καταχώριση εργασίας'; cancel.hidden=true; form.classList.remove('is-editing');
    document.querySelectorAll('tr.is-editing').forEach(function(r){r.classList.remove('is-editing');});
  }
  document.addEventListener('click',function(e){
    var btn=e.target.closest('[data-entry-edit]'); if(!btn)return;
    var row=btn.closest('tr'), d=JSON.parse(row.dataset.entry);
    reset();
    form.elements.entry_id.value=d.id;
    ['request_date','work_type','description','actual_minutes'].forEach(function(n){var f=form.elements[n]; if(f && f.type!=='hidden')f.value=d[n];});
    heading.textContent='Επεξεργασία εργασίας'; submit.textContent='Αποθήκευση αλλαγών'; cancel.hidden=false; form.classList.add('is-editing'); row.classList.add('is-editing');
    heading.scrollIntoView({behavior:'smooth',block:'center'});
    form.elements.work_type.focus();
  });
  cancel.addEventListener('click',reset);
})();
</script>
<?php else:
$totalPlans=count($this->plans);
$activePlans=count(array_filter($this->plans,fn($x)=>($x->status??'active')==='active'));
$completedPlans=max(0,$totalPlans-$activePlans);
?>
<section class="sp-dashboard-head">
  <div><span class="sp-eyebrow">Επισκόπηση</span><h1><?php echo htmlspecialchars($spCompany); ?> Support Plans</h1><p>Διαχείριση πακέτων, εργασιών και συνδρομών σε ένα ενιαίο περιβάλλον.</p></div>
  <a class="sp-primary-action" href="<?php echo Route::_('index.php?option=com_supportplans&tmpl=component&spapp=1&view=plans&mode=new&Itemid='.(int)$spItemid.''); ?>">＋ Νέο πακέτο</a>
</section>
<section class="sp-kpis">
  <article><span>Σύνολο πακέτων</span><strong><?php echo $totalPlans; ?></strong><small>όλες οι εγγραφές</small></article>
  <article><span>Ενεργά</span><strong><?php echo $activePlans; ?></strong><small>σε εξέλιξη</small></article>
  <article><span>Ολοκληρωμένα</span><strong><?php echo $completedPlans; ?></strong><small>κλειστά πακέτα</small></article>
</section>
<form action="<?php echo Route::_('index.php?option=com_supportplans&view=plans&tmpl=component&Itemid='.(int)$spItemid.''); ?>" method="post" name="adminForm" id="adminForm"><div class="sp-card"><h2>Πακέτα υποστήριξης</h2><div class="table-responsive"><table class="table"><thead><tr><th>Α/α</th><th>Πελάτης</th><th>Πλάνο</th><th>Ημ/νία αγοράς</th><th><?php echo htmlspecialchars($spWebsitesLabel, ENT_QUOTES, 'UTF-8'); ?></th><th>Κατάσταση</th><th></th></tr></thead><tbody><?php foreach($this->plans as $p): ?><tr><td><?php echo (int)$p->sequence_no; ?></td><td><?php echo htmlspecialchars($p->customer_name?:'—'); ?></td><td><?php echo htmlspecialchars($p->plan_type); ?></td><td><?php echo htmlspecialchars($dateGr($p->purchase_date)); ?></td><td><?php echo htmlspecialchars($p->supported_websites?:'—'); ?></td><td><?php echo $p->status==='completed'?'Ολοκληρωμένο':'Ενεργό'; ?></td><td><a class="btn btn-sm btn-outline-primary" href="<?php echo Route::_('index.php?option=com_supportplans&tmpl=component&spapp=1&view=plans&plan_id='.(int)$p->id.'&Itemid='.(int)$spItemid); ?>">Άνοιγμα</a></td></tr><?php endforeach; ?></tbody></table></div></div><input type="hidden" name="task" value=""><?php echo HTMLHelper::_('form.token'); ?></form>
<?php endif; ?></div>
</section></div></div></main>
</div>
