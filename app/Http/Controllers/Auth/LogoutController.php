<?php
namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Comun\FaultC;

use App\Models\Token;
use Session;
use Throwable;

class LogoutController extends Controller
{
    public function logout(Request $rq){//cette fonction utilisé par le requests externe
        try{
            if($rq->has("uid") && $rq->has('session_token') && $rq->has("x_token")){
                if(Auth::isConnected($rq->uid,$rq->session_token)){
                    $x_token=hash("sha256",$rq->uid.$rq->session_token.env('APP_KEY'),false);
                    if($x_token==$rq->x_token){
                        $token=$token->first();
                        $token->isConnected=0;
                        $token->save();
                        return ['flag'=>true];
                    }else
                        return ['flag'=>false];
                }else
                    return ['flag'=>false];
            }else
                return ['flag'=>false];
        }catch(Throwable $e){
            FaultC::save("LogoutController","logout",$e);
            return response()->json(['flag'=>false,'title'=>__('unsuccess'),'message'=>__("erreur505")]);
        }
    }
}
