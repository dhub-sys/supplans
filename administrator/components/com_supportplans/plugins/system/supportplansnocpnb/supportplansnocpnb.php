<?php
\defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;

final class PlgSystemSupportplansnocpnb extends CMSPlugin
{
    protected $app;

    public function onAfterRender(): void
    {
        if (!$this->app || !$this->app->isClient('site')) return;

        $input=$this->app->input;
        if ($input->getCmd('option') !== 'com_supportplans' || $input->getInt('spapp',0) !== 1) return;

        $body=(string)$this->app->getBody();
        if ($body==='') return;

        // Remove Web357 CPNB assets where identifiable.
        $body=preg_replace(
            '#<(?:script|link)\b[^>]*(?:web357|cpnb|cookiespolicy|cookies-policy)[^>]*(?:>.*?</script>|/?>)\s*#is',
            '',
            $body
        ) ?? $body;

        // Hide/remove every CPNB visual element, including the cookie-control button.
        $guard=<<<'HTML'
<style id="supportplans-no-cpnb">
#cpnb,[id^="cpnb"],[id*="-cpnb"],
.cpnb-modal-wrap,.cpnb-modal,.cpnb-modal-trigger,
[class^="cpnb-"],[class*=" cpnb-"],[class*="cpnb-"]{
 display:none!important;visibility:hidden!important;opacity:0!important;pointer-events:none!important;
}
@media print{
 #cpnb,[id^="cpnb"],[id*="-cpnb"],
 .cpnb-modal-wrap,.cpnb-modal,.cpnb-modal-trigger,
 [class^="cpnb-"],[class*=" cpnb-"],[class*="cpnb-"]{display:none!important}
}
</style>
<script>
(function(){
 function purge(){
  document.querySelectorAll('#cpnb,[id^="cpnb"],[id*="-cpnb"],.cpnb-modal-wrap,.cpnb-modal,.cpnb-modal-trigger,[class^="cpnb-"],[class*=" cpnb-"],[class*="cpnb-"]').forEach(function(e){e.remove();});
 }
 purge();
 if(document.documentElement){new MutationObserver(purge).observe(document.documentElement,{childList:true,subtree:true});}
})();
</script>
HTML;
        $body=str_ireplace('</head>',$guard.'</head>',$body);
        $this->app->setBody($body);
    }
}
