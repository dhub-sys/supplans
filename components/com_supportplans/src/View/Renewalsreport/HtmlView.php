<?php
namespace Support\Component\Supportplans\Site\View\Renewalsreport;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Support\Component\Supportplans\Site\Controller\OverviewController;
use Support\Component\Supportplans\Site\Helper\Notify;

/** Printable renewals report (payments, expiries, overdue) — saved as Pdf from the browser. */
class HtmlView extends BaseHtmlView
{
    public $params;
    public array $data = [];
    public array $parts = [];
    public string $periodLabel = '';

    public function display($tpl = null): void
    {
        $app = Factory::getApplication();
        $this->params = ComponentHelper::getParams('com_supportplans');
        [$period, $from, $to, $this->parts] = OverviewController::filters($app->input);
        $this->data = Notify::summaryRows($period, $from, $to);
        $this->periodLabel = OverviewController::label($period, $from, $to);
        $app->input->set('tmpl', 'component');
        $app->getDocument()->setTitle('Αναφορά ανανεώσεων ' . Notify::today());
        parent::display($tpl);
    }
}
