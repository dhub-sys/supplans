<?php
\defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Support\Component\Supportplans\Site\Helper\NotifyUi;
use Support\Component\Supportplans\Site\Helper\OfferHelper;
use Support\Component\Supportplans\Site\View\Notify\HtmlView;

echo \Support\Component\Supportplans\Site\Helper\Assets::css('supportplans.css', 'offers.css', 'notify.css');
$spItemid = OfferHelper::itemid();
$spParams = $this->params;
$spCompany = trim((string) $spParams->get('brand_name', '')) ?: Factory::getApplication()->get('sitename', 'Back Office');
$spLogoUrl = OfferHelper::assetUrl((string) $spParams->get('brand_logo', ''));
$spInitials = '';
foreach (preg_split('/\s+/u', $spCompany, -1, PREG_SPLIT_NO_EMPTY) as $word) { $spInitials .= \Support\Component\Supportplans\Site\Helper\OfferHelper::upperGr(mb_substr($word, 0, 1)); if (mb_strlen($spInitials) >= 2) break; }
if ($spInitials === '') $spInitials = 'SP';
$e = [NotifyUi::class, 'e'];
$templates = [
    'overview' => 'overview', 'settings' => 'settings',
    'clients' => $this->mode === 'list' ? 'clients' : 'client',
    'services' => $this->mode === 'list' ? 'services' : 'service',
    'catalog' => $this->mode === 'list' ? 'catalog' : 'catalogitem',
    'reports' => $this->mode === 'list' ? 'reports' : 'report',
    'campaigns' => $this->mode === 'list' ? 'campaigns' : 'campaign',
    'log' => $this->selected ? 'message' : 'log',
];
?>
<div class="sp-saas-root">
<header class="sp-appbar">
 <div class="sp-brand">
  <?php if ($spLogoUrl !== ''): ?><span class="sp-brandlogo sp-brandlogo-natural"><img src="<?php echo $e($spLogoUrl); ?>" alt="<?php echo $e($spCompany); ?>"></span><?php else: ?><span class="sp-brandmark"><?php echo $e($spInitials); ?></span><?php endif; ?>
  <div class="sp-brand-copy"><strong><?php echo $e($spCompany); ?></strong><small>Back Office</small></div>
 </div>
 <div class="sp-app-actions"><span class="sp-header-pill"><span class="sp-header-dot"></span>Workspace</span></div>
</header>
<main class="sp-app"><div class="sp-wrap"><div class="sp-app-layout"><aside class="sp-sidebar">
  <?php echo \Support\Component\Supportplans\Site\Helper\Sidebar::head(); ?>
  <?php echo \Support\Component\Supportplans\Site\Helper\Sidebar::nav('notify'); ?>
  <div class="sp-sidebar-bottom">
    <div class="sp-sidebar-help"><strong>Back Office</strong><span>Πακέτα, πλάνα, προσφορές και client management σε ένα σημείο.</span></div>
    <a class="sp-side-joomla" href="<?php echo Uri::root(); ?>index.php?option=com_supportplans&amp;task=auth.logout&amp;tmpl=component&amp;Itemid=<?php echo (int) $spItemid; ?>"><span>↪</span> Αποσύνδεση</a>
  </div>
</aside>
<section class="sp-workspace sp-offers-workspace">
 <nav class="sp-offer-tabs" aria-label="Client Management">
  <?php foreach (HtmlView::SECTIONS as $key => $label): ?>
  <a class="sp-offer-tab<?php echo $this->section === $key ? ' is-active' : ''; ?>" href="<?php echo NotifyUi::url($key === 'overview' ? '' : 'section=' . $key); ?>"><?php echo $e($label); ?><?php if ($key === 'log' && ($this->counts['failed'] ?? 0) > 0): ?> <span class="sp-offer-status is-rejected"><?php echo (int) $this->counts['failed']; ?></span><?php endif; ?></a>
  <?php endforeach; ?>
 </nav>
 <script>function spSubmit(task){var f=document.getElementById('adminForm');if(!f)return;if(!f.reportValidity||f.reportValidity()){f.querySelector('input[name="task"]').value=task;f.submit();}}</script>
 <script>window.spCustomVariables=function(){return <?php echo json_encode([['{greeting}','Προσφώνηση (π.χ. Αγαπητέ/ή πελάτη)'],['{client_name}','Όνομα πελάτη'],['{client_company}','Εταιρεία πελάτη'],['{service}','Υπηρεσία'],['{label}','Domain / περιγραφή'],['{expiry_date}','Ημ/νία λήξης'],['{expiry_phrase}','«λήγει σε 7 ημέρες»'],['{price}','Τιμή'],['{duration}','Διάρκεια'],['{period}','Περίοδος αναφοράς'],['{services_list}','Λίστα υπηρεσιών πελάτη']], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP); ?>;};</script>
<?php echo $this->loadTemplate($templates[$this->section]); ?>
</section></div></div></main>
</div>
<?php echo \Support\Component\Supportplans\Site\Helper\Assets::js('offers-editor.js'); ?>
