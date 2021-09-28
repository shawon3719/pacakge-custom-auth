<?php

namespace sws\smartauth\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use sws\smartauth\Models\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;

use function PHPUnit\Framework\isNull;

class AuthService
{
    public function register($request){

        $user = Auth::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if($user){
            $request->session()->flash('success','Registration successfull.');
        }else{
            $request->session()->flash('failed','Something went wrong, try again later!');
        }

        $token = Crypt::encrypt($request->email);
  
        Mail::send('smartauth::Email.userVerificationEmail', ['user' => $user, 'token' => $token], function($mail) use($user){
              $mail->from(config('sws-auth.send_email_from'));
              $mail->to($user->email);
              $mail->subject('User Verification E-mail');
          });
          
    }

    public function verifyEmail($token){

        $email = Crypt::decrypt($token);

        $user = Auth::where('email', $email)->first();

        if(!is_null($user) ){
                $user->email_verified_at = Carbon::now('Asia/Dhaka');
                $user->save();
                $data['type'] = 'success';
                $data['message'] = "Your e-mail is verified. You can login now.";
        } else {
            $data['type'] = 'failed';
            $data['message'] = 'Sorry your email cannot be identified.';
        }

        return $data;
    }

    public function forgotPassword($request){

        if(isset($request->email)){

            $user = Auth::where('email', $request->email);

            $token = Crypt::encrypt($request->email);

            if($user->exists()){

                if($user->first()->email_verified_at != ''){

                    Mail::send('smartauth::Email.passwordResetEmail', ['token' => $token], function($mail) use($request){
                        $mail->from(config('sws-auth.send_email_from'));
                        $mail->to($request->email);
                        $mail->subject('Password Reset E-mail');
                    });

                    $request->session()->flash('success', 'An email with password reset link has been sent to your mail. please check and follow the mentioned precedure.');
    
                }else{
    
                    $request->session()->flash('failed','your account is not verified yet. we have sent you a verification email at '.$request->email.'. please check your mail and verify your account.');

                }
            
            }else{

                $request->session()->flash('failed', 'you are not a registered user.');

            }
        }
    }

    public function resetPassword($request){

        if(isset($request->email)){
            
            $user = Auth::where('email', $request->email)->first();

            if(!empty($user) && $user->email != null){

                $user->password = Hash::make($request->password);
                $user->save();
                $request->session()->flash('success', 'Your Password has been updated successfully. You can login now with your new password.');
                return true;

            }else{

                $request->session()->flash('failed', 'No accound found for this email.');
                return false;

            }
        }

    }
}