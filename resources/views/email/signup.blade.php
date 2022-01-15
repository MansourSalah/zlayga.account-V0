<!DOCTYPE html>
<html lang="en">
<head>
<style>
    .container{
        background-color: whitesmoke;
        padding:40px 0px;
    }
    .titre{
        text-align: center;
        color: #14449a;
        font-size: xxx-large;
        font-family: system-ui;
    }
    .body{
        margin-top: 30px;
        font-family: system-ui;
    }
    .cadre{
        background-color: white;
        margin: auto;
        padding: 65px;
        width:60%;
    }
    .btn{
        border: 1px solid;
        height: 35px;
        display: block;
        width: 120px;
        background-color: #14449a;
        padding-top: 10px;
        text-decoration: auto;
        text-align:center;
        font-size: 35px;
        font-weight: 700;
        padding-bottom: 20px;
        margin:auto;
    }
</style>
</head>
<body style="overflow: visible;">
<div class="container">
    <div class="cadre">
        <div class="titre">{{__('societe')}}</div>
        <div class="body">
            <div class="message" style="text-align:center">
                <b>{{__('emailMessage1')}}</b>
            </div><br>
            <div style="text-align:center">
                <span class="btn"style="color:white;">{{$token}}</span>
            </div>
            <br>
            <p style="text-align:center">{{__('emailMessage2')}}</p>
        </div>
    </div>
</div>
</body>
</html>