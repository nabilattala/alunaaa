<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * @property int $id
 * @property string $name
 * @property string $username
 * @property string $email
 * @property string $password
 * @property string $role
 * @property string|null $address
 * @property string|null $profile_photo
 */
class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'address',
        'profile_photo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Daftar role yang tersedia.
     *
     * @return array
     */
    public static function getAvailableRoles(): array
    {
        return ['admin', 'kelas', 'pengguna'];
    }

    /**
     * Ambil primary key untuk JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return custom claims untuk JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }

    /**
     * Cek apakah user memiliki role tertentu.
     *
     * @param string $role
     * @return bool
     */
    public function hasRoleCustom(string $role): bool
    {
        return $this->role === $role;
    }
}
