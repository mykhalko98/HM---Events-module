<?php

namespace Modules\Events\Notifications;

use Illuminate\Bus\Queueable;
use Hubmachine\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Hubmachine\Notifications\MailExtended;

class EventSoonStart extends Notification implements ShouldQueue
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

    public $variables = ['time', 'subject_name', 'subject_url'];

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $options = $this->getOptions();

        $variables['time'] = $options['time'];
        $variables['subject_name'] = isset($options['subject_name']) ? $options['subject_name'] : $options['subject']->getName();
        $variables['subject_url']  = isset($options['subject_url']) ? $options['subject_url'] : $options['subject']->getUrl();

        $mail_extended = (new MailExtended);
        !empty($this->getSubject()) && $mail_extended->subject($this->getSubject());
        !empty($this->getMarkdown()) && $mail_extended->markdown($this->getMarkdown());
        $mail_extended->appendViewData($variables);
        return $mail_extended;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $content = $this->getContent();

        return [
            'content' => $content
        ];
    }

    public function getContent($options = [])
    {
        $options = $options + $this->options;

        if (!isset($options['subject'])) {
            return "";
        }
        $text = isset($options['text']) ? $options['text'] : null;
        if (empty($text)) {
            if (isset($options['data']['content']) && !empty($options['data']['content'])) {
                $content = $options['data']['content'];
            } else {
                $text = " coming soon.";
            }
        }

        if (!isset($content)) {
            $content = '<a href="' . $options['subject']->getUrl() . '" class="link-secondary">';
            $content .= $options['subject']->getName() . '</a>';
            $content .= $text;
        }
        return $content;
    }

    public static function groupedContent($group)
    {
        $last_notification = $group->notifications->last();

        $content = '<a href="' . $last_notification->subject->getUrl() . '" class="link-secondary">';
        $content .= $last_notification->subject->getName() . '</a>';
        $content .= " coming soon.";

        return $content;
    }
}
