<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class InviteResponseNotification extends Notification
{
    protected $status;
    protected $userName;

    public function __construct($status, $userName)
    {
        $this->status = $status;
        $this->userName = $userName;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => "The user {$this->userName} has {$this->status} the group invitation.",
            'status' => $this->status,
            'user_name' => $this->userName,
        ];
    }
}
