<?php

namespace Modules\Events\Notifications;

use Illuminate\Bus\Queueable;
use Hubmachine\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Hubmachine\Notifications\MailExtended;

class TicketSuccessfullyIssued extends Notification implements ShouldQueue
{
    use Queueable;

    protected $channels = [
        'mail' => [
            'online' => true,
            'offline' => true
        ],
        'database' => [
            'online' => true,
            'offline' => true
        ]
    ];

    public $groupable = false;

    public $variables = ['subject_url', 'order_number', 'mytickets_url'];


    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $options = $this->getOptions();

        $variables['subject_url']   = $options['subject_url'] ?? $options['subject']->getUrl();
        $variables['order_number']  = $options['order_number'] ?? $options['object']->getKey();
        $variables['mytickets_url'] = $options['mytickets_url'] ?? route('events.mytickets');

        $mail_extended = (new MailExtended);
        !empty($this->getSubject()) && $mail_extended->subject($this->getSubject());
        !empty($this->getMarkdown()) && $mail_extended->markdown($this->getMarkdown());
        $mail_extended->appendViewData($variables);
        return $mail_extended;
    }

    public function getContent($options = [])
    {
        $options = $options + $this->options;

        $content = 'Your ticket on the ' .
            (isset($options['subject'])
                ? '<a href="' .$options['subject']->getUrl(). '">'.$options['subject']->getName().'</a>'
                : (isset($options['subject_type']) ? '<strong> Deleted ' . (new $options['subject_type'])->getName() . '</strong>' : 'n/a'))
         . ' has been successfully issued.';

        return $content;
    }

    public static function getNotificationIcon() {
        return '<div class="icon-circle"><icon-image data-icon="confirmation_number"></icon-image></div>';
    }
}
