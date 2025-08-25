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
            details {
                padding: 0.25rem 0.75rem;
                border: 2px dashed #0000001a;
                border-radius: 0.5rem;
                background-color: #f5f9ff;
                color: #6d7178;
                font-weight: 600;
            }
            details [data-type="details-content"] {
                line-height: 0.5rem;
            }
            .footer {
                text-align: start;
            }
            h2 {
                margin: 0.5rem 0;
            }
            a {
                color: #7267ef !important;
                font-weight: bold;
            }
            p {
                color: #7c8088;
                margin: 0;
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
