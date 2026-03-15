<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">

    <!--Page Title-->
    <title>BoarSync</title>

    <!--Meta Keywords and Description-->
    <meta name="keywords" content="">
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no"/>

    <!--Favicon-->
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" title="Favicon"/>

    <!-- Main CSS Files -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v=2">

    <!-- Namari Color CSS -->
    <link rel="stylesheet" href="{{ asset('css/color.css') }}?v=2">

    <!--Icon Fonts - Font Awesome Icons-->
    <link rel="stylesheet" href="{{ asset('css/font-awesome.min.css') }}">

    <!-- Animate CSS-->
    <link href="{{ asset('css/animate.css') }}" rel="stylesheet" type="text/css">

    <!--Google Webfonts-->
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700,800' rel='stylesheet' type='text/css'>

    <style>
        a.nav-btn-ring {
            background-color: #333;
            color: #fff !important;
            font-weight: 600;
            opacity: 1 !important;
            border-radius: 0;
            border: none !important;
            text-align: center;
            line-height: 36px;
        }
        a.nav-btn-ring:hover,
        #header nav a.nav-btn-ring:hover,
        #header.nav-solid nav a.nav-btn-ring:hover {
            background-color: rgba(173, 20, 87, 0.6) !important;
            color: #fff !important;
            border: none !important;
            opacity: 1 !important;
        }
        a.button.button-cta {
            background-color: #333 !important;
            color: #fff !important;
            border: none;
            font-weight: 600;
        }
        a.button.button-cta:hover {
            background-color: #ad1457 !important;
            color: #fff !important;
            border: none;
        }
        /* Call-to-action section – subtitle more visible */
        #call-to-action .section-subtitle {
            font-weight: 500;
            color: #333;
            margin: 0;
        }
        /* Scroll-to-top button: pink background, white arrow */
        #scrollUp:before {
            background: #ad1457 !important;
            color: #fff !important;
        }
        #scrollUp:hover:before {
            background: #c2185b !important;
        }
        /* Introduction – 2×2 minimal cards */
        .introduction .intro-heading {
            text-align: center;
            margin-bottom: 16px;
            padding: 0;
        }
        .introduction .intro-heading::before {
            content: "";
            display: block;
            width: 40px;
            height: 4px;
            background: #ad1457;
            margin: 0 auto 18px auto;
            border-radius: 2px;
        }
        .introduction .intro-heading .section-title {
            margin: 0 0 4px 0;
            padding: 0;
        }
        .introduction .intro-heading .section-title::after {
            display: none;
        }
        .introduction .intro-heading .section-subtitle {
            margin: 0;
            padding: 8px 0;
            font-size: 0.85rem;
            font-weight: 500;
            color: #333;
            line-height: 1.5;
        }
        .intro-cards {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
            box-sizing: border-box;
        }
        .intro-card {
            display: flex;
            flex-direction: row;
            align-items: flex-start;
            padding: 20px 22px;
            border: 1px solid #e8e8e8;
            border-radius: 6px;
            background: #fff;
            box-sizing: border-box;
            min-height: 0;
        }
        .intro-card-icon {
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(173, 20, 87, 0.08);
            border-radius: 8px;
            margin-right: 16px;
            flex-shrink: 0;
        }
        .intro-card-icon svg {
            width: 22px;
            height: 22px;
            color: #ad1457;
        }
        .intro-card-body {
            flex: 1;
            min-width: 0;
        }
        .intro-card-body h4 {
            margin: 0 0 6px 0;
            font-size: 1rem;
            font-weight: 700;
            color: #222;
        }
        .intro-card-body p {
            margin: 0;
            font-size: 0.9rem;
            line-height: 1.5;
            color: #666;
        }
        @media (max-width: 640px) {
            .intro-cards { grid-template-columns: 1fr; }
        }
        /* Banner heading – responsive on small screens */
        @media (max-width: 767px) {
            #banner h1 {
                font-size: 38px !important;
                line-height: 1.2 !important;
            }
        }
        @media (max-width: 480px) {
            #banner h1 {
                font-size: 28px !important;
                line-height: 1.25 !important;
            }
        }
        /* Intro section – responsive on small screens */
        @media (max-width: 767px) {
            .introduction .intro-heading .section-title {
                font-size: 26px !important;
                line-height: 1.25 !important;
            }
            .introduction .intro-heading .section-subtitle {
                font-size: 0.9rem !important;
            }
        }
        @media (max-width: 480px) {
            .introduction .intro-heading .section-title {
                font-size: 22px !important;
                line-height: 1.3 !important;
            }
            .introduction .intro-heading .section-subtitle {
                font-size: 0.85rem !important;
            }
        }
        /* Features section – reduced font sizes, no ::after on titles */
        #features .section-title::after {
            display: none;
        }
        #features .section-title {
            font-size: 1.25rem !important;
            line-height: 1.3;
            padding: 2.5px 0 !important;
        }
        #features .section-subtitle {
            font-size: 0.9rem !important;
            margin: 5px 0 !important;
            font-weight: 500;
        }
        #features ul li span {
            font-size: 0.875rem;
            line-height: 1.45;
        }
    </style>
</head>
<body>

<!-- Preloader -->
<div id="preloader">
    <div id="status" class="la-ball-triangle-path">
        <div></div>
        <div></div>
        <div></div>
    </div>
</div>
<!--End of Preloader-->

<div class="page-border" data-wow-duration="0.7s" data-wow-delay="0.2s">
    <div class="top-border wow fadeInDown animated" style="visibility: visible; animation-name: fadeInDown;"></div>
    <div class="right-border wow fadeInRight animated" style="visibility: visible; animation-name: fadeInRight;"></div>
    <div class="bottom-border wow fadeInUp animated" style="visibility: visible; animation-name: fadeInUp;"></div>
    <div class="left-border wow fadeInLeft animated" style="visibility: visible; animation-name: fadeInLeft;"></div>
</div>

<div id="wrapper">

    <header id="banner" class="scrollto clearfix" data-enllax-ratio=".5">
        <div id="header" class="nav-collapse">
            <div class="row clearfix">
                <div class="col-1">

                    <!--Logo-->
                    <div id="logo">

                        <!--Logo that is shown on the banner-->
                        <!-- <img src="images/logo.png" id="banner-logo" alt="Landing Page"/> -->
                         <h3 id="banner-logo">BOAR SYNC</h3>
                        <!--End of Banner Logo-->

                        <!--The Logo that is shown on the sticky Navigation Bar-->
                        <!-- <img src="images/logo-2.png" id="navigation-logo" alt="Landing Page"/> -->
                        <h3 id="navigation-logo">BOAR SYNC</h3>
                        <!--End of Navigation Logo-->

                    </div>
                    <!--End of Logo-->


                    <!--Main Navigation-->
                    <nav id="nav-main" style="float: right;">
                        <ul style="text-align: right;">
                            @if (Route::has('login'))
                                @auth
                                    @php
                                        $role = auth()->user()->role;
                                    @endphp
                                    <li>
                                        <a href="{{ $role === 'admin'
                                            ? route('filament.admin.pages.admin-dashboard')
                                            : ($role === 'boar-raiser'
                                                ? route('filament.admin.pages.boar-raiser-dashboard')
                                                : ($role === 'customer'
                                                    ? route('filament.customer.pages.customer-dashboard')
                                                    : '#')) }}"
                                           class="nav-btn-ring"
                                           style="height: 36px; line-height: 36px; padding: 0 20px; margin-top: 18px; border-radius: 0; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; border: none; text-decoration: none; transition: all 0.3s ease; box-sizing: border-box; background-color: #333; color: #fff !important; font-weight: 600;">
                                            Dashboard
                                        </a>
                                    </li>
                                @else
                                    <li>
                                        <a href="{{ route('login') }}" class="nav-btn-ring" style="height: 36px; line-height: 36px; padding: 0 20px; margin-top: 18px; border-radius: 0; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; border: none; text-decoration: none; transition: all 0.3s ease; box-sizing: border-box; background-color: #333; color: #fff !important; font-weight: 600;">Login</a>
                                    </li>
                                    @if (Route::has('register'))
                                        <li>
                                            <a href="{{ route('register') }}" class="nav-btn-ring" style="height: 36px; line-height: 36px; padding: 0 20px; margin-top: 18px; border-radius: 0; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; border: none; text-decoration: none; transition: all 0.3s ease; box-sizing: border-box; background-color: #333; color: #fff !important; font-weight: 600;">Register</a>
                                        </li>
                                    @endif
                                @endauth
                            @endif
                        </ul>
                    </nav>
                    <!--End of Main Navigation-->

                    <div id="nav-trigger"><span></span></div>
                    <nav id="nav-mobile"></nav>

                </div>
            </div>
        </div><!--End of Header-->

        <!--Banner Content-->
        <div id="banner-content" class="row clearfix">

            <div class="col-38">

                <div class="section-heading">
                    <h1>SIMPLIFY YOUR PIGGERY MANAGEMENT</h1>
                    <h2>The only free, comprehensive piggery management system you'll ever need.</h2>
                </div>

                <!--Call to Action-->
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="button button-cta">GET STARTED</a>
                @else
                    <a href="{{ route('login') }}" class="button button-cta">GET STARTED</a>
                @endif
                <!--End Call to Action-->

            </div>

        </div><!--End of Row-->
    </header>

    <!--Main Content Area-->
    <main id="content">

        <!--Introduction-->
        <section id="about" class="introduction scrollto">
            <div class="row clearfix">
                <div class="col-1">
                    <div class="intro-heading section-heading wow fadeInUp" data-wow-delay="0.1s">
                        <h2 class="section-title">Streamline Your Pig Farming Operations</h2>
                        <p class="section-subtitle">BoarSync helps farmers manage their operations efficiently while gaining valuable insights into profitability.</p>
                    </div>
                    <div class="intro-cards">
                        <div class="intro-card wow fadeInUp" data-wow-delay="0.2s">
                            <div class="intro-card-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div class="intro-card-body">
                                <h4>Collect & Manage Data</h4>
                                <p>Transform farm activities into actionable business intelligence.</p>
                            </div>
                        </div>
                        <div class="intro-card wow fadeInUp" data-wow-delay="0.3s">
                            <div class="intro-card-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.5 6.5h.01M4 12h.01M4 18h.01M4 6h.01M12 6h.01M12 12h.01M12 18h.01M18 6h.01M18 12h.01M18 18h.01"/></svg>
                            </div>
                            <div class="intro-card-body">
                                <h4>Easy to Use</h4>
                                <p>Intuitive interface designed for farmers of all technical levels. Simple navigation and clear workflows make farm management effortless.</p>
                            </div>
                        </div>
                        <div class="intro-card wow fadeInUp" data-wow-delay="0.4s">
                            <div class="intro-card-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <div class="intro-card-body">
                                <h4>Optimize Operations</h4>
                                <p>Prioritize workflows to increase production and hit targets.</p>
                            </div>
                        </div>
                        <div class="intro-card wow fadeInUp" data-wow-delay="0.5s">
                            <div class="intro-card-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            </div>
                            <div class="intro-card-body">
                                <h4>Analyze Performance</h4>
                                <p>Gain quick, accurate insights into your farm's current situation.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--End of Introduction-->

        <!--Features-->
        <section id="features" class="scrollto clearfix">

            <div class="row clearfix">

                <!--Feature 1-->
                <div class="col-3 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="section-heading">
                        <h2 class="section-title">Powerful farm management tools</h2>
                        <p class="section-subtitle">Centralized data management for your entire operation</p>
                    </div>
                    <img src="{{ asset('img/features/ppg.png') }}" alt="Farm Management" style="width: 100%; height: auto; border-radius: 0; margin: 20px 0;"/>
                    <ul style="list-style: none; padding: 0;">
                        <li style="padding: 8px 0; border-bottom: 1px dotted #e1e1e1; display: flex; align-items: flex-start;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" style="width: 16px; height: 16px; color: #10b981; margin-top: 2px; margin-right: 8px; flex-shrink: 0;">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            <span>Designed specifically for small and medium scale farmers</span>
                        </li>
                        <li style="padding: 8px 0; border-bottom: 1px dotted #e1e1e1; display: flex; align-items: flex-start;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" style="width: 16px; height: 16px; color: #10b981; margin-top: 2px; margin-right: 8px; flex-shrink: 0;">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            <span>Intuitive interface that's easy to start using immediately</span>
                        </li>
                        <li style="padding: 8px 0; display: flex; align-items: flex-start;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" style="width: 16px; height: 16px; color: #10b981; margin-top: 2px; margin-right: 8px; flex-shrink: 0;">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            <span>Monitor farm activities from anywhere</span>
                        </li>
                    </ul>
                </div>
                <!--End of Feature 1-->

                <!--Feature 2-->
                <div class="col-3 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="section-heading">
                        <h2 class="section-title">Boost productivity efficiently</h2>
                        <p class="section-subtitle">Streamline your production workflow</p>
                    </div>
                    <img src="{{ asset('img/features/sales.png') }}" alt="Productivity" style="width: 100%; height: auto; border-radius: 0; margin: 20px 0;"/>
                    <ul style="list-style: none; padding: 0;">
                        <li style="padding: 8px 0; border-bottom: 1px dotted #e1e1e1; display: flex; align-items: flex-start;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" style="width: 16px; height: 16px; color: #10b981; margin-top: 2px; margin-right: 8px; flex-shrink: 0;">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            <span>Easily monitor and prioritize production targets</span>
                        </li>
                        <li style="padding: 8px 0; border-bottom: 1px dotted #e1e1e1; display: flex; align-items: flex-start;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" style="width: 16px; height: 16px; color: #10b981; margin-top: 2px; margin-right: 8px; flex-shrink: 0;">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            <span>Manage multiple production branches under one account</span>
                        </li>
                        <li style="padding: 8px 0; border-bottom: 1px dotted #e1e1e1; display: flex; align-items: flex-start;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" style="width: 16px; height: 16px; color: #10b981; margin-top: 2px; margin-right: 8px; flex-shrink: 0;">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            <span>Analyze production performance from weaning to sale</span>
                        </li>
                        <li style="padding: 8px 0; display: flex; align-items: flex-start;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" style="width: 16px; height: 16px; color: #10b981; margin-top: 2px; margin-right: 8px; flex-shrink: 0;">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            <span>Track mortality rates with detailed reporting</span>
                        </li>
                    </ul>
                </div>
                <!--End of Feature 2-->

                <!--Feature 3-->
                <div class="col-3 wow fadeInUp" data-wow-delay="0.5s">
                    <div class="section-heading">
                        <h2 class="section-title">Maximize your profitability</h2>
                        <p class="section-subtitle">Data-driven decisions for better results</p>
                    </div>
                    <img src="{{ asset('img/features/money.png') }}" alt="Profitability" style="width: 100%; height: auto; border-radius: 0; margin: 20px 0;"/>
                    <ul style="list-style: none; padding: 0;">
                        <li style="padding: 8px 0; border-bottom: 1px dotted #e1e1e1; display: flex; align-items: flex-start;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" style="width: 16px; height: 16px; color: #10b981; margin-top: 2px; margin-right: 8px; flex-shrink: 0;">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            <span>Track overall growth with consolidated performance results</span>
                        </li>
                        <li style="padding: 8px 0; border-bottom: 1px dotted #e1e1e1; display: flex; align-items: flex-start;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" style="width: 16px; height: 16px; color: #10b981; margin-top: 2px; margin-right: 8px; flex-shrink: 0;">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            <span>Use data insights to boost production management</span>
                        </li>
                        <li style="padding: 8px 0; display: flex; align-items: flex-start;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" style="width: 16px; height: 16px; color: #10b981; margin-top: 2px; margin-right: 8px; flex-shrink: 0;">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            <span>Identify and remove undesirable hogs using data-driven decisions</span>
                        </li>
                    </ul>
                </div>
                <!--End of Feature 3-->

            </div>

        </section>
        <!--End of Features-->

        <!--Call to Action-->
        <section id="call-to-action" class="scrollto clearfix">
            <div class="row clearfix">

                <div class="col-3">

                    <div class="section-heading">
                        <h3>GET STARTED</h3>
                        <h2 class="section-title">Start Managing Your Farm Today</h2>
                        <p class="section-subtitle">Join thousands of farmers who are already using BoarSync to streamline their operations and maximize profitability. Get started in minutes with our easy-to-use platform.</p>
                    </div>
                    
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="button button-cta" style="margin-top: 20px;">Register</a>
                    @else
                        <a href="{{ route('login') }}" class="button button-cta" style="margin-top: 20px;">Get Started</a>
                    @endif

                </div>

                <div class="col-2-3">
                    <img src="{{ asset('img/banner-images/pig.jpg') }}" alt="Pig Farm" style="width: 100%; height: auto; border-radius: 0;"/>
                </div>

            </div>
        </section>
        <!--End of Call to Action-->

    </main>
    <!--End Main Content Area-->


    <!--Footer-->
    <footer id="landing-footer" class="clearfix">
        <div class="row clearfix">

            <p id="copyright" class="col-2">&copy; {{ date('Y') }} BoarSync</p>
        </div>
    </footer>
    <!--End of Footer-->

</div>

<!-- Include JavaScript resources -->
<script src="{{ asset('js/jquery.1.8.3.min.js') }}"></script>
<script src="{{ asset('js/wow.min.js') }}"></script>
<script src="{{ asset('js/featherlight.min.js') }}"></script>
<script src="{{ asset('js/featherlight.gallery.min.js') }}"></script>
<script src="{{ asset('js/jquery.enllax.min.js') }}"></script>
<script src="{{ asset('js/jquery.scrollUp.min.js') }}"></script>
<script src="{{ asset('js/jquery.easing.min.js') }}"></script>
<script src="{{ asset('js/jquery.stickyNavbar.min.js') }}"></script>
<script src="{{ asset('js/jquery.waypoints.min.js') }}"></script>
<script src="{{ asset('js/images-loaded.min.js') }}"></script>
<script src="{{ asset('js/lightbox.min.js') }}"></script>
<script src="{{ asset('js/site.js') }}"></script>


</body>
</html>