<?php declare(strict_types=1);

namespace App\Application\Actions\Cup;

use App\Domain\AbstractAction;
use App\Domain\Entities\Task;
use App\Domain\Entities\User;
use App\Domain\Service\Notification\NotificationService;
use App\Domain\Service\Task\TaskService;

class RefreshAction extends AbstractAction
{
    protected function action(): \Slim\Http\Response
    {
        /** @var User $user */
        $user = $this->request->getAttribute('user', false);
        $notificationService = NotificationService::getWithContainer($this->container);

        $exists = (array) $this->request->getParam('tasks');
        $taskService = TaskService::getWithContainer($this->container);
        $tasks = collect()
            ->merge($taskService->read(['uuid' => array_keys($exists)]))
            ->merge($taskService->read(['order' => ['date' => 'desc'], 'limit' => 50]))
            ->keyBy('uuid')
            ->sortBy('date');
        $output = ['new' => [], 'update' => [], 'delete' => []];

        foreach ($tasks as $task) {
            /** @var Task $task */
            if (!in_array($task->getUuid()->toString(), array_keys($exists), true)) {
                $output['new'][] = array_except($task->toArray(), ['params', 'output']);
            } else {
                if (
                    in_array($task->getUuid()->toString(), array_keys($exists), true)
                    && (
                        $task->getStatus() !== $exists[$task->getUuid()->toString()]['status']
                        || (int) $task->getProgress() !== (int) $exists[$task->getUuid()->toString()]['progress']
                    )
                ) {
                    $output['update'][] = array_except($task->toArray(), ['params', 'output']);
                }
            }
        }
        foreach ($exists as $uuid => $props) {
            if (!isset($tasks[$uuid])) {
                $output['delete'][] = $uuid;
            }
        }

        return $this->respondWithJson([
            'notification' => $notificationService
                ->read([
                    'user_uuid' => [\Ramsey\Uuid\Uuid::NIL, $user->getUuid()],
                    'order' => ['date' => 'asc'],
                    'limit' => 25,
                ])
                ->whereNotIn('uuid', (array) $this->request->getParam('notifications'))
                ->map(fn ($item) => array_except($item->toArray(), ['params', 'user_uuid']))
                ->values(),

            'task' => $output,
        ]);
    }
}
