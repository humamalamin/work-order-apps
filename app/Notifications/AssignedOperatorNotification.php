<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssignedOperatorNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $workOrder;
    public $sender;

    /**
     * Create a new notification instance.
     */
    public function __construct($workOrder, $sender)
    {
        $this->workOrder = $workOrder;
        $this->sender    = $sender;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function databaseType(object $notifiable): string
    {
        return 'assign-work-order';
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = url('admin/work-orders');

        return (new MailMessage())
            ->from($this->sender->email, $this->sender->name)
            ->subject('Assign Work Order')
            ->greeting('Hi Mr. ' . $notifiable->name)
            ->line('You have work order with number:')
            ->line($this->workOrder->work_order_number)
            ->action('View Work Order', $url)
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            "body" => "work order number " . $this->workOrder->work_order_number,
            "icon" => null,
            "view" => "filament-notifications::notification",
            "color" => null,
            "title" => "Assign Operator Successfull",
            "format" => "filament",
            "status" => null,
            "actions" => [
                [
                    "url" => url('/admin/work-orders'),
                    "icon" => null,
                    "name" => "view",
                    "size" => "sm",
                    "view" => "filament-actions::button-action",
                    "color" => null,
                    "event" => null,
                    "label" => "View",
                    "tooltip" => null,
                    "iconSize" => null,
                    "eventData" => [],
                    "isDisabled" => false,
                    "isOutlined" => false,
                    "shouldClose" => false,
                    "iconPosition" => "before",
                    "extraAttributes" => [],
                    "shouldMarkAsRead" => true,
                    "dispatchDirection" => false,
                    "shouldMarkAsUnread" => false,
                    "dispatchToComponent" => null,
                    "shouldOpenUrlInNewTab" => true
            ]],
            "duration" => "persistent",
            "viewData" => [],
            "iconColor" => null,
        ];
    }
}
