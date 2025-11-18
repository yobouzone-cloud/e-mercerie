<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Connexion – Finger Style</title>
<style>
  @import url("https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap");

  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Plus Jakarta Sans', sans-serif;
  }

  :root {
    --main-color: #4F0341;
    --accent-color: #9333ea;
    --text-light: #fff;
    --error-color: #e63946;
    --border-color: #ddd;
    --text-muted: #666;
  }

  body {
    background: linear-gradient(135deg, #4F0341, #9333ea);
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    padding: 20px;
    color: #333;
    overflow-x: hidden;
  }

  .container {
    background-color: var(--text-light);
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    width: 100%;
    max-width: 900px;
    display: flex;
    flex-wrap: wrap;
    animation: fadeIn 1s ease;
    min-height: 500px;
  }

  .form-container {
    flex: 1 1 380px;
    padding: 50px 40px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    animation: slideInLeft 1.2s ease forwards;
  }

  .form-container h1 {
    font-size: 2rem;
    color: var(--main-color);
    font-weight: 700;
    margin-bottom: 20px;
    animation: fadeUp 0.8s forwards 0.2s;
  }

  input {
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 14px;
    width: 100%;
    margin-bottom: 15px;
    font-size: 14px;
    transition: border-color 0.3s, box-shadow 0.3s;
  }

  input:focus {
    border-color: var(--main-color);
    box-shadow: 0 0 8px rgba(79, 3, 65, 0.3);
    outline: none;
  }

  button {
    background-color: var(--main-color);
    color: var(--text-light);
    border: none;
    border-radius: 25px;
    padding: 14px 25px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s, transform 0.2s;
    white-space: nowrap;
  }

  button:hover {
    background-color: var(--accent-color);
    transform: translateY(-2px);
  }

  .illustration {
    flex: 1 1 380px;
    background: linear-gradient(135deg, #a84aff, #ff3fbf);
    display: flex;
    align-items: center;
    justify-content: center;
    animation: slideInRight 1.2s ease forwards;
    min-height: 300px;
  }

  .illustration img {
    width: 100%;
    max-width: 450px;
    height: 100%;
    object-fit: cover;
  }

  .signup-text {
    margin-top: 18px;
    font-size: 14px;
    text-align: center;
  }

  .alert.error {
    background-color: #ffe6e6;
    border: 1px solid var(--error-color);
    color: var(--error-color);
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 14px;
  }

  .alert.error ul {
    list-style: none;
    margin: 0;
    padding: 0;
  }

  .alert.error li {
    margin: 5px 0;
  }

  .password-group {
    position: relative;
    margin-bottom: 15px;
  }

  .pw-toggle {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    cursor: pointer;
    font-size: 14px;
    padding: 6px;
    color: var(--main-color);
    z-index: 2;
  }

  .form-actions {
    display: flex;
    gap: 10px;
    align-items: center;
    justify-content: space-between;
    margin-top: 10px;
    flex-wrap: wrap;
  }

  .form-actions a {
    color: var(--main-color);
    text-decoration: none;
    font-size: 14px;
    transition: color 0.3s;
  }

  .form-actions a:hover {
    color: var(--accent-color);
    text-decoration: underline;
  }

  /* =========================================================
     RESPONSIVE DESIGN OPTIMISÉ
  ========================================================= */

  /* GRANDES TABLETTES (≤ 1024px) */
  @media (max-width: 1024px) {
    .container {
      max-width: 95%;
      margin: 0 auto;
    }
    
    .form-container {
      padding: 40px 35px;
    }
    
    .illustration img {
      max-width: 380px;
    }
  }

  /* TABLETTES MOYENNES (≤ 850px) */
  @media (max-width: 850px) {
    body {
      padding: 15px;
    }

    .container {
      flex-direction: column;
      max-width: 600px;
      border-radius: 16px;
    }

    .illustration {
      order: -1;
      padding: 30px 0;
      min-height: 250px;
    }

    .illustration img {
      max-width: 320px;
      height: auto;
    }

    .form-container {
      padding: 35px 30px;
    }
    
    .form-container h1 {
      text-align: center;
    }
  }

  /* TABLETTES PETITES (≤ 768px) */
  @media (max-width: 768px) {
    .container {
      max-width: 95%;
    }
    
    .form-container {
      padding: 30px 25px;
    }
    
    .illustration {
      padding: 25px 0;
      min-height: 220px;
    }
    
    .illustration img {
      max-width: 280px;
    }
  }

  /* SMARTPHONES LARGE (≤ 650px) */
  @media (max-width: 650px) {
    body {
      padding: 10px;
    }

    .container {
      border-radius: 14px;
      min-height: auto;
    }

    .form-container h1 {
      font-size: 1.7rem;
      margin-bottom: 25px;
    }

    .form-actions {
      flex-direction: column;
      gap: 15px;
      align-items: stretch;
    }

    .form-actions button {
      width: 100%;
      order: 1;
    }

    .form-actions a {
      order: 2;
      text-align: center;
    }

    input {
      padding: 12px;
      font-size: 16px; /* Améliore l'accessibilité sur mobile */
      margin-bottom: 20px;
    }

    .password-group {
      margin-bottom: 20px;
    }

    .signup-text {
      margin-top: 25px;
      font-size: 15px;
    }
  }

  /* SMARTPHONES MOYENS (≤ 480px) */
  @media (max-width: 480px) {
    body {
      padding: 8px;
      align-items: flex-start;
      min-height: 100vh;
      padding-top: 20px;
      padding-bottom: 20px;
    }

    .container {
      border-radius: 12px;
      max-width: 100%;
    }

    .form-container {
      padding: 25px 20px;
    }

    .form-container h1 {
      font-size: 1.6rem;
      margin-bottom: 20px;
    }

    .illustration {
      padding: 20px 0;
      min-height: 180px;
    }

    .illustration img {
      max-width: 220px;
    }

    input {
      padding: 14px 12px;
      font-size: 16px;
      margin-bottom: 18px;
    }

    button {
      padding: 14px 20px;
      font-size: 15px;
      border-radius: 20px;
    }

    .pw-toggle {
      font-size: 13px;
      padding: 4px;
    }

    .signup-text {
      font-size: 14px;
      margin-top: 20px;
    }
  }

  /* SMARTPHONES TRÈS PETITS (≤ 360px) */
  @media (max-width: 360px) {
    body {
      padding: 5px;
    }

    .container {
      border-radius: 10px;
    }

    .form-container {
      padding: 20px 15px;
    }

    .form-container h1 {
      font-size: 1.5rem;
      margin-bottom: 15px;
    }

    .illustration {
      padding: 15px 0;
      min-height: 150px;
    }

    .illustration img {
      max-width: 180px;
    }

    input {
      padding: 12px 10px;
      font-size: 15px;
    }

    button {
      padding: 12px 18px;
      font-size: 14px;
    }

    .signup-text {
      font-size: 13px;
    }

    .form-actions a {
      font-size: 13px;
    }
  }

  /* ORIENTATION PAYSAGE SUR MOBILE */
  @media (max-height: 500px) and (orientation: landscape) {
    body {
      padding: 10px;
      align-items: flex-start;
    }

    .container {
      max-height: 90vh;
      overflow-y: auto;
    }

    .form-container {
      padding: 20px 25px;
    }

    .illustration {
      min-height: 200px;
      padding: 15px 0;
    }
  }

  /* HAUTE DENSITÉ PIXEL (Retina) */
  @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
    .container {
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
    }
  }

  /* MODE SOMBRE (respecte les préférences utilisateur) */
  @media (prefers-color-scheme: dark) {
    /* Garde les couleurs originales mais améliore le contraste */
    .container {
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    }
  }

  /* ANIMATIONS */
  @keyframes fadeIn { 
    from { opacity:0; transform:scale(0.95);} 
    to { opacity:1; transform:scale(1);} 
  }
  
  @keyframes slideInLeft { 
    from { transform:translateX(-100px); opacity:0;} 
    to { transform:translateX(0); opacity:1;} 
  }
  
  @keyframes slideInRight { 
    from { transform:translateX(100px); opacity:0;} 
    to { transform:translateX(0); opacity:1;} 
  }
  
  @keyframes fadeUp { 
    to { opacity:1; transform:translateY(0); } 
  }

  /* Amélioration de l'accessibilité */
  @media (prefers-reduced-motion: reduce) {
    * {
      animation-duration: 0.01ms !important;
      animation-iteration-count: 1 !important;
      transition-duration: 0.01ms !important;
    }
  }

  /* Focus visible pour l'accessibilité */
  button:focus-visible,
  input:focus-visible,
  .pw-toggle:focus-visible {
    outline: 2px solid var(--main-color);
    outline-offset: 2px;
  }
</style>
</head>
<body>

<div class="container">
  <div class="form-container">
    <form action="{{ route('login.submit') }}" method="POST">
      @csrf
      <h1>Connexion</h1>

      @if($errors->any())
        <div class="alert error">
          <ul>
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <input type="email" name="email" placeholder="Email" required>
      <div class="password-group">
        <input type="password" name="password" id="login_password" placeholder="Mot de passe" required />
        <button type="button" class="pw-toggle" data-target="#login_password" aria-label="Afficher le mot de passe">Afficher</button>
      </div>

      <input type="hidden" name="redirect_to" id="redirect_to" value="">
      <div class="form-actions">
        <a href="{{ route('password.request') }}">Mot de passe oublié ?</a>
        <button type="submit">Se connecter</button>
      </div>
    </form>

    <p class="signup-text">Pas encore de compte ? <a href="{{ route('register.form') }}">S'inscrire</a></p>
  </div>

  <div class="illustration">
    <img src="{{ asset('images/pexels.jpg') }}" alt="Illustration Connexion">
  </div>
</div>

<script>
  // Gestion de l'affichage/masquage du mot de passe
  document.addEventListener('DOMContentLoaded', function() {
    const toggleButtons = document.querySelectorAll('.pw-toggle');
    
    toggleButtons.forEach(button => {
      button.addEventListener('click', function() {
        const target = this.getAttribute('data-target');
        const input = document.querySelector(target);
        
        if (input.type === 'password') {
          input.type = 'text';
          this.textContent = 'Masquer';
          this.setAttribute('aria-label', 'Masquer le mot de passe');
        } else {
          input.type = 'password';
          this.textContent = 'Afficher';
          this.setAttribute('aria-label', 'Afficher le mot de passe');
        }
      });
    });

    // Populate redirect_to hidden input from localStorage if present
    try {
      const val = localStorage.getItem('post_login_return');
      if (val) {
        const input = document.getElementById('redirect_to');
        if (input) input.value = val;
      }
    } catch (e) {
      // ignore
    }

    // Amélioration : focus sur le premier champ de formulaire
    const firstInput = document.querySelector('input[type="email"]');
    if (firstInput) {
      setTimeout(() => firstInput.focus(), 300);
    }
  });
</script>

</body>
</html>