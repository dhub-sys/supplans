<?php
namespace Support\Component\Supportplans\Site\View\Activity;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Support\Component\Supportplans\Site\Helper\Activity;

/** Activity log of the Service Hub, with filters and export to Excel. */
class HtmlView extends BaseHtmlView
{
    public $params;
    public array $filters = [];
    public array $rows = [];
    public int $total = 0;
    public int $page = 1;
    public array $areas = [];
    public array $users = [];

    public function display($tpl = null): void
    {
        $this->params = ComponentHelper::getParams('com_supportplans');
        $in = Factory::getApplication()->input;
        $this->filters = self::filters($in);
        $this->page = max(1, $in->getInt('p', 1));
        [$this->rows, $this->total] = Activity::search($this->filters, $this->page, 50);
        $this->areas = Activity::areas();
        $this->users = Activity::users();
        parent::display($tpl);
    }

    public static function filters($in): array
    {
        return [
            'from' => trim((string) $in->getString('from', '')), 'to' => trim((string) $in->getString('to', '')),
            'area' => trim((string) $in->getString('area', '')), 'user' => trim((string) $in->getString('user', '')),
            'result' => $in->getCmd('result', ''), 'q' => trim((string) $in->getString('q', '')),
        ];
    }
}
