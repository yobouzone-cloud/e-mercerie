<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Inscription – Finger Style</title>
<style>
    @import url("https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;1,200;1,300;1,400;1,500;1,600;1,700;1,800&display=swap");

  /* === RESET === */
  * { margin:0; padding:0; box-sizing:border-box; font-family:'Plus Jakarta Sans', sans-serif !important; }

  /* === PALETTE === */
  :root {
      --main-color: #4F0341;
      --accent-color: #7a1761;
      --text-light: #fff;
      --text-dark: #333;
      --bg-light: #f8f8fa;
      --error-color: #e63946;
  }

  body {
      background: linear-gradient(135deg, #4F0341, #9333ea);
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      color: var(--text-dark);
      padding: 16px;
      overflow-x: hidden;
  }

  /* === CONTAINER === */
  .container {
      background-color: var(--text-light);
      border-radius: 20px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.1);
      overflow: hidden;
      width: 100%;
      max-width: 900px;
      display: flex;
      flex-wrap: wrap;
      animation: fadeIn 1s ease;
  }

  /* === FORM SECTION === */
  .form-container {
      flex: 1;
      min-width: 300px;
      padding: 40px 30px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      animation: slideInLeft 1.2s ease forwards;
  }

  .form-container h1 {
      font-size: clamp(1.5rem, 4vw, 2rem);
      color: var(--main-color);
      font-weight: 700;
      margin-bottom: 15px;
      opacity:0;
      transform: translateY(20px);
      animation: fadeUp 0.8s forwards 0.2s;
  }

  input, select {
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 14px;
      width: 100%;
      margin-bottom: 15px;
      font-size: 16px; /* Amélioration pour mobile */
      transition: border-color 0.3s, box-shadow 0.3s;
  }

  input:focus, select:focus {
      border-color: var(--main-color);
      box-shadow: 0 0 8px rgba(79,3,65,0.3);
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
      width: 100%;
  }

  button:hover {
      background-color: var(--accent-color);
      transform: translateY(-2px);
  }

  a {
      color: var(--main-color);
      text-decoration: none;
      font-size: 14px;
  }

  a:hover {
      text-decoration: underline;
  }

  .alert.error {
      background-color: #fdecea;
      color: var(--error-color);
      border-left: 4px solid var(--error-color);
      padding: 10px 15px;
      border-radius: 8px;
      margin-bottom: 15px;
      font-size: 14px;
  }

  .alert.error ul {
      margin-left: 16px;
  }

  .signup-text {
      margin-top: 18px;
      font-size: 14px;
      text-align: center;
      opacity:0;
      transform: translateY(20px);
      animation: fadeUp 0.8s forwards 1.2s;
  }

  /* === ILLUSTRATION === */
  .illustration {
      flex: 1;
      background: linear-gradient(135deg, #4F0341, #9333ea);
      display: flex;
      align-items: center;
      justify-content: center;
      min-width: 300px;
      min-height: 250px;
      animation: slideInRight 1.2s ease forwards;
  }

  .illustration img {
      width: 100%;
      height: 100%;
      max-width: 400px;
      height: auto;
      object-fit: contain;
  }

  /* === PASSWORD TOGGLE === */
  .password-container {
      position: relative;
      margin-bottom: 15px;
  }

  .password-container input {
      padding-right: 42px;
      margin-bottom: 0;
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
      width: auto;
  }

  /* === RESPONSIVE === */
  @media (max-width: 1024px) {
      .container { width: 95%; }
      .form-container { padding: 40px 30px; }
  }

  @media (max-width: 768px) {
      .container { 
          flex-direction: column; 
          width: 100%;
          border-radius: 16px;
      }
      .form-container { 
          padding: 30px 20px; 
          min-width: 100%;
      }
      .illustration { 
          width: 100%; 
          min-height: 200px; 
          padding: 20px;
      }
      .illustration img { 
          width: 70%; 
          max-width: 250px; 
      }
      input, select { 
          font-size: 16px; /* Important pour éviter le zoom sur iOS */
          padding: 12px; 
      }
      button { 
          padding: 12px 20px; 
          font-size: 16px; 
      }
  }

  @media (max-width: 480px) {
      body {
          padding: 12px;
      }
      .container {
          border-radius: 12px;
      }
      .form-container { 
          padding: 25px 16px; 
      }
      .form-container h1 { 
          font-size: 1.5rem; 
          margin-bottom: 12px;
      }
      input, select { 
          font-size: 16px; 
          padding: 12px; 
          margin-bottom: 12px;
      }
      button { 
          padding: 12px 20px; 
          font-size: 16px; 
      }
      .signup-text { 
          font-size: 13px; 
      }
      .illustration { 
          min-height: 180px; 
          padding: 15px;
      }
      .illustration img { 
          width: 60%; 
          max-width: 200px; 
      }
  }

  @media (max-width: 360px) {
      .form-container { 
          padding: 20px 12px; 
      }
      .form-container h1 { 
          font-size: 1.4rem; 
      }
      .signup-text { 
          font-size: 12px; 
      }
  }

  /* === ANIMATIONS === */
  @keyframes fadeIn { from { opacity:0; transform: scale(0.95); } to { opacity:1; transform:scale(1); } }
  @keyframes slideInLeft { from { transform: translateX(-100px); opacity:0; } to { transform: translateX(0); opacity:1; } }
  @keyframes slideInRight { from { transform: translateX(100px); opacity:0; } to { transform: translateX(0); opacity:1; } }
  @keyframes float { 0%,100%{transform:translateY(0);}50%{transform:translateY(-10px);} }
  @keyframes fadeUp { to { opacity:1; transform:translateY(0); } }
</style>
</head>
<body>

<div class="container">
  <!-- Formulaire d'inscription -->
  <div class="form-container sign-up-container">
      <form action="{{ route('register.submit') }}" method="POST">
          @csrf
          <h1>Inscription</h1>

          @if($errors->any())
              <div class="alert error">
                  <ul>
                      @foreach($errors->all() as $error)
                          <li>{{ $error }}</li>
                      @endforeach
                  </ul>
              </div>
          @endif

          <input type="text" name="name" placeholder="Nom" value="{{ old('name') }}" required>
          <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" required>
          
          <div class="password-container">
              <input type="password" name="password" id="register_password" placeholder="Mot de passe" required />
              <button type="button" class="pw-toggle" data-target="#register_password" aria-label="Afficher le mot de passe">Afficher</button>
          </div>
          
          <div class="password-container">
              <input type="password" name="password_confirmation" id="register_password_confirmation" placeholder="Confirmer mot de passe" required />
              <button type="button" class="pw-toggle" data-target="#register_password_confirmation" aria-label="Afficher le mot de passe">Afficher</button>
          </div>
          
          <select name="role" required>
              <option value="">-- Sélectionner un rôle --</option>
              <option value="couturier" {{ old('role')=='couturier'?'selected':'' }}>Couturier</option>
              <option value="mercerie" {{ old('role')=='mercerie'?'selected':'' }}>Mercerie</option>
          </select>

          <button type="submit">S'inscrire</button>
      </form>

      <p class="signup-text">Déjà inscrit ? <a href="{{ route('login.form') }}">Se connecter</a></p>
  </div>

  <!-- Illustration -->
  <div class="illustration">
      <img src="{{ asset('images/couturière.jpg') }}" alt="Illustration Inscription">
  </div>
</div>

<script>
// Gestion de l'affichage/masquage des mots de passe
document.addEventListener('DOMContentLoaded', function() {
    const toggleButtons = document.querySelectorAll('.pw-toggle');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const passwordInput = document.querySelector(targetId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                this.textContent = 'Masquer';
            } else {
                passwordInput.type = 'password';
                this.textContent = 'Afficher';
            }
        });
    });
    
    // Gestion des messages flash avec SweetAlert2
    @if(session('success') || session('error'))
        Swal.fire({
            icon: '{{ session("success") ? "success" : "error" }}',
            title: '{{ session("success") ? "Succès" : "Erreur" }}',
            text: `{{ session('success') ?? session('error') }}`,
            confirmButtonColor: '#4F0341'
        });
    @endif
});
</script>

<!-- SweetAlert2 pour messages flash -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>
</html>