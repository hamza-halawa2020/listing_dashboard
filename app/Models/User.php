<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\AdminPermissionRegistry;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'referral_code',
        'referred_by_user_id',
        'points_balance',
        'role', //admin, member, service_provider
        'national_id',
        'location_id',
        'birth_date',
        'gender',
        'address',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'points_balance' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            if (blank($user->referral_code)) {
                $user->referral_code = static::generateUniqueReferralCode();
            }
        });
    }

    public static function generateUniqueReferralCode(): string
    {
        do {
            $code = 'REF' . Str::upper(Str::random(8));
        } while (static::query()->where('referral_code', $code)->exists());

        return $code;
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function familyMembers()
    {
        return $this->hasMany(FamilyMember::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referred_by_user_id');
    }

    public function referredUsers()
    {
        return $this->hasMany(User::class, 'referred_by_user_id');
    }

    public function sentReferrals()
    {
        return $this->hasMany(Referral::class, 'referrer_user_id');
    }

    public function receivedReferral()
    {
        return $this->hasOne(Referral::class, 'referred_user_id');
    }

    public function pointTransactions()
    {
        return $this->hasMany(PointTransaction::class);
    }

    public function visits()
    {
        return $this->hasMany(Visit::class);
    }

    public function chatConversations()
    {
        return $this->belongsToMany(ChatConversation::class, 'chat_conversation_participants')
            ->withPivot(['is_admin', 'last_read_at', 'joined_at'])
            ->withTimestamps();
    }

    public function sentChatMessages()
    {
        return $this->hasMany(ChatMessage::class, 'sender_id');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole(AdminPermissionRegistry::panelRoles())
            || $this->hasAnyPermission(AdminPermissionRegistry::allPermissions());
    }
}
