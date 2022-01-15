<style>
     .terms{
        float:right;
    }
    @media only screen and (max-width: 767px){
        .terms{
            float:none;
        }
    }
</style>
<div class="row">
    <div class="col-md-3">
        <div class="form-group lang" style="width:100px">
            <select class="form-control" onchange="setLang(this)">
                <option value="ar" @if(App::getLocale()=='ar') selected @endif>عربية</option>
                <option value="en" @if(App::getLocale()=='en') selected @endif>English</option>
            </select>
        </div>
    </div>
    <div class="col-md-3">
        <a class="btn" href="/signup">{{__('signup')}}</a>
    </div>
    <div class="col-md-2">
        <a class="btn" href="/terms">{{__('help')}}</a>
    </div>
    <div class="col-md-4">
        @if(App::getLocale()=='ar')
            <a class="btn terms" href="/terms">{{__('terms')}} و {{__('conditions')}} </a>
        @else
            <a class="btn terms" href="/terms">{{__('terms')}} &amp; {{__('conditions')}} </a>
        @endif
    </div>
</div><!--end row-->