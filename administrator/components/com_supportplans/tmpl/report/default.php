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
$params = $this->params;
$brandName = trim((string)$params->get('brand_name', '')) ?: trim((string)\Joomla\CMS\Factory::getApplication()->get('sitename', ''));
if ($brandName === '') $brandName = 'Support';
$brandLogo = trim((string)$params->get('brand_logo', ''));
$pdfWatermark = trim((string)$params->get('pdf_watermark', ''));
$watermarkOpacity = max(0, min(100, (int)$params->get('watermark_opacity', 7))) / 100;
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
$logoSrc = $brandLogo !== '' ? (preg_match('#^https?://#i', $brandLogo) ? $brandLogo : rtrim(\Joomla\CMS\Uri\Uri::root(), '/') . '/' . ltrim($brandLogo, '/')) : '';
$watermarkSrc = $pdfWatermark !== '' ? (preg_match('#^https?://#i', $pdfWatermark) ? $pdfWatermark : rtrim(\Joomla\CMS\Uri\Uri::root(), '/') . '/' . ltrim($pdfWatermark, '/')) : '';
$netTotal = $subscription ? $subscriptionNet : ($ongoing ? $spent : (float)$this->plan->initial_amount);
$vatAmount = $netTotal * ($vatRate / 100);
$grossTotal = $netTotal + $vatAmount;
?>
<div class="sp-report sp-report-sheet" style="--sp-accent:<?php echo htmlspecialchars($accentColor); ?>;--sp-secondary:<?php echo htmlspecialchars($secondaryColor); ?>">
<div class="sp-no-print"><button class="btn btn-primary" onclick="window.print()">Αποθήκευση ως pdf / εκτύπωση</button></div>

<div class="sp-report-page sp-report-page-one"><?php if($watermarkSrc!==''): ?><img class="sp-pdf-watermark" style="opacity:<?php echo htmlspecialchars((string)$watermarkOpacity); ?>" src="<?php echo htmlspecialchars($watermarkSrc); ?>" alt="" aria-hidden="true"><?php endif; ?>
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
        <div class="sp-summary-card"><div class="sp-summary-label">Διάρκεια συνδρομής</div><div class="sp-summary-value"><?php echo (int)($this->plan->subscription_months??1); ?> μήν.</div></div>
        <div class="sp-summary-card"><div class="sp-summary-label">Αξία προ Φ.Π.Α.</div><div class="sp-summary-value"><?php echo number_format($subscriptionNet,2,',','.'); ?> €</div></div>
        <div class="sp-summary-card"><div class="sp-summary-label">Αξία με Φ.Π.Α. <?php echo number_format($vatRate,0); ?>%</div><div class="sp-summary-value"><?php echo number_format($subscriptionGross,2,',','.'); ?> €</div></div>
        <?php elseif($ongoing): ?>
        <div class="sp-summary-card"><div class="sp-summary-label">Πραγματικός χρόνος</div><div class="sp-summary-value"><?php echo $fmt($actualUsed); ?></div></div>
        <div class="sp-summary-card"><div class="sp-summary-label">Χρεώσιμος χρόνος</div><div class="sp-summary-value"><?php echo $fmt($used); ?></div></div>
        <div class="sp-summary-card"><div class="sp-summary-label">Κόστος προ Φ.Π.Α.</div><div class="sp-summary-value"><?php echo number_format($spent,2,',','.'); ?> €</div></div>
        <?php else: ?>
        <div class="sp-summary-card"><div class="sp-summary-label">Υπόλοιπο χρόνου</div><div class="sp-summary-value"><?php echo $fmt($rem); ?></div></div>
        <div class="sp-summary-card"><div class="sp-summary-label">Διαθέσιμο ποσό προ Φ.Π.Α.</div><div class="sp-summary-value"><?php echo number_format(max(0,(float)$this->plan->initial_amount-$spent),2,',','.'); ?> €</div></div>
        <div class="sp-summary-card"><div class="sp-summary-label">Χρησιμοποιήθηκαν</div><div class="sp-summary-value"><?php echo $fmt($used); ?></div></div>
        <?php endif; ?>
    </div>
</section>

</div>
<footer class="sp-document-footer">
    <div class="sp-footer-title"><?php echo htmlspecialchars($footerTitle); ?></div>
    <div class="sp-page-number">1</div>
</footer>
</div>

<div class="sp-report-page sp-report-page-two"><?php if($watermarkSrc!==''): ?><img class="sp-pdf-watermark" style="opacity:<?php echo htmlspecialchars((string)$watermarkOpacity); ?>" src="<?php echo htmlspecialchars($watermarkSrc); ?>" alt="" aria-hidden="true"><?php endif; ?>
<div class="sp-page-content">
<section class="sp-report-section sp-work-section">
    <h2><?php echo htmlspecialchars($workTitle); ?></h2>
    <table class="sp-work-table"><thead><tr><th>Ημ/νία</th><th>Αίτημα</th><th><?php echo $subscription?'Χρέωση':'Διάρκεια / κόστος'; ?></th></tr></thead><tbody>
    <?php foreach($this->entries as $e): ?><tr><td><?php echo htmlspecialchars($e->request_date); ?></td><td><?php echo htmlspecialchars($e->work_type); ?><?php if(trim((string)$e->description)!==''): ?><br><small><?php echo nl2br(htmlspecialchars($e->description)); ?></small><?php endif; ?></td><td><?php if($subscription): ?>Περιλαμβάνεται στη συνδρομή<?php else: ?><?php echo (int)$e->billable_minutes; ?>′ / <?php echo number_format((float)$e->cost,2,',','.'); ?> €<?php endif; ?></td></tr><?php endforeach; ?>
    <?php if (!$this->entries): ?><tr><td colspan="3">Δεν υπάρχουν καταχωρισμένες εργασίες.</td></tr><?php endif; ?>
    </tbody></table>
    <div class="sp-remaining"><?php if($subscription): ?>Σταθερή αξία συνδρομής: <?php echo number_format($subscriptionNet,2,',','.'); ?> € προ Φ.Π.Α. · <?php echo count($this->entries); ?> καταχωρίσεις εργασιών<?php elseif($ongoing): ?>Σύνολο χρεώσιμου χρόνου: <?php echo $fmt($used); ?> · Συνολική αξία εργασιών προ Φ.Π.Α.: <?php echo number_format($spent,2,',','.'); ?> €<?php else: ?>Υπολειπόμενος χρόνος υποστήριξης: <?php echo $fmt($rem); ?><?php endif; ?></div>
    <div class="sp-pdf-totals">
      <div><span>Καθαρή αξία</span><strong><?php echo number_format($netTotal,2,',','.'); ?> €</strong></div>
      <div><span>Φ.Π.Α. <?php echo number_format($vatRate,2,',','.'); ?>%</span><strong><?php echo number_format($vatAmount,2,',','.'); ?> €</strong></div>
      <div class="sp-pdf-grand-total"><span>Σύνολο με Φ.Π.Α.</span><strong><?php echo number_format($grossTotal,2,',','.'); ?> €</strong></div>
    </div>
</section>
</div>
<footer class="sp-document-footer">
    <div class="sp-footer-title"><?php echo htmlspecialchars($footerTitle); ?></div>
    <div class="sp-page-number">2</div>
</footer>
</div>
</div>
