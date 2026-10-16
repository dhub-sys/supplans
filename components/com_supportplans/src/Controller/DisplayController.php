<?php
namespace Support\Component\Supportplans\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

class DisplayController extends BaseController
{
    protected $default_view = 'app';

    public function display($cachable = false, $urlparams = []): BaseController
    {
        $this->input->set('view', $this->input->getCmd('view', 'app'));

        return parent::display($cachable, $urlparams);
    }
}
