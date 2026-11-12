<?php
namespace Support\Component\Supportplans\Site\View\Structures;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Support\Component\Supportplans\Site\Helper\Structure;

/** Website structure proposals: list, new and edit (sitemap tree + content per section). */
class HtmlView extends BaseHtmlView
{
    public $params;
    public string $mode = 'list';
    public array $rows = [];
    public ?object $selected = null;
    public string $nextNumber = '';

    public function display($tpl = null): void
    {
        $this->params = ComponentHelper::getParams('com_supportplans');
        $in = Factory::getApplication()->input;
        $id = $in->getInt('id');
        if ($id > 0 && ($this->selected = Structure::find($id))) {
            $this->mode = 'edit';
        } elseif ($in->getCmd('mode') === 'new') {
            $this->mode = 'new';
            $this->nextNumber = Structure::nextNumber();
        } else {
            // The list of structure proposals is the documents page (same look, by number).
            Factory::getApplication()->redirect(\Support\Component\Supportplans\Site\Helper\OfferHelper::url('view=documents&type=structure', false));
            return;
            $this->rows = array_values(array_filter(Structure::all(), static fn($r) => \Support\Component\Supportplans\Site\Helper\Year::inYear((string) $r->proposal_date)));
        }
        parent::display($tpl);
    }
}
