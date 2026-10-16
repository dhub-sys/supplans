<?php
\defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Support\Component\Supportplans\Site\Helper\OfferHelper;

HTMLHelper::_('stylesheet', 'com_supportplans/supportplans.css', ['version' => 'auto', 'relative' => true]);
HTMLHelper::_('stylesheet', 'com_supportplans/offers.css', ['version' => 'auto', 'relative' => true]);
HTMLHelper::_('script', 'com_supportplans/offers-editor.js', ['version' => 'auto', 'relative' => true], ['defer' => true]);
HTMLHelper::_('script', 'com_supportplans/sp-sortable.js', ['version' => 'auto', 'relative' => true], ['defer' => true]);
$spItemid = OfferHelper::itemid();
$spParams = $this->params;
$spCompany = trim((string) $spParams->get('brand_name', '')) ?: Factory::getApplication()->get('sitename', 'Support Plans');
$spLogoUrl = OfferHelper::assetUrl((string) $spParams->get('brand_logo', ''));
$spInitials = '';
foreach (preg_split('/\s+/u', $spCompany, -1, PREG_SPLIT_NO_EMPTY) as $word) { $spInitials .= mb_strtoupper(mb_substr($word, 0, 1)); if (mb_strlen($spInitials) >= 2) break; }
if ($spInitials === '') $spInitials = 'SP';
$e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$tabs = ['offers' => 'Προσφορές', 'templates' => 'Templates', 'services' => 'Υπηρεσίες', 'variables' => 'Μεταβλητές'];
?>
<div class="sp-saas-root">
<header class="sp-appbar">
 <div class="sp-brand">
  <?php if ($spLogoUrl !== ''): ?><span class="sp-brandlogo sp-brandlogo-natural"><img src="<?php echo $e($spLogoUrl); ?>" alt="<?php echo $e($spCompany); ?>"></span><?php else: ?><span class="sp-brandmark"><?php echo $e($spInitials); ?></span><?php endif; ?>
  <div class="sp-brand-copy"><strong><?php echo $e($spCompany); ?></strong><small>Service Hub</small></div>
 </div>
 <div class="sp-app-actions"><span class="sp-header-pill"><span class="sp-header-dot"></span>Workspace</span></div>
</header>
<main class="sp-app"><div class="sp-wrap"><div class="sp-app-layout"><aside class="sp-sidebar">
  <div class="sp-sidebar-section-label">Διαχείριση</div>
  <nav class="sp-side-nav"><a class="sp-side-link" href="<?php echo Route::_('index.php?option=com_supportplans&view=plans&tmpl=component&spapp=1&Itemid='.(int)$spItemid.''); ?>"><span class="sp-side-icon"><svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M7 8h10M7 12h4M7 16h6"/></svg></span><span>Πακέτα</span></a><a class="sp-side-link" href="<?php echo Route::_('index.php?option=com_supportplans&view=serviceplans&tmpl=component&spapp=1&Itemid='.(int)$spItemid.''); ?>"><span class="sp-side-icon"><svg viewBox="0 0 24 24"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5z"/><path d="M4 5.5v16M8 7h8M8 11h6"/></svg></span><span>Πλάνα</span></a><a class="sp-side-link is-active" href="<?php echo Route::_('index.php?option=com_supportplans&view=offers&tmpl=component&spapp=1&Itemid='.(int)$spItemid); ?>"><span class="sp-side-icon"><svg viewBox="0 0 24 24"><path d="M6 3h8l4 4v14H6z"/><path d="M14 3v4h4"/><path d="M14.2 11.6a2.6 2.6 0 1 0 0 3.8M9.5 12.8h3.2M9.5 14.4h3.2"/></svg></span><span>Προσφορές</span></a><a class="sp-side-link" href="<?php echo Route::_('index.php?option=com_supportplans&view=settings&tmpl=component&spapp=1&Itemid='.(int)$spItemid.''); ?>"><span class="sp-side-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.83 2.83-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .6 1.7 1.7 0 0 0-.4 1.1V21h-4v-.1A1.7 1.7 0 0 0 8.6 19.4a1.7 1.7 0 0 0-1.88.34l-.06.06-2.83-2.83.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-.6-1 1.7 1.7 0 0 0-1.1-.4H3v-4h.1A1.7 1.7 0 0 0 4.6 8.6a1.7 1.7 0 0 0-.34-1.88l-.06-.06 2.83-2.83.06.06A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-.6 1.7 1.7 0 0 0 .4-1.1V3h4v.1A1.7 1.7 0 0 0 15.4 4.6a1.7 1.7 0 0 0 1.88-.34l.06-.06 2.83 2.83-.06.06A1.7 1.7 0 0 0 19.4 9c.1.4.3.75.6 1 .3.25.7.4 1.1.4h.1v4h-.1c-.4 0-.8.15-1.1.4-.3.25-.5.6-.6 1z"/></svg></span><span>Ρυθμίσεις</span></a>
<a class="sp-side-link" href="<?php echo Route::_('index.php?option=com_supportplans&view=help&tmpl=component&spapp=1&Itemid='.(int)$spItemid); ?>"><span class="sp-side-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M9.8 9a2.3 2.3 0 1 1 3.7 1.8c-.9.6-1.5 1-1.5 2.2"/><path d="M12 17h.01"/></svg></span><span>Οδηγίες</span></a></nav>
  <div class="sp-sidebar-bottom">
    <div class="sp-sidebar-help"><strong>Service Hub</strong><span>Πακέτα, πλάνα, αναφορές και προσφορές σε ένα σημείο.</span></div>
    <a class="sp-side-joomla" href="<?php echo Uri::root(); ?>index.php?option=com_supportplans&amp;task=auth.logout&amp;tmpl=component&amp;Itemid=<?php echo (int) $spItemid; ?>"><span>↪</span> Αποσύνδεση</a>
  </div>
</aside>
<section class="sp-workspace sp-offers-workspace">
 <nav class="sp-offer-tabs" aria-label="Προσφορές">
  <?php foreach ($tabs as $key => $label): ?>
  <a class="sp-offer-tab<?php echo $this->section === $key ? ' is-active' : ''; ?>" href="<?php echo OfferHelper::url('view=offers' . ($key === 'offers' ? '' : '&section=' . $key)); ?>"><?php echo $e($label); ?></a>
  <?php endforeach; ?>
 </nav>
 <script>
 // Offer fields for the editor's "Μεταβλητή…" list.
 window.spCustomVariables = function () { return <?php echo json_encode(array_map(static fn($f) => ['{' . $f['key'] . '}', $f['label']], OfferHelper::fields($this->params)), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP); ?>; };
 </script>
 <script>function spSubmit(task){var f=document.getElementById('adminForm');if(!f)return;if(!f.reportValidity||f.reportValidity()){f.querySelector('input[name="task"]').value=task;f.submit();}}</script>
<?php
if ($this->section === 'variables') {
    echo $this->loadTemplate('fields');
} elseif ($this->section === 'services') {
    echo $this->loadTemplate($this->mode === 'list' ? 'services' : 'service');
} elseif ($this->section === 'templates') {
    echo $this->loadTemplate($this->mode === 'list' ? 'templates' : 'template');
} else {
    echo $this->loadTemplate($this->mode === 'list' ? 'list' : ($this->mode === 'new' ? 'new' : 'edit'));
}
?>
</section></div></div></main>
</div>
