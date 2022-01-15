<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Comun\EmailC;
use App\Http\Controllers\Comun\FaultC;
use App\Http\Controllers\Comun\LangC;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Throwable;
use Validator;
use Session;

use App\Models\User;use App\Models\Role;use App\Models\Service;

class RegisterController extends Controller
{
    //paramètres
        
    //fonction secondaire
    private function _validate($request){//return [false,message] si request est non validé
        $role=[
            'name'=>['required','max:255'],
            'email'=>['required','email','max:255'],
            'password'=>['required','min:8','same:password2'],
        ];
        $message=['password.same'=>'Please check the password.'];
        if(LangC::getLang()=="ar"){
            $message=[
                'name.required'=>'يرجى إدخال الإسم.',
                'name.max'=>'يجب ألا يزيد الاسم عن 255 حرفًا.',
                'email.required'=>'يرجى إدخل بريدك الإلكتروني.',
                'email.email'=>"يرجى احترام تنسيق عنوان البريد الإلكتروني.",
                'email.max'=>"يجب أن يحتوي البريد الإلكتروني على 255 حرفًا كحد أقصى.",
                'password.required'=>'من فضلك أدخل رقمك السري.',
                'password.min'=>'يجب أن تتكون كلمة المرور من 8 أحرف على الأقل.',
                'password.same'=>'يرجى التحقق من كلمة السر.',
            ];
        }
        $validator = Validator::make($request->all(), $role,$message);
        return ['flag'=>$validator->passes(),'title'=>__('unsuccess'),'message'=>$validator->errors()->first()];
    }
    //fonction principale
    public function check(Request $rq){
        try{
            if(Auth::isConnected()['flag'])
                return false;
            //validatore étape 1
            $_validate= $this->_validate($rq);
            if(!$_validate['flag']){//if the data is not validated
                return response()->json($_validate);
            }
            //Validator étape 2
            if(User::where('email',$rq->email)->exists()){
                return response()->json(['flag'=>false,'title'=>__('unsuccess'),'message'=>__('emailExist')]); 
            }
            if(Auth::isMax('auth_check_register_max',10))
                return response()->json(['flag'=>false,'title'=>__('unsuccess'),'message'=>__('checkMax')]);
            //save new user info
            $new_user=['name'=>$rq->name,'email'=>trim(strtolower($rq->email)),'password'=>password_hash($rq->password, PASSWORD_DEFAULT)];
            Session::put('auth_new_user',$new_user);
            //generate token
            if(!Session::has('auth_token_register'))
                Session::put("auth_token_register",rand(10000,99999));
            //send Email          
            EmailC::send('signup',$rq->email,['token'=>Session::get("auth_token_register")]);    
            return response()->json(['flag'=>true,'title'=>__('success'),'message'=>__('sendCode')]);   
        }catch(Throwable $e){
            FaultC::save("RegisterController","check",$e);
            return response()->json(['flag'=>false,'title'=>__('unsuccess'),'message'=>__("erreur505")]);
        }
    }
    public function add(Request $rq){
        try{
            if(Auth::isConnected()['flag'])
                return false;
            if(Auth::isMax('auth_add_register_max',10))
                return response()->json(['flag'=>false,'title'=>__('unsuccess'),'message'=>__('checkMax')]);
            //validatore step 1
            if($rq->code!=Session::get('auth_token_register')){
                return response()->json(['flag'=>false,'title'=>__('unsuccess'),'message'=>__('codeIncorrect')]);
            }
            //add it in database
            $new_user=Session::get('auth_new_user');
            $user=new User();
            $user->name=$new_user['name'];
            $user->email=$new_user['email'];
            $user->password=$new_user['password'];
            $user->save();
            
            //initiate Session
            Session::forget('auth_add_register_max');
            Session::forget('auth_check_register_max');
            Session::forget('auth_new_user');
            Session::forget('auth_token_register');
            return response()->json(['flag'=>true]);
        }catch(Throwable $e){
            FaultC::save("RegisterController","add",$e);
            return response()->json(['flag'=>false,'title'=>__('unsuccess'),'message'=>__("erreur505")]);
        }
    }
}
