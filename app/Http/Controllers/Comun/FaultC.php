<?php

namespace App\Http\Controllers\Comun;

use Illuminate\Http\Request;

use App\Models\Fault;

use Throwable;

class FaultC
{
    public static function save($controller,$function,$exception){
        try{
            $flt= new Fault();
            $flt->controller=$controller;
            $flt->function=$function;
            $flt->exception=$exception;
            $flt->save();
        }catch(Throwable $e){
            throw new \Exception("Désolé, erreur originale");
        }
    }
}
