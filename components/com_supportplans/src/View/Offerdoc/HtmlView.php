<?php
namespace Support\Component\Supportplans\Site\View\Offerdoc;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/** Printable offer document (saved as PDF through the browser print dialog). */
class HtmlView extends BaseHtmlView
{
    public $params;
    public object $offer;
    public array $items = [];
    /** Set by OfferController::preview(): ['offer' => object, 'items' => array] built from unsaved form data. */
    public ?array $preview = null;

    public function display($tpl = null): void
    {
        $app = Factory::getApplication();
        $session = $app->getSession();
        if ((int) $session->get('supportplans.auth.user_id', 0) < 1 || !$session->get('supportplans.otp.verified', false)) {
            throw new \RuntimeException('Login required', 403);
        }
        $this->params = ComponentHelper::getParams('com_supportplans');
        if ($this->preview) {
            $this->offer = $this->preview['offer'];
            $this->items = $this->preview['items'];
            $app->input->set('tmpl', 'component');
            parent::display($tpl);
            return;
        }
        $db = Factory::getContainer()->get('DatabaseDriver');
        $id = $app->input->getInt('id');
        $q = $db->getQuery(true)->select('*')->from($db->quoteName('#__supportplans_offers'))->where($db->quoteName('id') . '=' . (int) $id);
        $offer = $db->setQuery($q)->loadObject();
        if (!$offer) {
            throw new \RuntimeException('Offer not found', 404);
        }
        $this->offer = $offer;
        $q = $db->getQuery(true)->select('*')->from($db->quoteName('#__supportplans_offer_items'))->where($db->quoteName('offer_id') . '=' . (int) $id)->order('ordering ASC, id ASC');
        $this->items = $db->setQuery($q)->loadObjectList();
        $app->input->set('tmpl', 'component');
        parent::display($tpl);
    }
}
