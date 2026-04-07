<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DemandeRepondue extends Notification
{
    use Queueable;

    protected $message;
    private $route; // route Angular

    public function __construct($message, $route = null)
    {
        $this->message = $message;
        $this->route = $route; // ex: '/demandes/encadrement'
    }

    public function via($notifiable)
    {
        // "database" = stockée en BDD
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => $this->message,
            'route' => $this->route, // on ajoute la route ici
        ];
    }
}