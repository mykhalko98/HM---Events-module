<?php

namespace Modules\Events\Notifications;

use Illuminate\Bus\Queueable;
use Hubmachine\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Hubmachine\Notifications\MailExtended;

class RefundRequest extends Notification implements ShouldQueue
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

    public $variables = ['user_name', 'subject_url', 'description', 'refund_link'];


    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $options = $this->getOptions();

        $variables['user_name']    = $options['user_name'] ?? '<a href="' . $options['author']->getUrl() . '">' . $options['author']->getName() . '</a>';
        $variables['subject_url']  = $options['subject_url'] ?? $options['subject']->getUrl();
        $variables['description']  = $options['description']
            ? "<p><blockquote style='color: #555555;padding:1.2em 30px 1.2em 20px;border-left:8px solid #78C0A8;position: relative;background:#EDEDED;'>{$options['description']}</blockquote></p>"
            : '';
        $variables['refund_link']  = $options['refund_link'];

        $mail_extended = (new MailExtended);
        !empty($this->getSubject()) && $mail_extended->subject($this->getSubject());
        !empty($this->getMarkdown()) && $mail_extended->markdown($this->getMarkdown());
        $mail_extended->appendViewData($variables);
        return $mail_extended;
    }

    /**
     * Prepare data for 'data' column.
     *
     * @param mixed $notifiable
     * @return array|void
     */
    public function toArray($notifiable)
    {
        $options = $this->getOptions();
        return ['description' => $options['description'] ?? ''];
    }

    public function getContent($options = [])
    {
        $options = $options + $this->options;

        $content = isset($options['author'])
            ? '<a href="' . $options['author']->getUrl() . '" class="link-secondary">' . $options['author']->getName() . '</a>'
            : (isset($options['author_type']) ? '<strong> Deleted ' . (new $options['author_type'])->getName() . '</strong>' : 'n/a');
        $content .= ' sent ';
        if (isset($options['subject'])) {
            $content .= '<a href="' . route('events.event.dashboard', ['link' => $options['subject']->getLink(), 'order' => $options['object']->getKey()]) . '" class="link-secondary">' . __('refund request') . '</a>';
        } else {
            $content .= __('refund request');
        }

        return $content;
    }

    public static function getNotificationIcon() {
        return '<div class="icon-circle"><icon-image data-icon="event"></icon-image></div>';
    }
}
