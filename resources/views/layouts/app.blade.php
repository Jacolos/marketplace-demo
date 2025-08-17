<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Marketplace Orders System')</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @livewireStyles
    <style>
        /* Custom responsive styles */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.active { transform: translateX(0); }
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        
        /* Animation classes */
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Responsive table */
        @media (max-width: 640px) {
            .responsive-table thead {
                display: none;
            }
            
            .responsive-table tr {
                display: block;
                margin-bottom: 1rem;
                border: 1px solid #e5e7eb;
                border-radius: 0.5rem;
            }
            
            .responsive-table td {
                display: flex;
                justify-content: space-between;
                padding: 0.5rem;
                border: none;
            }
            
            .responsive-table td::before {
                content: attr(data-label);
                font-weight: bold;
                margin-right: 1rem;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <!-- Mobile menu button -->
                    <button id="mobile-menu-btn" class="md:hidden p-2 rounded-md text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <!-- Logo -->
                    <div class="flex-shrink-0 flex items-center ml-4 md:ml-0">
                        <h1 class="text-xl font-bold text-blue-600">
                            <i class="fas fa-shopping-cart mr-2"></i>
                            <span class="hidden sm:inline">Marketplace System</span>
                            <span class="sm:hidden">MS</span>
                        </h1>
                    </div>
                    
                    <!-- Desktop menu -->
                    <div class="hidden md:flex ml-10 space-x-4">
                        <a href="/dashboard" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md">
                            <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                        </a>
                        <a href="/orders" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md">
                            <i class="fas fa-clipboard-list mr-1"></i> Zamówienia
                        </a>
                        <a href="/products" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md">
                            <i class="fas fa-box mr-1"></i> Produkty
                        </a>
                        <a href="/customers" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md">
                            <i class="fas fa-users mr-1"></i> Klienci
                        </a>
                    </div>
                </div>
                
                <!-- User menu -->
                <div class="flex items-center space-x-4">
                    <button class="relative p-2 text-gray-700 hover:bg-gray-100 rounded-full">
                        <i class="fas fa-bell"></i>
                        <span class="absolute top-0 right-0 h-2 w-2 bg-red-500 rounded-full"></span>
                    </button>
                    
                    <div class="relative">
                        <button class="flex items-center text-sm rounded-full focus:outline-none">
                            <img class="h-8 w-8 rounded-full" src="https://ui-avatars.com/api/?name=Admin" alt="Avatar">
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile sidebar -->
    <div id="mobile-sidebar" class="sidebar fixed inset-y-0 left-0 z-40 w-64 bg-white shadow-xl md:hidden transition-transform duration-300">
        <div class="p-4">
            <button id="close-sidebar" class="absolute top-4 right-4 text-gray-500">
                <i class="fas fa-times"></i>
            </button>
            
            <nav class="mt-8 space-y-2">
                <a href="/dashboard" class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded">
                    <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                </a>
                <a href="/orders" class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded">
                    <i class="fas fa-clipboard-list mr-2"></i> Zamówienia
                </a>
                <a href="/products" class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded">
                    <i class="fas fa-box mr-2"></i> Produkty
                </a>
                <a href="/customers" class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded">
                    <i class="fas fa-users mr-2"></i> Klienci
                </a>
            </nav>
        </div>
    </div>

    <!-- Main content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 fade-in">
                <p class="font-bold">Sukces!</p>
                <p>{{ session('success') }}</p>
            </div>
        @endif
        
        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 fade-in">
                <p class="font-bold">Błąd!</p>
                <p>{{ session('error') }}</p>
            </div>
        @endif

        @yield('content')
    </main>

    @livewireScripts
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    
    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-btn').addEventListener('click', function() {
            document.getElementById('mobile-sidebar').classList.add('active');
        });
        
        document.getElementById('close-sidebar').addEventListener('click', function() {
            document.getElementById('mobile-sidebar').classList.remove('active');
        });
        
        // Notification system
        window.addEventListener('notify', event => {
            const notification = document.createElement('div');
            notification.className = `fixed top-20 right-4 p-4 rounded-lg shadow-lg z-50 fade-in ${
                event.detail.type === 'success' ? 'bg-green-500' : 'bg-red-500'
            } text-white`;
            notification.innerHTML = event.detail.message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        });
    </script>
</body>
</html>
