<?php

namespace App\Filament\Resources\WorkOrderResource\Pages;

use App\Filament\Resources\WorkOrderResource;
use App\Models\User;
use App\Notifications\AssignedOperatorNotification;
use Filament\Actions;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateWorkOrder extends CreateRecord
{
    protected static string $resource = WorkOrderResource::class;

    public function afterCreate(): void
    {
        $workOrder = $this->record;

        $user = User::find($workOrder->assigned_operator_id);
        $user->notify(new AssignedOperatorNotification($workOrder, Auth::user()));
    }
}
