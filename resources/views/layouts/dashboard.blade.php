<!-- resources/views/layouts/dashboard.blade.php -->
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
        .sidebar { 
            min-height: 100vh; 
            background-color: #f8f9fa; 
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        .nav-link.active { 
            background-color: #007bff; 
            color: white !important; 
            border-radius: 5px;
        }
        .nav-link:hover {
            background-color: #e9ecef;
            border-radius: 5px;
        }
        .nav-link.active:hover {
            background-color: #0056b3;
        }
    </style>
    
</head>
<body class="bg-light">
    @yield('content')
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>
