<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Comun\EmailC;
use App\Http\Controllers\Comun\FaultC;

use App\Models\User;

use Session;
use Validator;
use Throwable;

class ForgotController extends Controller
{
    //private function 
    private function _validate($request){//return [false,message] si request est non validé
        $role=[
            'password1'=>['required','min:8'],
            'password2'=>['required','min:8','same:password1'],
        ];
        $message=[];
        if(Session::get('lang')=="ar"){
            $message=[
                'password1.required'=>'يرجى إدخل بريدك الإلكتروني.',
                'password1.min'=>'يجب أن تتكون كلمة المرور من 8 أحرف على الأقل.',
                'password2.required'=>'يرجى إدخل بريدك الإلكتروني.',
                'password2.min'=>'يجب أن تتكون كلمة المرور من 8 أحرف على الأقل.',
                'password2.same'=>'يرجى التحقق من كلمة السر.',
            ];
        }
        $validator = Validator::make($request->all(), $role,$message);
        return ['flag'=>$validator->passes(),'title'=>__('unsuccess'),'message'=>$validator->errors()->first()];
    }
    //public function
    public function sendMail(Request $rq){
        try{
            if(Auth::isConnected()['flag'])
                return false;
            if($rq->email==null)
                return response()->json(['flag'=>false,'title'=>__('unsuccess'),'message'=>__('enterYourEmailAddress')]); 
            $user=User::where('email',$rq->email);
            if(!$user->exists())
                return response()->json(['flag'=>false,'title'=>__('unsuccess'),'message'=>__('emailSubject')]);
            if(Auth::isMax('auth_forgot_max',5))
                return response()->json(['flag'=>false,'title'=>__('unsuccess'),'message'=>__('checkMax')]);
    
            Session::put("auth_token_forgot",rand(10000,99999));
            Session::put('auth_forgot_email',trim(strtolower($rq->email)));
            //send Email          
            EmailC::send('forgotPassword',$rq->email,['token'=>Session::get("auth_token_forgot")]);    
            return response()->json(['flag'=>true]);
        }catch(Throwable $e){
            FaultC::save("ForgotController","sendMail",$e);
            return response()->json(['flag'=>false,'title'=>__('unsuccess'),'message'=>__('erreur505')]);
        }
    }
    public function checkCode(Request $rq){
        try{
            if(Auth::isConnected()['flag'])
                return false;
            if(Auth::isMax('auth_forgot_check_code_max',3))
                return response()->json(['flag'=>false,'title'=>__('unsuccess'),'message'=>__('checkMax')]);
            if($rq->code!=Session::get("auth_token_forgot"))
                return response()->json(['flag'=>false,'title'=>__('unsuccess'),'message'=>__('codeIncorrect')]);
            $token=Session::get("auth_token_forgot");// si j'utiliser rand(10000,99999) on aura un fail;
            Session::put("auth_token_forgot_page",$token);
            return response()->json(['flag'=>true,'code'=>$token]);
        }catch(Throwable $e){
            FaultC::save("ForgotController","checkCode",$e);
            return response()->json(['flag'=>false,'title'=>__('unsuccess'),'message'=>__('erreur505')]);
        }
    }
    public function changePassword(Request $rq){
        try{
            if(Auth::isConnected()['flag'])
                return false;
            if(Auth::isMax('auth_forgot_check_code2_max',3))
                return response()->json(['flag'=>false,'title'=>__('unsuccess'),'message'=>__('checkMax')]);
            //cette condition est trés important
            if(Session::get("auth_token_forgot_page") != Session::get('auth_token_forgot'))
                return response()->json(['flag'=>false,'title'=>__('unsuccess'),'message'=>__('codeIncorrect')]);
            if($rq->code!=Session::get('auth_token_forgot_page'))
                return response()->json(['flag'=>false,'title'=>__('unsuccess'),'message'=>__('codeIncorrect')]);
            //validatore step 1
            $_validate= $this->_validate($rq);
            if(!$_validate['flag']){//if the data is not validated
                return response()->json($_validate);
            }
            //change password
            $user=User::where('email',Session::get('auth_forgot_email'))->first();
            $user->password=password_hash($rq->password1, PASSWORD_DEFAULT);
            $user->save();
            //initialize the sessions
            Session::forget('auth_forgot_check_code_max');
            Session::forget("auth_forgot_check_code2_max");
            Session::forget('auth_forgot_max');
            Session::forget('auth_token_forgot');
            Session::forget('auth_forgot_email');
            Session::forget('auth_token_forgot_page');
            Session::forget('auth_token_forgot_page_max');
            return response()->json(['flag'=>true]);
        }catch(Throwable $e){
            FaultC::save("ForgotController","ChangePassword",$e);
            return parent::response(false,array("fr"=>"Une erreur est survenue. Veuillez réessayer plus tard","ar"=>"حدث خطأ. الرجاء معاودة المحاولة في وقت لاحق"));
        }
    }
   
}
