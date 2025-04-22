<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Tracker - @yield('title', 'Public Form')</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.alpinejs.dev/dist/cdn.min.js"></script>
    <!-- Add your custom CSS here -->
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @yield('styles')
</head>
<body class="bg-gray-100 min-h-screen">
    <header class="bg-blue-600 text-white shadow">
        <div class="container mx-auto px-4 py-6">
            <h1 class="text-3xl font-bold">Activity Tracker</h1>
            <p class="text-blue-100">Sales Mission Team</p>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        @yield('content')
    </main>

    <footer class="bg-gray-800 text-white py-6">
        <div class="container mx-auto px-4 text-center">
            <p>&copy; {{ date('Y') }} Sales Mission Team. All rights reserved.</p>
        </div>
    </footer>

    @yield('scripts')
</body>
</html> 