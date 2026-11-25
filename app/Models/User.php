<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    public function canAccessMenu(string $key): bool
    {
        if ($this->hasRole('Administrador')) {
            return true;
        }

        return in_array($key, $this->menuAccessKeys(), true);
    }

    public function menuAccessKeys(): array
    {
        if (! isset($this->menuAccessKeysCache)) {
            $this->menuAccessKeysCache = DB::table('user_menu_access')
                ->where('user_id', $this->id)->pluck('menu_key')->all();
        }

        return $this->menuAccessKeysCache;
    }

    private ?array $menuAccessKeysCache = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'activo',
        'telefono',
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
}
