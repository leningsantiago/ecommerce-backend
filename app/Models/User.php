<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'surname',
        'type_user',
        'state',
        'role_id',
        'email',
        'password',
    ];

    public function setPasswordAttribute($password) {
        if ($password) {
            $this->attributes["password"] = bcrypt($password);
        }
    }

    public function role() {
        return $this->belongsTo(Role::class);
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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    //put these methods at the bottom of your class body

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
        'email'=>$this->email,
        'name'=>$this->name
        ];
    }

    public function scopefilterAdvance($query, $state,$search) {
        if ($state) {
            $query->where("state", $state);
        }
        if ($search) {
            $query->where("name", "like","%".$search."%")->orWhere("surname", "like", "%".$search."%");
        }

        return $query;
    }

}
