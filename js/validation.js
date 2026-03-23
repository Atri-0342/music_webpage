// ---------- JS Validation & Dynamic Counters ----------
document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById('registrationForm');
    const password = document.getElementById('password');
    const passwordCount = document.getElementById('passwordCount');
    const phone = document.getElementById('phone');
    const phoneCount = document.getElementById('phoneCount');
    const maxPhone = 15;

    // Password length counter
    password.addEventListener('input', () => {
        passwordCount.textContent = password.value.length;
    });

    // Phone remaining counter
    phone.addEventListener('input', () => {
        const remaining = maxPhone - phone.value.length;
        phoneCount.textContent = remaining >= 0 ? remaining : 0;
    });

    // Form validation
    form.addEventListener('submit', function(e) {
        let errors = [];
        const nameVal = form.name.value.trim();
        const emailVal = form.email.value.trim();
        const passwordVal = password.value;
        const dobVal = form.dob.value;
        const cityVal = form.city.value.trim();
        const phoneVal = phone.value.trim();

        if (nameVal.length < 3) errors.push("Name must be at least 3 characters.");
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(emailVal)) errors.push("Invalid email format.");
        if (passwordVal.length < 6) errors.push("Password must be at least 6 characters.");
        if (!dobVal) errors.push("Please select your date of birth.");
        if (cityVal.length < 2) errors.push("City name is too short.");
        const phoneRegex = /^[0-9]{1,15}$/;
        if (!phoneRegex.test(phoneVal)) errors.push("Phone number must be up to 15 digits and only numbers.");

        if (errors.length > 0) {
            e.preventDefault();
            alert(errors.join("\n"));
        }
    });
});