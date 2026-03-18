<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Panel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class User extends Authenticatable implements FilamentUser, HasAvatar, MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'profile_picture',
        'name',
        'email',
        'password',
        'phone_number',
        'address',
        'role',
        'id_photo',
        'id_verified_at',
        'id_rejection_reason',
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
            'id_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return in_array($this->role, ['admin', 'boar-raiser', 'customer']);
    }

    public function getFilamentAvatarUrl(): ?string
    {
        if (!empty($this->profile_picture)) {

            if (!Str::startsWith($this->profile_picture, ['http://', 'https://'])) {
                $path = ltrim($this->profile_picture, '/');

                // Strip an accidental leading "storage/" if it was persisted that way.
                if (str_starts_with($path, 'storage/')) {
                    $path = substr($path, strlen('storage/'));
                }

                // For records that only stored the basename, assume the default directory.
                if (! str_contains($path, '/')) {
                    $path = 'profile-pictures/' . $path;
                }

                return Storage::disk('public')->url($path);
            }
            return $this->profile_picture;
        }

        return null;
    }

    public function boars()
    {
        return $this->hasMany(Boar::class);
    }

    public function studServices()
    {
        return $this->hasMany(StudService::class);
    }
}
