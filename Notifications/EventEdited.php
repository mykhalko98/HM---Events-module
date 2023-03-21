<?php

namespace Modules\Events\Notifications;

use Illuminate\Bus\Queueable;
use Hubmachine\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Hubmachine\Notifications\MailExtended;

class EventEdited extends Notification implements ShouldQueue
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

    public $variables = ['user_name', 'subject_name', 'subject_url'];


    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $options = $this->getOptions();

        $variables['user_name']  = isset($options['user_name']) ? $options['user_name'] : '<a href="'.$options['author']->getUrl().'">'.$options['author']->getName().'</a>';
        $variables['subject_name'] = isset($options['subject_name']) ? $options['subject_name'] : $options['subject']->getName();
        $variables['subject_url'] = isset($options['subject_url']) ? $options['subject_url'] : $options['subject']->getUrl();

        $mail_extended = (new MailExtended);
        !empty($this->getSubject()) && $mail_extended->subject($this->getSubject());
        !empty($this->getMarkdown()) && $mail_extended->markdown($this->getMarkdown());
        $mail_extended->appendViewData($variables);
        return $mail_extended;
    }

    public function getContent($options = [])
    {
        $options = $options + $this->options;

        $content = isset($options['author'])
            ? '<a href="' . $options['author']->getUrl() . '" class="link-secondary">' . $options['author']->getName() . '</a>'
            : (isset($options['author_type']) ? '<strong> Deleted ' . (new $options['author_type'])->getName() . '</strong>' : 'n/a');
        $content .= ' edited ';
        $content .= isset($options['subject'])
            ? '<a href="' . $options['subject']->getUrl() . '" class="link-secondary">' . $options['subject']->getName() . '</a>'
            : (isset($options['subject_type']) ? '<strong> deleted ' . (new $options['subject_type'])->getName() . '</strong>' : 'n/a');

        return $content;
    }

    public static function groupedContent($group)
    {
        $author = $group->authors->last();
        $last_notification = $group->notifications->last();

        $content = is_object($author)
            ? '<span class="text-pink">' . $author->first()->getName() . '</span>'
            : '<span class="text-pink">Deleted ' . (new $author)->getName() . '</span>';

        $content .= ' edited ';
        $content .= $last_notification->subject
            ? '<a href="' . ($last_notification->subject->getUrl()) . '" class="link-secondary">' . $last_notification->subject->getName() . '</a>'
            : '<span class="text-pink">' . (new $last_notification->subject_type)->getName() . '</a>';

        return $content;
    }

    public static function getNotificationIcon() {
        return '<div class="icon-circle"><icon-image data-icon="event"></icon-image></div>';
    }
}
