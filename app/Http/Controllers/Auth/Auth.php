<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Session;

use App\Models\User;use App\Models\Token;
class Auth{
     /*variables session
        ->auth_user
        ->auth_token_register
        ->auth_token_forgot
        ->auth_token_forgot_page
        ->auth_login_max
        ->auth_check_register_max
        ->auth_forgot_max
        ->auth_forgot_check_code_max
        ->auth_token_forgot_page_max
        ->auth_forgot_email //save email
        ->auth_forgot_check_code2_max
        ->auth_token_max
    */
    //Function
    public static function route(){
        //view
        Route::get("/signup", function(){return view('auth.signup');})->middleware(['language','myAuth']);
        Route::get("/signup/verfiy",function(){if(!Session::has("auth_new_user")) abort(404); else return view('auth.signupVerify');})->middleware(['language','myAuth']);
        Route::get("/signin", function(Request $rq){return view('auth.signin',["continue"=>$rq->continue,"service"=>$rq->service]);})->middleware(['language','myAuth']);
        Route::get("/forgot-password", function(){return view('auth.forgotPassword');})->middleware(['language','myAuth']);
        Route::get("/forgot-password/verify", function(){return view('auth.forgotVerify');})->middleware(['language','myAuth']);
        Route::get("/change-password/{code}", function($code){if($code != Session::get("auth_token_forgot_page") || Session::get("auth_token_forgot_page") != Session::get('auth_token_forgot')) abort(404); return view("auth.changePassword",['code'=>Session::get("auth_token_forgot_page")]);})->middleware(['language','myAuth']);
        
        route::get("/terms",function(){return view("terms");})->middleware(['language']);  

        //action
        Route::post("/api/user/add/check",[RegisterController::class,"check"])->middleware(['language']);//send Email
        Route::post("/api/user/add",[RegisterController::class,"add"])->middleware(['language']);//add user in database
        Route::post("/api/user/login",[LoginController::class,'login'])->middleware(['language']);
        Route::post("/api/user/forgot-password/sendMail",[ForgotController::class,'sendMail'])->middleware(['language']);
        Route::post("/api/user/forgot-password/checkCode",[ForgotController::class,'checkCode'])->middleware(['language']);
        Route::post("/api/user/forgot-password/changePassword",[ForgotController::class,'changePassword'])->middleware(['language']);
                             
        //extenanl API
        Route::get("/ext/api/getToken",function(){return csrf_token();});
        Route::get("/ext/api/user/login/check",[ExtController::class,'check_client']);
        Route::get("/ext/api/user/isConnected",[ExtController::class,'isConnected']);
        Route::get("/ext/api/user/edit/name",[ExtController::class,'edit_name']);
        Route::get("/ext/api/user/edit/password",[ExtController::class,'edit_password']);
        Route::get("/ext/api/user/edit/active",[ExtController::class,'edit_active']);
        
        Route::get("/ext/api/user/logout",[LogoutController::class,'logout']);
    }
    //========================================================
    public static function isMax($variable,$valeur){
        if(Session::has($variable)){
            if(Session::get($variable)<$valeur){
                Session::put($variable,Session::get($variable)+1);
                return false;
            }else{
                return true;
            }
        }else{
            Session::put($variable,1);
            return false;
        }
    }
    public static function isConnected($user_id=null,$session_token=null){
        if($user_id==null && $session_token==null){//pour l'application accouts local=>on a pas besoin de retourner les info user, car on peut la'cceder directement par ORM
            if(Session::has('auth_user')){//is connected
                $user=Session::get('auth_user');
                $token= Token::where('user_id',$user['user_id'])
                            ->where('session_token',$user['session_token'])
                            ->where('isConnected',1);
                if($token->exists()){
                    return ['flag'=>true];
                }else
                    return ['flag'=>false];
            }else
                return ['flag'=>false];
        }else{//pour les requets exterieur
            $token=Token::where("user_id",$user_id)
                        ->where('session_token',$session_token)
                        ->where('isConnected',1);
            if($token->exists()){
                //il ne faut pas initialiser access_token dans la base donne car le client peut envoyer un token vide =""
                $token=$token->first();
                $now   = time();
                $date2 = strtotime($token->token_generated_at);
                $diff  = abs($now - $date2)/60;
                if($diff<15){
                    return ['flag'=>true];
                }
                else{//s'il depasse le temps on doit le deconecté
                    $token->isConnected=0;
                    $token->save();
                    return ['flag'=>false];
                }
            }else{
                return ['flag'=>false];
            }
        }
    }
}