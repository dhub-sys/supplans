<?php
\defined('_JEXEC') or die;
$spServicesLabel=(string)\Joomla\CMS\Component\ComponentHelper::getParams('com_supportplans')->get('supported_websites_label','Υποστηριζόμενα websites');
use Joomla\CMS\HTML\HTMLHelper;
HTMLHelper::_('stylesheet', 'com_supportplans/supportplans.css', ['version'=>'auto','relative'=>true]);
$used = array_sum(array_map(fn($e)=>(int)$e->billable_minutes, $this->entries));
$spent = array_sum(array_map(fn($e)=>(float)$e->cost, $this->entries));
$actualUsed = array_sum(array_map(fn($e)=>(int)$e->actual_minutes, $this->entries));
$mode = $this->plan->billing_mode ?? 'prepaid';
$ongoing = $mode === 'ongoing';
$subscription = $mode === 'subscription';
$vatRate = (float)($this->plan->vat_rate ?? 24);
$subscriptionNet = (float)$this->plan->initial_amount;
$subscriptionGross = $subscriptionNet * (1 + $vatRate/100);
$rem = max(0, (int)$this->plan->initial_minutes-$used);
$fmt = fn($m)=>sprintf('%02d:%02d', intdiv(max(0,(int)$m),60), max(0,(int)$m)%60);
$dateGr = function($d) { if (!$d) return ''; try { return (new DateTime($d))->format('d/m/Y'); } catch (Throwable $e) { return $d; } };
$planLabel = trim((string)$this->plan->plan_type) ?: 'Support';
$hours = ((int)$this->plan->initial_minutes) / 60;
$rate = (float)$this->plan->hourly_rate;
$planDescription = trim((string)($this->plan->plan_description ?? ''));
if ($planDescription === '') {
    if ($subscription) {
        $planDescription = sprintf('Συνδρομητικό πλάνο διάρκειας %d μήνα/μηνών με σταθερή αξία %s € προ Φ.Π.Α. Οι εργασίες καταγράφονται αναλυτικά χωρίς ωριαία χρέωση και χωρίς υπολογισμό χρεώσιμου χρόνου.', (int)($this->plan->subscription_months ?? 1), number_format($subscriptionNet, 2, ',', '.'));
    } elseif ($ongoing) {
        $planDescription = sprintf('Το Ongoing πλάνο %s χρεώνεται με %s € / ώρα, με ελάχιστη χρέωση %d′ ανά αίτημα και στη συνέχεια διαβάθμιση ανά %d′. Οι τιμές είναι προ Φ.Π.Α.', $planLabel, number_format($rate, 2, ',', '.'), (int)$this->plan->min_minutes, (int)$this->plan->step_minutes);
    } else {
        $planDescription = sprintf('Το προπληρωμένο πακέτο %s περιλαμβάνει %s ώρες υποστήριξης, με κόστος %s € / ώρα, με ελάχιστη χρέωση %d′ ανά αίτημα και στη συνέχεια διαβάθμιση ανά %d′. Οι τιμές είναι προ Φ.Π.Α.', $planLabel, rtrim(rtrim(number_format($hours, 2, ',', '.'), '0'), ','), number_format($rate, 2, ',', '.'), (int)$this->plan->min_minutes, (int)$this->plan->step_minutes);
    }
}
$servicePdf = null;
if (!empty($this->plan->service_plan_id)) {
    try {
        $db = \Joomla\CMS\Factory::getContainer()->get('DatabaseDriver');
        $q = $db->getQuery(true)->select(['pdf_title','pdf_section_title','pdf_work_title','pdf_footer_title'])->from($db->quoteName('#__supportplans_serviceplans'))->where($db->quoteName('id').'='.(int)$this->plan->service_plan_id);
        $servicePdf = $db->setQuery($q)->loadObject();
    } catch (\Throwable $e) { $servicePdf = null; }
}
$params = $this->params ?? \Joomla\CMS\Component\ComponentHelper::getParams('com_supportplans');
$brandName = trim((string)$params->get('brand_name', '')) ?: trim((string)\Joomla\CMS\Factory::getApplication()->get('sitename', ''));
if ($brandName === '') $brandName = 'Support';
$brandLogo = trim((string)$params->get('brand_logo', ''));
$brandAddress = trim((string)$params->get('brand_address', ''));
$brandPhone = trim((string)$params->get('brand_phone', ''));
$brandEmail = trim((string)$params->get('brand_email', ''));
$brandWebsite = trim((string)$params->get('brand_website', ''));
$accentColor = trim((string)$params->get('accent_color', '#367d22'));
if (!preg_match('/^#[0-9a-fA-F]{6}$/', $accentColor)) $accentColor = '#367d22';
$secondaryColor = trim((string)$params->get('secondary_color', '#245719'));
if (!preg_match('/^#[0-9a-fA-F]{6}$/', $secondaryColor)) $secondaryColor = '#245719';
$globalReportTitle = trim((string)$params->get('report_title', 'Αναλυτικό δελτίο εργασιών πλάνου υποστήριξης')) ?: 'Αναλυτικό δελτίο εργασιών πλάνου υποστήριξης';
$reportBaseTitle = trim((string)($servicePdf->pdf_title ?? '')) ?: ($globalReportTitle . ' ' . $planLabel);
$sectionTitle = trim((string)($servicePdf->pdf_section_title ?? '')) ?: ('Πληροφορίες πλάνου υποστήριξης ' . $planLabel);
$workTitle = trim((string)($servicePdf->pdf_work_title ?? '')) ?: 'Αναλυτικό δελτίο εργασιών';
$globalFooter = trim((string)$params->get('report_footer', $globalReportTitle)) ?: $globalReportTitle;
$footerTitle = trim((string)($servicePdf->pdf_footer_title ?? '')) ?: ($globalFooter . ' ' . $planLabel);
$spBanks = [];
$spBanksJson = (string)$params->get('bank_accounts','');
if ($spBanksJson !== '') {
    $spDecodedBanks = json_decode($spBanksJson,true);
    if (is_array($spDecodedBanks)) {
        foreach ($spDecodedBanks as $spBank) {
            $spBankName=trim((string)($spBank['name']??''));
            $spBankIban=trim((string)($spBank['iban']??''));
            $spBankBeneficiary=trim((string)($spBank['beneficiary']??''));
            if ($spBankName!=='' || $spBankIban!=='' || $spBankBeneficiary!=='') {
                $spBanks[]=['name'=>$spBankName,'iban'=>$spBankIban,'beneficiary'=>$spBankBeneficiary];
            }
        }
    }
}
if (!$spBanks) {
    for ($spBankIndex=1; $spBankIndex<=3; $spBankIndex++) {
        $spBankName=trim((string)$params->get('bank_'.$spBankIndex.'_name',''));
        $spBankIban=trim((string)$params->get('bank_'.$spBankIndex.'_iban',''));
        $spBankBeneficiary=trim((string)$params->get('bank_'.$spBankIndex.'_beneficiary',''));
        if ($spBankName!=='' || $spBankIban!=='' || $spBankBeneficiary!=='') {
            $spBanks[]=['name'=>$spBankName,'iban'=>$spBankIban,'beneficiary'=>$spBankBeneficiary];
        }
    }
}
$logoSrc = $brandLogo !== '' ? (preg_match('#^https?://#i', $brandLogo) ? $brandLogo : rtrim(\Joomla\CMS\Uri\Uri::root(), '/') . '/' . ltrim($brandLogo, '/')) : '';
?>
<div class="sp-report sp-report-sheet" style="--sp-accent:<?php echo htmlspecialchars($accentColor); ?>;--sp-secondary:<?php echo htmlspecialchars($secondaryColor); ?>">
<div class="sp-no-print"><button class="btn btn-primary" onclick="window.print()">Αποθήκευση ως pdf / εκτύπωση</button></div>

<div class="sp-report-page sp-report-page-one">
<div class="sp-page-content">
<header class="sp-report-head">
    <div class="sp-report-topbar">
        <?php if ($logoSrc !== ''): ?><div class="sp-logo"><img src="<?php echo htmlspecialchars($logoSrc); ?>" alt="<?php echo htmlspecialchars($brandName); ?>"></div><?php endif; ?>
        <div class="sp-company-top">
            <b><?php echo htmlspecialchars($brandName); ?></b><br>
            <?php if ($brandAddress !== ''): ?><?php echo nl2br(htmlspecialchars($brandAddress)); ?><br><?php endif; ?>
            <?php if ($brandPhone !== '' || $brandEmail !== ''): ?><?php echo htmlspecialchars(trim($brandPhone . ($brandPhone !== '' && $brandEmail !== '' ? ' | ' : '') . $brandEmail)); ?><br><?php endif; ?>
            <?php if ($brandWebsite !== ''): ?><?php echo htmlspecialchars($brandWebsite); ?><?php endif; ?>
        </div>
    </div>
    <h1><?php echo htmlspecialchars($reportBaseTitle); ?></h1>
    <div class="sp-green-rule"></div>
    <div class="sp-doc-row"><b>Αρ. εντύπου</b><span><?php echo htmlspecialchars($this->plan->form_number ?: (string)$this->plan->sequence_no); ?></span></div>
    <div class="sp-doc-row"><b>Ημερομηνία έκδοσης</b><span><?php echo date('d/m/Y'); ?></span></div>
    <div class="sp-doc-row"><b>Πελάτης</b><span><?php echo htmlspecialchars($this->plan->customer_name); ?></span></div>
</header>

<section class="sp-report-section sp-plan-info-first-page">
    <h2><?php echo htmlspecialchars($sectionTitle); ?></h2>
    <p class="sp-plan-description"><?php echo nl2br(htmlspecialchars($planDescription)); ?></p>
    <div class="sp-info-row"><b><?php echo ($ongoing||$subscription)?'Ημερομηνία έναρξης πλάνου':'Ημερομηνία αγοράς πλάνου'; ?></b><span><?php echo htmlspecialchars($dateGr($this->plan->purchase_date)); ?></span></div>
    <div class="sp-info-row"><b><?php echo htmlspecialchars($spServicesLabel, ENT_QUOTES, 'UTF-8'); ?></b><span><?php echo htmlspecialchars($this->plan->supported_websites ?: '—'); ?></span></div>
</section>

<section class="sp-report-summary">
    <div class="sp-summary-heading">Σύνοψη πλάνου</div>
    <div class="sp-summary-cards">
        <?php if($subscription): ?>
        <div class="sp-summary-card"><div class="sp-summary-icon" aria-hidden="true"><svg viewBox="0 0 24 24" focusable="false"><rect x="4" y="5.5" width="16" height="14" rx="2"/><path d="M8 3.5v4M16 3.5v4M4 9.5h16"/></svg></div><div class="sp-summary-label">Διάρκεια συνδρομής</div><div class="sp-summary-value"><?php echo (int)($this->plan->subscription_months??1); ?> μήν.</div></div>
        <div class="sp-summary-card"><div class="sp-summary-icon" aria-hidden="true"><svg viewBox="0 0 24 24" focusable="false"><path d="M6 3.5h12v17l-2-1.4-2 1.4-2-1.4-2 1.4-2-1.4-2 1.4v-17Z"/><path d="M9 8h6M9 12h6M9 16h4"/></svg></div><div class="sp-summary-label">Αξία προ Φ.Π.Α.</div><div class="sp-summary-value"><?php echo number_format($subscriptionNet,2,',','.'); ?> €</div></div>
        <div class="sp-summary-card"><div class="sp-summary-icon" aria-hidden="true"><svg viewBox="0 0 24 24" focusable="false"><path d="M18 7.2A6.5 6.5 0 0 0 7.2 9.5M6 11h9M6 14h8.5M7.2 15.5A6.5 6.5 0 0 0 18 17"/></svg></div><div class="sp-summary-label">Αξία με Φ.Π.Α. <?php echo number_format($vatRate,0); ?>%</div><div class="sp-summary-value"><?php echo number_format($subscriptionGross,2,',','.'); ?> €</div></div>
        <?php elseif($ongoing): ?>
        <div class="sp-summary-card"><div class="sp-summary-icon" aria-hidden="true"><svg viewBox="0 0 24 24" focusable="false"><circle cx="12" cy="12" r="8.5"/><path d="M12 7.2v5.1l3.5 2.1"/></svg></div><div class="sp-summary-label">Συνολικός χρόνος</div><div class="sp-summary-value"><?php echo $fmt($used); ?></div></div>
        <div class="sp-summary-card"><div class="sp-summary-icon" aria-hidden="true"><svg viewBox="0 0 24 24" focusable="false"><path d="M5 6.5h12.5a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-11a2 2 0 0 1 2-2h10"/><path d="M14.5 11h5v5h-5a2.5 2.5 0 0 1 0-5Z"/><circle cx="15.3" cy="13.5" r=".55"/></svg></div><div class="sp-summary-label">Συνολικό κόστος</div><div class="sp-summary-value"><?php echo number_format($spent,2,',','.'); ?> €</div></div>
        <?php else: ?>
        <div class="sp-summary-card"><div class="sp-summary-icon" aria-hidden="true"><svg viewBox="0 0 24 24" focusable="false"><circle cx="12" cy="12" r="8.5"/><path d="M12 7.2v5.1l3.5 2.1"/></svg></div><div class="sp-summary-label">Υπόλοιπο χρόνου</div><div class="sp-summary-value"><?php echo $fmt($rem); ?></div></div>
        <div class="sp-summary-card"><div class="sp-summary-icon" aria-hidden="true"><svg viewBox="0 0 24 24" focusable="false"><path d="M5 6.5h12.5a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-11a2 2 0 0 1 2-2h10"/><path d="M14.5 11h5v5h-5a2.5 2.5 0 0 1 0-5Z"/><circle cx="15.3" cy="13.5" r=".55"/></svg></div><div class="sp-summary-label">Διαθέσιμο ποσό προ Φ.Π.Α.</div><div class="sp-summary-value"><?php echo number_format(max(0,(float)$this->plan->initial_amount-$spent),2,',','.'); ?> €</div></div>
        <div class="sp-summary-card"><div class="sp-summary-icon" aria-hidden="true"><svg viewBox="0 0 24 24" focusable="false"><path d="M7 4h10M7 20h10M8 4c0 3.4 1.2 5.3 4 8-2.8 2.7-4 4.6-4 8M16 4c0 3.4-1.2 5.3-4 8 2.8 2.7 4 4.6 4 8"/><path d="M9.7 17.3h4.6"/></svg></div><div class="sp-summary-label">Χρησιμοποιήθηκαν</div><div class="sp-summary-value"><?php echo $fmt($used); ?></div></div>
        <?php endif; ?>
    </div>
</section>

</div>
<footer class="sp-document-footer">
    <div class="sp-footer-title"><?php echo htmlspecialchars($footerTitle); ?></div>
    <div class="sp-page-number">1</div>
</footer>
</div>

<div class="sp-report-page sp-report-page-two">
<div class="sp-page-content">
<section class="sp-report-section sp-work-section">
    <h2><?php echo htmlspecialchars($workTitle); ?></h2>
    <table class="sp-work-table"><thead><tr><th>Ημ/νία</th><th>Αίτημα</th><th><?php echo $subscription?'Χρέωση':'Διάρκεια / κόστος'; ?></th></tr></thead><tbody>
    <?php foreach($this->entries as $e): ?><tr><td><?php echo htmlspecialchars($e->request_date); ?></td><td><?php echo htmlspecialchars($e->work_type); ?><?php if(trim((string)$e->description)!==''): ?><br><small><?php echo nl2br(htmlspecialchars($e->description)); ?></small><?php endif; ?></td><td><?php if($subscription): ?>Περιλαμβάνεται στη συνδρομή<?php else: ?><?php echo (int)$e->billable_minutes; ?>′ / <?php echo number_format((float)$e->cost,2,',','.'); ?> €<?php endif; ?></td></tr><?php endforeach; ?>
    <?php if (!$this->entries): ?><tr><td colspan="3">Δεν υπάρχουν καταχωρισμένες εργασίες.</td></tr><?php endif; ?>
    </tbody></table>
    </section>

<?php
// Financial summary must live inside the printable report page.
$spTotalBillableMinutes = 0;
foreach ($this->entries as $spEntry) {
    $spTotalBillableMinutes += (int)($spEntry->billable_minutes ?? 0);
}
$spHours = intdiv($spTotalBillableMinutes, 60);
$spMinutesPart = $spTotalBillableMinutes % 60;
$spBillableTimeFormatted = sprintf('%02d:%02d', $spHours, $spMinutesPart);
$spNet = $ongoing ? (float)$spent : (float)($this->plan->initial_amount ?? 0);
$spVatRate = (float)($this->plan->vat_rate ?? 0);
$spVat = $spNet * ($spVatRate / 100);
$spGross = $spNet + $spVat;
?>
<?php // Prepaid plans are paid in advance: no financial summary or bank accounts in their report.
if ($ongoing || $subscription): ?>
<div class="sp-page-two-bottom sp-financial-force">
<section class="sp-financial-summary">
    <div class="sp-financial-title sp-financial-heading-force">Οικονομική σύνοψη</div>
    <div class="sp-financial-row"><span>Σύνολο χρεώσιμου χρόνου</span><strong><?php echo $spBillableTimeFormatted; ?></strong></div>
    <div class="sp-financial-row"><span>Καθαρή αξία</span><strong><?php echo number_format($spNet,2,',','.'); ?> €</strong></div>
    <div class="sp-financial-row"><span>Φ.Π.Α. <?php echo number_format($spVatRate,0,',','.'); ?>%</span><strong><?php echo number_format($spVat,2,',','.'); ?> €</strong></div>
    <div class="sp-financial-row sp-financial-total"><span>Σύνολο με Φ.Π.Α.</span><strong><?php echo number_format($spGross,2,',','.'); ?> €</strong></div>
</section>
<?php if ($spBanks): ?>
<section class="sp-bank-summary">
    <div class="sp-bank-title">Τράπεζες / Λογαριασμοί</div>
    <div class="sp-bank-head"><span>Τράπεζα</span><span>IBAN</span><span>Όνομα δικαιούχου</span></div>
    <?php foreach ($spBanks as $spBank): ?>
    <div class="sp-bank-row">
        <span><?php echo htmlspecialchars($spBank['name']); ?></span>
        <span><?php echo htmlspecialchars($spBank['iban']); ?></span>
        <span><?php echo htmlspecialchars($spBank['beneficiary']); ?></span>
    </div>
    <?php endforeach; ?>
</section>
<?php endif; ?>
</div>
<?php endif; ?>
</div>
<footer class="sp-document-footer">
    <div class="sp-footer-title"><?php echo htmlspecialchars($footerTitle); ?></div>
    <div class="sp-page-number">2</div>
</footer>
</div>
</div>



<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Geom:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>

.sp-report,
.sp-report *,
.sp-report-page,
.sp-report-page *{
  font-family:'Geom',sans-serif!important;
}

.sp-page-two{display:flex;flex-direction:column}
.sp-page-two .sp-page-content{display:flex;flex:1 1 auto;min-height:0;flex-direction:column}
.sp-page-two-bottom{margin-top:auto;padding-top:16px;break-inside:avoid;page-break-inside:avoid}
.sp-financial-summary,.sp-bank-summary{width:100%;max-width:none;margin:0;border:1px solid #d9dee7;border-radius:10px;overflow:hidden;background:#fff;break-inside:avoid;page-break-inside:avoid}
.sp-financial-title,.sp-bank-title{padding:9px 14px;font-size:13px;font-weight:700;background:var(--sp-accent);color:var(--sp-secondary)}
.sp-financial-row{display:flex;justify-content:space-between;gap:24px;padding:8px 14px;border-bottom:1px solid #e7eaf0;font-size:12px;color:#111827}
.sp-financial-row:last-child{border-bottom:0}
.sp-financial-total{font-size:13px;font-weight:700;background:#f4f6f9}
.sp-bank-summary{margin-top:10px}
.sp-bank-head,.sp-bank-row{display:grid;grid-template-columns:1fr 1.65fr 1.25fr;gap:12px;padding:7px 14px;font-size:11px;color:#111827}
.sp-bank-head{font-weight:700;background:#f4f6f9;border-bottom:1px solid #d9dee7}
.sp-bank-row{border-bottom:1px solid #e7eaf0}
.sp-bank-row:last-child{border-bottom:0}
.sp-bank-head span,.sp-bank-row span{min-width:0;overflow-wrap:anywhere}
@media print{
 .sp-page-two{display:flex!important;flex-direction:column!important}
 .sp-page-two .sp-page-content{display:flex!important;flex:1 1 auto!important;flex-direction:column!important}
 .sp-page-two-bottom,.sp-financial-summary,.sp-bank-summary{display:block!important;visibility:visible!important}
 .sp-financial-title,.sp-bank-title,.sp-financial-total,.sp-bank-head{-webkit-print-color-adjust:exact;print-color-adjust:exact}
}

/* v1.0.74 - clearer work entries in the detailed work report */
.sp-work-table tbody tr:nth-child(odd) td{
  background:#f8fafc!important;
}
.sp-work-table tbody tr:nth-child(even) td{
  background:#ffffff!important;
}
.sp-work-table tbody td{
  border-bottom:1px solid #d7dde5!important;
  padding-top:10px!important;
  padding-bottom:10px!important;
}
.sp-work-table tbody tr:last-child td{
  border-bottom:0!important;
}

/* More breathing room between the two bottom summary panels */
.sp-page-two-bottom .sp-financial-summary{
  margin-bottom:18px!important;
}


/* v1.0.75 - visible alternating work rows in browser print/PDF */
.sp-work-table{
  -webkit-print-color-adjust:exact!important;
  print-color-adjust:exact!important;
}
.sp-work-table tbody tr:nth-child(odd),
.sp-work-table tbody tr:nth-child(odd) > td{
  background-color:#f0f2f5!important;
  -webkit-print-color-adjust:exact!important;
  print-color-adjust:exact!important;
}
.sp-work-table tbody tr:nth-child(even),
.sp-work-table tbody tr:nth-child(even) > td{
  background-color:#ffffff!important;
  -webkit-print-color-adjust:exact!important;
  print-color-adjust:exact!important;
}
.sp-work-table tbody tr > td{
  border-bottom:1px solid #cfd5dd!important;
  padding-top:10px!important;
  padding-bottom:10px!important;
}

/* Move the bottom financial/bank block 50px lower on the last page */
.sp-page-two-bottom{
  transform:translateY(50px)!important;
}


/* v1.0.76 - right align Duration / Cost column in detailed work report */
.sp-work-table thead th:last-child,
.sp-work-table tbody td:last-child{
  text-align:right!important;
}


/* v1.0.77 - emphasize the important financial summary */
.sp-financial-summary{
  font-size:12px!important;
}
.sp-financial-summary .sp-summary-title,
.sp-financial-summary h2,
.sp-financial-summary h3{
  font-size:14px!important;
  font-weight:700!important;
}
.sp-financial-summary table,
.sp-financial-summary td,
.sp-financial-summary th{
  font-size:12px!important;
}
.sp-financial-summary tr:last-child td,
.sp-financial-summary .sp-total-row,
.sp-financial-summary .sp-total-row td{
  font-size:13px!important;
  font-weight:700!important;
}


/* v1.0.78 - clearly larger financial summary typography */
.sp-financial-summary .sp-summary-title,
.sp-financial-summary h2,
.sp-financial-summary h3{
  font-size:16px!important;
  line-height:1.25!important;
  font-weight:800!important;
}
.sp-financial-summary table,
.sp-financial-summary tbody,
.sp-financial-summary tr,
.sp-financial-summary td,
.sp-financial-summary th{
  font-size:14px!important;
  line-height:1.35!important;
}
.sp-financial-summary td,
.sp-financial-summary th{
  padding-top:7px!important;
  padding-bottom:7px!important;
}
.sp-financial-summary tr:last-child td,
.sp-financial-summary .sp-total-row,
.sp-financial-summary .sp-total-row td{
  font-size:15px!important;
  font-weight:800!important;
}


/* v1.0.79 - requested financial summary typography */
.sp-financial-summary .sp-summary-title,
.sp-financial-summary h2,
.sp-financial-summary h3{
  font-size:19px!important;
  font-weight:800!important;
}
.sp-financial-summary table,
.sp-financial-summary tbody,
.sp-financial-summary tr,
.sp-financial-summary td,
.sp-financial-summary th{
  font-size:16px!important;
}
.sp-financial-summary tr:last-child td,
.sp-financial-summary .sp-total-row,
.sp-financial-summary .sp-total-row td{
  font-size:17px!important;
  font-weight:800!important;
}


/* v1.0.80 - direct styling of the rendered financial summary */
.sp-financial-force .sp-financial-heading-force{
  font-size:19px!important;
  line-height:1.25!important;
  font-weight:800!important;
}
.sp-financial-force .sp-financial-row,
.sp-financial-force .sp-financial-row *,
.sp-financial-force .sp-fin-row,
.sp-financial-force .sp-fin-row *,
.sp-financial-force .sp-summary-row,
.sp-financial-force .sp-summary-row *{
  font-size:16px!important;
  line-height:1.35!important;
}
.sp-financial-force .sp-financial-row,
.sp-financial-force .sp-fin-row,
.sp-financial-force .sp-summary-row{
  padding-top:8px!important;
  padding-bottom:8px!important;
}
.sp-financial-force .sp-financial-row:last-child,
.sp-financial-force .sp-financial-row:last-child *,
.sp-financial-force .sp-fin-row:last-child,
.sp-financial-force .sp-fin-row:last-child *,
.sp-financial-force .sp-summary-row:last-child,
.sp-financial-force .sp-summary-row:last-child *{
  font-size:17px!important;
  font-weight:800!important;
}
/* Fallback: all direct content rows inside the actual financial wrapper */
.sp-financial-force > div:not(.sp-financial-heading-force):not(.sp-financial-title),
.sp-financial-force > div:not(.sp-financial-heading-force):not(.sp-financial-title) *{
  font-size:16px!important;
}
@media print{
 .sp-financial-force .sp-financial-heading-force{font-size:19px!important;font-weight:800!important}
 .sp-financial-force .sp-financial-row,.sp-financial-force .sp-financial-row *,
 .sp-financial-force .sp-fin-row,.sp-financial-force .sp-fin-row *,
 .sp-financial-force .sp-summary-row,.sp-financial-force .sp-summary-row *{font-size:16px!important}
 .sp-financial-force .sp-financial-row:last-child,.sp-financial-force .sp-financial-row:last-child *,
 .sp-financial-force .sp-fin-row:last-child,.sp-financial-force .sp-fin-row:last-child *,
 .sp-financial-force .sp-summary-row:last-child,.sp-financial-force .sp-summary-row:last-child *{font-size:17px!important;font-weight:800!important}
}

</style>
