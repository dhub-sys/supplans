<?php
\defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Support\Component\Supportplans\Site\Helper\OfferHelper;

HTMLHelper::_('stylesheet', 'com_supportplans/offers.css', ['version' => 'auto', 'relative' => true]);

$e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$o = $this->offer;
$brand = OfferHelper::brand($this->params);
$money = static fn($v) => OfferHelper::money((float) $v);
$vatRate = (float) $o->vat_rate;
$totals = OfferHelper::totals($this->items, $vatRate);
$vars = OfferHelper::offerVars($o, $brand);
$v = static fn($text) => OfferHelper::vars((string) $text, $vars);
$titleLines = array_values(array_filter(array_map('trim', explode("\n", OfferHelper::vars((string) $o->doc_title, $vars))), 'strlen'));
$footer = trim(OfferHelper::vars((string) $o->footer_title, $vars)) ?: implode(', ', $titleLines);
$pricingTitle = trim($v($o->pricing_title)) ?: 'Αξία Έργου – Τρόπος Πληρωμής';
$banks = (int) $o->show_banks ? OfferHelper::banks($this->params) : [];
$fileTitle = trim($o->offer_number . ' - ' . ($o->project_name ?: $o->customer_name), ' -');
Factory::getApplication()->getDocument()->setTitle($fileTitle);
$vatLabel = rtrim(rtrim(number_format($vatRate, 2, ',', '.'), '0'), ',');
$section = 0;
?>
<div class="sp-od<?php echo $this->preview ? ' is-preview' : ''; ?>" style="--od-accent:<?php echo $e($brand['accent']); ?>;--od-on-accent:<?php echo $e($brand['secondary']); ?>">
 <div class="sp-od-toolbar sp-od-noprint">
  <button type="button" class="sp-od-print" onclick="window.print()">Αποθήκευση ως pdf / εκτύπωση</button>
  <span>Στο παράθυρο εκτύπωσης επιλέξτε «Αποθήκευση ως PDF», χαρτί A4 και απενεργοποιήστε τις «Κεφαλίδες και υποσέλιδα».</span>
 </div>

 <div class="sp-od-pages" id="sp-od-pages">
  <section class="sp-od-page sp-od-cover">
   <div class="sp-od-cover-top">
    <?php if ($brand['logo'] !== ''): ?><img class="sp-od-logo" src="<?php echo $e($brand['logo']); ?>" alt="<?php echo $e($brand['name']); ?>"><?php else: ?><div class="sp-od-logo-text"><?php echo $e($brand['name']); ?></div><?php endif; ?>
   </div>
   <h1 class="sp-od-title"><?php foreach ($titleLines as $line): ?><span><?php echo $e($line); ?></span><?php endforeach; ?><?php if ($o->project_name !== ''): ?><span><?php echo $e($o->project_name); ?></span><?php endif; ?></h1>
   <div class="sp-od-rule"></div>
   <?php if (trim((string) $o->doc_subtitle) !== ''): ?><div class="sp-od-subtitle"><?php echo $e($v($o->doc_subtitle)); ?></div><?php endif; ?>
   <div class="sp-od-meta">
    <div><span>Αρ. Προσφοράς</span><b><?php echo $e($o->offer_number); ?></b></div>
    <div><span>Ημερομηνία Προσφοράς</span><b><?php echo $e(OfferHelper::dateGr($o->offer_date)); ?></b></div>
    <div><span>Πελάτης</span><b><?php echo $e(mb_strtoupper($o->customer_name)); ?></b></div>
   </div>
   <div class="sp-od-company">
    <b><?php echo $e($brand['name']); ?></b>
    <?php if ($brand['address'] !== ''): ?><span><?php echo nl2br($e($brand['address'])); ?></span><?php endif; ?>
    <?php if ($brand['phone'] !== '' || $brand['email'] !== ''): ?><span><?php echo $e(implode(' | ', array_filter([$brand['phone'], $brand['email']]))); ?></span><?php endif; ?>
    <?php if ($brand['website'] !== ''): ?><a href="<?php echo $e(preg_match('#^https?://#i', $brand['website']) ? $brand['website'] : 'https://' . $brand['website']); ?>"><?php echo $e($brand['website']); ?></a><?php endif; ?>
   </div>
   <footer class="sp-od-footer"><div class="sp-od-footer-text"><?php echo $e($footer); ?> <b><?php echo $e($o->project_name); ?></b></div><div class="sp-od-num">1</div></footer>
  </section>
 </div>

 <!-- Flowing content: moved into A4 pages by the paginator below. -->
 <div class="sp-od-flow" id="sp-od-flow">
  <?php foreach ($this->items as $item): if (!(int) $item->show_description) continue; $section++; ?>
  <h2 class="sp-od-h sp-o-keep<?php echo !empty($item->page_break) ? ' sp-o-break' : ''; ?>"<?php echo empty($item->page_break) ? ' style="margin-top:' . max(0, min(80, (int) ($item->space_before ?? 5))) . 'mm"' : ''; ?>><span class="sp-od-h-no"><?php echo $section; ?>.</span><span class="sp-od-h-text"><?php echo $e($v($item->section_title)); ?></span></h2>
  <?php echo OfferHelper::render((string) $item->body, $vars); ?>
  <?php endforeach; ?>

  <?php if ($totals['rows']): $section++; ?>
  <h2 class="sp-od-h sp-o-keep"><span class="sp-od-h-no"><?php echo $section; ?>.</span><span class="sp-od-h-text"><?php echo $e($pricingTitle); ?></span></h2>
  <table class="sp-od-table sp-od-price">
   <thead><tr><th>Περιγραφή Υπηρεσίας</th><th>Τιμή</th><th>Έκπτωση</th><th>Αξία</th><th>ΦΠΑ</th><th>Σύνολο</th></tr></thead>
   <tbody>
    <?php foreach ($totals['rows'] as $r): ?>
    <tr><td><?php echo $e($v($r['label'])); ?></td><td><?php echo $money($r['price']); ?></td><td><?php echo $r['discount'] > 0 ? '-' : ''; ?><?php echo $money($r['discount']); ?></td><td><?php echo $money($r['net']); ?></td><td><?php echo $money($r['vat']); ?></td><td><?php echo $money($r['gross']); ?></td></tr>
    <?php endforeach; ?>
    <tr class="sp-od-total"><td>ΣΥΝΟΛΟ</td><td><?php echo $money($totals['price']); ?></td><td><?php echo $totals['discount'] > 0 ? '-' : ''; ?><?php echo $money($totals['discount']); ?></td><td><?php echo $money($totals['net']); ?></td><td><?php echo $money($totals['vat']); ?></td><td><?php echo $money($totals['gross']); ?></td></tr>
   </tbody>
  </table>
  <p>Η τελική τιμή για την παροχή των άνω περιγραφόμενων υπηρεσιών ορίζεται στα <strong>€ <?php echo $money($totals['net']); ?></strong> πλέον Φ.Π.Α. <?php echo $e($vatLabel); ?>% <strong>€ <?php echo $money($totals['vat']); ?></strong>, σύνολο <strong>€ <?php echo $money($totals['gross']); ?></strong><?php if ($totals['discount'] > 0): ?> και περιλαμβάνει συνολική έκπτωση στην τιμή προ ΦΠΑ <strong>€ <?php echo $money($totals['discount']); ?></strong><?php endif; ?>.</p>
  <?php endif; ?>

  <?php if (trim((string) $o->payment_text) !== ''): ?>
  <?php if (!$totals['rows']): $section++; ?><h2 class="sp-od-h sp-o-keep"><span class="sp-od-h-no"><?php echo $section; ?>.</span><span class="sp-od-h-text"><?php echo $e($pricingTitle); ?></span></h2><?php endif; ?>
  <?php echo OfferHelper::render((string) $o->payment_text, $vars); ?>
  <?php endif; ?>

  <?php if ($banks): ?>
  <table class="sp-od-table sp-od-banks">
   <thead><tr><th>ΤΡΑΠΕΖΑ</th><th>IBAN</th><th>ΔΙΚΑΙΟΥΧΟΣ</th></tr></thead>
   <tbody><?php foreach ($banks as $b): ?><tr><td><b><?php echo $e(mb_strtoupper($b['name'])); ?></b></td><td><?php echo $e($b['iban']); ?></td><td><?php echo $e($b['beneficiary']); ?></td></tr><?php endforeach; ?></tbody>
  </table>
  <?php endif; ?>

  <?php if (trim((string) $o->terms_body) !== ''): $section++; ?>
  <h2 class="sp-od-h sp-o-keep"><span class="sp-od-h-no"><?php echo $section; ?>.</span><span class="sp-od-h-text"><?php echo $e(trim($v($o->terms_title)) ?: 'Όροι Προσφοράς'); ?></span></h2>
  <?php echo OfferHelper::render((string) $o->terms_body, $vars); ?>
  <?php endif; ?>

  <?php if ($brand['signature'] !== ''): ?>
  <div class="sp-od-signature"><img src="<?php echo $e($brand['signature']); ?>" alt=""></div>
  <?php endif; ?>
 </div>

 <template id="sp-od-page-template">
  <section class="sp-od-page"><div class="sp-od-body"></div><footer class="sp-od-footer"><div class="sp-od-footer-text"><?php echo $e($footer); ?> <b><?php echo $e($o->project_name); ?></b></div><div class="sp-od-num"></div></footer></section>
 </template>
</div>
<script>
(function () {
  var pages = document.getElementById('sp-od-pages');
  var flow = document.getElementById('sp-od-flow');
  var tpl = document.getElementById('sp-od-page-template');
  var body = null;
  var SAFETY = 6; // px kept free at the bottom of every page for print rendering differences

  function newPage() {
    var page = tpl.content.firstElementChild.cloneNode(true);
    pages.appendChild(page);
    page.querySelector('.sp-od-num').textContent = pages.children.length;
    body = page.querySelector('.sp-od-body');
  }
  // scrollHeight never drops below clientHeight, so measure where the content actually ends.
  function overflows() {
    var last = body.lastElementChild;
    if (!last) return false;
    var bottom = last.getBoundingClientRect().bottom + (parseFloat(getComputedStyle(last).marginBottom) || 0);
    return bottom - body.getBoundingClientRect().top > body.clientHeight - SAFETY;
  }
  function splittable(el) {
    if (el.matches('ul,ol')) return el.children.length > 1;
    if (el.matches('table')) return el.tBodies.length && el.tBodies[0].rows.length > 1;
    return false;
  }
  // Moves the trailing children of `el` into a new element until `el` fits; returns the remainder.
  function split(el) {
    var rest = el.cloneNode(false);
    var from = el, to = rest;
    if (el.matches('table')) {
      if (el.tHead) rest.appendChild(el.tHead.cloneNode(true));
      to = document.createElement('tbody');
      rest.appendChild(to);
      from = el.tBodies[0];
    }
    while (overflows() && from.children.length > 1) to.insertBefore(from.lastElementChild, to.firstChild);
    if (overflows()) { // not even one row/item fits: put everything back and move the whole block
      while (to.firstElementChild) from.appendChild(to.firstElementChild);
      return null;
    }
    if (el.matches('ol') && !el.hasAttribute('start')) rest.setAttribute('start', String(from.children.length + 1));
    else if (el.matches('ol')) rest.setAttribute('start', String(parseInt(el.getAttribute('start'), 10) + from.children.length));
    return rest;
  }
  function place(el) {
    // Sections marked "new page" start on a fresh page (unless the current one is still empty).
    if (el.classList.contains('sp-o-break') && body.children.length) newPage();
    body.appendChild(el);
    if (!overflows()) return;
    if (splittable(el)) {
      var rest = split(el);
      if (rest) { newPage(); place(rest); return; }
    }
    // Move the block (and a heading that would be left alone) to a new page.
    var carry = [el];
    var prev = el.previousElementSibling;
    while (prev && prev.classList.contains('sp-o-keep')) { carry.unshift(prev); prev = prev.previousElementSibling; }
    if (carry.length === body.children.length) return; // already first on its page: let it be
    newPage();
    carry.forEach(function (node) { body.appendChild(node); });
  }
  function paginate() {
    var blocks = Array.prototype.slice.call(flow.children);
    newPage();
    blocks.forEach(place);
    flow.remove();
    if (body && !body.children.length) pages.removeChild(pages.lastElementChild);
  }
  var images = Array.prototype.slice.call(document.querySelectorAll('.sp-od img')).map(function (img) {
    return img.complete ? Promise.resolve() : new Promise(function (r) { img.addEventListener('load', r); img.addEventListener('error', r); });
  });
  var fonts = document.fonts && document.fonts.ready ? document.fonts.ready : Promise.resolve();
  window.spOfferReady = Promise.all(images.concat([fonts])).then(paginate);
})();
</script>
