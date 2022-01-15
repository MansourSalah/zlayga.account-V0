@extends('front')
@section('content')
<style>
    .terms{
        font-family: emoji;
        padding: 10px;
    }
    .terms .container{
        margin: auto;
        padding: 20px;
        border-radius: 6px;
        background:white;
    }
    .terms h3{
        text-align: center;
        font-weight: 600;
    }
    .terms p, .terms li{
        font-size:15px;
    }
</style>
<section class="terms">
    <div class="container">
        <div class="row">
            <div class="col-sm-12" style="text-align: end;">
                <a href="/signin" class="btn btn-primary">{{__('signInNow')}}</a>
            </div>
        </div>
        <br>
        <h3 >{{__('termsTitle')}}</h3>
        <div class="row">
            <div class="col-sm-12">
                <p class="text-rtl">{{__('termsP1')}}</p>
                <p class="text-rtl">{{__('termsP2')}}</p>
            </div>
        </div>
        <div class="row row-rtl">
            <div class="col-sm-12">
                <ol>
                    <li>{{__('termsLi1')}}</li>
                    <li>{{__('termsLi2')}}</li>
                    <li>{{__('termsLi3')}}</li>
                    <li>{{__('termsLi4')}}</li>
                </ol>
            </div>
        </div>
        
    </div>
</section>
@stop