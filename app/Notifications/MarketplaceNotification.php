<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MarketplaceNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $body,
        public ?string $url = null,
        public string $type = 'info'
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'url' => $this->url,
            'type' => $this->type,
        ];
    }
}
