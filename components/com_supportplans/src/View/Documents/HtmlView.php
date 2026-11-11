<?php
namespace Support\Component\Supportplans\Site\View\Documents;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Support\Component\Supportplans\Site\Helper\Documents;

/** Register of all documents (packages, offers, structure proposals) by number. */
class HtmlView extends BaseHtmlView
{
    public $params;
    public array $rows = [];
    public array $counts = [];
    public string $type = '';
    public string $q = '';
    public string $nextNumber = '';

    public function display($tpl = null): void
    {
        $this->params = ComponentHelper::getParams('com_supportplans');
        $in = Factory::getApplication()->input;
        $this->type = isset(Documents::TYPES[$in->getCmd('type', '')]) ? $in->getCmd('type', '') : '';
        $this->q = trim((string) $in->getString('q', ''));
        // Documents of the selected working year (Χρήση).
        $all = array_values(array_filter(Documents::all(), static fn($r) => \Support\Component\Supportplans\Site\Helper\Year::inYear($r['date'])));
        $this->counts = array_fill_keys(array_keys(Documents::TYPES), 0);
        foreach ($all as $r) $this->counts[$r['type']]++;
        $needle = mb_strtolower($this->q);
        $this->rows = array_values(array_filter($all, fn($r) => ($this->type === '' || $r['type'] === $this->type)
            && ($needle === '' || mb_strpos(mb_strtolower($r['number'] . ' ' . $r['customer'] . ' ' . $r['title']), $needle) !== false)));
        $this->nextNumber = Documents::nextNumber();
        parent::display($tpl);
    }
}
