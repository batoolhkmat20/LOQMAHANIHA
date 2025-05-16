previous_orders.html--> -----------------------------------------------------------------------------------------------------previous_orders.css--> /* تعريف المتغيرات */
:root {
    --default-font: "Segoe UI", sans-serif;
    --heading-font: "Amatic SC", sans-serif;
    --nav-font: "Inter", sans-serif;
    --background-color: #ffffff;
    --default-color: #212529;
    --heading-color: #37373f;
    --accent-color: #ce1212;
    --contrast-color: #ffffff;
    --light-gray: #ecf0f1;
    --mid-gray: #bdc3c7;
    --hover-accent: #c0392b;
    --secondary-color: #2980b9;
    --success-color: #27ae60;
}

/* إعدادات الخطوط واللون الأساسي */
body {
    font-family: var(--default-font);
    background-color: var(--light-gray);
    color: var(--default-color);
}

/* تخصيص الهيدر */
.header {
    background-color: var(--secondary-color);
    color: var(--contrast-color);
}

.sitename {
    font-size: 1.5rem;
    font-weight: bold;
    color: var(--contrast-color);
}

/* تخصيص شريط التنقل */
.navbar-nav .nav-link {
    color: var(--contrast-color);
}

.navbar-nav .nav-link:hover {
    color: var(--accent-color);
}

/* تخصيص عرض معلومات الطلب */
.order-card {
    background-color: var(--contrast-color);
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    transition: box-shadow 0.3s ease-in-out;
    border-left: 6px solid var(--accent-color);
}

.order-card:hover {
    box-shadow: 0 0 12px rgba(206, 18, 18, 0.3);
}

.order-card h5 {
    color: var(--accent-color);
    font-weight: bold;
}

.order-card p {
    margin: 6px 0;
    color: var(--default-color);
    font-size: 1rem;
}

.reorder-btn {
    background-color: var(--accent-color);
    color: var(--contrast-color);
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    font-weight: bold;
    transition: background-color 0.3s;
}

.reorder-btn:hover {
    background-color: var(--hover-accent);
}

/* تخصيص عرض تفاصيل الوجبة */
.card-body {
    padding: 15px;
}

.meal-details {
    display: none;
    padding-left: 15px;
    padding-top: 10px;
}

.card-body .expand-btn {
    background-color: transparent;
    border: none;
    color: var(--accent-color);
    cursor: pointer;
}

.card-body .expand-btn:hover {
    text-decoration: underline;
}

.meal-expanded .meal-details {
    display: block;
}

/* تخصيص عرض تفاصيل الوجبة داخل البطاقة */
.meal-details-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.meal-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #ddd;
    padding: 15px 0;
}

.meal-info {
    flex: 1;
    padding-right: 15px;
}

.meal-info p {
    margin: 5px 0;
    font-size: 14px;
    color: #333;
}

.meal-image img {
    max-width: 100px;
    max-height: 100px;
    border-radius: 8px;
    object-fit: cover;
}

.meal-item:last-child {
    border-bottom: none;
}

/* إضافة تأثير hover */
.meal-item:hover {
    background-color: #f7f7f7;
}

.card-footer {
    background-color: #f1f1f1;
    padding: 5px;
    text-align: center;
}

/* تخصيص العرض لطلبات مختلفة في الشاشات الكبيرة والصغيرة */
@media (min-width: 768px) {
    .col-md-4 {
        flex: 0 0 33.33%;
        max-width: 33.33%;
    }
}

@media (max-width: 767px) {
    .col-md-4 {
        flex: 0 0 100%;
        max-width: 100%;
    }
}

------------------------ previous_orders.js--> document.addEventListener("DOMContentLoaded", function () {
    const statusFilter = document.getElementById("statusFilter");
    const dateFilter = document.getElementById("dateFilter");

    function fetchOrders() {
        fetch('get_previous_orders.php')
            .then(response => response.text())  // جلب الاستجابة كـ نص
            .then(data => {
                console.log("Raw data:", data);  // طباعة البيانات الخام للتحقق

                try {
                    const jsonData = JSON.parse(data);  // محاولة تحويل النص إلى JSON
                    if (jsonData.status === "success") {
                        const ordersContainer = document.getElementById("ordersContainer");
                        let orders = jsonData.orders;

                        if (!orders || orders.length === 0) {
                            ordersContainer.innerHTML = '<p>لا توجد طلبات مطابقة.</p>';
                            return;
                        }

                        function formatDate(dateString) {
                            const date = new Date(dateString);
                            const month = String(date.getMonth() + 1).padStart(2, '0');
                            const day = String(date.getDate()).padStart(2, '0');
                            const year = date.getFullYear();
                            return ${month}/${day}/${year};
                        }

                        function displayOrders(ordersToDisplay) {
                            ordersContainer.innerHTML = '';

                            if (ordersToDisplay.length === 0) {
                                ordersContainer.innerHTML = '<p>لا توجد طلبات مطابقة.</p>';
                            } else {
                                ordersToDisplay.forEach(order => {
                                    const orderDiv = document.createElement('div');
                                    orderDiv.classList.add('col-12', 'card', 'mb-3');
                                    orderDiv.innerHTML = 
                                        <div class="card-header">
                                            <strong>طلب رقم ${order.order_id}</strong>
                                        </div>
                                        <div class="card-body">
                                            <p><strong>إجمالي السعر:</strong> ${order.total_price ? order.total_price + ' د.أ' : 'غير محدد'}</p>
                                            <p><strong>تاريخ الطلب:</strong> ${formatDate(order.order_date)}</p>
                                            <p><strong>الحالة:</strong> ${order.status}</p>
                                            <button class="btn btn-primary reorder-btn" data-order-id="${order.order_id}">إعادة الطلب</button>
                                            <button class="btn btn-secondary expand-btn" onclick="toggleDetails(event)">عرض التفاصيل</button>
                                            <div class="meal-details" style="display: none;">
                                                <ul class="list-group mt-3">
                                                    ${order.meals && order.meals.length > 0 ? order.meals.map(meal => {
                                                        return 
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
                                                                            return 
                                                                                <span style="text-decoration: line-through; color: red;">${originalTotal.toFixed(2)} د.أ</span>
                                                                                <span style="color: green; font-weight: bold; margin-right: 8px;">${discountedTotal.toFixed(2)} د.أ</span>
                                                                                <span style="background-color: #ffc107; color: #000; padding: 2px 6px; border-radius: 5px; font-size: 0.9em;">
                                                                                    خصم ${discountPercent}% 
                                                                                </span>
                                                                            ;
                                                                        } else {
                                                                            return <span style="color: green; font-weight: bold;">${discountedTotal.toFixed(2)} د.أ</span>;
                                                                        }
                                                                    })() : 'غير محدد'}</p>
                                                                </div>
                                                                <div>
                                                                    <img src="${meal.meal_image || ''}" alt="${meal.meal_name}" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;">
                                                                </div>
                                                            </div>
                                                        </li>
                                                        ;
                                                    }).join('') : '<li class="list-group-item">لا توجد وجبات.</li>'}
                                                </ul>
                                            </div>
                                        </div>
                                    ;
                                    ordersContainer.appendChild(orderDiv);
                                });

                                // ✅ بعد إضافة العناصر إلى DOM، ربط أحداث "إعادة الطلب"
                                document.querySelectorAll(".reorder-btn").forEach(button => {
                                    button.addEventListener("click", function (e) {
                                        const orderId = e.target.getAttribute("data-order-id");
                                        const order = orders.find(order => order.order_id == orderId);
                                        if (order && order.meals) {
                                            localStorage.setItem("reorderedMeals", JSON.stringify(order.meals));
                                            localStorage.setItem("totalPrice", order.total_price);
                                            window.location.href = "order.html";
                                        }
                                    });
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
                } catch (error) {
                    console.error("خطأ في تحويل البيانات:", error);
                }
            })
            .catch(error => {
                console.error("خطأ في الاتصال:", error);
            });
    }

    fetchOrders();
});

function toggleDetails(event) {
    const button = event.target;
    const mealDetails = button.closest('.card-body').querySelector('.meal-details');
    mealDetails.style.display = (mealDetails.style.display === 'none' || mealDetails.style.display === '') ? 'block' : 'none';
}----------------- get_previous_orders.php--> <?php
session_start();

// تأكد من أن المستخدم قد قام بتسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "لم تقم بتسجيل الدخول."]);
    exit();
}

$user_id = $_SESSION['user_id']; // معرف المستخدم من الجلسة

$host = 'localhost';
$db   = 'luqma';
$user = 'root';
$pass = '';

header('Content-Type: application/json');

try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // تعديل الاستعلام ليشمل فقط طلبات المستخدم الحالي
    $stmt = $conn->prepare("
    SELECT 
        o.id as order_id, 
        DATE_FORMAT(o.order_date, '%Y-%m-%d %H:%i:%s') as order_date,
        o.status, 
        o.total_price, 
        m.name as meal_name, 
        m.image as meal_image, 
        om.quantity, 
        om.price_per_unit as original_price,  
        om.price_after_discount, 
        c.name as chef_name 
    FROM orders o
    JOIN order_meals om ON o.id = om.order_id
    JOIN meals m ON om.meal_id = m.id
    JOIN chefs c ON m.chef_id = c.id
    WHERE o.user_id = :user_id  -- إضافة شرط للمستخدم
    ORDER BY o.order_date DESC
");

    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);  // ربط قيمة المستخدم بالاستعلام
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($orders)) {
        echo json_encode(["status" => "error", "message" => "لا توجد بيانات"]);
        exit();
    }

    $result = [];
    foreach ($orders as $order) {
        if (!isset($result[$order['order_id']])) {
            $result[$order['order_id']] = [
                'order_id' => $order['order_id'],
                'order_date' => $order['order_date'],
                'status' => $order['status'],
                'total_price' => $order['total_price'],
                'meals' => []
            ];
        }

        $result[$order['order_id']]['meals'][] = [
            'meal_name' => $order['meal_name'],
            'meal_image' => $order['meal_image'],
            'quantity' => $order['quantity'],
            'price' => $order['original_price'],  
            'price_after_discount' => $order['price_after_discount'],
            'chef_name' => $order['chef_name']
        ];
    }

    echo json_encode(["status" => "success", "orders" => array_values($result)]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "خطأ في قاعدة البيانات: " . $e->getMessage()]);
}

?>