document.addEventListener('DOMContentLoaded', function() {
  const sections = document.querySelectorAll('.section');
  const navItems = document.querySelectorAll('.sidebar li');

  // Function to switch sections
  function switchSection(sectionId) {
    // Remove active class from all sections
    sections.forEach(section => {
      section.classList.remove('active');
    });

    // Add active class to the selected section
    const targetSection = document.getElementById(sectionId);
    if (targetSection) {
      targetSection.classList.add('active');
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Update sidebar active class
    navItems.forEach(item => {
      item.classList.remove('active');
      if (item.getAttribute('data-section') === sectionId) {
        item.classList.add('active');
      }
    });
  }

  // Sidebar click events
  navItems.forEach(item => {
    item.addEventListener('click', function() {
      const sectionId = this.getAttribute('data-section');
      switchSection(sectionId);
    });
  });
});