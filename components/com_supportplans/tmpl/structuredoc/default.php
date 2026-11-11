<?php
\defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Support\Component\Supportplans\Site\Helper\Assets;
use Support\Component\Supportplans\Site\Helper\OfferHelper;
use Support\Component\Supportplans\Site\Helper\Structure;

echo Assets::css('offers.css', 'structure.css');

$e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$d = $this->doc;
$brand = OfferHelper::brand($this->params);
$vars = OfferHelper::offerVars((object) ['customer_name' => $d->customer_name, 'project_name' => $d->project_name, 'offer_number' => $d->number, 'offer_date' => $d->proposal_date], $brand, $this->params);
$v = static fn($text) => OfferHelper::vars((string) $text, $vars);
$nodes = Structure::treeRows(Structure::trails(Structure::numbered(Structure::nodes($d->tree))));
$counts = Structure::counts($nodes);
$project = trim((string) $d->project_name);
$titleLines = array_values(array_filter(array_map('trim', explode("\n", $v(Structure::label($d, 'doc_title')))), 'strlen'));
$footer = implode(', ', $titleLines);
$headerText = trim('Πρόταση δομής ' . $d->number . ($project !== '' ? ' · ' . $project : ''));
// Header geometry as in the offers (default logo 28px, 6mm gap).
$contentTopMm = round(8 + max($brand['logo'] !== '' ? 28 * 0.2646 : 0, 4.6) + 1.9 + 6, 2);
$headerVars = ';--od-header-logo:28px;--od-content-top:' . $contentTopMm . 'mm;--od-content-height:' . round(271 - $contentTopMm, 2) . 'mm';
$fileTitle = trim($d->number . ' - Πρόταση δομής - ' . ($project ?: $d->customer_name), ' -');
Factory::getApplication()->getDocument()->setTitle($fileTitle);
$withContent = array_values(array_filter($nodes, static fn($n) => $n['purpose'] !== '' || $n['content'] !== ''));
$section = 0;
$root = $project !== '' ? $project : ($d->customer_name ?: 'Ιστοσελίδα');
$h2 = static function (string $title, string $extra = '') use (&$section, $e): string {
    $section++;
    return '<h2 class="sp-od-h sp-o-keep' . $extra . '"><span class="sp-od-h-no">' . $section . '.</span><span class="sp-od-h-text">' . $e($title) . '</span></h2>';
};
?>
<div class="sp-od sp-sd<?php echo $this->preview ? ' is-preview' : ''; ?>" style="--od-accent:<?php echo $e($brand['accent']); ?>;--od-on-accent:<?php echo $e($brand['secondary']); ?><?php echo $headerVars; ?>">
 <div class="sp-od-toolbar sp-od-noprint">
  <button type="button" class="sp-od-print" onclick="window.print()">Αποθήκευση ως pdf / εκτύπωση</button>
  <span>Στο παράθυρο εκτύπωσης επιλέξτε «Αποθήκευση ως PDF», χαρτί A4 και απενεργοποιήστε τις «Κεφαλίδες και υποσέλιδα».</span>
 </div>

 <div class="sp-od-pages" id="sp-od-pages">
  <section class="sp-od-page sp-od-cover">
   <div class="sp-od-cover-top">
    <?php if ($brand['logo'] !== ''): ?><img class="sp-od-logo" src="<?php echo $e($brand['logo']); ?>" alt="<?php echo $e($brand['name']); ?>"><?php else: ?><div class="sp-od-logo-text"><?php echo $e($brand['name']); ?></div><?php endif; ?>
   </div>
   <h1 class="sp-od-title"><?php foreach ($titleLines as $line): ?><span><?php echo $e($line); ?></span><?php endforeach; ?><?php if ($project !== ''): ?><span><?php echo $e($project); ?></span><?php endif; ?></h1>
   <div class="sp-od-rule"></div>
   <?php if (trim((string) $d->doc_subtitle) !== ''): ?><div class="sp-od-subtitle"><?php echo $e($v($d->doc_subtitle)); ?></div><?php endif; ?>
   <div class="sp-od-meta">
    <div><span>Αρ. Πρότασης</span><b><?php echo $e($d->number); ?></b></div>
    <div><span>Ημερομηνία</span><b><?php echo $e(OfferHelper::dateGr($d->proposal_date)); ?></b></div>
    <div><span>Πελάτης</span><b><?php echo $e(OfferHelper::upperGr((string) $d->customer_name)); ?></b></div>
   </div>
   <div class="sp-od-company">
    <b><?php echo $e($brand['name']); ?></b>
    <?php if ($brand['address'] !== ''): ?><span><?php echo nl2br($e($brand['address'])); ?></span><?php endif; ?>
    <?php if ($brand['phone'] !== '' || $brand['email'] !== ''): ?><span><?php echo $e(implode(' | ', array_filter([$brand['phone'], $brand['email']]))); ?></span><?php endif; ?>
    <?php if ($brand['website'] !== ''): ?><a href="<?php echo $e(preg_match('#^https?://#i', $brand['website']) ? $brand['website'] : 'https://' . $brand['website']); ?>"><?php echo $e($brand['website']); ?></a><?php endif; ?>
   </div>
   <footer class="sp-od-footer"><div class="sp-od-footer-text"><?php echo $e($footer); ?> <b><?php echo $e($project); ?></b></div><div class="sp-od-num">1</div></footer>
  </section>
 </div>

 <!-- Flowing content: moved into A4 pages by the paginator. -->
 <div class="sp-od-flow" id="sp-od-flow">
  <?php if (trim((string) $d->intro_body) !== ''): ?>
  <?php echo $h2($v(Structure::label($d, 'intro_title'))); ?>
  <?php echo OfferHelper::render((string) $d->intro_body, $vars); ?>
  <?php endif; ?>

  <?php if ($nodes): ?>
  <?php echo $h2($v(Structure::label($d, 'sitemap_title')), $section ? ' sp-sd-gap' : ''); ?>
  <div class="sp-sm-stats sp-o-keep">
   <div><b><?php echo $counts['sections']; ?></b><span><?php echo $counts['sections'] === 1 ? 'κύρια ενότητα' : 'κύριες ενότητες'; ?></span></div>
   <div><b><?php echo $counts['pages']; ?></b><span><?php echo $counts['pages'] === 1 ? 'σελίδα συνολικά' : 'σελίδες συνολικά'; ?></span></div>
   <div><b><?php echo $counts['depth']; ?></b><span><?php echo $counts['depth'] === 1 ? 'επίπεδο πλοήγησης' : 'επίπεδα πλοήγησης'; ?></span></div>
  </div>
  <ul class="sp-sm">
   <li class="sp-sm-row is-root is-parent"><span class="sp-sm-node"><span class="sp-sm-mark"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.6 2.6 3.9 5.6 3.9 9s-1.3 6.4-3.9 9c-2.6-2.6-3.9-5.6-3.9-9S9.4 5.6 12 3z"/></svg></span><span class="sp-sm-title"><?php echo $e($root); ?></span></span></li>
   <?php foreach ($nodes as $n): $l = $n['level']; ?>
   <li class="sp-sm-row is-l<?php echo min($l, 2); ?><?php echo $n['parent'] ? ' is-parent' : ''; ?>" style="--l:<?php echo $l; ?>">
    <?php foreach ($n['rails'] as $c => $on): ?><span class="sp-sm-rail<?php echo $on ? ' is-on' : ''; ?>" style="--c:<?php echo $c; ?>"></span><?php endforeach; ?>
    <span class="sp-sm-elbow<?php echo $n['last'] ? ' is-last' : ''; ?>"></span>
    <span class="sp-sm-node"><span class="sp-sm-mark"><?php echo $l === 0 ? $e($n['number']) : ''; ?></span><?php if ($l > 0): ?><span class="sp-sm-no"><?php echo $e($n['number']); ?></span><?php endif; ?><span class="sp-sm-title"><?php echo $e($n['title'] !== '' ? $n['title'] : '—'); ?></span></span>
   </li>
   <?php endforeach; ?>
  </ul>
  <?php endif; ?>

  <?php if ($withContent): ?>
  <?php echo $h2($v(Structure::label($d, 'content_title')), ' sp-o-break'); ?>
  <?php foreach ($withContent as $n): ?>
  <div class="sp-sd-node sp-o-keep is-l<?php echo min($n['level'], 2); ?>">
   <?php if ($n['trail']): ?><div class="sp-sd-trail"><?php echo $e(implode(' › ', $n['trail'])); ?></div><?php endif; ?>
   <h3 class="sp-sd-title"><span class="sp-sd-no"><?php echo $e($n['number']); ?></span><span><?php echo $e($n['title'] !== '' ? $n['title'] : '—'); ?></span></h3>
  </div>
  <?php if ($n['purpose'] !== ''): ?><div class="sp-sd-purpose<?php echo $n['content'] !== '' ? ' sp-o-keep' : ''; ?>"><span>ΣΤΟΧΟΣ</span><?php echo $e($v($n['purpose'])); ?></div><?php endif; ?>
  <?php echo OfferHelper::render($n['content'], $vars); ?>
  <?php endforeach; ?>
  <?php endif; ?>

  <?php if (trim((string) $d->outro_body) !== ''): ?>
  <?php echo $h2($v(Structure::label($d, 'outro_title')), ' sp-sd-gap'); ?>
  <?php echo OfferHelper::render((string) $d->outro_body, $vars); ?>
  <?php endif; ?>

  <?php if ($brand['signature'] !== ''): ?>
  <div class="sp-od-signature"><img src="<?php echo $e($brand['signature']); ?>" alt=""></div>
  <?php endif; ?>
 </div>

 <template id="sp-od-page-template">
  <section class="sp-od-page has-header"><header class="sp-od-header"><?php if ($brand['logo'] !== ''): ?><img class="sp-od-header-logo" src="<?php echo $e($brand['logo']); ?>" alt="<?php echo $e($brand['name']); ?>"><?php else: ?><span class="sp-od-header-brand"><?php echo $e($brand['name']); ?></span><?php endif; ?><span class="sp-od-header-text"><?php echo $e($headerText); ?></span></header><div class="sp-od-body"></div><footer class="sp-od-footer"><div class="sp-od-footer-text"><?php echo $e($footer); ?> <b><?php echo $e($project); ?></b></div><div class="sp-od-num"></div></footer></section>
 </template>
</div>
<?php echo Assets::js('doc-paginate.js'); ?>
