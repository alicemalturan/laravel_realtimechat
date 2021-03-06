<!DOCTYPE html>
<html lang="zxx">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="assets/img/basic/favicon.ico" type="image/x-icon">
    <title>Chat</title>
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

            <div class="card no-b">
                <div class="card-body">
                    <h3 class="card-title text-center">Kay??t Ol</h3>
                    <form action="" class="register_form">
                        <div class="row justify-content-center p-5">
                            <div class="col-lg-6 col-md-8 col-sm-12">
                                <div class="form-group">
                                    <label>Kullan??c?? Ad??</label>
                                    <input type="text" class="form-control" name="username" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-8 col-sm-12">
                                <div class="form-group">
                                    <label>??ifre</label>
                                    <input type="password" class="form-control" name="password" autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="row justify-content-center p-5">
                            <div class="col-lg-6 ">
                                <button type="submit" class="btn btn-outline-primary form-control">Kay??t Ol</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>



        </div>
    </div>
</div>
    <!--/#app -->
    <script src="assets/js/app.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
    $(".register_form").submit(function(e){
        e.preventDefault();
        axios.post('/register',$(this).serialize()).then(function(){
            swal.fire({
                icon:"success",
                title:"Ba??ar??",
                text:"Kay??t Olundu"
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
