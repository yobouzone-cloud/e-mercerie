<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- CSRF token + WebPush public key for client-side subscription -->
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="webpush-public-key" content="{{ config('webpush.vapid.public') ?? config('services.webpush.public') }}">
<title>Liste des Merceries – Prodmast</title>
<style>
/* =========================================================
   GLOBAL — RESET, FONTS, VARIABLES
========================================================= */
@import url("https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap");
@import url("https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css");

:root {
  --primary-color: #4F0341;
  --primary-dark: #3a022c;
  --primary-light: #7a1761;
  --secondary-color: #FF6B95;
  --accent-color: #FFD166;
  --bg-light: #fefcff;
  --bg-white: #ffffff;
  --text-light: #ffffff;
  --text-dark: #2a2a2a;
  --text-muted: #6b7280;
  --border-light: #f0f0f0;
  --shadow-sm: 0 2px 8px rgba(0,0,0,0.06);
  --shadow-md: 0 8px 25px rgba(0,0,0,0.1);
  --shadow-lg: 0 15px 40px rgba(0,0,0,0.15);
  --radius-sm: 12px;
  --radius-md: 20px;
  --radius-lg: 28px;
  --transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
  --transition-fast: all 0.25s ease;
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Plus Jakarta Sans', sans-serif;
}

html {
  scroll-behavior: smooth;
}

body {
  background: var(--bg-light);
  color: var(--text-dark);
  overflow-x: hidden;
  line-height: 1.6;
}

/* =========================================================
   LOADER & ANIMATIONS
========================================================= */
@keyframes spin {
  to { transform: rotate(360deg); }
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-8px); }
}

@keyframes shimmer {
  0% { background-position: -468px 0; }
  100% { background-position: 468px 0; }
}

/* =========================================================
   HEADER
========================================================= */
header {
  position: fixed;
  top: 0; 
  left: 0;
  width: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 22px 80px;
  background: transparent;
  transition: var(--transition);
  z-index: 1000;
  backdrop-filter: blur(0px);
}

header.scrolled {
  background: rgba(255, 255, 255, 0.95);
  box-shadow: var(--shadow-sm);
  padding: 14px 50px;
  backdrop-filter: blur(10px);
}

.logo {
  font-weight: 800;
  font-size: 1.65rem;
  color: var(--text-light);
  transition: var(--transition);
  text-decoration: none;
  letter-spacing: -0.5px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.logo::before {
  content: "🪡";
  font-size: 1.4rem;
}

header.scrolled .logo {
  color: var(--primary-color);
}

.name {
  color: var(--text-light);
  font-weight: 600;
  font-size: 1rem;
}

.email {
  color: var(--text-light);
  font-weight: 400;
  font-size: 0.8rem;
}

.fa-chevron-down {
  color: var(--text-light);
  font-size: 0.8rem;
}

header.scrolled .fa-chevron-down {
  color: var(--primary-color);
  font-size: 0.8rem;
}

header.scrolled .name {
  color: var(--primary-color);
}

header.scrolled .email {
  color: var(--primary-color);
}

.btn-signin {
  background: var(--primary-color);
  color: var(--text-light);
  padding: 12px 26px;
  border-radius: var(--radius-lg);
  border: none;
  font-weight: 600;
  cursor: pointer;
  text-decoration: none;
  transition: var(--transition);
  box-shadow: 0 4px 12px rgba(79, 3, 65, 0.25);
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-signin:hover {
  background: var(--primary-light);
  transform: translateY(-3px);
  box-shadow: 0 8px 20px rgba(79, 3, 65, 0.35);
}

/* =========================================================
   DROPDOWN (profil)
========================================================= */
.profile-box {
  position: relative;
}

.dropdown-toggle {
  cursor: pointer;
  transition: var(--transition);
}

.dropdown-toggle:hover {
  transform: translateY(-2px);
}

.profile-info {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 4px;
  border-radius: 50px;
  transition: var(--transition);
}

.profile-info:hover {
  background: rgba(255, 255, 255, 0.15);
}

header.scrolled .profile-info:hover {
  background: rgba(79, 3, 65, 0.05);
}

.dropdown-menu {
  display: none;
  position: absolute;
  right: 0;
  top: calc(100% + 12px);
  background: rgba(255, 255, 255, 0.98);
  backdrop-filter: blur(15px);
  border-radius: var(--radius-md);
  min-width: 260px;
  box-shadow: var(--shadow-lg);
  padding: 12px 0;
  opacity: 0;
  transform: translateY(-15px);
  transition: var(--transition);
  z-index: 2000;
  border: 1px solid rgba(255, 255, 255, 0.2);
  overflow: hidden;
}

.dropdown-menu::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 1px;
  background: linear-gradient(90deg, transparent, var(--primary-light), transparent);
}

.dropdown-menu.show {
  display: block;
  opacity: 1;
  transform: translateY(0);
}

.dropdown-menu .divider {
  height: 1px;
  background: linear-gradient(to right, transparent, #e5e7eb, transparent);
  margin: 10px 0;
}

.dropdown-menu a,
.dropdown-menu button {
  display: flex;
  align-items: center;
  padding: 12px 20px;
  gap: 12px;
  width: 100%;
  color: var(--text-dark);
  text-decoration: none;
  font-size: 0.95rem;
  font-weight: 500;
  background: transparent;
  border: none;
  cursor: pointer;
  transition: var(--transition-fast);
  position: relative;
  overflow: hidden;
}

.dropdown-menu a::before,
.dropdown-menu button::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  height: 100%;
  width: 4px;
  background: var(--primary-color);
  transform: scaleY(0);
  transition: var(--transition-fast);
}

.dropdown-menu a i,
.dropdown-menu button i {
  color: var(--primary-color);
  width: 18px;
  text-align: center;
  transition: var(--transition-fast);
}

.dropdown-menu a:hover,
.dropdown-menu button:hover {
  background: rgba(79, 3, 65, 0.05);
  color: var(--primary-light);
  padding-left: 24px;
}

.dropdown-menu a:hover::before,
.dropdown-menu button:hover::before {
  transform: scaleY(1);
}

.dropdown-menu a:hover i,
.dropdown-menu button:hover i {
  transform: scale(1.1);
  color: var(--primary-light);
}

/* Block user info */
.dropdown-menu .author-info {
  display: flex;
  align-items: center;
  padding: 16px 20px;
  background: rgba(249, 244, 255, 0.6);
  margin: 0 12px 10px;
  border-radius: var(--radius-sm);
  border: 1px solid rgba(79, 3, 65, 0.1);
}

.dropdown-menu .author-info img {
  width: 50px;
  height: 50px;
  border-radius: var(--radius-sm);
  object-fit: cover;
  border: 2px solid rgba(79, 3, 65, 0.1);
}

/* =========================================================
   HERO SECTION
========================================================= */
.hero {
  height: 70vh;
  min-height: 600px;
  background: 
    linear-gradient(135deg, rgba(79,3,65,0.75) 0%, rgba(122,23,97,0.85) 100%),
    url('{{ asset("images/supplies.jpg") }}') center/cover no-repeat;
  color: var(--text-light);
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  text-align: center;
  padding: 0 20px;
  position: relative;
  overflow: hidden;
}

.hero::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.05' fill-rule='evenodd'/%3E%3C/svg%3E");
  animation: float 20s infinite linear;
}

.hero-content {
  max-width: 800px;
  position: relative;
  z-index: 2;
  animation: fadeIn 1s ease-out;
}

.hero h1 {
  font-size: 3.2rem;
  font-weight: 800;
  margin-bottom: 18px;
  line-height: 1.2;
  text-shadow: 0 2px 10px rgba(0,0,0,0.2);
}

.hero p {
  font-size: 1.25rem;
  max-width: 650px;
  margin: 0 auto;
  color: rgba(255, 255, 255, 0.9);
  font-weight: 400;
  margin-bottom: 10px;
}

.hero-buttons {
  margin-top: 32px;
  display: flex;
  gap: 18px;
  flex-wrap: wrap;
  justify-content: center;
}

/* Buttons */
.btn {
  padding: 14px 32px;
  border-radius: var(--radius-lg);
  border: none;
  cursor: pointer;
  font-weight: 600;
  transition: var(--transition);
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  font-size: 1rem;
  position: relative;
  overflow: hidden;
}

.btn::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 5px;
  height: 5px;
  background: rgba(255, 255, 255, 0.5);
  opacity: 0;
  border-radius: 100%;
  transform: scale(1, 1) translate(-50%);
  transform-origin: 50% 50%;
}

.btn:hover::after {
  animation: ripple 1s ease-out;
}

@keyframes ripple {
  0% {
    transform: scale(0, 0);
    opacity: 0.5;
  }
  100% {
    transform: scale(20, 20);
    opacity: 0;
  }
}

.btn-primary {
  background: var(--text-light);
  color: var(--primary-color);
  box-shadow: 0 6px 20px rgba(255, 255, 255, 0.25);
}

.btn-primary:hover {
  background: var(--primary-light);
  color: var(--text-light);
  transform: translateY(-4px);
  box-shadow: 0 12px 25px rgba(79, 3, 65, 0.35);
}

.btn-outline {
  background: transparent;
  border: 2px solid var(--text-light);
  color: var(--text-light);
  box-shadow: 0 4px 15px rgba(255, 255, 255, 0.15);
}

.btn-outline:hover {
  background: var(--text-light);
  color: var(--primary-color);
  transform: translateY(-4px);
  box-shadow: 0 10px 22px rgba(255, 255, 255, 0.25);
}

/* =========================================================
   TITRE DE SECTION
========================================================= */
.section-title {
  text-align: center;
  margin-top: 100px;
  font-size: 2.5rem;
  font-weight: 800;
  color: var(--primary-color);
  position: relative;
  padding-bottom: 18px;
}

.section-title::after {
  content: '';
  width: 100px;
  height: 5px;
  background: linear-gradient(90deg, var(--primary-color), var(--primary-light));
  border-radius: 3px;
  margin: 12px auto 0;
  display: block;
  box-shadow: 0 2px 8px rgba(79, 3, 65, 0.2);
}

/* =========================================================
   SEARCH BAR
========================================================= */
.search-container {
  display: flex;
  justify-content: center;
  margin-top: 32px;
  padding: 0 20px;
}

.search-wrapper {
  display: flex;
  justify-content: center;
  width: 100%;
  max-width: 720px;
  padding: 8px;
}

.search-bar {
  background: var(--bg-white);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-md);
  display: flex;
  align-items: center;
  padding: 12px 20px;
  transition: var(--transition);
  border: 1px solid transparent;
}

.search-bar:focus-within {
  box-shadow: 0 10px 30px rgba(79, 3, 65, 0.15);
  border-color: rgba(79, 3, 65, 0.2);
  transform: translateY(-3px);
}

.search-bar i {
  color: var(--text-muted);
  margin-right: 12px;
  font-size: 1.1rem;
}

.search-bar input {
  flex: 1;
  border: 0;
  outline: 0;
  padding: 8px 12px;
  font-size: 1rem;
  color: var(--text-dark);
  background: transparent;
}

.search-bar input::placeholder {
  color: var(--text-muted);
}

#merceries-loader-landing {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  border: 3px solid #f3f3f3;
  border-top: 3px solid var(--primary-light);
  animation: spin 1s linear infinite;
  margin-left: 8px;
}

/* =========================================================
   CARDS DE MERCERIES
========================================================= */
.merceries {
  padding: 80px 80px 100px;
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 40px;
  justify-items: center;
}

.card {
  background: var(--bg-white);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-md);
  overflow: hidden;
  opacity: 0;
  transform: translateY(30px);
  transition: var(--transition);
  width: 100%;
  max-width: 380px;
  position: relative;
}

.card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, var(--primary-color), var(--primary-light));
  transform: scaleX(0);
  transform-origin: left;
  transition: var(--transition);
}

.card:hover::before {
  transform: scaleX(1);
}

.card.visible {
  opacity: 1;
  transform: translateY(0);
}

.card:hover {
  transform: translateY(-10px) scale(1.02);
  box-shadow: var(--shadow-lg);
}

.card img {
  width: 100%;
  height: 220px;
  object-fit: cover;
  transition: var(--transition);
}

.card:hover img {
  transform: scale(1.05);
}

.card-content {
  padding: 24px;
}

.card-content h3 {
  font-size: 1.35rem;
  color: var(--primary-color);
  margin-bottom: 10px;
  font-weight: 700;
}

.card-content .description {
  color: var(--text-muted);
  margin-bottom: 16px;
  font-size: 0.95rem;
  line-height: 1.5;
}

.info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.9rem;
  margin-bottom: 20px;
}

.location {
  color: var(--text-muted);
  display: flex;
  align-items: center;
  gap: 5px;
}

.rating {
  color: #FFB800;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 4px;
}

.card-content .btn {
  margin-top: 8px;
  width: 100%;
  border-radius: var(--radius-sm);
  background: var(--primary-color);
  color: #fff;
  padding: 12px 20px;
  font-weight: 600;
  position: relative;
  overflow: hidden;
}

.card-content .btn:hover {
  background: var(--primary-light);
  transform: translateY(-3px);
  box-shadow: 0 8px 20px rgba(79, 3, 65, 0.3);
}

/* =========================================================
   FORMULAIRE DE COMPARAISON
========================================================= */
.supplies-form {
  background: var(--bg-white);
  padding: 32px;
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-md);
  margin: 40px auto;
  max-width: 1000px;
  transition: var(--transition);
  border: 1px solid rgba(0,0,0,0.05);
}

.supplies-form:hover {
  box-shadow: var(--shadow-lg);
  transform: translateY(-5px);
}

.supplies-form label {
  display: block;
  font-weight: 600;
  margin-bottom: 8px;
  color: var(--primary-color);
  font-size: 0.95rem;
}

.supplies-form select,
.supplies-form input[type="number"] {
  width: 100%;
  padding: 12px 16px;
  border: 1.5px solid #e5e7eb;
  font-size: 1rem;
  transition: var(--transition);
  background: var(--bg-white);
}

.supplies-form select:focus,
.supplies-form input[type="number"]:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(79, 3, 65, 0.15);
}

.form-filters {
  display: flex;
  gap: 16px;
  align-items: flex-end;
  margin-bottom: 24px;
  flex-wrap: wrap;
}

.form-filter-group {
  flex: 1;
  min-width: 200px;
}

.form-filter-group label {
  margin-bottom: 8px;
}

#quarter-loader {
  position: absolute;
  top: 42px;
  right: -28px;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  border: 3px solid #f3f3f3;
  border-top: 3px solid var(--primary-light);
  animation: spin 1s linear infinite;
}

.supplies-form .soft-btn.submit-btn {
  width: 100%;
  padding: 16px 24px;
  font-size: 1.1rem;
  font-weight: 700;
  color: #fff;
  background: var(--primary-color);
  border: none;
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: var(--transition);
  margin-bottom: 24px;
  box-shadow: 0 6px 18px rgba(79, 3, 65, 0.25);
  position: relative;
  overflow: hidden;
}

.supplies-form .soft-btn.submit-btn:hover {
  background: var(--primary-light);
  transform: translateY(-3px);
  box-shadow: 0 10px 25px rgba(79, 3, 65, 0.35);
}

/* =========================================================
   CARDS DE FOURNITURES
========================================================= */
.supplies-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 24px;
  margin-top: 24px;
}

.supply-card {
  background: var(--bg-white);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-sm);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  transition: var(--transition);
  border: 1px solid rgba(0,0,0,0.05);
  position: relative;
}

.supply-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(90deg, var(--primary-color), var(--primary-light));
  transform: scaleX(0);
  transition: var(--transition);
}

.supply-card:hover::before {
  transform: scaleX(1);
}

.supply-card:hover {
  transform: translateY(-8px);
  box-shadow: var(--shadow-lg);
}

.supply-image {
  overflow: hidden;
}

.supply-image img {
  width: 100%;
  height: 180px;
  object-fit: cover;
  transition: var(--transition);
}

.supply-card:hover .supply-image img {
  transform: scale(1.08);
}

.supply-content {
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  flex-grow: 1;
}

.supply-content h3 {
  font-size: 1.2rem;
  color: var(--primary-color);
  font-weight: 700;
  line-height: 1.3;
}

.supply-content .description {
  font-size: 0.9rem;
  color: var(--text-muted);
  line-height: 1.5;
  flex-grow: 1;
}

.price-qty {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-top: 8px;
}

.price {
  font-weight: 700;
  color: var(--primary-color);
  font-size: 1.1rem;
}

/* =========================================================
   QUANTITY CONTROLS - STYLES E-COMMERCE
========================================================= */
.quantity-group {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
}

.quantity-group label {
  font-size: 0.85rem;
  color: var(--text-muted);
  margin-bottom: 8px;
  font-weight: 600;
}

.quantity-controls {
  display: flex;
  align-items: center;
  background: var(--bg-white);
  border: 2px solid var(--border-light);
  border-radius: var(--radius-sm);
  overflow: hidden;
  transition: var(--transition);
}

.quantity-controls:focus-within {
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(79, 3, 65, 0.1);
}

.quantity-btn {
  background: var(--bg-light);
  border: none;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: var(--transition);
  color: var(--text-secondary);
}

.quantity-btn:hover {
  background: var(--primary-color);
  color: var(--text-light);
}

.quantity-btn:active {
  transform: scale(0.95);
}

.quantity-btn:disabled {
  background: var(--border-light);
  color: var(--text-muted);
  cursor: not-allowed;
  transform: none;
}

.quantity-btn:disabled:hover {
  background: var(--border-light);
  color: var(--text-muted);
}

.quantity-input {
  width: 60px;
  height: 36px;
  border: none;
  text-align: center;
  font-size: 0.95rem;
  font-weight: 600;
  color: var(--text-dark);
  background: var(--bg-white);
  outline: none;
  -moz-appearance: textfield;
}

.quantity-input::-webkit-outer-spin-button,
.quantity-input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

.quantity-input[data-measure="true"] {
  width: 80px;
}

/* Animation pour les changements de valeur */
@keyframes pulseUpdate {
  0% { transform: scale(1); }
  50% { transform: scale(1.05); }
  100% { transform: scale(1); }
}

.quantity-input.updated {
  animation: pulseUpdate 0.3s ease;
}

/* MESSAGE QUAND AUCUNE FOURNITURE */
.empty-message {
  text-align: center;
  grid-column: 1/-1;
  font-size: 1.1rem;
  color: var(--text-muted);
  padding: 40px 20px;
  background: var(--bg-white);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-sm);
}

/* =========================================================
   FOOTER
========================================================= */
footer {
  background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
  color: var(--text-light);
  text-align: center;
  padding: 50px 20px;
  margin-top: 100px;
  position: relative;
  overflow: hidden;
}

footer::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}

footer p {
  position: relative;
  z-index: 2;
  font-size: 1rem;
  opacity: 0.9;
}

/* =========================================================
   RESPONSIVE DESIGN
========================================================= */
@media (max-width: 1200px) {
  .merceries {
    padding: 70px 60px 90px;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 30px;
  }
}

@media (max-width: 1024px) {
  header { 
    padding: 18px 40px; 
  }
  
  header.scrolled {
    padding: 12px 30px;
  }
  
  .hero h1 {
    font-size: 2.8rem;
  }
  
  .merceries {
    padding: 60px 40px 80px;
  }
  
  .supplies-list {
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  }
}

@media (max-width: 768px) {
  header { 
    padding: 15px 25px; 
  }
  
  .logo { 
    font-size: 1.4rem; 
  }
  
  .btn-signin { 
    padding: 10px 20px; 
    font-size: 0.95rem; 
  }
  
  .hero { 
    height: 60vh; 
    min-height: 500px;
  }
  
  .hero h1 { 
    font-size: 2.3rem; 
  }
  
  .hero p {
    font-size: 1.1rem;
  }
  
  .section-title { 
    font-size: 2rem; 
    margin-top: 80px;
  }
  
  .merceries { 
    padding: 50px 25px 70px; 
    grid-template-columns: 1fr;
  }
  
  .hero-buttons {
    gap: 12px;
  }
  
  .btn {
    padding: 12px 24px;
    font-size: 0.95rem;
  }
  
  .supplies-form {
    padding: 24px;
  }
  
  .form-filters {
    flex-direction: column;
    gap: 16px;
  }
  
  .form-filter-group {
    width: 100%;
  }
  
  /* Responsive adjustments for quantity controls */
  .quantity-controls {
    border-width: 1px;
  }
  
  .quantity-btn {
    width: 32px;
    height: 32px;
  }
  
  .quantity-input {
    width: 50px;
    height: 32px;
    font-size: 0.9rem;
  }
  
  .quantity-input[data-measure="true"] {
    width: 70px;
  }
}

@media (max-width: 480px) {
  header { 
    padding: 12px 20px; 
  }
  
  .logo { 
    font-size: 1.3rem; 
  }
  
  .hero { 
    height: 55vh; 
    min-height: 450px;
  }
  
  .hero h1 { 
    font-size: 2rem; 
    margin-bottom: 12px;
  }
  
  .hero p {
    font-size: 1rem;
  }
  
  .section-title { 
    font-size: 1.8rem; 
  }
  
  .merceries { 
    padding: 40px 20px 60px; 
  }
  
  .card-content {
    padding: 20px;
  }
  
  .supplies-form {
    padding: 20px;
    margin: 30px 15px;
  }
  
  .search-wrapper {
    padding: 5px;
  }
  
  .search-bar {
    padding: 10px 16px;
  }
}

/* =========================================================
   UTILITY CLASSES
========================================================= */
.hidden {
  display: none !important;
}

.text-center {
  text-align: center;
}

.mb-4 {
  margin-bottom: 24px;
}

.rounded-circle {
  border-radius: 50% !important;
}

.d-none {
  display: none !important;
}

.d-md-block {
  display: block !important;
}

@media (max-width: 767px) {
  .d-md-block {
    display: none !important;
  }
}

.bg-transparent {
  background: transparent !important;
}

.border-0 {
  border: 0 !important;
}

.loader {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  border: 3px solid #f3f3f3;
  border-top: 3px solid var(--primary-light);
  animation: spin 1s linear infinite;
}

/* =========================================================
   LOADER DE COMPARAISON - STYLES MANQUANTS
========================================================= */
.compare-loader {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(255, 255, 255, 0.98);
  display: none;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  z-index: 9999;
  backdrop-filter: blur(12px);
}

.compare-loader:not(.hidden) {
  display: flex;
}

.loader-spinner {
  width: 70px;
  height: 70px;
  border: 5px solid #f3f3f3;
  border-top: 5px solid var(--primary-color);
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 24px;
}

.compare-loader p {
  font-size: 1.3rem;
  color: var(--primary-color);
  font-weight: 600;
  text-align: center;
  margin: 0;
}

/* Animation de pulse pour le texte */
@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.7; }
}

.compare-loader p {
  animation: pulse 2s infinite;
}

/* =========================================================
   PAGINATION STYLES AMÉLIORÉS
========================================================= */
.pagination-block {
  display: flex;
  justify-content: center;
  margin-top: 40px;
  padding: 20px 0;
}

.pagination {
  display: flex;
  gap: 8px;
  list-style: none;
  padding: 0;
  margin: 0;
  align-items: center;
  flex-wrap: wrap;
  justify-content: center;
}

.pagination .page-item {
  margin: 2px;
}

.pagination .page-link {
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 44px;
  height: 44px;
  padding: 0 16px;
  border: 2px solid transparent;
  border-radius: var(--radius-sm);
  background: var(--bg-white);
  color: var(--text-dark);
  text-decoration: none;
  font-weight: 600;
  font-size: 0.95rem;
  transition: var(--transition);
  box-shadow: var(--shadow-sm);
  position: relative;
  overflow: hidden;
}

.pagination .page-link:hover {
  background: var(--primary-color);
  color: var(--text-light);
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
  border-color: var(--primary-color);
}

.pagination .page-item.active .page-link {
  background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
  color: var(--text-light);
  border-color: var(--primary-color);
  box-shadow: 0 4px 15px rgba(79, 3, 65, 0.3);
  transform: translateY(-1px);
}

.pagination .page-item.disabled .page-link {
  background: var(--bg-light);
  color: var(--text-muted);
  cursor: not-allowed;
  opacity: 0.6;
  transform: none;
  box-shadow: none;
}

.pagination .page-item.disabled .page-link:hover {
  background: var(--bg-light);
  color: var(--text-muted);
  transform: none;
  box-shadow: none;
}

/* Style spécial pour les boutons précédent/suivant */
.pagination .page-item:first-child .page-link,
.pagination .page-item:last-child .page-link {
  font-weight: 700;
  padding: 0 20px;
  background: var(--bg-white);
  border: 2px solid var(--border-light);
}

.pagination .page-item:first-child .page-link:hover,
.pagination .page-item:last-child .page-link:hover {
  background: var(--primary-color);
  border-color: var(--primary-color);
  color: var(--text-light);
}

/* Points de suspension */
.pagination .page-item .page-link span {
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Animation de pulse pour la page active */
@keyframes activePulse {
  0%, 100% { transform: translateY(-1px) scale(1); }
  50% { transform: translateY(-1px) scale(1.05); }
}

.pagination .page-item.active .page-link {
  animation: activePulse 2s ease-in-out infinite;
}

/* =========================================================
   BOUTON DE RÉINITIALISATION AMÉLIORÉ
========================================================= */
.reset-btn {
  background: linear-gradient(135deg, #f8fafc, #f1f5f9) !important;
  color: #475569 !important;
  border: 2px solid #e2e8f0 !important;
  padding: 12px 24px !important;
  border-radius: var(--radius-sm) !important;
  font-weight: 600 !important;
  font-size: 0.95rem !important;
  cursor: pointer !important;
  transition: var(--transition) !important;
  display: inline-flex !important;
  align-items: center !important;
  gap: 8px !important;
  box-shadow: 0 2px 8px rgba(71, 85, 105, 0.1) !important;
  position: relative !important;
  overflow: hidden !important;
}

.reset-btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.6), transparent);
  transition: var(--transition);
}

.reset-btn:hover::before {
  left: 100%;
}

.reset-btn:hover {
  background: linear-gradient(135deg, #f1f5f9, #e2e8f0) !important;
  color: #334155 !important;
  border-color: #cbd5e1 !important;
  transform: translateY(-2px) !important;
  box-shadow: 0 4px 15px rgba(71, 85, 105, 0.2) !important;
}

.reset-btn:active {
  transform: translateY(0) !important;
  box-shadow: 0 2px 5px rgba(71, 85, 105, 0.15) !important;
}

.reset-btn i {
  font-size: 0.9rem;
  transition: var(--transition);
}

.reset-btn:hover i {
  transform: rotate(-180deg);
}

/* Animation de succès après réinitialisation */
@keyframes resetSuccess {
  0% { background: linear-gradient(135deg, #f8fafc, #f1f5f9); }
  50% { background: linear-gradient(135deg, #dcfce7, #bbf7d0); }
  100% { background: linear-gradient(135deg, #f8fafc, #f1f5f9); }
}

.reset-btn.success {
  animation: resetSuccess 1s ease-in-out;
}

/* Container pour les boutons d'action */
.form-actions {
  display: flex;
  gap: 16px;
  align-items: center;
  margin-bottom: 24px;
  flex-wrap: wrap;
}

@media (max-width: 768px) {
  .form-actions {
    flex-direction: column;
    align-items: stretch;
  }
  
  .form-actions .submit-btn,
  .form-actions .reset-btn {
    width: 100%;
    justify-content: center;
  }
  
  .pagination .page-link {
    min-width: 40px;
    height: 40px;
    padding: 0 12px;
    font-size: 0.9rem;
  }
}

/* Style pour le compteur de résultats */
.results-count {
  text-align: center;
  color: var(--text-muted);
  font-size: 0.95rem;
  margin-top: 12px;
  padding: 8px 16px;
  background: var(--bg-light);
  border-radius: var(--radius-sm);
  display: inline-block;
}
</style>
</head>
<body>

<header id="header">
  <a href="{{ route('landing') }}" class="logo">E-mercerie</a>
  @if(!Auth::check())
    <a href="{{ route('login.form') }}" class="btn-signin">
      <i class="fas fa-sign-in-alt"></i> Se connecter
    </a>
  @else
    @php $user = auth()->user(); @endphp
    <div class="profile-box">
      <button class="dropdown-toggle bg-transparent border-0" type="button" id="profile"
        data-bs-toggle="dropdown" aria-expanded="false">
        <div class="profile-info">
          <div class="image">
            <img src="{{ $user->avatar_url ?? asset('images/placeholder-60.png') }}" alt="Avatar" class="rounded-circle" width="40" style="object-fit:cover;">
          </div>
          <div class="d-none d-md-block" style="text-align:left;">
            <div class="name">{{ $user->name }}</div>
            <div class="email">{{ $user->email }}</div>
          </div>
          <i class="fas fa-chevron-down"></i>
        </div>
      </button>
      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profile">
        <li>
          <div class="author-info">
            <div class="image">
              <img src="{{ $user->avatar_url ?? asset('images/placeholder-60.png') }}" alt="image" style="width:50px;height:50px;object-fit:cover;border-radius:var(--radius-sm);">
            </div>
            <div class="content" style="margin-left:12px;">
              <h4 style="margin:0;font-size:1rem;font-weight:700;">{{ $user->name }}</h4>
              <a href="#" style="font-size:0.85rem;color:var(--text-muted);text-decoration:none;">{{ $user->email }}</a>
            </div>
          </div>
        </li>
        <li class="divider"></li>
        <li>
          <a href="{{ route('merceries.profile.edit') }}" class="dropdown-item">
            <i class="fa-solid fa-user"></i> Mon Profile
          </a>
        </li>
        <li>
          @if(auth()->user()->isCouturier())
          <a href="{{ route('merceries.index') }}" class="dropdown-item">
            <i class="fa-solid fa-gauge"></i> Tableau de bord
          </a>
          @elseif(auth()->user()->isMercerie())
          <a href="{{ route('orders.index') }}" class="dropdown-item">
            <i class="fa-solid fa-gauge"></i> Tableau de bord
          </a>
          @endif
        </li>
        <li class="divider"></li>
        <li>
          <form action="{{ route('logout') }}" method="POST" class="logout-form">
            @csrf
            <button type="submit" class="bg-transparent border-0 dropdown-item" style="padding-left:20px;">
              <i class="fa-solid fa-right-from-bracket"></i> Déconnexion
            </button>
          </form>
        </li>
      </ul>
    </div>
  @endif
</header>

<section class="hero">
  <div class="hero-content">
    <h1>Trouvez votre mercerie idéale</h1>
    <p>Découvrez les meilleures merceries de votre région et leurs produits uniques.</p>
    <div class="hero-buttons">
      @auth
        @if(auth()->user()->isMercerie())
          <a href="{{ route('merchant.supplies.index') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Ajouter une fourniture
          </a>
        @elseif(auth()->user()->isCouturier())
          <a href="{{ route('merceries.index') }}" class="btn btn-primary">
            Consulter des Merceries
          </a>
        @endif
      @else
        <a href="{{ route('login.form') }}" class="btn btn-primary">
          <i class="fas fa-rocket"></i> Démarrer
        </a>
      @endauth
    </div>
  </div>
</section>

<section>
  <div class="supplies-container">
    <!-- Titre principal -->
      <h2 class="section-title">Sélection des fournitures</h2>

    <!-- Barre de recherche -->
    <div class="search-container">
      <div class="search-wrapper">
        <div class="search-bar">
          <i class="fa fa-search"></i>
          <input type="text" id="search-live" placeholder="Rechercher une fourniture..." autocomplete="off" />
          <div id="search-loader" class="loader hidden"></div>
        </div>
      </div>
    </div>

    <!-- Formulaire -->
    <form id="compare-form" class="supplies-form" action="{{ route('merceries.compare') }}" method="POST">
      @csrf

      <div class="form-actions">
        <button type="submit" class="soft-btn submit-btn">
          <i class="fas fa-chart-bar"></i> Comparer les merceries
        </button>
        <button type="button" id="reset-supplies-values" class="reset-btn">
          <i class="fas fa-undo"></i> Réinitialiser les valeurs
        </button>
      </div>

      <!-- Optional: filter by city / quarter -->
      <div style="display:flex;gap:12px;align-items:center;margin:12px 0;flex-wrap:wrap;">
        <div>
          <label for="city_id">Ville (optionnel)</label>
          <select id="city_id" name="city_id">
                  <option value="">Toutes les villes</option>
                  @foreach(\App\Models\City::orderBy('name')->get() as $city)
                          <option value="{{ $city->id }}" @if(old('city_id') == $city->id) selected @endif>{{ $city->name }}</option>
                  @endforeach
          </select>
        </div>

        <div style="display:flex;align-items:center;gap:8px;">
          <div>
            <label for="quarter_id">Quartier (optionnel)</label>
            <select id="quarter_id" name="quarter_id" @if(!old('city_id')) disabled @endif>
                    <option value="">Tous les quartiers</option>
                    @if(old('city_id'))
                            @foreach(\App\Models\Quarter::where('city_id', old('city_id'))->orderBy('name')->get() as $q)
                                    <option value="{{ $q->id }}" @if(old('quarter_id') == $q->id) selected @endif>{{ $q->name }}</option>
                            @endforeach
                    @endif
            </select>
          </div>
          <div id="quarter-loader" class="loader hidden" style="width:18px;height:18px;margin-top:18px;margin-left:6px;"></div>
        </div>
      </div>

      <div class="supplies-list" id="supplies-list">
        @forelse($supplies as $supply)
          <div class="supply-card" data-id="{{ $supply->id }}">
            <div class="supply-image">
              <img src="{{ $supply->image_url ?? asset('images/default.png') }}" alt="{{ $supply->name }}">
            </div>

            <div class="supply-content">
              <h3>{{ $supply->name }}</h3>
              <p class="description">{{ $supply->description }}</p>

              <div class="price-qty">
                <div class="price">
                  @php
                    $isMeasure = false;
                    if (!empty($supply->sale_mode) && $supply->sale_mode === 'measure') {
                        $isMeasure = true;
                    }
                    if (! $isMeasure) {
                        $isMeasure = \App\Models\MerchantSupply::where('supply_id', $supply->id)->where('sale_mode', 'measure')->exists();
                    }
                    $unit = $supply->measure ?? 'm';
                  @endphp
                  <!-- <span class="amount">{{ number_format($supply->price, 0, ',', ' ') }} FCFA{{ $isMeasure ? ' / ' . $unit : '' }}</span> -->
                </div>
                <div class="quantity-group">
                  @if($isMeasure)
                    <label for="measure_{{ $supply->id }}">Mesure ({{ $unit }})</label>
                    <div class="quantity-controls">
                      <button type="button" class="quantity-btn minus" data-target="measure_{{ $supply->id }}" data-step="0.5">
                        <i class="fas fa-minus"></i>
                      </button>
                      <input type="text" 
                             name="items[{{ $supply->id }}][measure_requested]" 
                             id="measure_{{ $supply->id }}" 
                             value="0" 
                             class="quantity-input"
                             data-measure="true"
                             data-unit="{{ $unit }}"
                             placeholder="0{{ $unit }}" />
                      <button type="button" class="quantity-btn plus" data-target="measure_{{ $supply->id }}" data-step="0.5">
                        <i class="fas fa-plus"></i>
                      </button>
                    </div>
                  @else
                    <label for="quantity_{{ $supply->id }}">Quantité</label>
                    <div class="quantity-controls">
                      <button type="button" class="quantity-btn minus" data-target="quantity_{{ $supply->id }}">
                        <i class="fas fa-minus"></i>
                      </button>
                      <input type="number" 
                             name="items[{{ $supply->id }}][quantity]" 
                             id="quantity_{{ $supply->id }}" 
                             value="0" 
                             min="0" 
                             class="quantity-input" />
                      <button type="button" class="quantity-btn plus" data-target="quantity_{{ $supply->id }}">
                        <i class="fas fa-plus"></i>
                      </button>
                    </div>
                  @endif
                </div>
              </div>
            </div>
          </div>
        @empty
                <p class="empty-message">Aucune fourniture disponible pour le moment.</p>
        @endforelse
      </div>   
      <!-- Pagination for supplies -->
      @if(method_exists($supplies, 'links'))
        <div class="pagination-block">
          {{ $supplies->links('pagination::bootstrap-5') }}
        </div>
        @if($supplies->total() > 0)
          <div class="text-center">
            <span class="results-count">
              Affichage de {{ $supplies->firstItem() }} à {{ $supplies->lastItem() }} sur {{ $supplies->total() }} résultats
            </span>
          </div>
        @endif
      @endif
    </form>
    <!-- Loader de comparaison -->
    <div id="compare-loader" class="compare-loader hidden">
      <div class="loader-spinner"></div>
      <p>Comparaison en cours, veuillez patienter...</p>
    </div>
  </div>
</section>
 
<section>
  <h2 class="section-title">Liste des Merceries</h2>

  <!-- SEARCH (landing) -->
  <div class="search-container">
    <div class="search-wrapper">
      <div class="search-bar">
        <i class="fa fa-search"></i>
        <input id="search-merceries-landing" type="text" placeholder="Ville, Quartier..." autocomplete="off">
        <div id="merceries-loader-landing" class="loader hidden"></div>
      </div>
    </div>
  </div>

  <section class="merceries" id="merceries-container">
    @if(isset($merceries) && $merceries->isNotEmpty())
      @foreach($merceries as $m)
        <div class="card">
          <img src="{{ $m->avatar_url ? asset($m->avatar_url) : 'https://via.placeholder.com/600x300?text=Mercerie' }}" alt="{{ $m->name }}">
          <div class="card-content">
            <h3>{{ $m->name }}</h3>
            <div class="info">
              <div class="location">📍 {{ $m->city }}@if(isset($m->quarter) && $m->quarter) — {{ $m->quarter->name ?? $m->quarter }}@endif</div>
            </div>
            <a href="{{ route('merceries.show',$m->id) }}" class="btn">Voir plus</a>
          </div>
        </div>
      @endforeach
    @else
      <div class="empty-message">Aucune mercerie trouvée pour le moment.</div>
    @endif
  </section>
</section>

<footer>
  <p>© 2025 Prodmast — Tous droits réservés.</p>
</footer>

<script>
  // Expose small runtime configuration for external landing.js
  window.LANDING = {
    rootUrl: "{{ url('/') }}",
    routes: {
      suppliesSearch: "{{ route('api.supplies.search') }}",
      merceriesSearch: "{{ route('api.merceries.search') }}"
    },
    assetDefaultImage: "{{ asset('images/default.png') }}",
    csrfToken: "{{ csrf_token() }}"
  };
</script>
<script src="{{ asset('js/landing.js') }}"></script>

</body>
</html>