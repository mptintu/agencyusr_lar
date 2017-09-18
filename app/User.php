<?php

namespace App;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use SoapBox\Formatter\Formatter;
use \RecursiveIteratorIterator;
use \RecursiveArrayIterator;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract
{
   
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'map_user_accounts';
    protected $primaryKey = 'map_user_id';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id','username','password'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    
    public function getAuthIdentifier() {
        
    }
    
    public function getAuthPassword() {
        
    }
    
    public function getRememberToken() {
        
    }
    
    public function setRememberToken($value){
        
    }
    
    public function getRememberTokenName() {
        
    }
    
     public function getAuthIdentifierName() {
        
    }
    
}
