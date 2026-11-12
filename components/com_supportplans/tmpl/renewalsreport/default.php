<?php
\defined('_JEXEC') or die;
use Support\Component\Supportplans\Site\Helper\Notify;
use Support\Component\Supportplans\Site\Helper\OfferHelper;

$e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$m = static fn($v) => number_format((float) $v, 2, ',', '.') . ' €';
$brand = OfferHelper::brand($this->params);
$d = $this->data;
$client = static fn($r) => trim((string) ($r->client_name ?: $r->client_company)) ?: (string) $r->client_email;
$sum = static fn(array $rows, string $k) => array_sum(array_map(static fn($r) => (float) $r->$k, $rows));
$range = static fn(array $r) => Notify::dateGr($r[0]) . ($r[0] !== $r[1] ? ' – ' . Notify::dateGr($r[1]) : '');
$has = fn($k) => in_array($k, $this->parts, true);
?>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Geom:wght@300..800&amp;display=swap" rel="stylesheet">
<style>
@page{size:A4;margin:14mm 12mm 16mm}
html,body{background:#e9ecf1}
.rr{--acc:<?php echo $e($brand['accent'] ?: '#367d22'); ?>;font-family:'Geom',Arial,Helvetica,sans-serif;color:#111;max-width:210mm;margin:18px auto 40px;background:#fff;padding:14mm 12mm;box-shadow:0 4px 22px rgba(16,24,40,.12);box-sizing:border-box}
.rr *{font-family:inherit;box-sizing:border-box}
.rr-bar{max-width:210mm;margin:18px auto 0;display:flex;gap:12px;align-items:center;font:13px Arial,sans-serif;color:#475467}
.rr-bar button{background:#2563eb;color:#fff;border:0;border-radius:9px;padding:10px 16px;font-weight:700;font-size:14px;cursor:pointer}
.rr-head{display:flex;justify-content:space-between;align-items:flex-start;gap:20px;border-bottom:1.6mm solid var(--acc);padding-bottom:5mm;margin-bottom:6mm}
.rr-head img{max-height:16mm;max-width:60mm}
.rr-head h1{margin:0;font-size:20pt;font-weight:600}
.rr-head p{margin:2mm 0 0;font-size:10pt;color:#475467}
.rr-cards{display:grid;grid-template-columns:repeat(3,1fr);gap:4mm;margin-bottom:7mm}
.rr-card{border:1px solid #e4e7ec;border-top:1.2mm solid var(--c);border-radius:2.5mm;padding:3.5mm 4mm;-webkit-print-color-adjust:exact;print-color-adjust:exact}
.rr-card span{display:block;font-size:9pt;font-weight:600;color:#475467}
.rr-card strong{display:block;font-size:15pt;margin:1mm 0}
.rr-card small{font-size:8pt;color:#667085}
.rr h2{font-size:12.5pt;font-weight:600;margin:6mm 0 2.5mm;display:flex;justify-content:space-between;align-items:baseline;gap:10px}
.rr h2 small{font-size:9pt;color:#667085;font-weight:400}
.rr table{width:100%;border-collapse:collapse;font-size:8.8pt;line-height:1.3}
.rr thead{display:table-header-group}
.rr tr{break-inside:avoid}
.rr th{text-align:left;font-weight:700;background:#f4f6f9;border-bottom:1px solid #d0d5dd;padding:1.6mm 2mm;-webkit-print-color-adjust:exact;print-color-adjust:exact}
.rr td{border-bottom:1px solid #eef0f3;padding:1.5mm 2mm;vertical-align:top}
.rr td.n,.rr th.n{text-align:right;white-space:nowrap}
.rr tfoot td{font-weight:700;border-top:1.2px solid #111;border-bottom:0}
.rr small.mut{color:#667085}
.rr .empty{color:#667085;font-size:9.5pt;margin:0 0 4mm}
.rr-foot{margin-top:8mm;font-size:8pt;color:#667085;border-top:1px solid #d0d5dd;padding-top:2mm}
@media print{html,body{background:#fff}.rr-bar{display:none}.rr{box-shadow:none;margin:0;max-width:none;padding:0}}
</style>
<div class="rr-bar"><button type="button" onclick="window.print()">Αποθήκευση ως pdf / εκτύπωση</button><span>Επιλέξτε «Αποθήκευση ως PDF», χαρτί A4, χωρίς «Κεφαλίδες και υποσέλιδα».</span></div>
<div class="rr">
 <div class="rr-head">
  <div><h1>Αναφορά ανανεώσεων</h1><p>Περίοδος: <b><?php echo $e($this->periodLabel); ?></b> · Δημιουργία: <?php echo $e(Notify::datetimeGr(gmdate('Y-m-d H:i:s'))); ?></p></div>
  <?php if ($brand['logo'] !== ''): ?><img src="<?php echo $e($brand['logo']); ?>" alt="<?php echo $e($brand['name']); ?>"><?php else: ?><strong><?php echo $e($brand['name']); ?></strong><?php endif; ?>
 </div>
 <div class="rr-cards">
  <?php if ($has('paid')): ?><div class="rr-card" style="--c:#16a34a"><span>Πληρώθηκαν</span><strong><?php echo $m($sum($d['paid'], 'gross')); ?></strong><small><?php echo count($d['paid']); ?> πληρωμές · <?php echo $e($range($d['paidRange'])); ?> · με ΦΠΑ</small></div><?php endif; ?>
  <?php if ($has('due')): ?><div class="rr-card" style="--c:#2563eb"><span>Προς πληρωμή</span><strong><?php echo $m($sum($d['due'], 'gross')); ?></strong><small><?php echo count($d['due']); ?> υπηρεσίες · <?php echo $e($range($d['dueRange'])); ?> · με ΦΠΑ</small></div><?php endif; ?>
  <?php if ($has('overdue')): ?><div class="rr-card" style="--c:#dc2626"><span>Ληγμένες, απλήρωτες</span><strong><?php echo $m($sum($d['overdue'], 'gross')); ?></strong><small><?php echo count($d['overdue']); ?> υπηρεσίες · με ΦΠΑ</small></div><?php endif; ?>
 </div>

 <?php if ($has('paid')): ?>
 <h2>Πληρωμές <small><?php echo $e($range($d['paidRange'])); ?></small></h2>
 <?php if (!$d['paid']): ?><p class="empty">Καμία πληρωμή στην περίοδο.</p><?php else: ?>
 <table><thead><tr><th>Πληρωμή</th><th>Πελάτης</th><th>Υπηρεσία</th><th>Λήξη πριν → νέα</th><th class="n">Ποσό</th></tr></thead><tbody>
 <?php foreach ($d['paid'] as $r): ?><tr><td><?php echo $e(Notify::dateGr($r->paid_on)); ?></td><td><?php echo $e($client($r)); ?></td><td><?php echo $e($r->service_name); ?><?php if ((string) $r->label !== ''): ?> <small class="mut">(<?php echo $e($r->label); ?>)</small><?php endif; ?><?php if ((string) $r->note !== ''): ?><br><small class="mut"><?php echo $e($r->note); ?></small><?php endif; ?></td><td><?php echo $e(Notify::dateGr($r->old_expiry) . ' → ' . Notify::dateGr($r->new_expiry)); ?></td><td class="n"><?php echo $m($r->gross); ?></td></tr><?php endforeach; ?>
 </tbody><tfoot><tr><td colspan="4">Σύνολο (με ΦΠΑ)</td><td class="n"><?php echo $m($sum($d['paid'], 'gross')); ?></td></tr></tfoot></table>
 <?php endif; endif; ?>

 <?php foreach (['due' => 'Λήξεις — προς πληρωμή', 'overdue' => 'Ληγμένες, απλήρωτες'] as $k => $title): if (!$has($k)) continue; ?>
 <h2><?php echo $e($title); ?> <?php if ($k === 'due'): ?><small><?php echo $e($range($d['dueRange'])); ?></small><?php endif; ?></h2>
 <?php if (!$d[$k]): ?><p class="empty"><?php echo $k === 'due' ? 'Καμία υπηρεσία δεν λήγει στην περίοδο.' : 'Καμία ληγμένη απλήρωτη υπηρεσία.'; ?></p><?php else: ?>
 <table><thead><tr><th>Λήξη</th><th>Πελάτης</th><th>Υπηρεσία</th><th class="n">Προ ΦΠΑ</th><th class="n">Με ΦΠΑ</th></tr></thead><tbody>
 <?php foreach ($d[$k] as $r): ?><tr><td><?php echo $e(Notify::dateGr($r->expiry_date)); ?><?php if ($k === 'overdue'): ?><br><small class="mut">πριν <?php echo (int) $r->days; ?> ημ.</small><?php endif; ?></td><td><?php echo $e($client($r)); ?><br><small class="mut"><?php echo $e($r->client_email); ?></small></td><td><?php echo $e($r->service_name); ?><?php if ((string) $r->label !== ''): ?> <small class="mut">(<?php echo $e($r->label); ?>)</small><?php endif; ?></td><td class="n"><?php echo $m($r->net); ?></td><td class="n"><?php echo $m($r->gross); ?></td></tr><?php endforeach; ?>
 </tbody><tfoot><tr><td colspan="3">Σύνολο</td><td class="n"><?php echo $m($sum($d[$k], 'net')); ?></td><td class="n"><?php echo $m($sum($d[$k], 'gross')); ?></td></tr></tfoot></table>
 <?php endif; endforeach; ?>
 <div class="rr-foot"><?php echo $e($brand['name']); ?> · Αναφορά ανανεώσεων · ΦΠΑ <?php echo $e(Notify::vatLabel()); ?></div>
</div>
