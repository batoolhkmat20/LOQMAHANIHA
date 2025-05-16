document.addEventListener("DOMContentLoaded", function () {
    const statusFilter = document.getElementById("statusFilter");
    const dateFilter = document.getElementById("dateFilter");

    function fetchOrders() {
        fetch('get_previous_orders.php')
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    const ordersContainer = document.getElementById("ordersContainer");
                    let orders = data.orders;

                    if (!orders || orders.length === 0) {
                        ordersContainer.innerHTML = '<p>لا توجد طلبات مطابقة.</p>';
                        return;
                    }

                    function formatDate(dateString) {
                        const date = new Date(dateString);
                        const month = String(date.getMonth() + 1).padStart(2, '0');
                        const day = String(date.getDate()).padStart(2, '0');
                        const year = date.getFullYear();
                        return `${month}/${day}/${year}`;
                    }

                    function displayOrders(ordersToDisplay) {
                        ordersContainer.innerHTML = '';

                        if (ordersToDisplay.length === 0) {
                            ordersContainer.innerHTML = '<p>لا توجد طلبات مطابقة.</p>';
                        } else {
                            ordersToDisplay.forEach(order => {
                                const orderDiv = document.createElement('div');
                                orderDiv.classList.add('col-12', 'card', 'mb-3');
                                orderDiv.innerHTML = `
                                    <div class="card-header">
                                        <strong>طلب رقم ${order.order_id}</strong>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>إجمالي السعر:</strong> ${order.total_price ? order.total_price + ' د.أ' : 'غير محدد'}</p>
                                        <p><strong>تاريخ الطلب:</strong> ${formatDate(order.order_date)}</p>
                                        <p><strong>الحالة:</strong> ${order.status}</p>
                                        <button class="btn btn-secondary expand-btn" onclick="toggleDetails(event)">عرض التفاصيل</button>
                                        <div class="meal-details" style="display: none;">
                                            <ul class="list-group mt-3">
                                                ${order.meals && order.meals.length > 0 ? order.meals.map(meal => {
                                                    return `
                                                    <li class="list-group-item">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <p><strong>الوجبة:</strong> ${meal.meal_name || 'غير محدد'}</p>
                                                                <p><strong>الشيف:</strong> ${meal.chef_name || 'غير محدد'}</p>
                                                                <p><strong>الكمية:</strong> ${meal.quantity || 'غير محددة'}</p>
                                                                <p><strong>السعر:</strong> 
${meal.price && meal.price_after_discount ? (() => {
    const quantity = parseInt(meal.quantity);
    const originalTotal = parseFloat(meal.price) * quantity;
    const discountedTotal = parseFloat(meal.price_after_discount);
    const discountPercent = Math.round((1 - (discountedTotal / originalTotal)) * 100);

    if (discountPercent > 0) {
        return `
            <span style="text-decoration: line-through; color: red;">${originalTotal.toFixed(2)} د.أ</span>
            <span style="color: green; font-weight: bold; margin-right: 8px;">${discountedTotal.toFixed(2)} د.أ</span>
            <span style="background-color: #ffc107; color: #000; padding: 2px 6px; border-radius: 5px; font-size: 0.9em;">
                خصم ${discountPercent}% 
            </span>
        `;
    } else {
        return `<span style="color: green; font-weight: bold;">${discountedTotal.toFixed(2)} د.أ</span>`;
    }
})() : 'غير محدد'}</p>
                                                            </div>
                                                            <div>
                                                                <img src="${meal.meal_image || ''}" alt="${meal.meal_name}" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;">
                                                            </div>
                                                        </div>
                                                        ${order.status === 'completed' ? `
                                                            <div class="mt-3">
                                                                <p><strong>قيّم الشيف:</strong></p>
                                                                <div class="star-rating" data-type="chef" data-order-id="${order.order_id}" data-meal-name="${meal.meal_name}">
                                                                    ${[1, 2, 3, 4, 5].map(i => `<i class="fas fa-star" data-value="${i}" style="cursor:pointer;"></i>`).join('')}
                                                                </div>
                                                                <p class="mt-2"><strong>قيّم الوجبة:</strong></p>
                                                                <div class="star-rating" data-type="meal" data-order-id="${order.order_id}" data-meal-name="${meal.meal_name}">
                                                                    ${[1, 2, 3, 4, 5].map(i => `<i class="fas fa-star" data-value="${i}" style="cursor:pointer;"></i>`).join('')}
                                                                </div>
                                                            </div>
                                                        ` : ''}
                                                    </li>
                                                    `;
                                                }).join('') : '<li class="list-group-item">لا توجد وجبات.</li>'}
                                            </ul>
                                        </div>
                                    </div>
                                `;
                                ordersContainer.appendChild(orderDiv);
                            });
                        }
                    }

                    function filterOrders() {
                        const selectedDate = dateFilter.value;
                        const selectedStatus = statusFilter.value;

                        const filteredOrders = orders.filter(order => {
                            let matchesDate = true;
                            let matchesStatus = true;

                            if (selectedDate) {
                                const orderDateOnly = order.order_date.split(' ')[0];
                                matchesDate = orderDateOnly === selectedDate;
                            }

                            if (selectedStatus !== 'all') {
                                matchesStatus = order.status === selectedStatus;
                            }

                            return matchesDate && matchesStatus;
                        });

                        displayOrders(filteredOrders);
                    }

                    statusFilter.addEventListener('change', filterOrders);
                    dateFilter.addEventListener('change', filterOrders);

                    filterOrders();
                } else {
                    console.log("حدث خطأ في استرجاع البيانات.");
                }
            })
            .catch(error => {
                console.error("خطأ في الاتصال:", error);
            });
    }

    fetchOrders();
});

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll('.star-rating i').forEach(star => {
        star.addEventListener('click', function () {
            const orderId = this.closest('.star-rating').getAttribute('data-order-id');
            const mealName = this.closest('.star-rating').getAttribute('data-meal-name');
            const type = this.closest('.star-rating').getAttribute('data-type');
            const ratingValue = this.getAttribute('data-value');

            // إرسال التقييم إلى السيرفر
            fetch('save_rating.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `order_id=${orderId}&meal_name=${mealName}&type=${type}&rating=${ratingValue}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(`تم إرسال التقييم لل${type} بنجاح!`);
                } else {
                    alert(`حدث خطأ أثناء إرسال التقييم.`);
                }
            })
            .catch(error => {
                console.error("خطأ في الاتصال:", error);
            });
        });
    });
});

function toggleDetails(event) {
    const button = event.target;
    const mealDetails = button.closest('.card-body').querySelector('.meal-details');
    mealDetails.style.display = (mealDetails.style.display === 'none' || mealDetails.style.display === '') ? 'block' : 'none';
}
