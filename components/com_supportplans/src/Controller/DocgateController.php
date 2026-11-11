<?php
namespace Support\Component\Supportplans\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;
use Support\Component\Supportplans\Site\Helper\DocGate;
use Support\Component\Supportplans\Site\Helper\ShareLink;

/** Public: one-time code for a protected document link (request and check). */
class DocgateController extends BaseController
{
    public function send(): void
    {
        [$type, $token] = $this->target();
        if (!Session::checkToken()) {
            $this->back($type, $token, 'Η σελίδα έληξε. Δοκιμάστε ξανά.');
            return;
        }
        $error = DocGate::sendCode($type, $token, max(0, $this->input->getInt('i', 0)));
        $this->back($type, $token, $error, $error === '' ? 'Σας στείλαμε τον κωδικό. Ελέγξτε το email σας (και τα ανεπιθύμητα).' : '');
    }

    public function verify(): void
    {
        [$type, $token] = $this->target();
        if (!Session::checkToken()) {
            $this->back($type, $token, 'Η σελίδα έληξε. Δοκιμάστε ξανά.');
            return;
        }
        $this->back($type, $token, DocGate::verify($type, $token, (string) $this->input->getString('code', '')));
    }

    private function target(): array
    {
        $type = $this->input->getCmd('doc_type', '');
        $token = (string) $this->input->getString('t', '');
        if (!in_array($type, ['offer', 'structure', 'plan'], true) || !ShareLink::isToken($token)) {
            throw new \RuntimeException('Not found', 404);
        }
        return [$type, $token];
    }

    private function back(string $type, string $token, string $error, string $info = ''): void
    {
        $app = Factory::getApplication();
        if ($error !== '') $app->enqueueMessage($error, 'error');
        if ($info !== '') $app->enqueueMessage($info);
        $app->redirect(DocGate::linkUrl($type, $token));
    }
}
