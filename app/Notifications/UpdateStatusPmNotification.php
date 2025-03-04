<?php

namespace App\Notifications;

use App\Models\WorkOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UpdateStatusPmNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $workOrder;
    /**
     * Create a new notification instance.
     */
    public function __construct(WorkOrder $workOrder)
    {
        $this->workOrder = $workOrder;
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

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
                    ->line('Work Order Number : '. $this->workOrder->work_order_number)
                    ->line('Status Changed to : '. $this->workOrder->status)
                    ->action('View', url('/admin/work-orders'))
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
            "body" => "work order number " . $this->workOrder->work_order_number . ' has been changed status to '. $this->workOrder->status,
            "icon" => null,
            "view" => "filament-notifications::notification",
            "color" => null,
            "title" => "Update Status Successfull",
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
