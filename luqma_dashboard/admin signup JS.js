const container = document.getElementById("container");
const registerBtn = document.getElementById("register");
const loginBtn = document.getElementById("login");

registerBtn.addEventListener("click", () => {
  container.classList.add("active");
});

loginBtn.addEventListener("click", () => {
  container.classList.remove("active");
});

// ✅ ربط زر تسجيل الدخول بالتحقق
document.getElementById("loginForm").addEventListener("submit", function (e) {
  e.preventDefault(); // ❌ امنع إعادة تحميل الصفحة

  const username = document.getElementById("username").value.trim();
  const password = document.getElementById("password").value.trim();

  if (username === "admin@admin.com" && password === "admin123") {
    // ✅ التوجيه فقط في حال البيانات صحيحة
    window.location.href = "dashboard.html";
  } else {
    // ❌ إذا كانت خاطئة، اعرض رسالة تنبيه فقط
    alert("اسم المستخدم أو كلمة المرور غير صحيحة");
  }
});
