(function () {
  console.log('app.js chargé'); // Debug log to confirm app.js is loaded

  /* ========= Preloader ======== */
  const preloader = document.getElementById('preloader');

  window.addEventListener('load', function () {
    if (preloader) {
      preloader.style.display = 'none';
    }
  });

  /* ========= Add Box Shadow in Header on Scroll ======== */
  window.addEventListener('scroll', function () {
    const header = document.querySelector('.header');
    if (header) {
      if (window.scrollY > 0) {
        header.style.boxShadow = '0px 0px 30px 0px rgba(200, 208, 216, 0.30)';
      } else {
        header.style.boxShadow = 'none';
      }
    }
  });

  /* ========= sidebar toggle ======== */
  const sidebarNavWrapper = document.querySelector(".sidebar-nav-wrapper");
  const mainWrapper = document.querySelector(".main-wrapper");
  const menuToggleButton = document.querySelector("#menu-toggle");
  const menuToggleButtonIcon = document.querySelector("#menu-toggle i");
  const overlay = document.querySelector(".overlay");

  if (menuToggleButton && sidebarNavWrapper && mainWrapper && overlay) {
    menuToggleButton.addEventListener("click", () => {
      console.log('Bouton menu-toggle cliqué'); // Debug log to confirm button click
      sidebarNavWrapper.classList.toggle("active");
      mainWrapper.classList.toggle("active");
      overlay.classList.toggle("active");

      // Toggle icon
      if (menuToggleButtonIcon) {
        if (sidebarNavWrapper.classList.contains("active")) {
          menuToggleButtonIcon.classList.remove("fa-chevron-left");
          menuToggleButtonIcon.classList.add("fa-bars");
        } else {
          menuToggleButtonIcon.classList.remove("fa-bars");
          menuToggleButtonIcon.classList.add("fa-chevron-left");
        }
      }
    });

    overlay.addEventListener("click", () => {
      sidebarNavWrapper.classList.remove("active");
      mainWrapper.classList.remove("active");
      overlay.classList.remove("active");

      // Reset icon to hamburger
      if (menuToggleButtonIcon) {
        menuToggleButtonIcon.classList.add("fa-chevron-left");
        menuToggleButtonIcon.classList.remove("fa-bars");
      }
    });
  }
})();