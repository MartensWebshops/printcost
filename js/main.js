document.addEventListener('DOMContentLoaded', function() {
  const hamburger = document.querySelector('.hamburger-icon');
  const dropdown = document.querySelector('.dropdown-content');

  hamburger.addEventListener('click', function() {
      dropdown.classList.toggle('show');
  });

  document.addEventListener('click', function(event) {
      if (!hamburger.contains(event.target) && !dropdown.contains(event.target)) {
          dropdown.classList.remove('show');
      }
  });
});
