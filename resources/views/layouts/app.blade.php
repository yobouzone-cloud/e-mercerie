<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="webpush-public-key" content="{{ config('services.webpush.public') }}">
  @auth
    <meta name="current-user-id" content="{{ auth()->user()->id }}">
  @endauth
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="shortcut icon" href="assets/images/favicon.svg" type="image/x-icon" />
    <title>@yield('title', 'e-Mercerie')</title>

    <!-- ========== All CSS files linkup ========= -->
    <!-- <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/lineicons.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="assets/css/materialdesignicons.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="assets/css/fullcalendar.css" />
    <link rel="stylesheet" href="assets/css/fullcalendar.css" />
    <link rel="stylesheet" href="assets/css/main.css" /> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <link rel="stylesheet" href="{{ asset('css/lineicons.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Pour les notifications en temps réel -->
    <script src="https://js.pusher.com/7.0/pusher.min.js"></script>

    @stack('styles')
  </head>
  <body>
    <!-- ======== Preloader =========== -->
    <!-- <div id="preloader">
      <div class="spinner"></div>
    </div> -->
    <!-- ======== Preloader =========== -->

    <!-- ======== sidebar-nav start =========== -->
    <aside class="sidebar-nav-wrapper">
      <div class="navbar-logo">
        <a href="index.html">
          <img src="assets/images/logo/logo.svg" alt="logo" />
        </a>
      </div>
      <nav class="sidebar-nav">
        <ul>
            <li class="nav-item">
                <a href="{{ route('landing') }}">
                <span class="icon">
                    <i class="fa-solid fa-house"></i>
                </span>
                <span class="text">Accueil</span>
                </a>
            </li>
        @guest
            <li class="nav-item">
                <a href="{{ route('login.form') }}">
                <span class="icon">
                    <i class="fa-solid fa-right-to-bracket"></i>
                </span>
                <span class="text">Connexion</span>
                </a>
            </li>
            <!-- <li class="nav-item">
                <a href="{{ route('register.form') }}">
                <span class="icon">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M3.33334 3.35442C3.33334 2.4223 4.07954 1.66666 5.00001 1.66666H15C15.9205 1.66666 16.6667 2.4223 16.6667 3.35442V16.8565C16.6667 17.5519 15.8827 17.9489 15.3333 17.5317L13.8333 16.3924C13.537 16.1673 13.1297 16.1673 12.8333 16.3924L10.5 18.1646C10.2037 18.3896 9.79634 18.3896 9.50001 18.1646L7.16668 16.3924C6.87038 16.1673 6.46298 16.1673 6.16668 16.3924L4.66668 17.5317C4.11731 17.9489 3.33334 17.5519 3.33334 16.8565V3.35442ZM4.79168 5.04218C4.79168 5.39173 5.0715 5.6751 5.41668 5.6751H10C10.3452 5.6751 10.625 5.39173 10.625 5.04218C10.625 4.69264 10.3452 4.40927 10 4.40927H5.41668C5.0715 4.40927 4.79168 4.69264 4.79168 5.04218ZM5.41668 7.7848C5.0715 7.7848 4.79168 8.06817 4.79168 8.41774C4.79168 8.76724 5.0715 9.05066 5.41668 9.05066H10C10.3452 9.05066 10.625 8.76724 10.625 8.41774C10.625 8.06817 10.3452 7.7848 10 7.7848H5.41668ZM4.79168 11.7932C4.79168 12.1428 5.0715 12.4262 5.41668 12.4262H10C10.3452 12.4262 10.625 12.1428 10.625 11.7932C10.625 11.4437 10.3452 11.1603 10 11.1603H5.41668C5.0715 11.1603 4.79168 11.4437 4.79168 11.7932ZM13.3333 4.40927C12.9882 4.40927 12.7083 4.69264 12.7083 5.04218C12.7083 5.39173 12.9882 5.6751 13.3333 5.6751H14.5833C14.9285 5.6751 15.2083 5.39173 15.2083 5.04218C15.2083 4.69264 14.9285 4.40927 14.5833 4.40927H13.3333ZM12.7083 8.41774C12.7083 8.76724 12.9882 9.05066 13.3333 9.05066H14.5833C14.9285 9.05066 15.2083 8.76724 15.2083 8.41774C15.2083 8.06817 14.9285 7.7848 14.5833 7.7848H13.3333C12.9882 7.7848 12.7083 8.06817 12.7083 8.41774ZM13.3333 11.1603C12.9882 11.1603 12.7083 11.4437 12.7083 11.7932C12.7083 12.1428 12.9882 12.4262 13.3333 12.4262H14.5833C14.9285 12.4262 15.2083 12.1428 15.2083 11.7932C15.2083 11.4437 14.9285 11.1603 14.5833 11.1603H13.3333Z" />
                    </svg>
                </span>
                <span class="text">Inscription</span>
                </a>
            </li> -->
        @else
            @if(auth()->user() && auth()->user()->role === 'admin')
              <li class="nav-item">
                  <a href="{{ route('admin.supplies.index') }}">
                  <span class="icon">
                      <i class="fa-solid fa-shield-halved"></i>
                  </span>
                  <span class="text">Admin - Fournitures</span>
                  </a>
              </li>
            @endif
            @if(auth()->user()->isCouturier())
              <li class="nav-item">
                  <a href="{{ route('merceries.index') }}">
                  <span class="icon">
                      <i class="fa-solid fa-scissors"></i>
                  </span>
                  <span class="text">Merceries</span>
                  </a>
              </li>
              <!-- <li class="nav-item">
                  <a href="{{ route('supplies.selection') }}">
                  <span class="icon">
                      <i class="fa-solid fa-check-double"></i>
                  </span>
                  <span class="text">Sélection Fournitures</span>
                  </a>
              </li> -->
              <li class="nav-item">
                  <a href="{{ route('orders.index') }}">
                  <span class="icon">
                      <i class="fa-solid fa-arrow-left"></i>
                  </span>
                  <span class="text">Mes Commandes</span>
                  </a>
              </li>
            @elseif(auth()->user()->isMercerie())
                <li class="nav-item">
                    <a href="{{ route('merchant.supplies.index') }}">
                    <span class="icon">
                        <i class="fa-solid fa-scissors"></i>
                    </span>
                    <span class="text">Mes Fournitures</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('merchant.supplies.create') }}">
                    <span class="icon">
                        <i class="fa-solid fa-plus"></i>
                    </span>
                    <span class="text">Ajouter une fourniture</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="{{ route('orders.index') }}">
                    <span class="icon">
                        <i class="fa-solid fa-arrow-right"></i>
                    </span>
                    <span class="text">Commandes Reçues</span>
                    </a>
                </li>
            @endif
            @if(auth()->check())
            <li class="nav-item">
              <a href="{{ route('notifications.index') }}">
                <span class="icon">
                  <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                      d="M10.8333 2.50008C10.8333 2.03984 10.4602 1.66675 9.99999 1.66675C9.53975 1.66675 9.16666 2.03984 9.16666 2.50008C9.16666 2.96032 9.53975 3.33341 9.99999 3.33341C10.4602 3.33341 10.8333 2.96032 10.8333 2.50008Z" />
                    <path
                      d="M17.5 5.41673C17.5 7.02756 16.1942 8.33339 14.5833 8.33339C12.9725 8.33339 11.6667 7.02756 11.6667 5.41673C11.6667 3.80589 12.9725 2.50006 14.5833 2.50006C16.1942 2.50006 17.5 3.80589 17.5 5.41673Z" />
                    <path
                      d="M11.4272 2.69637C10.9734 2.56848 10.4947 2.50006 10 2.50006C7.10054 2.50006 4.75003 4.85057 4.75003 7.75006V9.20873C4.75003 9.72814 4.62082 10.2393 4.37404 10.6963L3.36705 12.5611C2.89938 13.4272 3.26806 14.5081 4.16749 14.9078C7.88074 16.5581 12.1193 16.5581 15.8326 14.9078C16.732 14.5081 17.1007 13.4272 16.633 12.5611L15.626 10.6963C15.43 10.3333 15.3081 9.93606 15.2663 9.52773C15.0441 9.56431 14.8159 9.58339 14.5833 9.58339C12.2822 9.58339 10.4167 7.71791 10.4167 5.41673C10.4167 4.37705 10.7975 3.42631 11.4272 2.69637Z" />
                    <path
                      d="M7.48901 17.1925C8.10004 17.8918 8.99841 18.3335 10 18.3335C11.0016 18.3335 11.9 17.8918 12.511 17.1925C10.8482 17.4634 9.15183 17.4634 7.48901 17.1925Z" />
                  </svg>
                </span>
                <span class="text">Notifications</span>
              </a>
            </li>
            @endif
            <!-- <li class="nav-item nav-item-has-children">
                <a
                href="#0"
                class="collapsed"
                data-bs-toggle="collapse"
                data-bs-target="#ddmenu_4"
                aria-controls="ddmenu_4"
                aria-expanded="false"
                aria-label="Toggle navigation"
                >
                <span class="icon">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M1.66666 5.41669C1.66666 3.34562 3.34559 1.66669 5.41666 1.66669C7.48772 1.66669 9.16666 3.34562 9.16666 5.41669C9.16666 7.48775 7.48772 9.16669 5.41666 9.16669C3.34559 9.16669 1.66666 7.48775 1.66666 5.41669Z" />
                    <path
                        d="M1.66666 14.5834C1.66666 12.5123 3.34559 10.8334 5.41666 10.8334C7.48772 10.8334 9.16666 12.5123 9.16666 14.5834C9.16666 16.6545 7.48772 18.3334 5.41666 18.3334C3.34559 18.3334 1.66666 16.6545 1.66666 14.5834Z" />
                    <path
                        d="M10.8333 5.41669C10.8333 3.34562 12.5123 1.66669 14.5833 1.66669C16.6544 1.66669 18.3333 3.34562 18.3333 5.41669C18.3333 7.48775 16.6544 9.16669 14.5833 9.16669C12.5123 9.16669 10.8333 7.48775 10.8333 5.41669Z" />
                    <path
                        d="M10.8333 14.5834C10.8333 12.5123 12.5123 10.8334 14.5833 10.8334C16.6544 10.8334 18.3333 12.5123 18.3333 14.5834C18.3333 16.6545 16.6544 18.3334 14.5833 18.3334C12.5123 18.3334 10.8333 16.6545 10.8333 14.5834Z" />
                    </svg>
                </span>
                <span class="text">UI Elements </span>
                </a>
                <ul id="ddmenu_4" class="collapse dropdown-nav">
                <li>
                    <a href="alerts.html"> Alerts </a>
                </li>
                <li>
                    <a href="buttons.html"> Buttons </a>
                </li>
                <li>
                    <a href="cards.html"> Cards </a>
                </li>
                <li>
                    <a href="typography.html"> Typography </a>
                </li>
                </ul>
            </li> -->
        @endguest
          <!-- <li class="nav-item nav-item-has-children">
            <a
              href="#0"
              class="collapsed"
              data-bs-toggle="collapse"
              data-bs-target="#ddmenu_55"
              aria-controls="ddmenu_55"
              aria-expanded="false"
              aria-label="Toggle navigation"
            >
              <span class="icon">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path
                    d="M5.48663 1.1466C5.77383 0.955131 6.16188 1.03274 6.35335 1.31994L6.87852 2.10769C7.20508 2.59755 7.20508 3.23571 6.87852 3.72556L6.35335 4.51331C6.16188 4.80052 5.77383 4.87813 5.48663 4.68666C5.19943 4.49519 5.12182 4.10715 5.31328 3.81994L5.83845 3.03219C5.88511 2.96221 5.88511 2.87105 5.83845 2.80106L5.31328 2.01331C5.12182 1.72611 5.19943 1.33806 5.48663 1.1466Z" />
                  <path
                    d="M2.49999 5.83331C2.03976 5.83331 1.66666 6.2064 1.66666 6.66665V10.8333C1.66666 13.5948 3.90523 15.8333 6.66666 15.8333H9.99999C12.1856 15.8333 14.0436 14.431 14.7235 12.4772C14.8134 12.4922 14.9058 12.5 15 12.5H16.6667C17.5872 12.5 18.3333 11.7538 18.3333 10.8333V8.33331C18.3333 7.41284 17.5872 6.66665 16.6667 6.66665H15C15 6.2064 14.6269 5.83331 14.1667 5.83331H2.49999ZM14.9829 11.2496C14.9942 11.1123 15 10.9735 15 10.8333V7.91665H16.6667C16.8967 7.91665 17.0833 8.10319 17.0833 8.33331V10.8333C17.0833 11.0634 16.8967 11.25 16.6667 11.25H15L14.9898 11.2498L14.9829 11.2496Z" />
                  <path
                    d="M8.85332 1.31994C8.6619 1.03274 8.27383 0.955131 7.98663 1.1466C7.69943 1.33806 7.62182 1.72611 7.81328 2.01331L8.33848 2.80106C8.38507 2.87105 8.38507 2.96221 8.33848 3.03219L7.81328 3.81994C7.62182 4.10715 7.69943 4.49519 7.98663 4.68666C8.27383 4.87813 8.6619 4.80052 8.85332 4.51331L9.37848 3.72556C9.70507 3.23571 9.70507 2.59755 9.37848 2.10769L8.85332 1.31994Z" />
                  <path
                    d="M10.4867 1.1466C10.7738 0.955131 11.1619 1.03274 11.3533 1.31994L11.8785 2.10769C12.2051 2.59755 12.2051 3.23571 11.8785 3.72556L11.3533 4.51331C11.1619 4.80052 10.7738 4.87813 10.4867 4.68666C10.1994 4.49519 10.1218 4.10715 10.3133 3.81994L10.8385 3.03219C10.8851 2.96221 10.8851 2.87105 10.8385 2.80106L10.3133 2.01331C10.1218 1.72611 10.1994 1.33806 10.4867 1.1466Z" />
                  <path
                    d="M2.49999 16.6667C2.03976 16.6667 1.66666 17.0398 1.66666 17.5C1.66666 17.9602 2.03976 18.3334 2.49999 18.3334H14.1667C14.6269 18.3334 15 17.9602 15 17.5C15 17.0398 14.6269 16.6667 14.1667 16.6667H2.49999Z" />
                </svg>
              </span>
              <span class="text">Icons</span>
            </a>
            <ul id="ddmenu_55" class="collapse dropdown-nav">
              <li>
                <a href="icons.html"> LineIcons </a>
              </li>
              <li>
                <a href="mdi-icons.html"> MDI Icons </a>
              </li>
            </ul>
          </li>
          <li class="nav-item nav-item-has-children">
            <a
              href="#0"
              class="collapsed"
              data-bs-toggle="collapse"
              data-bs-target="#ddmenu_5"
              aria-controls="ddmenu_5"
              aria-expanded="false"
              aria-label="Toggle navigation"
            >
              <span class="icon">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path
                    d="M4.16666 3.33335C4.16666 2.41288 4.91285 1.66669 5.83332 1.66669H14.1667C15.0872 1.66669 15.8333 2.41288 15.8333 3.33335V16.6667C15.8333 17.5872 15.0872 18.3334 14.1667 18.3334H5.83332C4.91285 18.3334 4.16666 17.5872 4.16666 16.6667V3.33335ZM6.04166 5.00002C6.04166 5.3452 6.32148 5.62502 6.66666 5.62502H13.3333C13.6785 5.62502 13.9583 5.3452 13.9583 5.00002C13.9583 4.65485 13.6785 4.37502 13.3333 4.37502H6.66666C6.32148 4.37502 6.04166 4.65485 6.04166 5.00002ZM6.66666 6.87502C6.32148 6.87502 6.04166 7.15485 6.04166 7.50002C6.04166 7.8452 6.32148 8.12502 6.66666 8.12502H13.3333C13.6785 8.12502 13.9583 7.8452 13.9583 7.50002C13.9583 7.15485 13.6785 6.87502 13.3333 6.87502H6.66666ZM6.04166 10C6.04166 10.3452 6.32148 10.625 6.66666 10.625H9.99999C10.3452 10.625 10.625 10.3452 10.625 10C10.625 9.65485 10.3452 9.37502 9.99999 9.37502H6.66666C6.32148 9.37502 6.04166 9.65485 6.04166 10ZM9.99999 16.6667C10.9205 16.6667 11.6667 15.9205 11.6667 15C11.6667 14.0795 10.9205 13.3334 9.99999 13.3334C9.07949 13.3334 8.33332 14.0795 8.33332 15C8.33332 15.9205 9.07949 16.6667 9.99999 16.6667Z" />
                </svg>
              </span>
              <span class="text"> Forms </span>
            </a>
            <ul id="ddmenu_5" class="collapse dropdown-nav">
              <li>
                <a href="form-elements.html"> From Elements </a>
              </li>
            </ul>
          </li>
          <li class="nav-item">
            <a href="tables.html">
              <span class="icon">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path
                    d="M1.66666 4.16667C1.66666 3.24619 2.41285 2.5 3.33332 2.5H16.6667C17.5872 2.5 18.3333 3.24619 18.3333 4.16667V9.16667C18.3333 10.0872 17.5872 10.8333 16.6667 10.8333H3.33332C2.41285 10.8333 1.66666 10.0872 1.66666 9.16667V4.16667Z" />
                  <path
                    d="M1.875 13.75C1.875 13.4048 2.15483 13.125 2.5 13.125H17.5C17.8452 13.125 18.125 13.4048 18.125 13.75C18.125 14.0952 17.8452 14.375 17.5 14.375H2.5C2.15483 14.375 1.875 14.0952 1.875 13.75Z" />
                  <path
                    d="M2.5 16.875C2.15483 16.875 1.875 17.1548 1.875 17.5C1.875 17.9602 2.15483 18.3334 2.5 18.3334H17.5C17.8452 18.3334 18.125 17.8452 18.125 17.5C18.125 17.1548 17.8452 16.875 17.5 16.875H2.5Z" />
                </svg>
              </span>
              <span class="text">Tables</span>
            </a>
          </li> -->
          
        </ul>
      </nav>
      <!-- <div class="promo-box">
        <div class="promo-icon">
          <img class="mx-auto" src="./assets/images/logo/logo-icon-big.svg" alt="Logo">
        </div>
        <h3>Upgrade to PRO</h3>
        <p>Improve your development process and start doing more with PlainAdmin PRO!</p>
        <a href="https://plainadmin.com/pro" target="_blank" rel="nofollow" class="main-btn primary-btn btn-hover">
          Upgrade to PRO
        </a>
      </div> -->
    </aside>
    <div class="overlay"></div>
    <!-- ======== sidebar-nav end =========== -->

    <!-- ======== main-wrapper start =========== -->
    <main class="main-wrapper">
      <!-- ========== header start ========== -->
      <header class="header">
        <div class="container-fluid">
          <div class="row">
            <div class="col-lg-5 col-md-5 col-6">
              <div class="header-left d-flex align-items-center">
                <div class="menu-toggle-btn mr-15">
                  <button id="menu-toggle" class="main-btn primary-btn btn-hover">
                    <i class="fas fa-chevron-left me-2"></i> Menu
                  </button>
                </div>
        
              </div>
            </div>
            @if(auth()->check() && (auth()->user()->isCouturier() || auth()->user()->isMercerie()))
            <div class="col-lg-7 col-md-7 col-6">
              <div class="header-right">
                <!-- notification start -->
                <div class="notification-box ml-15 d-md-flex">
                  <button class="dropdown-toggle position-relative" type="button" id="notification" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path
                        d="M11 20.1667C9.88317 20.1667 8.88718 19.63 8.23901 18.7917H13.761C13.113 19.63 12.1169 20.1667 11 20.1667Z"
                        fill="" />
                      <path
                        d="M10.1157 2.74999C10.1157 2.24374 10.5117 1.83333 11 1.83333C11.4883 1.83333 11.8842 2.24374 11.8842 2.74999V2.82604C14.3932 3.26245 16.3051 5.52474 16.3051 8.24999V14.287C16.3051 14.5301 16.3982 14.7633 16.564 14.9352L18.2029 16.6342C18.4814 16.9229 18.2842 17.4167 17.8903 17.4167H4.10961C3.71574 17.4167 3.5185 16.9229 3.797 16.6342L5.43589 14.9352C5.6017 14.7633 5.69485 14.5301 5.69485 14.287V8.24999C5.69485 5.52474 7.60672 3.26245 10.1157 2.82604V2.74999Z"
                        fill="" />
                    </svg>
                    @php $unreadCount = auth()->check() ? auth()->user()->unreadNotifications()->count() : 0; @endphp
                    <span id="notification-unread-badge" data-count="{{ $unreadCount }}" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display:{{ $unreadCount > 0 ? 'inline-block' : 'none' }}; font-size:0.65rem; display:flex; justify-content:center; align-items:center;">{{ $unreadCount }}</span>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notification" style="width:320px;">
                    @php
                      $dropdownNotifications = auth()->user()->notifications()->latest()->take(3)->get();
                    @endphp
                    @forelse($dropdownNotifications as $notification)
                      <li>
                        <a href="{{ ($notification->data['url'] ?? '#') . '?notif=' . $notification->id }}" class="d-flex align-items-start p-2 text-decoration-none text-dark">
                          <!-- <div class="image me-2">
                            <img src="{{ asset('assets/images/lead/lead-6.png') }}" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:6px;" />
                          </div> -->
                          <div class="content">
                            <h6 class="mb-1 {{ $notification->read_at ? '' : 'fw-bold' }}">
                              {!! \Illuminate\Support\Str::limit($notification->data['message'] ?? 'Notification', 80) !!}
                              @if(!$notification->read_at)
                                <span class="badge bg-primary ms-2">Nouveau</span>
                              @endif
                            </h6>
                            <p class="mb-0 text-sm text-gray">{{ $notification->created_at->diffForHumans() }}</p>
                          </div>
                        </a>
                      </li>
                    @empty
                      <li class="px-3 py-2 text-center text-muted">Aucune notification</li>
                    @endforelse
                    <li class="px-3"><a href="{{ route('notifications.index') }}">Voir toutes les notifications</a></li>
                  </ul>
                </div>
                <!-- notification end -->
                <!-- cart start -->
                @if(auth()->user()->isCouturier())
                <div class="cart-box ms-3">
                  <button id="cart-button" class="btn btn-link position-relative p-0" type="button" aria-label="Panier">
                    <i class="fa fa-shopping-cart fa-lg" style="color: var(--primary-color);"></i>
                    <span id="cart-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" 
                          style="
                            min-width: 22px;
                            height: 22px;
                            font-weight: 700;
                            display: none;
                            align-items: center;
                            justify-content: center;
                            box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
                            animation: pulse 2s infinite;
                            line-height: 1;
                            padding: 2px 6px;
                          ">0</span>
                  </button>
                </div>

                @endif
                <!-- cart end -->
                <!-- message start -->
                <!-- <div class="header-message-box ml-15">
                  <button class="dropdown-toggle" type="button" id="message" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path
                        d="M7.74866 5.97421C7.91444 5.96367 8.08162 5.95833 8.25005 5.95833C12.5532 5.95833 16.0417 9.4468 16.0417 13.75C16.0417 13.9184 16.0364 14.0856 16.0259 14.2514C16.3246 14.138 16.6127 14.003 16.8883 13.8482L19.2306 14.629C19.7858 14.8141 20.3141 14.2858 20.129 13.7306L19.3482 11.3882C19.8694 10.4604 20.1667 9.38996 20.1667 8.25C20.1667 4.70617 17.2939 1.83333 13.75 1.83333C11.0077 1.83333 8.66702 3.55376 7.74866 5.97421Z"
                        fill="" />
                      <path
                        d="M14.6667 13.75C14.6667 17.2938 11.7939 20.1667 8.25004 20.1667C7.11011 20.1667 6.03962 19.8694 5.11182 19.3482L2.76946 20.129C2.21421 20.3141 1.68597 19.7858 1.87105 19.2306L2.65184 16.8882C2.13062 15.9604 1.83338 14.89 1.83338 13.75C1.83338 10.2062 4.70622 7.33333 8.25004 7.33333C11.7939 7.33333 14.6667 10.2062 14.6667 13.75ZM5.95838 13.75C5.95838 13.2437 5.54797 12.8333 5.04171 12.8333C4.53545 12.8333 4.12504 13.2437 4.12504 13.75C4.12504 14.2563 4.53545 14.6667 5.04171 14.6667C5.54797 14.6667 5.95838 14.2563 5.95838 13.75ZM9.16671 13.75C9.16671 13.2437 8.7563 12.8333 8.25004 12.8333C7.74379 12.8333 7.33338 13.2437 7.33338 13.75C7.33338 14.2563 7.74379 14.6667 8.25004 14.6667C8.7563 14.6667 9.16671 14.2563 9.16671 13.75ZM11.4584 14.6667C11.9647 14.6667 12.375 14.2563 12.375 13.75C12.375 13.2437 11.9647 12.8333 11.4584 12.8333C10.9521 12.8333 10.5417 13.2437 10.5417 13.75C10.5417 14.2563 10.9521 14.6667 11.4584 14.6667Z"
                        fill="" />
                    </svg>
                    <span></span>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="message">
                    <li>
                      <a href="#0">
                        <div class="image">
                          <img src="assets/images/lead/lead-5.png" alt="" />
                        </div>
                        <div class="content">
                          <h6>Jacob Jones</h6>
                          <p>Hey!I can across your profile and ...</p>
                          <span>10 mins ago</span>
                        </div>
                      </a>
                    </li>
                    <li>
                      <a href="#0">
                        <div class="image">
                          <img src="assets/images/lead/lead-3.png" alt="" />
                        </div>
                        <div class="content">
                          <h6>John Doe</h6>
                          <p>Would you mind please checking out</p>
                          <span>12 mins ago</span>
                        </div>
                      </a>
                    </li>
                    <li>
                      <a href="#0">
                        <div class="image">
                          <img src="assets/images/lead/lead-2.png" alt="" />
                        </div>
                        <div class="content">
                          <h6>Anee Lee</h6>
                          <p>Hey! are you available for freelance?</p>
                          <span>1h ago</span>
                        </div>
                      </a>
                    </li>
                  </ul>
                </div> -->
                <!-- message end -->
                <!-- profile start -->
                 
                @php $user = auth()->user(); @endphp
                <div class="profile-box ml-15">
                  <button class="dropdown-toggle bg-transparent border-0" type="button" id="profile"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="profile-info">
                      <div class="info">
                        <div class="image">
                          <img src="{{ $user->avatar_url }}" alt="Avatar" class="rounded-circle" width="60">

                        </div>
                        <div class=" d-none d-md-block ms-2">
                          <h6 class="fw-500">{{ auth()->user()->name }}</h6>
                          <p>{{ auth()->user()->email }}</p>
                        </div>
                      </div>
                    </div>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profile">
                    <li>
                      <div class="author-info flex items-center !p-1">
                        <div class="image">
                          <img src="{{ $user->avatar_url }}" alt="image">
                        </div>
                        <div class="content">
                          <h4 class="text-sm">{{ auth()->user()->name }}</h4>
                          <a class="text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white text-xs" href="#">{{ auth()->user()->email }}</a>
                        </div>
                      </div>
                    </li>
                    <li class="divider"></li>
                    <li>
                      <a href="{{ route('profile.edit') }}">
                        <i class="fa-solid fa-user"></i> Mon Profile
                      </a>
                    </li>
                    <!-- <li>
                      <a href="#0">
                        <i class="lni lni-alarm"></i> Notifications
                      </a>
                    </li>
                    <li>
                      <a href="#0"> <i class="lni lni-inbox"></i> Messages </a>
                    </li>
                    <li>
                      <a href="#0"> <i class="lni lni-cog"></i> Settings </a>
                    </li> -->
                    <li class="divider"></li>
                    <li>
                      <form action="{{ route('logout') }}" method="POST" class="logout-form">
                        @csrf
                        <button type="submit" class="bg-transparent border-0"> Déconnexion</button>
                      </form>
                    </li>
                  </ul>
                </div>
                <!-- profile end -->
                 @endif
              </div>
            </div>
          </div>
        </div>
      </header>
      <!-- Container des notifications -->
    <div id="notifications-container" class="position-fixed top-0 end-0 p-3" style="z-index: 1055;">
        <!-- Les notifications toast apparaîtront ici -->
    </div>
  <!-- Container for Web Push activation button -->
  <div id="push-permission" class="position-fixed bottom-0 start-0 m-3" style="z-index:1055"></div>
      <!-- Cart modal -->
      <div class="modal fade" id="cartModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-end">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">🛍️ Votre panier</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>

            <div class="modal-body">
              <div id="cart-items">
                <!-- Exemple d’item -->
                <div class="cart-item">
                  <div class="item-info">
                    <img src="https://via.placeholder.com/55" alt="Produit">
                    <div>
                      <div class="item-name">Bobine de fil violet</div>
                      <small>Quantité : 2</small>
                    </div>
                  </div>
                  <div class="item-price">3 500 FCFA</div>
                </div>
              </div>
            </div>

            <div class="modal-footer d-flex justify-content-between align-items-center">
              <div>Total : <strong id="cart-total">7 000 FCFA</strong></div>
              <div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                  Continuer mes achats
                </button>
                <button type="button" id="preview-cart-btn" class="btn btn-primary">
                  Prévisualiser la commande
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ========== header end ========== -->
      <!-- === Modal: Complete Merchant Profile (triggered when merchant profile incomplete) === -->
      <div class="modal fade" id="completeProfileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content rounded-4">
            <div class="modal-header">
              <h5 class="modal-title">Compléter votre profil</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <p>Avant d'ajouter votre première fourniture, veuillez compléter les informations de votre mercerie (adresse, ville, téléphone).</p>
            </div>
            <div class="modal-footer">
              <a href="{{ route('merceries.profile.edit') }}" style="background: #4F0341; color: white; padding: 10px 20px; border-radius: 5px;">Compléter mon profil</a>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Plus tard</button>
            </div>
          </div>
        </div>
      </div>
        <!-- @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif -->
      <!-- ========== section start ========== -->
        <section class="section">
            <div class="container-fluid">
                <!-- ========== title-wrapper start ========== -->
                <div class="title-wrapper">
                    <div class="row align-items-center">
                    <div class="col-lg-12">
                            <div class="title">
                                @yield('content')
                            </div>
                        </div>
                    </div>
                </div>
                
        </section>
      <!-- ========== section end ========== -->

      <!-- ========== footer start =========== -->
      <footer class="footer">
        <div class="container-fluid">
          <div class="row">
            <div class="col-md-6 order-last order-md-first">
              <div class="copyright text-center text-md-start">
                <p class="text-sm">
                  Designed and Developed
                </p>
              </div>
            </div>
            <!-- end col-->
            <div class="col-md-6">
              <div class="terms d-flex justify-content-center justify-content-md-end">
                <a href="#0" class="text-sm">Term & Conditions</a>
                <a href="#0" class="text-sm ml-15">Privacy & Policy</a>
              </div>
            </div>
          </div>
          <!-- end row -->
        </div>
        <!-- end container -->
      </footer>
      <!-- ========== footer end =========== -->
    </main>
    <!-- ======== main-wrapper end =========== -->

    <!-- ========= All Javascript files linkup ======== -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jvectormap@2.0.5/jquery-jvectormap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jvectormap@2.0.5/jquery-jvectormap-world-mill.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/polyfill@3.0.0/polyfill.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- À la fin de votre body dans layouts/app.blade.php -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/fr.js"></script>

     <!-- Scripts globaux -->
    <script src="{{ asset('js/app.js') }}"></script>
  <script src="{{ asset('js/push.js') }}"></script>

    @if(session('success'))
      <script>
        Swal.fire({
          icon: 'success',
          title: 'Succès',
          text: '{{ session('success') }}',
          showConfirmButton: false,
          timer: 2000
        });
      </script>
    @endif


    @if(session('success') || session('error'))
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const toast = document.createElement('div');
        toast.className = 'toast align-items-center text-bg-{{ session("success") ? "success" : "danger" }} border-0 position-fixed top-0 end-0 m-3';
        toast.role = 'alert';
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    {{ session('success') ?? session('error') }}
                </div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        document.body.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
    });
</script>
@endif

@auth
<script>
  // Clear any stored post-login return value after successful authentication
  try { localStorage.removeItem('post_login_return'); } catch (e) { /* ignore */ }
</script>
@endauth

@if(session('showProfileModal') || session('show_profile_modal'))
<script>
  document.addEventListener('DOMContentLoaded', function () {
    try {
      var modalEl = document.getElementById('completeProfileModal');
      if (modalEl && typeof bootstrap !== 'undefined') {
        var bsModal = new bootstrap.Modal(modalEl);
        bsModal.show();
      }
    } catch (e) {
      console.error('Failed to show complete profile modal', e);
    }
  });
</script>
@endif


  <!-- Script pour les notifications -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.11.3/echo.iife.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pusher/8.0.0/pusher.min.js"></script>
  <script>
        // Écoute des notifications Laravel
    // Initialisation de Laravel Echo avec Pusher
    Pusher.logToConsole = false;
    // Enhanced Echo initialization with explicit ws options and auth headers for debugging
    window.Echo = new Echo({
      broadcaster: 'pusher',
      key: '{{ config('broadcasting.connections.pusher.key') }}',
      cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}',
      wsHost: '{{ env('VITE_PUSHER_HOST') ?: (env('PUSHER_HOST') ?: 'ws-' . env('PUSHER_APP_CLUSTER')) }}',
      wsPort: {{ env('PUSHER_PORT', 443) }},
      wssPort: {{ env('PUSHER_PORT', 443) }},
      forceTLS: {{ env('PUSHER_SCHEME', 'https') === 'https' ? 'true' : 'false' }},
      disableStats: true,
      enabledTransports: ['ws', 'wss'],
      auth: {
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'X-Requested-With': 'XMLHttpRequest'
        }
      }
    });

    // Debug: log pusher connection events
    try {
      const pusher = Echo.connector?.pusher;
      if (pusher) {
        pusher.connection.bind('connected', () => console.info('Pusher connected'));
        pusher.connection.bind('disconnected', () => console.warn('Pusher disconnected'));
        pusher.connection.bind('error', (err) => console.error('Pusher error', err));
        pusher.connection.bind('state_change', (states) => console.debug('Pusher state change', states));
      }
    } catch (e) {
      console.warn('Echo/Pusher debug setup failed', e);
    }

    // Helper to update unread badge
    function setUnreadBadge(count) {
      const badge = document.getElementById('notification-unread-badge');
      if (!badge) return;
      badge.dataset.count = count;
      badge.textContent = count;
      badge.style.display = count > 0 ? 'inline-block' : 'none';
    }

    function incrementUnreadBadge(by = 1) {
      const badge = document.getElementById('notification-unread-badge');
      if (!badge) return;
      const cur = parseInt(badge.dataset.count || '0', 10) || 0;
      setUnreadBadge(cur + by);
    }

    function decrementUnreadBadge(by = 1) {
      const badge = document.getElementById('notification-unread-badge');
      if (!badge) return;
      const cur = parseInt(badge.dataset.count || '0', 10) || 0;
      const next = Math.max(0, cur - by);
      setUnreadBadge(next);
    }

    // Écoute des notifications pour l'utilisateur connecté
    Echo.private('App.Models.User.{{ auth()->id() }}')
        .notification((notification) => {
            showToastNotification(notification);
            // increment unread badge when a new notification arrives
            incrementUnreadBadge(1);
        });

        // Fonction pour afficher les notifications toast
        function showToastNotification(notification) {
            const container = document.getElementById('notifications-container');
            const toast = document.createElement('div');
            toast.className = 'toast align-items-center text-white bg-primary border-0';
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');
            
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        <strong>${notification.message || 'Nouvelle notification'}</strong>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            
            container.appendChild(toast);
            
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            
            // Supprimer le toast après disparition
            toast.addEventListener('hidden.bs.toast', function () {
                toast.remove();
            });
        }
    </script>

  <script>
    // Mark dropdown notification as read when clicked, then navigate
    document.addEventListener('DOMContentLoaded', function() {
      const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      document.querySelectorAll('ul.dropdown-menu[aria-labelledby="notification"] a').forEach(a => {
        a.addEventListener('click', function(e) {
          const href = this.getAttribute('href') || '#';
          const url = new URL(href, window.location.origin);
          const notifId = url.searchParams.get('notif');

          if (notifId) {
            e.preventDefault();
            fetch(`/notifications/${notifId}/read`, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf
              },
              body: JSON.stringify({})
            }).then(() => {
              // Remove the "Nouveau" badge from the dropdown item
              const badge = this.querySelector('.badge.bg-primary');
              if (badge) badge.remove();
              // Remove the "Nouveau" badge from the notification card (if present)
              const notifCard = document.getElementById(`notif-${notifId}`);
              if (notifCard) {
                const parent = notifCard.closest('.single-notification');
                if (parent) {
                  const nouveauBadge = parent.querySelector('.badge.bg-primary');
                  if (nouveauBadge) nouveauBadge.remove();
                }
              }
              // decrement unread badge and navigate
              try { decrementUnreadBadge(1); } catch(e) {}
              window.location.href = href;
            }).catch(() => {
              window.location.href = href;
            });
          }
          // otherwise let the anchor behave normally
        });
      });
    });
  </script>


    <!-- Scripts spécifiques -->
    @stack('scripts')

  <!-- Cart script -->
  <script src="{{ asset('js/cart.js') }}?v={{ filemtime(public_path('js/cart.js')) }}"></script>

  </body>
</html>
