<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'user_id',
        'activity_id',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function isTaskCompletion(): bool
    {
        return $this->type === 'task_completion';
    }

    public function isDeadlineApproaching(): bool
    {
        return $this->type === 'deadline_approaching';
    }

    public function markAsRead(): void
    {
        $this->is_read = true;
        $this->save();
    }
}
