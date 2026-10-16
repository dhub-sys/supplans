<?php
namespace Support\Component\Supportplans\Administrator\View\Report;
\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    public object $plan;
    public array $entries = [];
    public $params;

    public function display($tpl = null): void
    {
        $this->params = ComponentHelper::getParams('com_supportplans');
        $db = Factory::getContainer()->get('DatabaseDriver');
        $id = Factory::getApplication()->input->getInt('plan_id');
        $q = $db->getQuery(true)->select('p.*')->from($db->quoteName('#__supportplans_plans', 'p'))->where('p.id=' . (int) $id);
        $this->plan = $db->setQuery($q)->loadObject();
        if (!$this->plan) {
            throw new \RuntimeException('Plan not found', 404);
        }
        $q = $db->getQuery(true)->select('*')->from($db->quoteName('#__supportplans_entries'))->where('plan_id=' . (int) $id)->order('id ASC');
        $this->entries = $db->setQuery($q)->loadObjectList();
        parent::display($tpl);
    }
}
