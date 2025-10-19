<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name'))</title>
    <style>
        /* Peyda Font Definitions for Email */
        @font-face {
            font-family: PeydaWebFaNum;
            font-style: normal;
            font-weight: 100;
            src: url('{{ asset('fonts/peyda/fonts/woff2/PeydaWebFaNum-Thin.woff2') }}') format('woff2'),
                 url('{{ asset('fonts/peyda/fonts/woff/PeydaWebFaNum-Thin.woff') }}') format('woff');
        }
        @font-face {
            font-family: PeydaWebFaNum;
            font-style: normal;
            font-weight: 200;
            src: url('{{ asset('fonts/peyda/fonts/woff2/PeydaWebFaNum-ExtraLight.woff2') }}') format('woff2'),
                 url('{{ asset('fonts/peyda/fonts/woff/PeydaWebFaNum-ExtraLight.woff') }}') format('woff');
        }
        @font-face {
            font-family: PeydaWebFaNum;
            font-style: normal;
            font-weight: 300;
            src: url('{{ asset('fonts/peyda/fonts/woff2/PeydaWebFaNum-Light.woff2') }}') format('woff2'),
                 url('{{ asset('fonts/peyda/fonts/woff/PeydaWebFaNum-Light.woff') }}') format('woff');
        }
        @font-face {
            font-family: PeydaWebFaNum;
            font-style: normal;
            font-weight: 400;
            src: url('{{ asset('fonts/peyda/fonts/woff2/PeydaWebFaNum-Regular.woff2') }}') format('woff2'),
                 url('{{ asset('fonts/peyda/fonts/woff/PeydaWebFaNum-Regular.woff') }}') format('woff');
        }
        @font-face {
            font-family: PeydaWebFaNum;
            font-style: normal;
            font-weight: 500;
            src: url('{{ asset('fonts/peyda/fonts/woff2/PeydaWebFaNum-Medium.woff2') }}') format('woff2'),
                 url('{{ asset('fonts/peyda/fonts/woff/PeydaWebFaNum-Medium.woff') }}') format('woff');
        }
        @font-face {
            font-family: PeydaWebFaNum;
            font-style: normal;
            font-weight: 600;
            src: url('{{ asset('fonts/peyda/fonts/woff2/PeydaWebFaNum-SemiBold.woff2') }}') format('woff2'),
                 url('{{ asset('fonts/peyda/fonts/woff/PeydaWebFaNum-SemiBold.woff') }}') format('woff');
        }
        @font-face {
            font-family: PeydaWebFaNum;
            font-style: normal;
            font-weight: 700;
            src: url('{{ asset('fonts/peyda/fonts/woff2/PeydaWebFaNum-Bold.woff2') }}') format('woff2'),
                 url('{{ asset('fonts/peyda/fonts/woff/PeydaWebFaNum-Bold.woff') }}') format('woff');
        }
        @font-face {
            font-family: PeydaWebFaNum;
            font-style: normal;
            font-weight: 800;
            src: url('{{ asset('fonts/peyda/fonts/woff2/PeydaWebFaNum-ExtraBold.woff2') }}') format('woff2'),
                 url('{{ asset('fonts/peyda/fonts/woff/PeydaWebFaNum-ExtraBold.woff') }}') format('woff');
        }
        @font-face {
            font-family: PeydaWebFaNum;
            font-style: normal;
            font-weight: 900;
            src: url('{{ asset('fonts/peyda/fonts/woff2/PeydaWebFaNum-Black.woff2') }}') format('woff2'),
                 url('{{ asset('fonts/peyda/fonts/woff/PeydaWebFaNum-Black.woff') }}') format('woff');
        }

        /* Base Email Styles with Peyda Font */
        body {
            margin: 0;
            padding: 0;
            font-family: 'PeydaWebFaNum', 'Tahoma', 'Arial', sans-serif !important;
            background-color: #F5F5F5;
            direction: rtl;
            line-height: 1.6;
            min-height: 100vh;
        }

        * {
            font-family: 'PeydaWebFaNum', 'Tahoma', 'Arial', sans-serif !important;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            direction: rtl;
        }

        .email-card {
            background-color: #FFFFFF;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 40px;
            text-align: center;
            margin: 20px 0;
        }

        .logo {
            margin-bottom: 30px;
        }

        .logo-img {
            height: 40px;
            width: auto;
            margin: 0;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            color: #000000;
            margin: 20px 0;
            line-height: 1.4;
        }

        .content {
            font-size: 16px;
            color: #333333;
            line-height: 1.6;
            margin: 20px 0 30px 0;
            text-align: center;
        }

        .button {
            display: inline-block;
            background-color: #E62117;
            color: #ffffff;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin: 20px 0;
            transition: background-color 0.3s ease;
        }

        .button:hover {
            background-color: #c41e14;
        }

        .footer {
            margin-top: auto;
            padding-top: 20px;
            font-size: 14px;
            color: #666666;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .footer-logo-img {
            height: 20px;
            width: auto;
            margin: 0;
        }

        .welcome-message {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            color: #155724;
            font-size: 14px;
            text-align: center;
        }

        .warning-text {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            color: #856404;
            font-size: 14px;
            text-align: center;
        }

        .url-text {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            color: #495057;
            font-size: 12px;
            word-break: break-all;
            direction: ltr;
            text-align: center;
        }

        .button {
            color: #fff !important;
        }

        /* Responsive Design */
        @media only screen and (max-width: 600px) {
            .email-container {
                padding: 10px;
            }

            .email-card {
                padding: 20px;
            }

            .title {
                font-size: 20px;
            }

            .content {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-card">
            @yield('content')
        </div>

        <!-- Footer -->
        <div class="footer">
            <img src="https://varzeshpod-prod.s3.ir-thr-at1.arvanstorage.com/uploads/images/default/user-8/2025/10/19/EkeDwVgmts3OAOUPUY8eXQF7ZA9WVPAeoQjD2MT6.jpg" alt="Finybo" class="footer-logo-img" width="80" height="20" style="height: 20px; width: auto;">
            <span>©2025 Finybo, All rights reserved.</span>
        </div>
    </div>
</body>
</html>
