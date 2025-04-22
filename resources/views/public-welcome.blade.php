<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Activity Tracker - Sales Mission Team</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <header class="bg-blue-600 text-white shadow">
        <div class="container mx-auto px-4 py-8">
            <h1 class="text-4xl font-bold">Activity Tracker</h1>
            <p class="text-blue-100 text-xl">Sales Mission Team</p>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-6">
            <div class="bg-white p-8 rounded-lg shadow-md">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Team Member Access</h2>
                <p class="text-gray-600 mb-6">Access the dashboard to manage subtasks, activities, and view progress reports.</p>
                
                <div class="flex space-x-4">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="px-6 py-3 bg-blue-600 text-white font-medium rounded-md shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="px-6 py-3 bg-blue-600 text-white font-medium rounded-md shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                Log in
                            </a>

                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="px-6 py-3 border border-blue-600 text-blue-600 font-medium rounded-md hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                    Register
                                </a>
                            @endif
                        @endauth
                    @endif
                </div>
            </div>
            
            <div class="bg-white p-8 rounded-lg shadow-md">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Public Activity Submission</h2>
                <p class="text-gray-600 mb-6">Not a registered user? You can still submit your activities using our public form. No login required!</p>
                
                <a href="{{ route('public.form') }}" class="inline-flex items-center px-6 py-3 bg-green-600 text-white font-medium rounded-md shadow hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Submit Activities
                </a>
            </div>
        </div>
        
        <div class="mt-12 bg-white rounded-lg shadow-md p-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">About Activity Tracker</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="border border-gray-200 rounded-lg p-6">
                    <div class="flex items-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <h3 class="ml-3 text-lg font-medium">Task Management</h3>
                    </div>
                    <p class="text-gray-600">Efficiently organize, track, and manage tasks and subtasks with deadlines and priorities.</p>
                </div>
                
                <div class="border border-gray-200 rounded-lg p-6">
                    <div class="flex items-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <h3 class="ml-3 text-lg font-medium">Progress Tracking</h3>
                    </div>
                    <p class="text-gray-600">Monitor progress with real-time updates, charts, and reports for better decision making.</p>
                </div>
                
                <div class="border border-gray-200 rounded-lg p-6">
                    <div class="flex items-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <h3 class="ml-3 text-lg font-medium">Team Collaboration</h3>
                    </div>
                    <p class="text-gray-600">Collaborate seamlessly with team members, assign tasks, and share updates.</p>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-gray-800 text-white py-6 mt-12">
        <div class="container mx-auto px-4 text-center">
            <p>&copy; {{ date('Y') }} Sales Mission Team. All rights reserved.</p>
        </div>
    </footer>
</body>
</html> 