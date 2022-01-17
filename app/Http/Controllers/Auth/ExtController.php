<?php
namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Comun\FaultC;

use App\Models\User;use App\Models\LoginInfo;use App\Models\Token;

use Session;
use Validator;
use Throwable;

class ExtController extends Controller
{
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
            FaultC::save("ExtController","check_client",$e);
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
                Session::forget('auth_token_max');
            return $auth;
        }catch(Throwable $e){
            FaultC::save("ExtController","isConnected",$e);
            return ['flag'=>false,'title'=>__('unsuccess'),'message'=>__("erreur505")];
        }
    }
    //reserver pour admin.rancho.ma
    public function edit_active(Request $rq){
        try{
            if($rq->has('active') && $rq->has('session_token') && $rq->has('uid') && $rq->has('x_token')){
                if($rq->active!=0 && $rq->active!=1)
                    return ['flag'=>false];
                if(Auth::isConnected($rq->uid,$rq->session_token)['flag']){
                    $x_token=hash("sha256",$rq->active.$rq->uid.$rq->session_token.env('APP_KEY').'admin.zlayga.com',false);
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
            FaultC::save("ExtController","edit_password",$e);
            return ['flag'=>false,'title'=>__('unsuccess'),'message'=>__("erreur505")];
        }
    }
    //ces fonction reserver pour myAccount
    public function edit_name(Request $rq){
        try{
            //x_token = hash(name.$session_token.env('APP_KEY').'myaccount.rancho.ma')
            if($rq->has('name') && $rq->has('session_token') && $rq->has('uid') && $rq->has('x_token')){
                if(Auth::isConnected($rq->uid,$rq->session_token)['flag']){
                    $x_token=hash("sha256",$rq->name.$rq->uid.$rq->session_token.env('APP_KEY').'myaccount.zlayga.com',false);
                    if($x_token==$rq->x_token){
                        $user=User::find($rq->uid);
                        $user->name=$rq->name;
                        $user->save();
                        return ['flag'=>true];
                    }else
                        return ['flag'=>false];
                }else
                    return ['flag'=>false];  
            }else
                return ['flag'=>false];
        }catch(Throwable $e){
            FaultC::save("ExttController","edit_name",$e);
            return ['flag'=>false,'title'=>__('unsuccess'),'message'=>__("erreur505")];
        }
    }
    public function edit_password(Request $rq){
        try{
            if($rq->has('password1') && $rq->has('password2') && $rq->has('session_token') && $rq->has('uid') && $rq->has('x_token')){
                if(Auth::isConnected($rq->uid,$rq->session_token)['flag']){
                    $odlPassword= $rq->password1;
                    $newPassword= $rq->password2;
                    $x_token=hash("sha256",$odlPassword.$newPassword.$rq->uid.$rq->session_token.env('APP_KEY').'myaccount.zlayga.com',false);
                    if($x_token==$rq->x_token){
                        $user=User::find($rq->uid);
                        if($user->password==$odlPassword){
                             $user->password=$newPassword;
                            $user->save();
                            return ['flag'=>true];
                        }else
                            return ['flag'=>false];
                    }else
                        return ['flag'=>false];
                }else
                    return ['flag'=>false];  
            }else
                return ['flag'=>false];
        }catch(Throwable $e){
            FaultC::save("ExtController","edit_password",$e);
            return ['flag'=>false,'title'=>__('unsuccess'),'message'=>__("erreur505")];
        }
    }
}
