<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta name="color-scheme" content="light" />
        <meta name="supported-color-schemes" content="light" />
        <title>{{ Billmora::getGeneral('company_name') }}</title>

        <style>
            @import url("https://fonts.googleapis.com/css?family=Plus Jakarta Sans");

            .a3s,
            body {
                font-family: "Plus Jakarta Sans", sans-serif !important;
                background-color: #fff;
                margin: 0;
                padding: 0;
            }
            .container {
                max-width: 600px;
                background-color: #fff;
                border: 2px solid #eceeff;
                border-radius: 1.5rem;
                display: block;
                margin: 4rem auto;
                padding: 2rem;
            }
            .header {
                color: #7267ef;
                text-align: start;
            }
            .header img {
                width: 64px;
                height: auto;
                border-radius: 5px;
            }
            .body {
                font-size: 16px;
                padding: 2rem 0;
                margin: 2rem 0;
                border-top: 2px solid #eceeff;
                border-bottom: 2px solid #eceeff;
                color: #7c8088;
            }
            .footer {
                text-align: start;
            }
            h2 {
                margin: 0.5rem 0;
            }
            a {
                color: #7267ef;
                font-weight: bold;
            }
            p {
                color: #7c8088;
                margin: 0;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                background-color: #f5f9ff;
                border: 1px solid #eceeff;
                border-radius: 8px;
            }
            table td {
                padding: 12px 16px;
                color: #6d7178;
                border-bottom: 1px solid #eceeff;
                font-size: 15px;
            }
            table tr:last-child td {
                border-bottom: none;
            }
            table td:first-child {
                font-weight: bold;
                width: 40%;
            }
            table td:last-child {
                text-align: right;
                font-weight: 600;
                color: #7267ef;
            }
            .btn {
                display: inline-block;
                padding: 10px 18px;
                color: #ffffff;
                text-decoration: none;
                border-radius: 6px;
                font-weight: bold;
                text-align: center;
            }
            .btn-primary { background-color: #7267ef; }
            .btn-danger { background-color: #e74c3c; }
            .btn-success { background-color: #2ecc71; }
            .alert {
                padding: 15px;
                border-radius: 8px;
                margin: 15px 0;
                font-size: 15px;
            }
            .alert p { margin: 0; color: inherit; }
            .alert-info {
                background-color: #f5f9ff;
                border: 1px dashed #7267ef;
                color: #554bb9;
            }
            .alert-warning {
                background-color: #fff8e1;
                border: 1px dashed #f1c40f;
                color: #b97700;
            }
            .alert-danger {
                background-color: #fdeaea;
                border: 1px dashed #e74c3c;
                color: #c0392b;
            }
            .alert-success {
                background-color: #eafaf1;
                border: 1px dashed #2ecc71;
                color: #27ae60;
            }
            .text-center { text-align: center; }
            .text-right { text-align: right; }
            
            .text-danger { color: #e74c3c; }
            .text-success { color: #2ecc71; }
            .text-muted { color: #9ca3af; }
            .divider {
                border: none;
                border-top: 2px dashed #eceeff;
                margin: 20px 0;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <img src="{{ Billmora::getGeneral('company_logo') }}" alt="company logo"/>
            </div>
            <div class="body">
                {!! $body !!}
            </div>
            <div class="footer">
                <p>&copy; 2025 <a href="{{ config('app.url') }}" target="_blank">{{ Billmora::getGeneral('company_name') }}</a>. All rights reserved.</p>
            </div>
        </div>
    </body>
</html>
