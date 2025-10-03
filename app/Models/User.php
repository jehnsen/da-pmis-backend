<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'email',
        'full_name',
        'username',
        'password',
        'school_id',
        'role_id',
        'department_id',
    ];

    

    // Relationships
    // public function school(): BelongsTo
    // {
    //     return $this->belongsTo(School::class);
    // }

    // public function role(): BelongsTo
    // {
    //     return $this->belongsTo(Role::class);
    // }

    // public function department(): BelongsTo
    // {
    //     return $this->belongsTo(Department::class);
    // }

    public function incidentTimelineCreated(): HasMany
    {
        return $this->hasMany(IncidentTimeline::class);
    }



    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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

    /**
     * Override the default login field.
     *
     * @return string
     */
    public function username()
    {
        return 'username';
    }
}
