let selectedMeals = loadSelectedMeals();
const orderDetails = document.getElementById("orderDetails");
const orderTotal = document.getElementById("orderTotal");
const cartCountElement = document.getElementById("cartCount");

// لتسجيل الدخول
const userActions = document.getElementById("userActions");
const user = JSON.parse(localStorage.getItem("user"));

if (user && user.name) {
  userActions.innerHTML = `<span id="userActions">👋 مرحبًا، ${user.name}</span>`;
  if (previousOrdersLink) previousOrdersLink.style.display = "block";
} else {
  userActions.innerHTML = `
    <a href="/LUQMA/login/user.html" class="btn btn-outline-primary">
      <i class="fas fa-sign-in-alt me-1"></i> تسجيل الدخول
    </a>
  `;
  if (previousOrdersLink) previousOrdersLink.style.display = "none";
}

function logoutUser() {
  localStorage.removeItem("user");
  location.reload();
}

function updateCartCount() {
  const uniqueMeals = [...new Set(selectedMeals.map(meal => meal.name))];
  const totalUniqueItems = uniqueMeals.length;
  if (cartCountElement) cartCountElement.textContent = totalUniqueItems;
}

function saveSelectedMeals() {
  localStorage.setItem("selectedMeals", JSON.stringify(selectedMeals));
}

function loadSelectedMeals() {
  return JSON.parse(localStorage.getItem("selectedMeals")) || [];
}

function renderOrders() {
  orderDetails.innerHTML = "";
  let total = 0;

  if (selectedMeals.length === 0) {
    orderDetails.innerHTML = "<tr><td colspan='5'>لا توجد وجبات في الطلب.</td></tr>";
  } else {
    selectedMeals.forEach((meal, index) => {
      meal.quantity = meal.quantity || 1;
      const rawPrice = meal.originalPrice || meal.price || 0;
      const discountPrice = rawPrice - (meal.discount || 0);
      const discountPercentage = meal.discount && rawPrice > 0
        ? ((meal.discount / rawPrice) * 100).toFixed(0)
        : 0;

      const mealRow = document.createElement("tr");
      mealRow.innerHTML = `
        <td>${meal.name}</td>
        <td>
          <input type="number" value="${meal.quantity}" data-index="${index}" class="quantity-input" min="1" />
        </td>
        <td>
          ${meal.discount ? `
            <div style="text-decoration: line-through; margin-bottom: 5px;">
              ${rawPrice.toFixed(2)} د.أ
            </div>` : ''}
          <div style="font-size: 1.1em; font-weight: bold; color: green;">
            ${discountPrice.toFixed(2)} د.أ
          </div>
          ${meal.discount ? `
            <div style="color: red; font-size: 0.9em;">
              خصم ${discountPercentage}%
            </div>` : ''}
        </td>
        <td>
          <div style="font-size: 1.2em; font-weight: bold;">
            ${(discountPrice * meal.quantity).toFixed(2)} د.أ
          </div>
        </td>
        <td>
          <button class="btn btn-danger delete-btn" data-index="${index}">
            حذف
          </button>
        </td>
      `;
      orderDetails.appendChild(mealRow);
      total += discountPrice * meal.quantity;
    });
  }

  orderTotal.textContent = total.toFixed(2);
  updateCartCount();
}

window.onload = () => {
  if (!user || !user.id) {
    localStorage.setItem('redirectTo', window.location.href);
    showCustomAlert("يرجى تسجيل الدخول قبل تأكيد الطلب.");
  } else {
    const pendingOrders = JSON.parse(localStorage.getItem('pendingOrders'));

    if (pendingOrders && pendingOrders.length > 0) {
      pendingOrders.forEach(newMeal => {
        const existingMeal = selectedMeals.find(meal => meal.name === newMeal.name);
        if (existingMeal) {
          existingMeal.quantity = (existingMeal.quantity || 1) + (newMeal.quantity || 1);
        } else {
          selectedMeals.push(newMeal);
        }
      });
      saveSelectedMeals();
      renderOrders();
      localStorage.removeItem('pendingOrders');
    } else {
      selectedMeals = loadSelectedMeals();
      renderOrders();
    }
  }
};

orderDetails.addEventListener("change", (event) => {
  if (event.target.classList.contains("quantity-input")) {
    const index = event.target.dataset.index;
    const newQuantity = parseInt(event.target.value);
    if (newQuantity > 0) {
      selectedMeals[index].quantity = newQuantity;
      saveSelectedMeals();
      renderOrders();
    }
  }
});

orderDetails.addEventListener("click", (event) => {
  if (event.target.classList.contains("delete-btn")) {
    const index = event.target.dataset.index;
    selectedMeals.splice(index, 1);
    saveSelectedMeals();
    renderOrders();
  }
});

document.getElementById("confirmOrderBtn").addEventListener("click", () => {
  if (!user || !user.id) {
    localStorage.setItem('redirectTo', window.location.href);
    showCustomAlert("يرجى تسجيل الدخول قبل تأكيد الطلب.");
    return;
  }

  if (selectedMeals.length > 0) {
    const order = {
      user_id: user.id,
      total_price: parseFloat(orderTotal.textContent),
      status: 'pending',
      date: new Date().toLocaleString(),
    };

    const order_items = selectedMeals.map(meal => {
      const rawPrice = meal.originalPrice || meal.price || 0;
      const discount = meal.discount || 0;
      const priceAfterDiscount = rawPrice - discount;

      return {
        meal_id: parseInt(meal.id),
        quantity: meal.quantity,
        price_per_unit: rawPrice,
        discount: discount,
        price_after_discount: priceAfterDiscount
      };
    }).filter(item => item.meal_id && !isNaN(item.meal_id));

    const orderData = { order, order_items };

    fetch('save_order.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(orderData),
    })
    .then(async response => {
      const data = await response.json();
      if (data.status === 'success') {
        showCustomAlert('تم حفظ الطلب بنجاح!');
        const previousOrders = JSON.parse(localStorage.getItem("orders")) || [];
        previousOrders.push({
          id: Date.now(),
          date: order.date,
          meals: selectedMeals,
          total_price: order.total_price,
          status: 'تم التأكيد'
        });
        localStorage.setItem("orders", JSON.stringify(previousOrders));

        selectedMeals = [];
        saveSelectedMeals();
        renderOrders();
      } else {
        showCustomAlert('فشل حفظ الطلب: ' + data.message);
      }
    })
    .catch(error => {
      showCustomAlert('حدث خطأ أثناء إرسال الطلب.');
    });
  } else {
    showCustomAlert("لا توجد طلبات لتأكيدها!");
  }
});

document.getElementById("clearOrdersBtn").addEventListener("click", () => {
  selectedMeals = [];
  saveSelectedMeals();
  renderOrders();
});

document.getElementById("addMoreBtn").addEventListener("click", () => {
  saveSelectedMeals();
  localStorage.setItem("pendingOrders", JSON.stringify(selectedMeals));

  const previousOrders = JSON.parse(localStorage.getItem("orders")) || [];
  const currentOrder = {
    id: Date.now(),
    date: new Date().toLocaleString(),
    meals: selectedMeals
  };
  previousOrders.push(currentOrder);
  localStorage.setItem("orders", JSON.stringify(previousOrders));

  window.location.href = "../chefAndMeal/chefs.php";
});

function showCustomAlert(message) {
  const alertBox = document.getElementById('customAlert');
  const messageBox = document.getElementById('customAlertMessage');
  messageBox.textContent = message;
  alertBox.classList.remove('d-none');
}

document.addEventListener("DOMContentLoaded", () => {
  const alertOkBtn = document.getElementById("alertOkBtn");
  if (alertOkBtn) {
    alertOkBtn.addEventListener("click", () => {
      hideCustomAlert();
      if (pendingRedirect) {
        pendingRedirect = false;
        window.location.href = "/login/user.html";
      }
    });
  } else {
    console.error("العنصر alertOkBtn غير موجود في الـ DOM");
  }
});

function hideCustomAlert() {
  const alertBox = document.getElementById('customAlert');
  alertBox.classList.add('d-none');
}
