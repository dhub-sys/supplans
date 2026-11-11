<?php
/**
 * Task - Dashboard Notifications.
 *
 * Adds the routine "Dashboard – Ειδοποιήσεις" to Joomla's Scheduled Tasks. Each run
 * queues the renewal reminders that are due and sends the next batch of queued e-mails
 * (reminders, maintenance reports, campaigns). Schedule it every 5 minutes.
 */
defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Event\SubscriberInterface;

final class PlgTaskSupportplansnotify extends CMSPlugin implements SubscriberInterface
{
    use TaskPluginTrait;

    protected const TASKS_MAP = [
        'supportplans.notify' => [
            'langConstPrefix' => 'PLG_TASK_SUPPORTPLANSNOTIFY_RUN',
            'method'          => 'runNotifications',
        ],
    ];

    protected $autoloadLanguage = true;

    public static function getSubscribedEvents(): array
    {
        return [
            'onTaskOptionsList' => 'advertiseRoutines',
            'onExecuteTask'     => 'standardRoutineHandler',
        ];
    }

    protected function runNotifications(ExecuteTaskEvent $event): int
    {
        $helper = JPATH_SITE . '/components/com_supportplans/src/Helper/Notify.php';
        if (!class_exists(\Support\Component\Supportplans\Site\Helper\Notify::class)) {
            if (!is_file($helper)) {
                $this->logTask('Dashboard is not installed.', 'error');
                return Status::KNOCKOUT;
            }
            require_once JPATH_SITE . '/components/com_supportplans/src/Helper/OfferHelper.php';
            require_once JPATH_SITE . '/components/com_supportplans/src/Helper/Activity.php';
            require_once $helper;
        }
        try {
            $r = \Support\Component\Supportplans\Site\Helper\Notify::run();
            $this->logTask(sprintf('Reminders queued: %d, sent: %d, failed: %d', $r['queued'], $r['sent'], $r['failed']));
            return Status::OK;
        } catch (\Throwable $e) {
            $this->logTask($e->getMessage(), 'error');
            return Status::KNOCKOUT;
        }
    }
}
