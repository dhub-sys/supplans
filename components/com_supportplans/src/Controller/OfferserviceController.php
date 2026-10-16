<?php
namespace Support\Component\Supportplans\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;
use Support\Component\Supportplans\Site\Helper\OfferHelper;

/** Library of services / text sections that offer templates are built from. */
class OfferserviceController extends BaseController
{
    public function apply(): void { $this->store(false); }
    public function save(): void { $this->store(true); }

    private function store(bool $close): void
    {
        Session::checkToken() or jexit('Invalid Token');
        $app = Factory::getApplication();
        $in = $app->input;
        $db = Factory::getContainer()->get('DatabaseDriver');
        $id = $in->getInt('id');
        $name = trim($in->getString('name'));
        if ($name === '') {
            $app->enqueueMessage('Συμπληρώστε το όνομα της υπηρεσίας.', 'error');
            OfferHelper::redirect('view=offers&section=services' . ($id ? '&id=' . $id : '&mode=new'));
            return;
        }
        $now = Factory::getDate()->toSql();
        $row = (object) [
            'name'             => $name,
            'section_title'    => trim($in->getString('section_title')),
            'table_label'      => trim($in->getString('table_label')),
            'body'             => str_replace("\r\n", "\n", (string) $in->get('body', '', 'raw')),
            'lists'            => json_encode(OfferHelper::lists((array) $in->get('lists', [], 'array')), JSON_UNESCAPED_UNICODE),
            'price'            => round($in->getFloat('price', 0), 2),
            'show_description' => $in->getInt('show_description', 0) ? 1 : 0,
            'in_table'         => $in->getInt('in_table', 0) ? 1 : 0,
            'page_break'       => $in->getInt('page_break', 0) ? 1 : 0,
            'space_before'     => max(0, min(80, $in->getInt('space_before', 5))),
            'published'        => $in->getInt('published', 1) ? 1 : 0,
            'ordering'         => $in->getInt('ordering', 0),
            'modified'         => $now,
        ];
        if ($id > 0) {
            $row->id = $id;
            $db->updateObject('#__supportplans_offer_services', $row, 'id');
        } else {
            $row->created = $now;
            $db->insertObject('#__supportplans_offer_services', $row, 'id');
            $id = (int) $row->id;
        }
        $app->enqueueMessage('Η υπηρεσία αποθηκεύτηκε.');
        OfferHelper::redirect('view=offers&section=services' . ($close ? '' : '&id=' . $id));
    }

    public function delete(): void
    {
        Session::checkToken() or jexit('Invalid Token');
        $app = Factory::getApplication();
        $db = Factory::getContainer()->get('DatabaseDriver');
        $id = $app->input->getInt('id');
        if ($id > 0) {
            // Offers keep their own copy of each service, so existing offers are not affected.
            $q = $db->getQuery(true)->delete($db->quoteName('#__supportplans_offer_services'))->where($db->quoteName('id') . '=' . (int) $id);
            $db->setQuery($q)->execute();
            $app->enqueueMessage('Η υπηρεσία διαγράφηκε. Οι υπάρχουσες προσφορές δεν επηρεάζονται.');
        }
        OfferHelper::redirect('view=offers&section=services');
    }
}
