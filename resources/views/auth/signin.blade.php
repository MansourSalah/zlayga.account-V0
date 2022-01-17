@extends('front')
@section('content')

@include('css.comun')
<!-- Pre-loader end -->
<section class="login p-fixed d-flex text-center  common-img-bg">
    <!-- Container-fluid starts -->
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <!-- Authentication card start -->
                <div class="signup-card card-block auth-body mr-auto ml-auto">
                    <form class="md-float-material">
                        <div class="text-center logo">
                            {{__('rancho')}} <span style="color:#3329cc"> {{__("academy")}}</span>
                        </div>
                        <input type="hidden" name="continue" value="{{$continue}}">
                        <input type="hidden" name="service" value="{{$service}}">
                        <div class="auth-box">
                            <div class="row m-b-20">
                                <div class="col-md-12">
                                    <h3 class="text-center txt-primary">{{__('signinTitle')}}</h3>
                                </div>
                            </div>
                            <hr/>
                            <div class="row row-rtl">
                                <div class="col-sm-12">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="email" placeholder="{{__('email')}}">
                                        <span class="md-line"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="row row-rtl">
                                <div class="col-sm-12">
                                    <div class="input-group">
                                        <input type="password" class="form-control" name="password" placeholder="{{__('choosePassword')}}">
                                        <span class="md-line"></span>
                                    </div>
                                </div>
                            </div>                            
                            <div class="row row-rtl">
                                <div class="col-sm-4">
                                    <a class="text-rtl" href="/forgot-password"><strong>{{__('ForgotYourPassword')}}</strong></a>
                                </div>
                            </div><br>
                            <div class="row">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary" style="width:100%;"><span id="loading">{{__('loading')}}</span><span id="action">{{__('signInNow')}}</span></button>
                                </div>
                            </div>                            
                            <hr/>
                            @include('comun.foot')
                        </div>
                    </form>
                    <!-- end of form -->
                </div>
                <!-- Authentication card end -->
            </div>
            <!-- end of col-sm-12 -->
        </div>
        <!-- end of row -->
    </div>
    <!-- end of container-fluid -->
</section>
<script>
  $("form").submit(function(event) {
    event.preventDefault();
    var fd=new FormData(this);    
    $("#loading").css("display","block");
    $("#action").css("display","none");

    $.ajax({
        url: '/api/user/login',
        method:"POST",
        data:fd,
        contentType: false,processData: false,
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        success: function(data){
            $("#action").css("display","block");
            $("#loading").css("display","none");
            if(!data['flag']){
                $.dialog({
                    title: data['title'],
                    content:data['message'],
                });
            }
            else
                window.location.href = data['continue'];
        }
    });
    
});
</script>
@stop