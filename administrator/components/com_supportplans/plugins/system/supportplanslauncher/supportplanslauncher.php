<?php
defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;

final class PlgSystemSupportplanslauncher extends CMSPlugin
{
    protected $app;

    public function onAfterRender(): void
    {
        if (!$this->app->isClient('administrator')) {
            return;
        }

        $doc = $this->app->getDocument();
        if (!$doc || $doc->getType() !== 'html') {
            return;
        }

        $body = $this->app->getBody();
        if ($body === '' || stripos($body, '</body>') === false) {
            return;
        }

        $script = <<<'HTML'
<script id="supportplans-admin-launcher">
(function () {
  var serviceHubUrl = '../index.php?option=com_supportplans&view=app&tmpl=component&Itemid=__SP_ITEMID__';

  // Joomla's own "Components > Support Plans" entries also open the standalone Service Hub in a new tab.
  function redirectComponentLinks() {
    var links = document.querySelectorAll('#sidebar-wrapper a[href*="option=com_supportplans"], .sidebar-wrapper a[href*="option=com_supportplans"], #menu a[href*="option=com_supportplans"], .quickicons-for-components a[href*="option=com_supportplans"]');
    for (var i = 0; i < links.length; i++) {
      if (links[i].closest('#supportplans-top-launcher')) continue;
      links[i].href = serviceHubUrl;
      links[i].target = '_blank';
      links[i].rel = 'noopener';
    }
  }

  function installSupportPlansButton() {
    redirectComponentLinks();
    if (document.getElementById('supportplans-top-launcher')) return;

    var candidates = [
      '#sidebar-wrapper .main-nav',
      '#sidebar-wrapper nav',
      '#sidebar-wrapper ul',
      '.sidebar-wrapper .main-nav',
      '.sidebar-wrapper nav',
      '.sidebar-wrapper ul',
      'nav.main-nav',
      '#menu'
    ];

    var host = null;
    for (var i=0;i<candidates.length;i++) {
      host = document.querySelector(candidates[i]);
      if (host) break;
    }
    if (!host) return;

    var isList = host.tagName === 'UL' || host.tagName === 'OL';
    var wrap = document.createElement(isList ? 'li' : 'div');
    wrap.id = 'supportplans-top-launcher';
    wrap.className = isList ? 'nav-item supportplans-launcher-item' : 'supportplans-launcher-item';

    var a = document.createElement('a');
    a.href = serviceHubUrl;
    a.target = '_blank';
    a.rel = 'noopener';
    a.className = 'supportplans-launcher-link';
    a.setAttribute('aria-label', 'Support Plans');

    a.innerHTML =
      '<span class="supportplans-launcher-icon" aria-hidden="true">' +
        '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' +
          '<path d="M6 2.75h9l3 3V21.25H6z"></path>' +
          '<path d="M15 2.75v3h3M9 10h6M9 14h6M9 18h4"></path>' +
        '</svg>' +
      '</span><span class="supportplans-launcher-text">Support Plans</span>';

    wrap.appendChild(a);

    // Put it visibly near the top, but after Joomla's home/dashboard item.
    if (host.children.length > 1) host.insertBefore(wrap, host.children[1]);
    else host.appendChild(wrap);

    if (!document.getElementById('supportplans-launcher-style')) {
      var st = document.createElement('style');
      st.id = 'supportplans-launcher-style';
      st.textContent =
        '#supportplans-top-launcher{margin:5px 8px!important;list-style:none!important}' +
        '#supportplans-top-launcher .supportplans-launcher-link{display:flex!important;align-items:center!important;gap:10px!important;min-height:42px!important;padding:9px 12px!important;border-radius:8px!important;text-decoration:none!important;color:#fff!important;font-weight:600!important}' +
        '#supportplans-top-launcher .supportplans-launcher-link:visited{color:#fff!important}' +
        '#supportplans-top-launcher .supportplans-launcher-link:hover{background:rgba(255,255,255,.10)!important;color:#fff!important}' +
        '#supportplans-top-launcher .supportplans-launcher-icon{display:inline-flex!important;align-items:center!important;justify-content:center!important;flex:0 0 22px!important;color:#fff!important}' +
        '#supportplans-top-launcher .supportplans-launcher-icon svg{stroke:#fff!important}' +
        '#supportplans-top-launcher .supportplans-launcher-text{white-space:nowrap!important;color:#fff!important}';
      document.head.appendChild(st);
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', installSupportPlansButton);
  } else {
    installSupportPlansButton();
  }
  setTimeout(installSupportPlansButton, 400);
  setTimeout(installSupportPlansButton, 1200);
})();
</script>
HTML;

        $itemid = max(1, (int) \Joomla\CMS\Component\ComponentHelper::getParams('com_supportplans')->get('standalone_itemid', 785));
        $script = str_replace('__SP_ITEMID__', (string) $itemid, $script);
        $this->app->setBody(str_ireplace('</body>', $script . '</body>', $body));
    }
}
