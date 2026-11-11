<?php
namespace Support\Component\Supportplans\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;
use Support\Component\Supportplans\Site\Helper\DocMail;
use Support\Component\Supportplans\Site\Helper\OfferHelper;

/** Email sending of any document (offer, structure proposal, package) and its private link. */
class DocmailController extends BaseController
{
    public function send(): void
    {
        Session::checkToken() or jexit('Invalid Token');
        [$type, $doc] = $this->doc();
        if (!$doc) {
            OfferHelper::redirect('view=documents');
            return;
        }
        $file = $this->input->files->get('attachment', [], 'raw');
        $error = DocMail::send($type, $doc, [
            'to' => $this->input->getString('to', ''), 'cc' => $this->input->getString('cc', ''),
            'subject' => $this->input->getString('subject', ''), 'message' => (string) $this->input->get('message', '', 'raw'),
            'with_link' => $this->input->getInt('with_link', 0) === 1,
        ], is_array($file) ? $file : null);
        if ($error !== '') {
            Factory::getApplication()->enqueueMessage($error, 'error');
        }
        OfferHelper::redirect(DocMail::backUrl($type, (int) $doc->id));
    }

    public function revoke(): void
    {
        Session::checkToken() or jexit('Invalid Token');
        [$type, $doc] = $this->doc();
        if ($doc) {
            DocMail::revoke($type, (int) $doc->id);
            Factory::getApplication()->enqueueMessage('Ο σύνδεσμος προβολής απενεργοποιήθηκε. Με νέα αποστολή δημιουργείται νέος.');
            OfferHelper::redirect(DocMail::backUrl($type, (int) $doc->id));
            return;
        }
        OfferHelper::redirect('view=documents');
    }

    private function doc(): array
    {
        $type = $this->input->getCmd('doc_type', '');
        return [$type, DocMail::isType($type) ? DocMail::find($type, $this->input->getInt('id')) : null];
    }
}
