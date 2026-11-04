<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Guide - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="min-h-screen bg-gray-50">
        <!-- Navigation -->
        <nav class="bg-white shadow-lg border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <a href="{{ route('user-guide.index') }}" class="flex items-center">
                            <i class="fas fa-book text-blue-600 text-2xl"></i>
                            <span class="ml-2 text-xl font-semibold text-gray-800">User Guide</span>
                        </a>
                        <div class="hidden md:block ml-10">
                            <div class="flex items-center space-x-4">
                                <a href="{{ route('user-guide.index') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                                    <i class="fas fa-home"></i> Home
                                </a>
                                <a href="{{ route('user-guide.categories') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                                    <i class="fas fa-folder"></i> Categories
                                </a>
                                <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                                    <i class="fas fa-arrow-left"></i> Back to System
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <form action="{{ route('user-guide.search') }}" method="GET" class="relative">
                            <input type="text" name="q" placeholder="Search guide..."
                                   class="w-48 md:w-64 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <button type="submit" class="absolute right-3 top-2.5 text-gray-400 hover:text-blue-600">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            @yield('content')
        </div>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 mt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="text-center text-gray-600 text-sm">
                    <p>KPLC Meter Central Laboratory System - User Guide</p>
                    <p class="mt-1">Version 1.0 | Last Updated: June 2026</p>
                </div>
            </div>
        </footer>
    </div>

    @stack('scripts')
</body>
</html>
