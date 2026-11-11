<?php
\defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
use Support\Component\Supportplans\Site\Helper\OfferHelper;
use Support\Component\Supportplans\Site\Helper\Structure;

$e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$s = $this->selected;
$docUrl = OfferHelper::url('view=structuredoc&id=' . (int) $s->id);
$fileTitle = trim($s->number . ' - Πρόταση δομής - ' . ($s->project_name ?: $s->customer_name), ' -');
$nodes = Structure::nodes($s->tree);
$label = static fn(string $f) => $e(trim((string) $s->$f));
$ph = static fn(string $f) => $e(Structure::DEFAULTS[$f]);
// "Continue on the same page, N mm after the previous section" or "Start on a new page".
$layoutPick = static function (string $key) use ($s, $e): string {
    $break = (int) ($s->{$key . '_break'} ?? ($key === 'content' ? 1 : 0));
    $space = (int) ($s->{$key . '_space'} ?? ($key === 'intro' ? 5 : 8));
    $label = Structure::SECTIONS[$key];
    return '<div class="sp-section-layout" data-layout-pick><span class="sp-field-label">' . $e($label) . ' στο Pdf</span><div class="sp-oi-layout">'
        . '<select name="' . $key . '_break" aria-label="' . $e($label) . ': θέση στο Pdf"><option value="0"' . ($break ? '' : ' selected') . '>Συνέχεια στην ίδια σελίδα</option><option value="1"' . ($break ? ' selected' : '') . '>Ξεκινά σε νέα σελίδα</option></select>'
        . '<label class="sp-oi-space" data-space' . ($break ? ' hidden' : '') . '>απόσταση <input type="number" min="0" max="80" step="1" name="' . $key . '_space" value="' . $space . '" aria-label="Απόσταση από την προηγούμενη ενότητα σε mm"> mm</label></div></div>';
};
?>
<div class="sp-standalone-actions sp-offer-actions">
 <button type="button" class="btn btn-primary" onclick="spSubmit('structure.apply')">Αποθήκευση</button>
 <button type="button" class="btn btn-outline-secondary" onclick="spSubmit('structure.save')">Αποθήκευση &amp; κλείσιμο</button>
 <button type="button" class="btn btn-secondary" onclick="spPrintDoc()" title="Αποθηκεύστε πρώτα τις αλλαγές">Εκτύπωση / Pdf</button>
 <button type="button" class="btn btn-outline-secondary" onclick="spPreviewDoc()" title="Προβολή του εγγράφου με τις τρέχουσες αλλαγές, χωρίς αποθήκευση">Προεπισκόπηση</button>
 <a class="btn btn-outline-secondary" href="<?php echo OfferHelper::url('view=structures'); ?>">Κλείσιμο</a>
</div>
<iframe id="sp-doc-print-frame" title="Πρόταση δομής" src="<?php echo $docUrl; ?>" style="position:fixed;width:1px;height:1px;right:0;bottom:0;border:0;opacity:0;pointer-events:none;" aria-hidden="true"></iframe>
<dialog class="sp-preview" id="sp-preview" aria-label="Προεπισκόπηση πρότασης δομής">
 <div class="sp-preview-bar">
  <div><strong>Προεπισκόπηση</strong><span id="sp-preview-note">Με τις τρέχουσες αλλαγές — δεν έχουν αποθηκευτεί ακόμα.</span></div>
  <div class="sp-preview-actions">
   <button type="button" class="btn btn-outline-secondary btn-sm" onclick="spPreviewDoc()">↻ Ανανέωση</button>
   <button type="button" class="btn btn-secondary btn-sm" onclick="spPrintPreview()">Εκτύπωση / Pdf</button>
   <button type="button" class="btn btn-primary btn-sm" onclick="spSubmit('structure.apply')">Αποθήκευση</button>
   <button type="button" class="sp-preview-close" onclick="document.getElementById('sp-preview').close()" aria-label="Κλείσιμο">×</button>
  </div>
 </div>
 <div class="sp-preview-body"><div class="sp-preview-loading" id="sp-preview-loading">Δημιουργία προεπισκόπησης…</div><iframe name="sp-preview-frame" id="sp-preview-frame" title="Προεπισκόπηση πρότασης δομής"></iframe></div>
</dialog>

<form action="<?php echo $e(OfferHelper::url('view=structures&id=' . (int) $s->id, false)); ?>" method="post" name="adminForm" id="adminForm">
 <div class="sp-card">
  <div class="sp-title-row"><div><h2>Πρόταση δομής <?php echo $e($s->number); ?></h2><div class="sp-plan-subtitle"><?php echo $e($s->customer_name . ($s->project_name ? ' · ' . $s->project_name : '')); ?></div></div><span class="sp-offer-status is-<?php echo $e($s->status); ?>"><?php echo $e(OfferHelper::statusLabel($s->status)); ?></span></div>
  <div class="sp-grid sp-offer-head-grid">
   <label>Αρ. πρότασης<input name="number" value="<?php echo $e($s->number); ?>" required></label>
   <label>Ημερομηνία<input type="date" name="proposal_date" value="<?php echo $e($s->proposal_date); ?>" required></label>
   <label>Πελάτης<input name="customer_name" value="<?php echo $e($s->customer_name); ?>" required></label>
   <label>Έργο / Ιστοσελίδα<input name="project_name" value="<?php echo $e($s->project_name); ?>" placeholder="Εμφανίζεται και ως ρίζα του δέντρου"></label>
   <label>Κατάσταση<select name="status"><?php foreach (OfferHelper::STATUSES as $k => $l): ?><option value="<?php echo $k; ?>" <?php echo $s->status === $k ? 'selected' : ''; ?>><?php echo $e($l); ?></option><?php endforeach; ?></select></label>
  </div>
 </div>

 <div class="sp-card">
  <div class="sp-title-row"><h3>Sitemap — ενότητες &amp; υποενότητες</h3><div class="sp-oi-all"><button type="button" class="btn btn-sm btn-outline-secondary" data-st-expand>Άνοιγμα όλων</button><button type="button" class="btn btn-sm btn-outline-secondary" data-st-collapse>Κλείσιμο όλων</button></div></div>
  <p class="sp-foot">Κάθε γραμμή είναι μια σελίδα ή ενότητα. Με τα βελάκια <b>←</b> <b>→</b> τη μετατρέπετε σε υποενότητα της από πάνω ή την ανεβάζετε επίπεδο (έως 5 επίπεδα), με τα <b>↑</b> <b>↓</b> αλλάζετε σειρά — μαζί μετακινούνται και οι υποενότητές της. Στο πεδίο τίτλου: <kbd>Enter</kbd> νέα ενότητα από κάτω, <kbd>Tab</kbd> / <kbd>Shift+Tab</kbd> επίπεδο. Πατήστε <b>Περιεχόμενο</b> για τον στόχο και τις προτάσεις περιεχομένου της ενότητας.</p>
  <div class="sp-st" id="sp-st" data-max-level="<?php echo Structure::MAX_LEVEL; ?>"></div>
  <div class="sp-oi-add">
   <button type="button" class="btn btn-outline-primary" data-st-add-root>＋ Κύρια ενότητα</button>
   <span class="sp-st-summary" id="sp-st-summary"></span>
  </div>
  <script type="application/json" id="sp-st-data"><?php echo json_encode($nodes, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP); ?></script>
  <input type="hidden" name="tree" id="sp-st-tree" value="">
 </div>

 <div class="sp-card">
  <h3>Κείμενα εγγράφου</h3>
  <div class="sp-grid">
   <label class="sp-wide">Τίτλος εξωφύλλου (μία γραμμή ανά σειρά — το Έργο προστίθεται ως τελευταία γραμμή)<textarea name="doc_title" rows="2" placeholder="<?php echo $ph('doc_title'); ?>"><?php echo $label('doc_title'); ?></textarea></label>
   <label class="sp-wide">Υπότιτλος εξωφύλλου<input name="doc_subtitle" value="<?php echo $label('doc_subtitle'); ?>" placeholder="π.χ. SITEMAP &amp; ΠΡΟΤΑΣΕΙΣ ΠΕΡΙΕΧΟΜΕΝΟΥ"></label>
   <div class="sp-wide sp-st-pair"><label>Τίτλος εισαγωγής<input name="intro_title" value="<?php echo $label('intro_title'); ?>" placeholder="<?php echo $ph('intro_title'); ?>"></label> <?php echo $layoutPick('intro'); ?></div>
   <div class="sp-wide sp-field"><span class="sp-field-label">Εισαγωγή (πρώτη ενότητα μετά το εξώφυλλο — αν μείνει κενή δεν εμφανίζεται)</span><textarea name="intro_body" rows="5" data-sp-editor><?php echo $e($s->intro_body); ?></textarea></div>
   <div class="sp-wide sp-st-pair"><label>Τίτλος sitemap<input name="sitemap_title" value="<?php echo $label('sitemap_title'); ?>" placeholder="<?php echo $ph('sitemap_title'); ?>"></label> <?php echo $layoutPick('sitemap'); ?></div>
   <div class="sp-wide sp-st-pair"><label>Τίτλος προτάσεων περιεχομένου<input name="content_title" value="<?php echo $label('content_title'); ?>" placeholder="<?php echo $ph('content_title'); ?>"></label> <?php echo $layoutPick('content'); ?></div>
   <div class="sp-wide sp-st-pair"><label>Τίτλος κλεισίματος<input name="outro_title" value="<?php echo $label('outro_title'); ?>" placeholder="<?php echo $ph('outro_title'); ?>"></label> <?php echo $layoutPick('outro'); ?></div>
   <div class="sp-wide sp-field"><span class="sp-field-label">Κλείσιμο (τελευταία ενότητα, π.χ. επόμενα βήματα, υλικό που χρειαζόμαστε — αν μείνει κενό δεν εμφανίζεται)</span><textarea name="outro_body" rows="5" data-sp-editor><?php echo $e($s->outro_body); ?></textarea></div>
  </div>
 </div>
 <div class="sp-card">
  <h3>Εσωτερικές σημειώσεις</h3>
  <div class="sp-grid"><label class="sp-wide">Δεν εμφανίζονται στο Pdf<textarea name="notes" rows="3"><?php echo $e($s->notes); ?></textarea></label></div>
 </div>
 <input type="hidden" name="id" value="<?php echo (int) $s->id; ?>">
 <input type="hidden" name="task" value="">
 <?php echo HTMLHelper::_('form.token'); ?>
</form>
<div class="sp-card sp-offer-danger">
 <form method="post" action="<?php echo $e(OfferHelper::url('task=structure.duplicate', false)); ?>"><input type="hidden" name="id" value="<?php echo (int) $s->id; ?>"><?php echo HTMLHelper::_('form.token'); ?><button class="btn btn-outline-primary">Αντίγραφο ως νέα πρόταση</button></form>
 <form method="post" action="<?php echo $e(OfferHelper::url('task=structure.delete', false)); ?>" onsubmit="return confirm('Να διαγραφεί οριστικά η πρόταση δομής;');"><input type="hidden" name="id" value="<?php echo (int) $s->id; ?>"><?php echo HTMLHelper::_('form.token'); ?><button class="btn btn-outline-danger">Διαγραφή πρότασης</button></form>
</div>
<script>window.spDocFileTitle = <?php echo json_encode($fileTitle, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG); ?>;</script>
