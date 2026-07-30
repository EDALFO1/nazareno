<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="{{ config('app.name') }} — Sistema de Gestión">
  <title>@yield('titulo', config('app.name'))</title>

  <link href="{{ asset('favicon.ico') }}" rel="icon" type="image/x-icon">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <link href="{{ asset('NiceAdmin/assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('NiceAdmin/assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
  <link href="{{ asset('css/responsive-mobile.css') }}" rel="stylesheet">

  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    html, body {
      height: 100%;
      font-family: 'Poppins', sans-serif;
      background-color: #0f172a;
      overflow-x: hidden;
    }
  </style>

  @stack('styles')
</head>

<body>

  @yield('contenido')

  <script src="{{ asset('NiceAdmin/assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  @stack('scripts')

</body>

</html>
