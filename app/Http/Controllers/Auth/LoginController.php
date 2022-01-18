<?php
namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Comun\FaultC;

use App\Models\User;use App\Models\Token;use App\Models\UserSess;

use Session;
use Validator;
use Throwable;

class LoginController extends Controller
{
    //fonction secondaire
    private function _validate($request){//return [false,message] si request est non validé
        $role=[
            'email'=>['required','email','max:255'],
            'password'=>['required'],
        ];
        $message=[];
        if(Session::get('lang')=="ar"){
            $message=[
                'email.required'=>'يرجى إدخل بريدك الإلكتروني.',
                'email.email'=>"يرجى احترام تنسيق عنوان البريد الإلكتروني.",
                'email.max'=>"يجب أن يحتوي البريد الإلكتروني على 255 حرفًا كحد أقصى.",
                'password.required'=>'من فضلك أدخل رقمك السري.',
            ];
        }
        $validator = Validator::make($request->all(), $role,$message);
        return ['flag'=>$validator->passes(),'title'=>__('unsuccess'),'message'=>$validator->errors()->first()];
    }
    public function login(Request $rq){
        try{
            if(Auth::isConnected()['flag'])//test sur la session local de mon browser
                return false;
            //validatore step 1
            $_validate= $this->_validate($rq);
            if(!$_validate['flag']){//if the data is not validated
                return response()->json($_validate);
            }
            //validator step 2
            //multi login robot
            if(Auth::isMax('auth_login_max',10))
                return response()->json(['flag'=>false,'title'=>__('unsuccess'),'message'=>__('checkMax')]);
            $user= User::where('email',$rq->email);
            //isActive ??
            if(!$user->exists())
                return response()->json([false,'title'=>__('unsuccess'),'message'=>__('userExist')]);
            $user=$user->first();
            if(!$user->active)
                return response()->json([false,"title"=>__('unsuccess'),"message"=>__('accountActive')]); 
            if(!password_verify($rq->password,$user->password))
                return response()->json([false,"title"=>__('unsuccess'),'message'=>__('passwordExist')]);
            //initiate session 'auth_login_max'
            Session::forget('auth_login_max');
            //========================================================
            //generer tokens: Hsher access_token by App_key
            $access_token=hash("sha256",date('Y-m-d H:i:s.').gettimeofday()['usec'],false);
            $session_token=csrf_token();
            //test existance session token 
            $token= Token::where('session_token',$session_token)->where('user_id',$user->id);
            if(!$token->exists()) $token= new Token();
            else $token =$token->first();
            //save info login in table login_info
            $token->user_id=$user->id;
            $token->session_token=$session_token;
            $token->access_token=substr($access_token,0,60);//comme un petit cryptage
            $token->token_generated_at=date("Y-m-d H:i:s");
            $token->isConnected=1;
            $token->save();
            //========================================================
            $service="myacc";
            if($rq->service != "" && $rq->service!="myacc")
                $service=$rq->service;
            //ajouter userID dans la table sessions
            /*
            DB::table('sessions')->where('id', Session::getId())
                ->update(['user_id' => $user->id]);*/
            $session=UserSess::where('user_id',$user->id)->where('session_id',Session::getId());
            if(!$session->exists()){
                $session= new UserSess(); $session->user_id=$user->id; $session->session_id=Session::getId(); $session->save();
            }
            //get url redirection
            $continue=env('DEFAULT_URL')."?code1=".$session_token."&uid=".$user->id."&code2=".$access_token;
            if($rq->continue!="")
                $continue=$rq->continue."?code1=".$session_token."&uid=".$user->id."&code2=".$access_token;
            Session::put("auth_user",['user_id'=>$user->id,'session_token'=>$session_token]);
            return response()->json(['flag'=>true,'continue'=>$continue]);
        }catch(Throwable $e){
            FaultC::save("LoginController","login",$e);
            return response()->json(['flag'=>false,'title'=>__('unsuccess'),'message'=>__("erreur505")]);
        }
    }
    public function check_client(Request $rq){//requet externe
        try{
            if(Auth::isMax('auth_token_max',10))//cette session pour les request externe 
                return ['flag'=>false];           
            if($rq->has("user_id") && $rq->has("access_token") && $rq->has('session_token') && $rq->has('x_token')){            
                $x_token=hash("sha256",$rq->user_id.$rq->session_token.$rq->access_token.env('APP_KEY'),false);        
                if($x_token!=$rq->x_token)
                    return ['flag'=>false]; 
                //est ce que je doit priciser le service lorsque la genration de access token
                $token=Token::where("user_id",$rq->user_id)
                            ->where('session_token',$rq->session_token)
                            ->where('isConnected',1)
                            ->where('access_token',$rq->access_token);
                if($token->exists()){
                    //il ne faut pas initialiser access token_ dans la base donne car le client peut envoyer un token vide =""
                    $token= $token->first();
                    $now   = time();
                    $date2 = strtotime($token->token_generated_at);
                    $diff  = abs($now - $date2)/60;
                    if($diff<1){
                        Session::forget('auth_token_max');
                        $user=User::find($rq->user_id);
                        return ['flag'=>true,'email'=>$user->email,'name'=>$user->name];                    
                    }
                    else
                        return ['flag'=>false];
                }else{
                    return ['flag'=>false];
                }
            }else{
                return ['flag'=>false];
            }
        }catch(Throwable $e){
            FaultC::save("LoginController","check_client",$e);
            return ['flag'=>false,'title'=>__('unsuccess'),'message'=>__("erreur505")];
        }
    }
    public function isConnected(Request $rq){//Request Externe
        try{
            if(Auth::isMax('auth_token_max',10)){
                return ['flag'=>false];
            }    
            if(!$rq->has('user_id') || !$rq->has('session_token') || !$rq->has('x_token'))
                return ['flag'=>false];
            $x_token=hash("sha256",$rq->user_id.$rq->session_token.env('APP_KEY'),false);        
            if($x_token!=$rq->x_token)
                return ['flag'=>false];
            $auth=Auth::isConnected($rq->user_id,$rq->session_token);
            if($auth['flag'])//si true
                Session::forget('auth_isConnected_max');
            return $auth;
        }catch(Throwable $e){
            FaultC::save("LoginController","isConnected",$e);
            return ['flag'=>false,'title'=>__('unsuccess'),'message'=>__("erreur505")];
        }
    }
    public function edit_active(Request $rq){
        try{
            if($rq->has('active') && $rq->has('session_token') && $rq->has('uid') && $rq->has('x_token')){
                if($rq->active!=0 && $rq->active!=1)
                    return ['flag'=>false];
                if(Auth::isConnected($rq->uid,$rq->session_token)['flag']){
                    $x_token=hash("sha256",$rq->active.$rq->uid.$rq->session_token.env('APP_KEY').'admin.rancho.ma',false);
                    if($x_token==$rq->x_token){
                        $user=User::find($rq->uid);
                        $user->active=$rq->active;
                        $user->save();
                        return ['flag'=>true];
                    }else
                        return ['flag'=>false];
                }else
                    return ['flag'=>false];  
            }else
                return ['flag'=>false];
        }catch(Throwable $e){
            FaultC::save("ForgotController","edit_password",$e);
            return ['flag'=>false,'title'=>__('unsuccess'),'message'=>__("erreur505")];
        }
    }

}
