let generatedOTP = '';

function sendOTP() {
  const email = document.getElementById('emailInput').value;

  if (!email || !email.includes('@')) {
    alert('Please enter a valid email.');
    return;
  }

  generatedOTP = Math.floor(1000 + Math.random() * 9000).toString(); // 4-digit OTP

  document.getElementById('otpDisplay').innerText = `Your OTP (for testing): ${generatedOTP}`;
  document.getElementById('loginContainer').style.display = 'none';
  document.getElementById('otpContainer').style.display = 'block';
}

function verifyOTP() {
  const userOTP = document.getElementById('otpInput').value;

  if (userOTP === generatedOTP) {
    document.getElementById('otpContainer').style.display = 'none';
    document.getElementById('successContainer').style.display = 'block';
  } else {
    document.getElementById('otpResult').innerText = 'Invalid OTP. Please try again.';
  }
}
