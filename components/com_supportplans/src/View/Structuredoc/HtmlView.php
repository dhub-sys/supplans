<?php
namespace Support\Component\Supportplans\Site\View\Structuredoc;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Support\Component\Supportplans\Site\Helper\ShareLink;
use Support\Component\Supportplans\Site\Helper\Structure;

/** Printable structure proposal (saved as PDF through the browser print dialog). */
class HtmlView extends BaseHtmlView
{
    public $params;
    public ?object $doc = null;
    /** Set by StructureController::preview() with the unsaved form data. */
    public ?object $preview = null;

    public function display($tpl = null): void
    {
        $app = Factory::getApplication();
        $this->params = ComponentHelper::getParams('com_supportplans');
        // Private link sent to the customer: the token alone grants read-only access.
        $token = (string) $app->input->getString('t', '');
        if ($token !== '' && !$this->preview) {
            $this->doc = ShareLink::structure($token);
            if (!$this->doc) {
                throw new \RuntimeException('Not found', 404);
            }
            ShareLink::protectPage();
            $app->input->set('tmpl', 'component');
            parent::display($tpl);
            return;
        }
        $session = $app->getSession();
        if ((int) $session->get('supportplans.auth.user_id', 0) < 1 || !$session->get('supportplans.otp.verified', false)) {
            throw new \RuntimeException('Login required', 403);
        }
        $this->doc = $this->preview ?: Structure::find($app->input->getInt('id'));
        if (!$this->doc) {
            throw new \RuntimeException('Not found', 404);
        }
        $app->input->set('tmpl', 'component');
        parent::display($tpl);
    }
}
