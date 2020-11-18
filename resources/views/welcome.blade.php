<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>GameKrow Staging Backend</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Nunito', sans-serif;
                font-weight: 200;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 13px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }
        </style>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            {{-- @if (Route::has('login'))
                <div class="top-right links">
                    @auth
                        <a href="{{ url('/home') }}">Home</a>
                    @else
                        <a href="{{ route('login') }}">Login</a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}">Register</a>
                        @endif
                    @endauth
                </div>
            @endif --}}

            <div class="content">
                <img src="https://gamekrow.com/logo-dark.png" alt="">
                <div class="title m-b-md">
                    GameKrow Staging Backend.<br/>  Go to <a href="{{ url('https://staging.gamekrow.com') }}">Home</a>.
                </div>

{{--
                <input type="submit" style="cursor:pointer;" value="Pay Now" id="submit" />


                <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
                <script type="text/javascript" src="https://ravesandboxapi.flutterwave.com/flwv3-pug/getpaidx/api/flwpbf-inline.js"></script>
                <script type="text/javascript">
                    document.addEventListener("DOMContentLoaded", function(event) {
                        document.getElementById('submit').addEventListener('click', function () {

                            var flw_ref = "", chargeResponse = "", trxref = "FDKHGKd"+ Math.random(), API_publicKey = "FLWPUBK_TEST-ff5fc95e89db6c3a3a95d99ffb06ba78-X";

                            getpaidSetup(
                                {
                                    PBFPubKey: API_publicKey,
                                    customer_email: "user@example.com",
                                    amount: 2000,
                                    customer_phone: "234099940409",
                                    currency: "NGN",
                                    txref: "rave-123456",
                                    meta: [{metaname:"flightID", metavalue: "AP1234"}],
                                    onclose:function(response) {
                                    },
                                    callback:function(response) {
                                        // txref = response.data.txRef, chargeResponse = response.data.chargeResponseCode;
                                        // alert(txref)
                                        console.log(response)
                                    }
                                }
                            );
                        });
                    });
                </script> --}}

            </div>
        </div>
    </body>
</html>
