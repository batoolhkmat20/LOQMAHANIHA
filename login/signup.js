<<<<<<< HEAD
// Toggle between register and login


document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('container');
    const registerBtn = document.getElementById('register');
    const loginBtn = document.getElementById('login');

    registerBtn?.addEventListener('click', () => {
        container.classList.add("active");
    });

    loginBtn?.addEventListener('click', () => {
        container.classList.remove("active");
    });
});

// Wait for the page to load
// Show/hide password - Signup
function togglePasswordVisibility() {
    const passwordInput = document.getElementById("signup-password");
    const toggleIcon = document.getElementById("toggleIcon");

    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        toggleIcon.classList.remove("fa-eye-slash");
        toggleIcon.classList.add("fa-eye");
    } else {
        passwordInput.type = "password";
        toggleIcon.classList.remove("fa-eye");
        toggleIcon.classList.add("fa-eye-slash");
    }
}

// Show/hide password - Signin
function toggleSigninPasswordVisibility() {
    const passwordInput = document.getElementById("signin-password");
    const toggleIcon = document.getElementById("toggleSigninIcon");

    if (passwordInput.type === "password") {
        passwordInput.type = "text";      
        toggleIcon.classList.remove("fa-eye-slash");
        toggleIcon.classList.add("fa-eye");

    } else {
        passwordInput.type = "password";
        toggleIcon.classList.remove("fa-eye");
        toggleIcon.classList.add("fa-eye-slash");
}
}
// reset password
function showResetModal() {
    document.getElementById("resetPasswordModal").style.display = "flex";
}

function closeResetModal() {
    document.getElementById("resetPasswordModal").style.display = "none";
    document.getElementById("resetMessage").innerText = "";
    document.getElementById("resetEmail").value = "";
}

function sendResetEmail() {
    const email = document.getElementById("resetEmail").value;
    const message = document.getElementById("resetMessage");

    if (!email) {
        message.innerText = "الرجاء إدخال بريدك الإلكتروني.";
        return;
    }

    // هنا المفترض يكون في اتصال مع السيرفر
    // مثل: fetch('/reset-password', {method: 'POST', body: JSON.stringify({email})})

    // مؤقتاً نعرض رسالة تأكيد فقط
    message.innerText = "تم إرسال رابط إعادة التعيين إلى بريدك الإلكتروني إذا كان مسجلاً لدينا.";
}


document.getElementById('signup-phone').addEventListener('input', function () {
    this.value = this.value.replace(/\D/g, '').slice(0, 10);
});

// التحقق عند محاولة إرسال الفورم
document.getElementById('signup-form').addEventListener('submit', function(event) {
    const phoneInput = document.getElementById('signup-phone');
    const phoneError = document.getElementById('phone-error');

    if (phoneInput.value.length !== 10) {
        event.preventDefault(); // منع إرسال الفورم
        phoneError.style.display = 'block'; // عرض رسالة الخطأ
    } else {
        phoneError.style.display = 'none'; // إخفاء الرسالة إذا صحيح
    }
});
=======
const container = document.getElementById('container');
const registerBtn = document.getElementById('register');
const loginBtn = document.getElementById('login');

registerBtn.addEventListener('click', () => {
    container.classList.add("active");
});

loginBtn.addEventListener('click', () => {
    container.classList.remove("active");
});


// fsefefsfs
// JS
>>>>>>> 026c1d8a75c7064739d73a2728452725095720b9
