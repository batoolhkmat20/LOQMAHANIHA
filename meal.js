
document.getElementById('priceFilter').addEventListener('change', function() {
    const selectedPrice = this.value;
    const mealCards = document.querySelectorAll('.meal-card');  // تحديد جميع الوجبات
    
    mealCards.forEach(function(mealCard) {
      const mealPrice = parseFloat(mealCard.getAttribute('data-price'));  // الحصول على السعر من data-price

      // إظهار أو إخفاء الوجبة بناءً على الفلتر
      if (selectedPrice === 'all' || mealPrice <= parseFloat(selectedPrice)) {
        mealCard.style.display = 'block';  // إظهار الوجبة
      } else {
        mealCard.style.display = 'none';  // إخفاء الوجبة
      }
    });
  });

//total
  document.addEventListener('DOMContentLoaded', function () {
    const checkboxes = document.querySelectorAll('.meal-checkbox');
    const totalPriceEl = document.getElementById('totalPrice');
    let total = 0;

    checkboxes.forEach(checkbox => {
      checkbox.addEventListener('change', function () {
        const price = parseFloat(this.getAttribute('data-price'));
        if (this.checked) {
          total += price;
        } else {
          total -= price;
        }
        totalPriceEl.textContent = total.toFixed(2);
      });
    });
  });

//discoount

  document.addEventListener('DOMContentLoaded', function () {
    const mealCards = document.querySelectorAll('.meal-card');
    const totalPriceEl = document.getElementById('totalPrice');
    let total = 0;

    mealCards.forEach(card => {
      const checkbox = card.querySelector('.meal-checkbox');
      const oldPriceEl = card.querySelector('.old-price');
      const newPriceEl = card.querySelector('.new-price');
      const discountLabel = card.querySelector('.discount-label');

      const rawPrice = parseFloat(card.getAttribute('data-price'));
      const discount = "0.25";//افتراضي
      // parseFloat(card.getAttribute('data-discount')) || 0;//عشان اجيب القيمه 

      const hasDiscount = discount > 0;
      const finalPrice = hasDiscount ? rawPrice - (rawPrice * discount) : rawPrice;

      // ✅ تحديث السعر بناءً على الخصم
      if (hasDiscount) {
        oldPriceEl.textContent = rawPrice.toFixed(2) + ' د.أ';
        oldPriceEl.classList.remove('d-none');

        discountLabel.textContent = `خصم ${(discount * 100).toFixed(0)}٪`;
        discountLabel.classList.remove('d-none');
      }

      newPriceEl.textContent = finalPrice.toFixed(2) + ' د.أ';

      // ✅ التعامل مع السلة
      checkbox.addEventListener('change', function () {
        if (this.checked) {
          total += finalPrice;
        } else {
          total -= finalPrice;
        }
        totalPriceEl.textContent = total.toFixed(2);
      });
    });
  });

