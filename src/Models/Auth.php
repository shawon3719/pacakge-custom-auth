<?php

namespace sws\smartauth\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

use function PHPUnit\Framework\isNull;

class Auth extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public $table = 'users';

    protected $hidden = [
        'remember_token',
        'password',
    ];

    protected $dates = [
        'email_verified_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'remember_token',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function getEmailVerifiedAtAttribute($value)
    {
        return $value ? Carbon::createFromFormat('Y-m-d H:i:s', $value)->format('Y-m-d H:i:s') : null;
    }

    public function setEmailVerifiedAtAttribute($value)
    {
        $this->attributes['email_verified_at'] = $value ? Carbon::createFromFormat('Y-m-d H:i:s', $value)->format('Y-m-d H:i:s') : null;
    }

    public function setPasswordAttribute($input)
    {
        if ($input) {
            $this->attributes['password'] = app('hash')->needsRehash($input) ? Hash::make($input) : $input;
        }
    }

    public function scopeIsEmailVerified($query)
    {
        return $query->where('email_verified_at','!=','');
    }

    public static function loginAttempt($request){

        $credentials = $request->only('email', 'password');

        $userInfo = Auth::where('email','=', $credentials['email'])->first();

        if(!$userInfo){

            $request->session()->flash('failed','We do not recognize your email address');
            return false;
            
        }else
        {
            
            if($userInfo->email_verified_at != ''){

                if(Hash::check($credentials['password'], $userInfo->password)){

                    $request->session()->put('LoggedUser', $userInfo);
                    return true;

                }else{

                    $request->session()->flash('failed','Incorrect password');
                    return false;

                }

            }else{

                $request->session()->flash('failed','your account is not verified yet. we have sent you a verification email at '.$userInfo->email.'. please check your mail and verify your account.');
                return false;

            }
            
        }

    }

    public static function user(){
        if(Session::get('LoggedUser')){
            return Session::get('LoggedUser');
        }else{
            return false;
        }
    }

}
