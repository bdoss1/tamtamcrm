<?php

namespace App\Listeners\Project;

use App\Factory\NotificationFactory;
use App\Repositories\NotificationRepository;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProjectRestored implements ShouldQueue
{
    /**
     * @var NotificationRepository
     */
    protected NotificationRepository $notification_repo;

    /**
     * Create the event listener.
     *
     * @param NotificationRepository $notification_repo
     */
    public function __construct(NotificationRepository $notification_repo)
    {
        $this->notification_repo = $notification_repo;
    }

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle($event)
    {
        $data = [
            'id'          => $event->project->id,
            'customer_id' => $event->project->customer_id,
            'message'     => 'A project was restored'
        ];

        $fields = [
            'notifiable_id'   => $event->project->user_id,
            'account_id'      => $event->project->account_id,
            'notifiable_type' => get_class($event->project),
            'type'            => get_class($this),
            'data'            => json_encode($data),
            'action'          => 'restored'
        ];

        $notification = NotificationFactory::create($event->project->account_id, $event->project->user_id);
        $notification->entity_id = $event->project->id;
        $this->notification_repo->save($notification, $fields);
    }
}
