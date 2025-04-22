<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'parent_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    public function ledSubTasks(): HasMany
    {
        return $this->hasMany(SubTask::class, 'leader_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'user_id');
    }

    public function assignedActivities(): HasMany
    {
        return $this->hasMany(Activity::class, 'assigned_by');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(TaskNotification::class, 'user_id');
    }

    public function isSc(): bool
    {
        return $this->role === 'sc';
    }

    public function isCaptain(): bool
    {
        return $this->role === 'captain';
    }

    public function isLeader(): bool
    {
        return $this->role === 'leader';
    }

    public function isMember(): bool
    {
        return $this->role === 'member';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // Users below: will get all users in the hierarchy under this user
    public function getAllUsersBelow(): array
    {
        $result = [];
        
        // Get direct children
        $children = $this->children;
        
        foreach ($children as $child) {
            $result[] = $child;
            
            // For each child, get their children recursively
            $childUsers = $child->getAllUsersBelow();
            $result = array_merge($result, $childUsers);
        }
        
        return $result;
    }

    // Users above: will get all users in the hierarchy above this user
    public function getAllUsersAbove(): array
    {
        $result = [];
        $currentUser = $this;
        
        while ($currentUser->parent) {
            $result[] = $currentUser->parent;
            $currentUser = $currentUser->parent;
        }
        
        return $result;
    }

    // Get captain: for leaders and members
    public function getCaptain()
    {
        if ($this->role === 'leader') {
            return $this->parent;
        }
        
        if ($this->role === 'member' && $this->parent && $this->parent->parent) {
            return $this->parent->parent;
        }
        
        return null;
    }

    // Get SC: the top level user
    public function getSC()
    {
        $parents = $this->getAllUsersAbove();
        return end($parents) ?: null;
    }

    // Check if a user is a direct child of this user
    public function isDirectSupervisorOf(User $user): bool
    {
        return $user->parent_id === $this->id;
    }

    // Check if a user is in this user's hierarchy
    public function isInHierarchyOf(User $user): bool
    {
        $usersBelow = $user->getAllUsersBelow();
        foreach ($usersBelow as $userBelow) {
            if ($userBelow->id === $this->id) {
                return true;
            }
        }
        return false;
    }
}
