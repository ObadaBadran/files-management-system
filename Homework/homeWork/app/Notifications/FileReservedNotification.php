<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class FileReservedNotification extends Notification
{
    use Queueable;

    private $file;
    private $user;

    public function __construct($file, $user)
    {
        $this->file = $file;
        $this->user = $user;
    }

    public function via($notifiable)
    {
        // يمكنك استخدام "mail", "database", "broadcast", إلخ.
        return ['database', 'broadcast']; // إرسال عبر قاعدة البيانات و Broadcast
    }

    public function toDatabase($notifiable)
    {
        return [
            'file_id' => $this->file->id,
            'file_name' => $this->file->name,
            'message' => 'تم حجز الملف بواسطة ' . $this->user->name
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'file_id' => $this->file->id,
            'file_name' => $this->file->name,
            'message' => 'تم حجز الملف بواسطة ' . $this->user->name
        ]);
    }
}

