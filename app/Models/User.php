<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'photo_path',
        'password',
        'notify_mail',
        'notify_sms',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'notify_mail' => 'boolean',
            'notify_sms' => 'boolean',
        ];
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
    public function technicalVisits(): HasMany
    {
        return $this->hasMany(TechnicalVisit::class);
    }

    public function photoUrl(): ?string
    {
        return $this->photo_path ? asset('storage/'.$this->photo_path) : null;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole('admin');
    }
    public function insuranceQuotes(): HasMany
    {
        return $this->hasMany(InsuranceQuote::class);
    }
}