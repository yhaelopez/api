<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use App\Models\User;

trait RestoreStamps
{
    /**
     * Boot the trait and add the restored event listener
     */
    public static function bootRestoreStamps(): void
    {
        static::restoring(function ($model) {
            $model->recordRestoreStamp();
        });
    }

    /**
     * Record who restored the model and when
     */
    public function recordRestoreStamp(): void
    {
        $this->restored_by = Auth::id();

        $this->restored_at = now();
    }

    /**
     * Get the user who restored this model
     */
    public function restorer()
    {
        return $this->belongsTo(User::class, 'restored_by');
    }
}
