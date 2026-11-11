<?php
\defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Support\Component\Supportplans\Site\Helper\Assets;
use Support\Component\Supportplans\Site\Helper\OfferHelper;
use Support\Component\Supportplans\Site\Helper\Sidebar;

echo Assets::css('supportplans.css', 'offers.css', 'structure-editor.css');
$spItemid = OfferHelper::itemid();
$spCompany = trim((string) $this->params->get('brand_name', '')) ?: Factory::getApplication()->get('sitename', 'Dashboard');
$spLogoUrl = OfferHelper::assetUrl((string) $this->params->get('brand_logo', ''));
$spInitials = '';
foreach (preg_split('/\s+/u', $spCompany, -1, PREG_SPLIT_NO_EMPTY) as $word) { $spInitials .= OfferHelper::upperGr(mb_substr($word, 0, 1)); if (mb_strlen($spInitials) >= 2) break; }
$e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<div class="sp-saas-root">
<header class="sp-appbar">
 <div class="sp-brand">
  <?php if ($spLogoUrl !== ''): ?><span class="sp-brandlogo sp-brandlogo-natural"><img src="<?php echo $e($spLogoUrl); ?>" alt="<?php echo $e($spCompany); ?>"></span><?php else: ?><span class="sp-brandmark"><?php echo $e($spInitials ?: 'SP'); ?></span><?php endif; ?>
  <div class="sp-brand-copy"><strong><?php echo $e($spCompany); ?></strong><small>Dashboard</small></div>
 </div>
 <div class="sp-app-actions"><span class="sp-header-pill"><span class="sp-header-dot"></span>Workspace</span></div>
</header>
<main class="sp-app"><div class="sp-wrap"><div class="sp-app-layout"><aside class="sp-sidebar">
  <?php echo Sidebar::head(); ?>
  <?php echo Sidebar::nav('structures'); ?>
  <div class="sp-sidebar-bottom">
    <div class="sp-sidebar-help"><strong>Dashboard</strong><span>Πακέτα, πλάνα, προσφορές και client management σε ένα σημείο.</span></div>
    <a class="sp-side-joomla" href="<?php echo Uri::root(); ?>index.php?option=com_supportplans&amp;task=auth.logout&amp;tmpl=component&amp;Itemid=<?php echo (int) $spItemid; ?>"><span>↪</span> Αποσύνδεση</a>
  </div>
</aside>
<section class="sp-workspace sp-offers-workspace">
 <script>function spSubmit(task){var f=document.getElementById('adminForm');if(!f)return;if(!f.reportValidity||f.reportValidity()){if(window.spBeforeSubmit)window.spBeforeSubmit();f.querySelector('input[name="task"]').value=task;f.submit();}}</script>
 <?php echo $this->loadTemplate($this->mode); ?>
</section></div></div></main>
</div>
<?php if ($this->mode === 'edit') echo Assets::js('offers-editor.js', 'structure-editor.js'); ?>
