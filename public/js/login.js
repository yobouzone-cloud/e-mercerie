document.addEventListener("DOMContentLoaded", () => {
    const signUpButton = document.getElementById('signUp');
    const signInButton = document.getElementById('signIn');
    const container = document.getElementById('container');

    if (signUpButton && signInButton && container) {
        signUpButton.addEventListener('click', () => {
            container.classList.add("right-panel-active");
        });

        signInButton.addEventListener('click', () => {
            container.classList.remove("right-panel-active");
        });
    }
});

// Password show/hide toggles (works both on login and register pages)
document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.pw-toggle').forEach(btn=>{
        btn.addEventListener('click', function(e){
            const target = this.getAttribute('data-target');
            if(!target) return;
            const input = document.querySelector(target);
            if(!input) return;
            if(input.type === 'password'){
                input.type = 'text';
                this.textContent = 'Cacher';
                this.setAttribute('aria-label','Cacher le mot de passe');
            } else {
                input.type = 'password';
                this.textContent = 'Afficher';
                this.setAttribute('aria-label','Afficher le mot de passe');
            }
        });
    });
});








// <!DOCTYPE html>
// <html lang="fr">
// <head>
//     <meta charset="UTF-8">
//     <meta name="viewport" content="width=device-width, initial-scale=1.0">
//     <title>@yield('title', 'e-Mercerie')</title>

//     <!-- Styles globaux -->
//     <link rel="stylesheet" href="{{ asset('css/app.css') }}">

//     <!-- Styles spécifiques -->
//     @stack('styles')
// </head>
// <body>
//         <nav>
//             <div class="navbar">
//                 <div class="contain nav-container">
//                     <input class="checkbox" type="checkbox" name="" id="" />
//                     <div class="hamburger-lines">
//                         <span class="line line1"></span>
//                         <span class="line line2"></span>
//                         <span class="line line3"></span>
//                     </div>
//                     <div class="logo">
//                         <h1>Navbar</h1>
//                     </div>
//                     <div class="menu-items">
//                         <li><a href="{{ route('supplies.index') }}">Accueil</a></li>

//                         @guest
//                             <li><a href="{{ route('login.form') }}">Connexion</a></li>
//                             <li><a href="{{ route('register.form') }}">Inscription</a></li>
//                         @else
//                             @if(auth()->user()->isCouturier())
//                                 <li><a href="{{ route('supplies.selection') }}">Sélection Fournitures</a></li>
//                                 <li><a href="{{ route('orders.index') }}">Mes Commandes</a></li>
//                             @elseif(auth()->user()->isMercerie())
//                                 <li><a href="{{ route('merchant.supplies.index') }}">Mes Fournitures</a></li>
//                                 <li><a href="{{ route('orders.index') }}">Commandes Reçues</a></li>
//                             @endif

//                             <li>
//                                 <form action="{{ route('logout') }}" method="POST" class="logout-form">
//                                     @csrf
//                                     <button type="submit" class="btn-logout">Déconnexion</button>
//                                 </form>
//                             </li>
//                         @endguest
//                     </div>
//                 </div>

//             </div>
//         </nav>

//         @if(session('success'))
//             <div class="alert alert-success">{{ session('success') }}</div>
//         @endif

//         @if(session('error'))
//             <div class="alert alert-error">{{ session('error') }}</div>
//         @endif

//     <main class="main-content">
//         @yield('content')
//     </main>

//     <footer class="footer">
//         <p>© {{ date('Y') }} e-Mercerie — Tous droits réservés</p>
//     </footer>

//     <!-- Scripts globaux -->
//     <script src="{{ asset('js/app.js') }}"></script>

//     <!-- Scripts spécifiques -->
//     @stack('scripts')
// </body>
// </html>

