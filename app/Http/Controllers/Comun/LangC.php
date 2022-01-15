<?php

namespace App\Http\Controllers\Comun;
use Illuminate\Http\Request;
//use Illuminate\Foundation\Application;

use App\Models\Fault;
use Throwable;
use Session;

class LangC
{   
    private static $langs= ["ar","en"];

    public function __construct() {
        if(!Session::has("myLocale"))
            Session::put("myLocale",self::$langs[0]);
    }
    public static function setLang($lang){
        try{
            if(in_array($lang, self::$langs)){
                Session::put("myLocale",$lang);
            }
            return Session::get("myLocale"); 
        }catch(Throwable $e){
            FaultC::save("LangC","setLang",$e);
            return response()->json(['flag'=>false,'title'=>__('unsuccess'),'message'=>__("erreur505")]);
        }
    }
    //facultative
    public static function getLang(){
        return Session::get("myLocale");
    }
}
