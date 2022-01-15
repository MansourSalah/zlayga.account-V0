<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\Auth;
use App\Http\Controllers\Comun\LangC;
use Illuminate\Support\Facades\DB;

use Session;
/*PreProd */
Route::get("/session",function(){Session::flush(); return "Session Vide";});

/*Complémentaire*/
new LangC();
Route::get("/api/lang",function(Request $rq){LangC::setLang($rq->lang);});
Route::redirect('/', '/signin');
/*Authentification */
Auth::route();



