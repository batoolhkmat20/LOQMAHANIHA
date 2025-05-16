// Section switching
function showSection(sectionId) {
  const sections = document.querySelectorAll('.section');
  sections.forEach(sec => sec.classList.remove('active'));

  const target = document.getElementById(sectionId);
  if (target) target.classList.add('active');
}

// Handle button clicks once DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
  attachCustomerActionListeners();
  attachChiefActionListeners();
});

// Customer table buttons
function attachCustomerActionListeners() {
  const editButtons = document.querySelectorAll('.edit-btn');
  const deleteButtons = document.querySelectorAll('.delete-btn');

  editButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      alert('Edit customer (functionality coming soon)');
    });
  });

  deleteButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      const confirmed = confirm('Are you sure you want to delete this customer?');
      if (confirmed) {
        alert('Customer deleted (front-end only)');
      }
    });
  });
}

// Chief table buttons
function attachChiefActionListeners() {
  const editButtons = document.querySelectorAll('.chief-edit-btn');
  const deleteButtons = document.querySelectorAll('.chief-delete-btn');

  editButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      alert('Edit chief (functionality coming soon)');
    });
  });

  deleteButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      const confirmed = confirm('Are you sure you want to delete this chief?');
      if (confirmed) {
        alert('Chief deleted (front-end only)');
      }
    });
  });
}
