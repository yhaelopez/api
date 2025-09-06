<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

trait UserStamps
{
    /**
     * Boot the trait and add the event listeners
     */
    public static function bootUserStamps(): void
    {
        static::creating(function ($model) {
            $model->recordCreatedBy();
        });

        static::updating(function ($model) {
            $model->recordUpdatedBy();
        });

        static::deleting(function ($model) {
            $model->recordDeletedBy();
        });

        static::restoring(function ($model) {
            $model->recordRestoreStamp();
        });
    }

    /**
     * Record who created the model
     * Only records if it's a user creating another user (not self, not admin)
     */
    public function recordCreatedBy(): void
    {
        // Only record if it's a user doing the action (api guard), not an admin
        if (Auth::guard('api')->check()) {
            $this->created_by = Auth::guard('api')->id();
        }
    }

    /**
     * Record who updated the model
     * Only records if it's a user updating another user (not self, not admin)
     */
    public function recordUpdatedBy(): void
    {
        // Only record if it's a user doing the action (api guard), not an admin
        if (Auth::guard('api')->check()) {
            $this->updated_by = Auth::guard('api')->id();
        }
    }

    /**
     * Record who deleted the model
     * Only records if it's a user deleting another user (not self, not admin)
     */
    public function recordDeletedBy(): void
    {
        // Only record if it's a user doing the action (api guard), not an admin
        if (Auth::guard('api')->check()) {
            $this->deleted_by = Auth::guard('api')->id();
        }
    }

    /**
     * Record who restored the model and when
     * Only records if it's a user restoring another user (not self, not admin)
     */
    public function recordRestoreStamp(): void
    {
        // Only record if it's a user doing the action (api guard), not an admin
        if (Auth::guard('api')->check()) {
            $this->restored_by = Auth::guard('api')->id();
            $this->restored_at = now();
        }
    }

    /**
     * Get the user who created this model
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this model
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who deleted this model
     */
    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user who restored this model
     */
    public function restorer()
    {
        return $this->belongsTo(User::class, 'restored_by');
    }
}
