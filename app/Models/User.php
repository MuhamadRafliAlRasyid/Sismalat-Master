<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Jetstream\HasTeams;
use App\Models\Traits\HasHashId;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Jetstream\HasProfilePhoto;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Bagian;

class User extends Authenticatable
{
    use HasApiTokens,HasFactory,HasProfilePhoto,HasTeams,Notifiable,TwoFactorAuthenticatable,HasHashId;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'bagian_id',
        'google_id',
        'profile_photo_path',
    ];
    public function bagian()
    {
        return $this->belongsTo(Bagian::class);
    }

    public function routeNotificationForSlack($notification)
    {
        return env('SLACK_WEBHOOK_URL');
    }
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
        'hashid',
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
    public function isAdmin()
{
    return $this->role === 'admin';
}

public function isSuper()
{
    return $this->role === 'super';
}

public function isKaryawan()
{
    return $this->role === 'karyawan';
}

public function isAdminOrSuper()
{
    return in_array($this->role, ['admin', 'super']);
}
}
