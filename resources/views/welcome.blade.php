<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'BoarSync') }} - Your No 1 Free Piggery Management System</title>

    <!-- Preload Images -->
    <link rel="preload" as="image" href="{{ asset('img/main.jpg') }}">
    <link rel="preload" as="image" href="{{ asset('img/ppg.png') }}">
    <link rel="preload" as="image" href="{{ asset('img/money.png') }}">
    <link rel="preload" as="image" href="{{ asset('img/sales.png') }}">
    <link rel="preload" as="image" href="{{ asset('img/pigg.jpg') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        /* Compact minimalistic styles */
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            line-height: 1.5;
            color: #374151;
        }
        
        .header-logo {
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        
        .compact-section {
            padding-top: 2rem;
            padding-bottom: 2rem;
        }
        
        .feature-card {
            transition: transform 0.2s ease;
            border: 1px solid #f3f4f6;
            height: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-2px);
        }
        
        .check-icon {
            color: #10b981;
            flex-shrink: 0;
        }
        
        .btn-primary {
            background-color: #2563eb;
            transition: all 0.2s ease;
        }
        
        .btn-primary:hover {
            background-color: #1d4ed8;
            transform: translateY(-1px);
        }
        
        .btn-outline {
            border: 1px solid #2563eb;
            transition: all 0.2s ease;
        }
        
        .btn-outline:hover {
            background-color: #2563eb;
            color: white;
        }
        
        .compact-list li {
            margin-bottom: 0.5rem;
        }
        
        .content-container {
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Image cropping and sizing */
        .hero-image, .feature-image {
            object-fit: cover;
            object-position: center;
            max-height: 400px;
            width: 100%;
        }

        .cta-image {
            object-fit: cover;
            object-position: center;
            max-height: 300px;
            width: 100%;
        }

        /* Animation keyframes */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* Animation classes */
        .animate-on-scroll {
            opacity: 0;
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.8s ease-out forwards;
        }

        .animate-fade-in-left {
            animation: fadeInLeft 0.8s ease-out forwards;
        }

        .animate-fade-in-right {
            animation: fadeInRight 0.8s ease-out forwards;
        }

        .animate-fade-in {
            animation: fadeIn 1s ease-out forwards;
        }

        .animate-scale-in {
            animation: scaleIn 0.6s ease-out forwards;
        }

        /* Stagger delays for multiple elements */
        .delay-100 {
            animation-delay: 0.1s;
        }

        .delay-200 {
            animation-delay: 0.2s;
        }

        .delay-300 {
            animation-delay: 0.3s;
        }

        .delay-400 {
            animation-delay: 0.4s;
        }
    </style>
</head>

<body class="bg-white">
    <!-- Header with Laravel Auth -->
    <header class="fixed w-full bg-white z-50 border-b border-gray-100 py-3">
        <div class="content-container px-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <img src="{{ asset('img/logo.png') }}" alt="BOARSYNC LOGO" class="block" style="height: 3rem;" width="48"
                        height="48" fetchpriority="high" />
                
                    <div class="flex flex-col">
                        <div class="text-lg font-bold">BOAR SYNC</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Piggery Farm Manager</div>
                    </div>
                </div>
                <div class="hidden md:flex space-x-5 items-center">
                    @if (Route::has('login'))
                        @auth
                            @php
                                $role = auth()->user()->role;
                            @endphp
                            <a
                                href="{{ $role === 'admin'
                                    ? route('filament.admin.pages.admin-dashboard')
                                    : ($role === 'boar-raiser'
                                        ? route('filament.admin.pages.boar-raiser-dashboard')
                                        : ($role === 'customer'
                                            ? route('filament.customer.pages.customer-dashboard')
                                            : '#')) }}"
                                class="px-4 py-2 text-blue-600 border border-blue-600 hover:bg-blue-600 hover:text-white rounded text-sm transition-colors">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                                class="px-4 py-2 text-blue-600 border border-blue-600 rounded text-sm btn-outline">
                                Log in
                            </a>

                            @if (Route::has('register'))
                                <a href="{{ route('register') }}"
                                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors text-sm btn-primary">
                                    Register
                                </a>
                            @endif
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="pt-24 pb-8 md:pt-28 bg-white">
        <div class="content-container px-4">
            <div class="flex flex-col md:flex-row items-center">
                <div class="md:w-1/2 md:pr-12 text-center md:text-left mb-6 md:mb-0 animate-fade-in-left">
                    <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2 leading-tight">
                        Simplify Your Piggery Management
                    </h1>
                    <p class="text-gray-600 mb-5 max-w-md mx-auto md:mx-0">
                        The only free, comprehensive piggery management system you'll ever need.
                    </p>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                            class="inline-block px-6 py-2.5 bg-blue-600 text-white rounded text-md font-medium btn-primary animate-scale-in delay-200">
                            Get Started Free
                        </a>
                    @endif
                </div>
                <div class="md:w-1/2 animate-fade-in-right delay-100">
                    <img src="{{ asset('img/main.jpg') }}" alt="Pig Farm Management Dashboard"
                        class="hero-image rounded-lg shadow-sm max-w-md mx-auto">
                </div>
            </div>
        </div>
    </section>

    <!-- Features Introduction -->
    <section class="py-8 bg-gray-50" id="features">
        <div class="content-container px-4">
            <div class="text-center mb-8 animate-fade-in-up">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Streamline Your Pig Farming Operations</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    BoarSync helps farmers manage their operations efficiently while gaining valuable insights into profitability.
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-5">
                <!-- Feature 1 -->
                <div class="p-5 bg-white rounded-lg feature-card animate-on-scroll" data-animate="animate-fade-in-up delay-100">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mb-3 mx-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2 text-center">Collect & Manage Data</h3>
                    <p class="text-gray-600 text-center text-sm">
                        Transform farm activities into actionable business intelligence.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="p-5 bg-white rounded-lg feature-card animate-on-scroll" data-animate="animate-fade-in-up delay-200">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mb-3 mx-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2 text-center">Analyze Performance</h3>
                    <p class="text-gray-600 text-center text-sm">
                        Gain quick, accurate insights into your farm's current situation.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="p-5 bg-white rounded-lg feature-card animate-on-scroll" data-animate="animate-fade-in-up delay-300">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mb-3 mx-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2 text-center">Optimize Operations</h3>
                    <p class="text-gray-600 text-center text-sm">
                        Prioritize workflows to increase production and hit targets.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Detailed Features -->
    <section class="py-8 bg-white">
        <div class="content-container px-4">
            <!-- Feature 1 Detail -->
            <div class="flex flex-col md:flex-row items-center mb-12">
                <div class="md:w-1/2 md:pr-12 mb-6 md:mb-0 animate-on-scroll" data-animate="animate-fade-in-left">
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Powerful farm management tools</h3>
                    <ul class="space-y-1.5 compact-list">
                        <li class="flex items-start">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-gray-600 text-sm">Centralized data management for your entire operation</span>
                        </li>
                        <li class="flex items-start">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-gray-600 text-sm">Designed specifically for small and medium scale farmers</span>
                        </li>
                        <li class="flex items-start">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-gray-600 text-sm">Intuitive interface that's easy to start using immediately</span>
                        </li>
                        <li class="flex items-start">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-gray-600 text-sm">Monitor farm activities from anywhere</span>
                        </li>
                    </ul>
                </div>
                <div class="md:w-1/2 animate-on-scroll" data-animate="animate-fade-in-right delay-100">
                    <img src="{{ asset('img/ppg.png') }}" alt="Farm Management Features" class="feature-image rounded-lg shadow-sm">
                </div>
            </div>

            <!-- Feature 2 Detail -->
            <div class="flex flex-col md:flex-row-reverse items-center mb-12">
                <div class="md:w-1/2 md:pl-12 mb-6 md:mb-0 animate-on-scroll" data-animate="animate-fade-in-right">
                    <div class="md:pl-8">
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Boost productivity efficiently</h3>
                        <ul class="space-y-1.5 compact-list">
                            <li class="flex items-start">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-gray-600 text-sm">Easily monitor and prioritize production targets</span>
                            </li>
                            <li class="flex items-start">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-gray-600 text-sm">Manage multiple production branches under one account</span>
                            </li>
                            <li class="flex items-start">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-gray-600 text-sm">Analyze production performance from weaning to sale</span>
                            </li>
                            <li class="flex items-start">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-gray-600 text-sm">Track mortality rates with detailed reporting</span>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="md:w-1/2 animate-on-scroll" data-animate="animate-fade-in-left delay-100">
                    <img src="{{ asset('img/money.png') }}" alt="Productivity Features"
                        class="feature-image rounded-lg shadow-sm">
                </div>
            </div>

            <!-- Feature 3 Detail -->
            <div class="flex flex-col md:flex-row items-center">
                <div class="md:w-1/2 md:pr-12 mb-6 md:mb-0 animate-on-scroll" data-animate="animate-fade-in-left">
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Maximize your profitability</h3>
                    <ul class="space-y-1.5 compact-list">
                        <li class="flex items-start">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-gray-600 text-sm">Track overall growth with consolidated performance results</span>
                        </li>
                        <li class="flex items-start">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-gray-600 text-sm">Use data insights to boost production management</span>
                        </li>
                        <li class="flex items-start">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-gray-600 text-sm">Identify and remove undesirable hogs using data-driven decisions</span>
                        </li>
                    </ul>
                </div>
                <div class="md:w-1/2 animate-on-scroll" data-animate="animate-fade-in-right delay-100">
                    <img src="{{ asset('img/sales.png') }}" alt="Profit Optimization" class="feature-image rounded-lg shadow-sm">
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-8 bg-gradient-to-r from-blue-50 to-blue-100">
        <div class="content-container px-4 text-center">
            <h2 class="text-2xl md:text-3xl font-semibold text-gray-800 mb-2 animate-fade-in-up">Ready to transform your pig farming?</h2>
            <p class="text-gray-600 mb-5 max-w-xl mx-auto animate-fade-in-up delay-100">Start managing your operation more efficiently with BoarSync today.</p>

            @if (Route::has('register'))
                <a href="{{ route('register') }}"
                   class="inline-block bg-blue-600 text-white px-5 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors animate-scale-in delay-200">
                    Get Started Now
                </a>
            @endif

            <div class="mt-8 max-w-3xl mx-auto animate-on-scroll" data-animate="animate-fade-in delay-300">
                <img src="{{ asset('img/pigg.jpg') }}" alt="Pig Farm Management"
                     class="cta-image rounded-md shadow-sm">
            </div>
        </div>
    </section>


    <!-- Footer -->
    <footer class="py-6 bg-gray-50">
        <div class="content-container px-4 text-center">
            <a href="#" class="text-blue-600 hover:text-blue-700 inline-flex items-center text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                Back to top
            </a>
            <div class="mt-4 text-gray-500 text-sm">
                &copy; {{ date('Y') }} BoarSync. All rights reserved.
            </div>
        </div>
    </footer>

    <script>
        // Scroll-triggered animations using Intersection Observer
        document.addEventListener('DOMContentLoaded', function() {
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const element = entry.target;
                        // Get the animation classes from data-animate attribute
                        const animateClasses = element.getAttribute('data-animate');
                        
                        if (animateClasses) {
                            element.classList.remove('animate-on-scroll');
                            // Split the classes and add them
                            animateClasses.split(' ').forEach(cls => {
                                if (cls.trim()) {
                                    element.classList.add(cls.trim());
                                }
                            });
                        }
                        observer.unobserve(element);
                    }
                });
            }, observerOptions);

            // Observe all elements with animate-on-scroll class
            document.querySelectorAll('.animate-on-scroll').forEach(el => {
                observer.observe(el);
            });
        });
    </script>
</body>

</html>