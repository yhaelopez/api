<?php

namespace App\Traits;

use App\Models\Admin;
use Illuminate\Support\Facades\Auth;

trait AdminStamps
{
    /**
     * Boot the trait and add the event listeners
     */
    public static function bootAdminStamps(): void
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
     */
    public function recordCreatedBy(): void
    {
        if (Auth::guard('admin')->check()) {
            $this->created_by = Auth::guard('admin')->id();
        }
    }

    /**
     * Record who updated the model
     */
    public function recordUpdatedBy(): void
    {
        if (Auth::guard('admin')->check()) {
            $this->updated_by = Auth::guard('admin')->id();
        }
    }

    /**
     * Record who deleted the model
     */
    public function recordDeletedBy(): void
    {
        if (Auth::guard('admin')->check()) {
            $this->deleted_by = Auth::guard('admin')->id();
        }
    }

    /**
     * Record who restored the model and when
     */
    public function recordRestoreStamp(): void
    {
        if (Auth::guard('admin')->check()) {
            $this->restored_by = Auth::guard('admin')->id();
            $this->restored_at = now();
        }
    }

    /**
     * Get the admin who created this model
     */
    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    /**
     * Get the admin who last updated this model
     */
    public function updater()
    {
        return $this->belongsTo(Admin::class, 'updated_by');
    }

    /**
     * Get the admin who deleted this model
     */
    public function deleter()
    {
        return $this->belongsTo(Admin::class, 'deleted_by');
    }

    /**
     * Get the admin who restored this model
     */
    public function restorer()
    {
        return $this->belongsTo(Admin::class, 'restored_by');
    }
}
