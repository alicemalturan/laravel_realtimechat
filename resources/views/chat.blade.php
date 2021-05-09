<!DOCTYPE html>
<html lang="zxx">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="assets/img/basic/favicon.ico" type="image/x-icon">
    <title>Chat</title>
    <meta name="token" content="{{csrf_token()}}">
    <meta name="me" content="{{\Illuminate\Support\Facades\Auth::user()->username}}">
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/app.css">
    <style>
        .loader {
            position: fixed;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: #F5F8FA;
            z-index: 9998;
            text-align: center;
        }

        .plane-container {
            position: absolute;
            top: 50%;
            left: 50%;
        }
    </style>

    <!-- Js -->
    <!--
    --- Head Part - Use Jquery anywhere at page.
    --- http://writing.colin-gourlay.com/safely-using-ready-before-including-jquery/
    -->
    <script>(function(w,d,u){w.readyQ=[];w.bindReadyQ=[];function p(x,y){if(x=="ready"){w.bindReadyQ.push(y);}else{w.readyQ.push(x);}};var a={ready:p,bind:p};w.$=w.jQuery=function(f){if(f===d||f===u){return a}else{p(f)}}})(window,document)</script>
</head>
<body class="theme-dark">
<!-- Pre loader -->
<div id="loader" class="loader">
    <div class="plane-container">
        <div class="preloader-wrapper small active">
            <div class="spinner-layer spinner-blue">
                <div class="circle-clipper left">
                    <div class="circle"></div>
                </div><div class="gap-patch">
                    <div class="circle"></div>
                </div><div class="circle-clipper right">
                    <div class="circle"></div>
                </div>
            </div>

            <div class="spinner-layer spinner-red">
                <div class="circle-clipper left">
                    <div class="circle"></div>
                </div><div class="gap-patch">
                    <div class="circle"></div>
                </div><div class="circle-clipper right">
                    <div class="circle"></div>
                </div>
            </div>

            <div class="spinner-layer spinner-yellow">
                <div class="circle-clipper left">
                    <div class="circle"></div>
                </div><div class="gap-patch">
                    <div class="circle"></div>
                </div><div class="circle-clipper right">
                    <div class="circle"></div>
                </div>
            </div>

            <div class="spinner-layer spinner-green">
                <div class="circle-clipper left">
                    <div class="circle"></div>
                </div><div class="gap-patch">
                    <div class="circle"></div>
                </div><div class="circle-clipper right">
                    <div class="circle"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="app">



<div class="row">
    <div class="container-fluid relative animatedParent animateOnce p-lg-5">

        <div class="row row-eq-height my-3">
            <div class="col-md-12 pt-3 pb-3">
                <div class="">
                    <div class="card-body  chat-widget p-3 slimScroll" data-height="600">
                        <div class="w-body w-scroll ">
                            <ul class="list-unstyled mainchat_list">
                                <!-- Chat by us. Use the class "by-me". -->


                                <!-- Chat by other. Use the class "by-other". -->










                            </ul>
                        </div>
                    </div>
                    <div class="card-footer ">
                        <!-- Chat button -->
                        <form class="chat_form main_chat">
                            <input type="hidden" value="main">
                            <div class="input-group">


                                <input type="text" name="message" class="form-control" id="validationCustom01" placeholder="Mesaj"  autocomplete="off" value="" required>
                                <span class="input-group-btn ml-2">
                                        <button type="submit" class="btn btn-primary">Gönder</button>
                                    </span>

                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>


    </div>
</div>

<!--/#app -->
<script src="assets/js/app.js"></script>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="/js/app.js"></script>
<script>
    $(document).ready(function (){

        $(".main_chat").submit(function(e){
            e.preventDefault();
            axios.post('/chat',$(this).serialize()).then(function (){
                $("#validationCustom01").val("");
            });
        });
    });
</script>

<!--
--- Footer Part - Use Jquery anywhere at page.
--- http://writing.colin-gourlay.com/safely-using-ready-before-including-jquery/
-->
<script>(function($,d){$.each(readyQ,function(i,f){$(f)});$.each(bindReadyQ,function(i,f){$(d).bind("ready",f)})})(jQuery,document)</script>

</body>
</html>
