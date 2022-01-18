<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;use App\Models\LoginInfo;use App\Models\Token;
use Session;
class Authentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $rq, Closure $next)
    {
        if($rq->isMethod('get')){
            if(Session::has('auth_user')){//is connected
                $user=Session::get('auth_user');
                $token= Token::where('user_id',$user['user_id'])
                            ->where('session_token',$user['session_token'])
                            ->where('isConnected',1);
                if($token->exists() ){//si deja existe
                    $token= $token->first();
                    //generer token
                    $access_token=hash("sha256",date('Y-m-d H:i:s.').gettimeofday()['usec'],false);
                    //modifier access token et la date de generation
                    $token->access_token=substr($access_token,0,60);//comme un petit cryptage
                    $token->token_generated_at=date("Y-m-d H:i:s");
                    $token->save();
                    //========================================================
                    $service="myacc";
                    if($rq->service != "" && $rq->service!="myacc")
                        $service=$rq->service;
                        
                    //get url redirection
                    $continue=env('DEFAULT_URL')."?code1=".$user['session_token']."&uid=".$user['user_id']."&code2=".$access_token;
                    if($rq->continue!="")
                        $continue=$rq->continue."?code1=".$user['session_token']."&uid=".$user['user_id']."&code2=".$access_token;
                    return redirect($continue);
                    
                }
            }
        }
        return $next($rq);
    }
}
