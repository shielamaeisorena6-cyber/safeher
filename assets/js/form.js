/**
 * SafeHer - Form Validation and Interactive Utilities
 */

document.addEventListener('DOMContentLoaded', function () {
  // 1. Role Change Handler (Toggle Barangay Dropdown in Admin User Creation)
  const roleSelect = document.getElementById('userRoleSelect');
  const barangayGroup = document.getElementById('barangayFieldGroup');
  if (roleSelect && barangayGroup) {
    function updateBarangayVisibility() {
      if (roleSelect.value === 'barangay_staff') {
        barangayGroup.style.display = 'block';
        const select = barangayGroup.querySelector('select');
        if (select) select.required = true;
      } else {
        barangayGroup.style.display = 'none';
        const select = barangayGroup.querySelector('select');
        if (select) {
          select.required = false;
          select.value = '';
        }
      }
    }
    roleSelect.addEventListener('change', updateBarangayVisibility);
    updateBarangayVisibility();
  }

  // 2. SMS Character Counter
  const smsTextarea = document.querySelector('textarea[name="message"]');
  const smsCounter = document.getElementById('smsCharCount');
  if (smsTextarea && smsCounter) {
    function updateSmsCount() {
      const len = smsTextarea.value.length;
      const parts = Math.ceil(len / 160) || 1;
      smsCounter.textContent = `${len} characters (${parts} SMS segment${parts > 1 ? 's' : ''})`;
    }
    smsTextarea.addEventListener('input', updateSmsCount);
    updateSmsCount();
  }

  // 3. Phone Number formatting/validation (Philippine standard: 09XXXXXXXXX or +639XXXXXXXXX)
  const phoneInputs = document.querySelectorAll('input[type="tel"], input[name="phone"]');
  phoneInputs.forEach(input => {
    input.addEventListener('blur', function () {
      let val = this.value.trim().replace(/[\s-]/g, '');
      if (val && !/^(09|\+639)\d{9}$/.test(val)) {
        this.setCustomValidity('Please enter a valid Philippine mobile number (e.g. 09171234567 or +639171234567)');
      } else {
        this.setCustomValidity('');
      }
    });
  });

  // 4. Password confirmation checking
  const registerForm = document.querySelector('form[action*="register"]');
  if (registerForm) {
    registerForm.addEventListener('submit', function (e) {
      const pass = registerForm.querySelector('input[name="password"]');
      const confirmPass = registerForm.querySelector('input[name="confirm_password"]');
      if (pass && confirmPass && pass.value !== confirmPass.value) {
        e.preventDefault();
        alert('Passwords do not match. Please verify and try again.');
        confirmPass.focus();
      }
    });
  }
});
