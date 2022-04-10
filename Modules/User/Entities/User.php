<?php

namespace Modules\User\Entities;

use \Cartalyst\Sentinel\Users\EloquentUser;
use Laravel\Passport\HasApiTokens;

class User extends EloquentUser
{
    use HasApiTokens;
    protected $loginNames = ['email','username'];

    protected $fillable = [
        'email',
        'username',
        'password',
        'last_name',
        'first_name',
        'permissions',
    ];

}
