<!DOCTYPE html>
<html lang="en">
<head>
  @include('client::layouts.wrapper')
</head>
<body>
  <main class="maintenance">
    <div class="card">
      <div class="header center">
        <h1 class="text-primary">Oops, site under Maintenance</h1>
      </div>
      <div class="body">
        <h3>{{ $message }}</h3>
      </div>
    </div>
  </main>
</body>
</html>