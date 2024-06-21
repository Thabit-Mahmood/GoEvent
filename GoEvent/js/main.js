document.getElementById('show_password').addEventListener('change', function () {
  const password = document.getElementById('password');
  const confirmPassword = document.getElementById('confirm_password');
  const type = this.checked ? 'text' : 'password';
  password.type = type;
  confirmPassword.type = type;
});

document.getElementById('registerForm').addEventListener('submit', function (event) {
  let valid = true;

  // Custom email validation
  const email = document.getElementById('email');
  const emailError = document.getElementById('email-error');
  if (!email.value) {
    emailError.textContent = 'Please fill out this field';
    emailError.classList.add('active');
    email.classList.add('input-error');
    valid = false;
  } else if (!email.value.includes('@')) {
    emailError.textContent = 'Please include an "@" in the email address';
    emailError.classList.add('active');
    email.classList.add('input-error');
    valid = false;
  } else {
    emailError.textContent = '';
    emailError.classList.remove('active');
    email.classList.remove('input-error');
  }

  if (!valid) {
    event.preventDefault();
    return;
  }

  // Custom password validation
  const password = document.getElementById('password');
  const passwordError = document.getElementById('password-error');
  if (!password.value) {
    passwordError.textContent = 'Please fill out this field';
    passwordError.classList.add('active');
    password.classList.add('input-error');
    valid = false;
  } else {
    passwordError.textContent = '';
    passwordError.classList.remove('active');
    password.classList.remove('input-error');
  }

  if (!valid) {
    event.preventDefault();
    return;
  }

  // Custom confirm password validation
  const confirmPassword = document.getElementById('confirm_password');
  const confirmPasswordError = document.getElementById('confirm-password-error');
  if (!confirmPassword.value) {
    confirmPasswordError.textContent = 'Please fill out this field';
    confirmPasswordError.classList.add('active');
    confirmPassword.classList.add('input-error');
    valid = false;
  } else if (password.value !== confirmPassword.value) {
    confirmPasswordError.textContent = 'Passwords do not match';
    confirmPasswordError.classList.add('active');
    confirmPassword.classList.add('input-error');
    valid = false;
  } else {
    confirmPasswordError.textContent = '';
    confirmPasswordError.classList.remove('active');
    confirmPassword.classList.remove('input-error');
  }

  if (!valid) {
    event.preventDefault();
    return;
  }
});

document.getElementById('email').addEventListener('input', function () {
  if (this.classList.contains('input-error')) {
    this.classList.remove('input-error');
    document.getElementById('email-error').classList.remove('active');
  }
});

document.getElementById('password').addEventListener('input', function () {
  if (this.classList.contains('input-error')) {
    this.classList.remove('input-error');
    document.getElementById('password-error').classList.remove('active');
  }
});

document.getElementById('confirm_password').addEventListener('input', function () {
  if (this.classList.contains('input-error')) {
    this.classList.remove('input-error');
    document.getElementById('confirm-password-error').classList.remove('active');
  }
});