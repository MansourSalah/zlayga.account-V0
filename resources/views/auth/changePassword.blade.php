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
                        <div class="auth-box">
                            <div class="row m-b-20">
                                <div class="col-md-12">
                                    <h3 class="text-center txt-primary">{{__('verifyAccount')}}</h3>
                                </div>
                            </div>
                            <hr/>
                            <div class="row row-rtl">
                                <div class="col-sm-12">
                                    <div class="input-group">
                                        <input type="password" class="form-control" name="password1" placeholder="{{__('choosePassword')}}">
                                    </div>
                                </div>
                            </div>  
                            <div class="row row-rtl">
                                <div class="col-sm-12">
                                    <div class="input-group">
                                        <input type="password" class="form-control" name="password2" placeholder="{{__('confirmPassword')}}">
                                    </div>
                                </div>
                            </div>    
                            <input type="hidden" name="code" value="{{$code}}">                
                            <div class="col-md-12" style="padding:0px;">
                                <button type="submit" class="btn btn-primary" style="width:100%;"><span id="loading">{{__('loading')}}</span><span id="action">{{__('send')}}</span></button>
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
        url: '/api/user/forgot-password/changePassword',
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
                window.location.href = "/signin";
        }
    });
});
</script>
@stop