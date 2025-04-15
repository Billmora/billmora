<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="color-scheme" content="light">
  <meta name="supported-color-schemes" content="light">
  <title>{{ Billmora::getGeneral('company_name', 'Billmora') }}</title>

  <style>
    body {
      font-family: "Plus Jakarta Sans", sans-serif;
      background-color: #fff;
      margin: 0;
      padding: 0;
    }
    .container {
      max-width: 600px;
      background-color: #FBFDFF;
      border: 1px solid #0000001a;
      border-radius: 0.5rem;
      display: block;
      margin: 4rem auto;
    }
    .bar {
      min-height: 0.65rem;
      border-top-left-radius: 0.5rem;
      border-top-right-radius: 0.5rem;
      width: 100%;
      background-color: #3384ff;
      background-image: repeating-linear-gradient(
        -45deg,
        transparent,
        transparent calc(10px / 2),
        #FBFDFF calc(10px / 2),
        #FBFDFF 10px
      );
    }
    .header {
      color: #3384ff;
      text-align: center;
      margin: 1.5rem 0;
    }
    .body {
      font-size: 16px;
      padding: 1rem;
      border-top: 2px dashed #0000001a;
      border-bottom: 2px dashed #0000001a;
      color: #7c8088;
    }
    details {
      padding: 0.25rem 0.75rem;
      border: 2px dashed #0000001a;
      border-radius: 0.5rem;
      background-color: #F5F9FF;
      color: #6d7178;
      font-weight: 600;
    }
    details [data-type="details-content"] {
      line-height: 0.5rem;
    }
    .footer {
      text-align: center;
      margin: 1.5rem 0;
    }
    h2 {
      margin: 0.5rem 0
    }
    a {
      color: #3384ff !important;
      text-decoration: none;
      font-weight: bold;
    }
    p {
      color: #7c8088;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="bar"></div>
    <div class="header">
      <h2>{{ Billmora::getGeneral('company_name', 'Billmora') }}</h2>
    </div>
    <div class="body">
      @yield('body')
    </div>
    <div class="footer">
      <p>&copy; 2025 <a href="{{ config('app.url') }}" target="_blank">{{ Billmora::getGeneral('company_name', 'Billmora') }}</a>. All rights reserved.</p>
    </div>
  </div>
</body>
</html>