<?php

namespace App;

use Illuminate\Support\Facades\Auth;

trait CreatedByTrait
{
    /**
     * Boot the trait.
     */
    public static function bootCreatedByTrait(): void
    {
        static::creating(function ($model) {
            // Set created_by_id dengan ID user yang sedang login
            if (Auth::check()) {
                $model->created_by_id = Auth::id();
            }
        });
    }
}
