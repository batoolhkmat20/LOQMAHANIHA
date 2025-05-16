
document.getElementById('filterBtn').addEventListener('click', function () {
  const selectedLocation = document.getElementById('locationFilter').value;
  const chefCards = document.querySelectorAll('.menu-item'); // <-- تم التعديل هنا

  chefCards.forEach(card => {
    const chefLocation = card.querySelector('[data-location]')?.getAttribute('data-location');

    if (selectedLocation === 'all' || chefLocation === selectedLocation) {
      card.style.display = 'block';
    } else {
      card.style.display = 'none';
    }
  });
});
