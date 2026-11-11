<?php
namespace Support\Component\Supportplans\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;
use Support\Component\Supportplans\Site\Helper\OfferHelper;
use Support\Component\Supportplans\Site\Helper\Structure;

/** Website structure proposals: create, save, preview, duplicate and delete. */
class StructureController extends BaseController
{
    public function create(): void
    {
        Session::checkToken() or jexit('Invalid Token');
        $in = $this->input;
        $customer = trim($in->getString('customer_name'));
        if ($customer === '') {
            $this->app->enqueueMessage('Συμπληρώστε τον πελάτη.', 'error');
            OfferHelper::redirect('view=structures&mode=new');
            return;
        }
        $db = Factory::getContainer()->get('DatabaseDriver');
        $now = Factory::getDate()->toSql();
        $row = (object) [
            'number' => trim($in->getString('number')) ?: Structure::nextNumber(),
            'proposal_date' => $this->date($in->getString('proposal_date')),
            'customer_name' => mb_substr($customer, 0, 190),
            'project_name' => mb_substr(trim($in->getString('project_name')), 0, 190),
            'doc_title' => Structure::DEFAULTS['doc_title'],
            'doc_subtitle' => '',
            'intro_title' => Structure::DEFAULTS['intro_title'],
            'intro_body' => '',
            'sitemap_title' => Structure::DEFAULTS['sitemap_title'],
            'content_title' => Structure::DEFAULTS['content_title'],
            'outro_title' => Structure::DEFAULTS['outro_title'],
            'outro_body' => '',
            'tree' => json_encode($this->starter($in->getCmd('starter', 'basic')), JSON_UNESCAPED_UNICODE),
            'status' => 'draft',
            'notes' => '',
            'created' => $now,
            'modified' => $now,
        ];
        $db->insertObject(Structure::T, $row, 'id');
        $this->app->input->set('id', (int) $row->id);
        OfferHelper::redirect('view=structures&id=' . (int) $row->id);
    }

    public function apply(): void { $this->store(false); }
    public function save(): void { $this->store(true); }

    private function store(bool $close): void
    {
        Session::checkToken() or jexit('Invalid Token');
        $id = $this->input->getInt('id');
        if ($id < 1 || !Structure::find($id)) {
            OfferHelper::redirect('view=structures');
            return;
        }
        $row = $this->collect();
        if ($row->customer_name === '') {
            $this->app->enqueueMessage('Συμπληρώστε τον πελάτη.', 'error');
            OfferHelper::redirect('view=structures&id=' . $id);
            return;
        }
        $row->id = $id;
        $row->modified = Factory::getDate()->toSql();
        Factory::getContainer()->get('DatabaseDriver')->updateObject(Structure::T, $row, 'id');
        $this->app->enqueueMessage('Η πρόταση δομής αποθηκεύτηκε.');
        OfferHelper::redirect($close ? 'view=structures' : 'view=structures&id=' . $id);
    }

    /** The document with the unsaved form data (shown in the preview dialog). */
    public function preview(): void
    {
        Session::checkToken() or jexit('Invalid Token');
        $stored = Structure::find($this->input->getInt('id'));
        if (!$stored) {
            throw new \RuntimeException('Not found', 404);
        }
        foreach ($this->collect() as $k => $v) {
            $stored->$k = $v;
        }
        $this->input->set('tmpl', 'component');
        $view = $this->getView('structuredoc', 'html', 'Site', ['base_path' => $this->basePath, 'layout' => 'default']);
        $view->document = $this->app->getDocument();
        $view->preview = $stored;
        $view->display();
    }

    public function duplicate(): void
    {
        Session::checkToken() or jexit('Invalid Token');
        $row = Structure::find($this->input->getInt('id'));
        if (!$row) {
            OfferHelper::redirect('view=structures');
            return;
        }
        unset($row->id);
        $row->number = Structure::nextNumber();
        $row->proposal_date = date('Y-m-d');
        $row->status = 'draft';
        $row->created = $row->modified = Factory::getDate()->toSql();
        Factory::getContainer()->get('DatabaseDriver')->insertObject(Structure::T, $row, 'id');
        $this->app->enqueueMessage('Δημιουργήθηκε αντίγραφο ' . $row->number . '.');
        OfferHelper::redirect('view=structures&id=' . (int) $row->id);
    }

    public function delete(): void
    {
        Session::checkToken() or jexit('Invalid Token');
        $id = $this->input->getInt('id');
        if ($id > 0) {
            $db = Factory::getContainer()->get('DatabaseDriver');
            $db->setQuery($db->getQuery(true)->delete($db->quoteName(Structure::T))->where('id=' . $id))->execute();
            $this->app->enqueueMessage('Η πρόταση δομής διαγράφηκε.');
        }
        OfferHelper::redirect('view=structures');
    }

    private function collect(): object
    {
        $in = $this->input;
        $text = fn(string $k, int $max = 255) => mb_substr(trim($in->getString($k, '')), 0, $max);
        // Rich text from the editor: raw (it is escaped when rendered), CRLF normalised.
        $raw = fn(string $k) => mb_substr(str_replace("\r\n", "\n", trim((string) $in->get($k, '', 'raw'))), 0, 60000);
        $status = $in->getCmd('status', 'draft');
        return (object) [
            'number' => $text('number', 40),
            'proposal_date' => $this->date($in->getString('proposal_date')),
            'customer_name' => $text('customer_name', 190),
            'project_name' => $text('project_name', 190),
            'doc_title' => $text('doc_title'),
            'doc_subtitle' => $text('doc_subtitle'),
            'intro_title' => $text('intro_title'),
            'intro_body' => $raw('intro_body'),
            'sitemap_title' => $text('sitemap_title'),
            'content_title' => $text('content_title'),
            'outro_title' => $text('outro_title'),
            'outro_body' => $raw('outro_body'),
            'tree' => json_encode(Structure::nodes((string) $in->get('tree', '[]', 'raw')), JSON_UNESCAPED_UNICODE),
            'status' => isset(OfferHelper::STATUSES[$status]) ? $status : 'draft',
            'notes' => mb_substr(trim((string) $in->get('notes', '', 'raw')), 0, 5000),
        ];
    }

    private function date(string $v): string
    {
        $d = \DateTime::createFromFormat('Y-m-d', trim($v));
        return $d ? $d->format('Y-m-d') : date('Y-m-d');
    }

    /** Ready-made starting trees. */
    private function starter(string $kind): array
    {
        $n = static fn(string $t, int $l = 0) => ['title' => $t, 'level' => $l, 'purpose' => '', 'content' => ''];
        $trees = [
            'basic' => [$n('Αρχική'), $n('Η εταιρεία'), $n('Ποιοι είμαστε', 1), $n('Η ομάδα μας', 1), $n('Υπηρεσίες'), $n('Υπηρεσία 1', 1), $n('Υπηρεσία 2', 1),
                $n('Έργα / Portfolio'), $n('Blog / Νέα'), $n('Επικοινωνία')],
            'eshop' => [$n('Αρχική'), $n('Κατάστημα'), $n('Κατηγορία 1', 1), $n('Υποκατηγορία', 2), $n('Κατηγορία 2', 1), $n('Προσφορές', 1),
                $n('Η εταιρεία'), $n('Εξυπηρέτηση πελατών'), $n('Τρόποι πληρωμής', 1), $n('Αποστολές & επιστροφές', 1), $n('Συχνές ερωτήσεις', 1),
                $n('Επικοινωνία'), $n('Ο λογαριασμός μου'), $n('Όροι χρήσης & πολιτική απορρήτου')],
            'empty' => [$n('Αρχική')],
        ];
        return $trees[$kind] ?? $trees['basic'];
    }
}
