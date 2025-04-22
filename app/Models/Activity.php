<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'sub_task_id',
        'user_id',
        'assigned_by',
        'title',
        'description',
        'status',
        'attachment',
        'deadline',
        'completed_at',
        'external_id',
        'external_name',
        'external_email',
    ];

    protected $casts = [
        'deadline' => 'date',
        'completed_at' => 'datetime',
    ];

    public function subTask(): BelongsTo
    {
        return $this->belongsTo(SubTask::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(TaskNotification::class);
    }

    public function isWaiting(): bool
    {
        return $this->status === 'waiting';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'inprogress';
    }

    public function isDone(): bool
    {
        return $this->status === 'done';
    }

    public function isExternalSubmission(): bool
    {
        return !is_null($this->external_id);
    }
}
