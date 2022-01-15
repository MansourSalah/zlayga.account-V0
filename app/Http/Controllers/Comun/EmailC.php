<?php
namespace App\Http\Controllers\Comun;

use App\Http\Controllers\Controller;

use Mail;
use Throwable;
use Session;

class EmailC
{
    //Setting
    private static $from_email='account@rancho.ma';
    private static $from_name='Rancho Academy';
    
    //Function
    public static function send($view,$to,$data,$subject=null) {//tout d'abort veuillez configurer la sécurité de votre compte Gmail
        try{
            $view='email.'.$view;
            if($subject==null){
                $subject=__('emailSubject');
            }
            $dt=["to"=>$to,"subject"=>$subject];
            Mail::send($view, $data, 
                function($message) use ($dt){
                    $message->to($dt['to'], 'Client')->subject($dt['subject']);
                    $message->from(self::$from_email,self::$from_name);
                }
            );
        }catch(Throwable $e){
            FaultC::save('Email','send',$e->getMessage());
            return response()->json(['flag'=>false,'title'=>__('unsuccess'),'message'=>__("erreur505")]);
        }
    }
}
