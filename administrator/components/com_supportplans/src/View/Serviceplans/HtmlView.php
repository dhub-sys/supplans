<?php
namespace Support\Component\Supportplans\Administrator\View\Serviceplans;
\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
 public $params;
    public array $items = [];
    public ?object $selected = null;
    public string $mode = 'list';

    public function display($tpl = null): void
    {
        $this->params = ComponentHelper::getParams('com_supportplans');
        $app = Factory::getApplication();
        $db = Factory::getContainer()->get('DatabaseDriver');
        $id = $app->input->getInt('id');
        $this->mode = $app->input->getCmd('mode', $id ? 'edit' : 'list');
        $q = $db->getQuery(true)->select('*')->from($db->quoteName('#__supportplans_serviceplans'))->order($db->quoteName('name').' ASC');
        $this->items = $db->setQuery($q)->loadObjectList();
        if ($id) {
            foreach ($this->items as $item) if ((int)$item->id === $id) { $this->selected = $item; break; }
        }
        $this->addToolbar();
        parent::display($tpl);
    }

    private function addToolbar(): void
    {
        if ($this->mode === 'new') {
            ToolbarHelper::title('Νέο πλάνο', 'plus');
            ToolbarHelper::apply('serviceplan.apply');
            ToolbarHelper::save('serviceplan.save');
            ToolbarHelper::save2new('serviceplan.save2new');
            ToolbarHelper::cancel('serviceplan.cancel');
            return;
        }
        if ($this->selected) {
            ToolbarHelper::title('Επεξεργασία πλάνου', 'pencil-alt');
            ToolbarHelper::apply('serviceplan.apply');
            ToolbarHelper::save('serviceplan.save');
            ToolbarHelper::save2new('serviceplan.save2new');
            ToolbarHelper::cancel('serviceplan.cancel', 'JTOOLBAR_CLOSE');
            return;
        }
        ToolbarHelper::title('Πλάνα υποστήριξης', 'list');
        ToolbarHelper::addNew('serviceplan.add', 'Νέο Πλάνο');
    }
}
