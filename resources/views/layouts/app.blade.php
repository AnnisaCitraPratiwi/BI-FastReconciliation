<!-- resources/views/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @stack('head')
    <title>@yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .bg-bank-blue { background-color: #2c3e50; }
        .text-bank-blue { color: #2c3e50; }
        .btn-bank { background-color: #3498db; border-color: #3498db; }
        .btn-bank:hover { background-color: #2980b9; border-color: #2980b9; }
        .sidebar { min-height: 100vh; background-color: #f8f9fa; }
        .nav-link.active { background-color: #007bff; color: white !important; }
    </style>
</head>
<body>
    @yield('content')
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
