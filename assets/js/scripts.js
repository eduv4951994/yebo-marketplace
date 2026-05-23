// Show/Hide Password Toggle
function togglePassword(inputId, toggleTextId) {
    const input = document.getElementById(inputId);
    const toggleText = document.getElementById(toggleTextId);
    if (input.type === "password") {
        input.type = "text";
        toggleText.innerText = "Hide";
    } else {
        input.type = "password";
        toggleText.innerText = "Show";
    }
}

// Simple Registration Check (Just checks length now)
function validateRegistration(event) {
    const pass = document.getElementById('password').value;
    const errorMsg = document.getElementById('pass-error');

    errorMsg.style.display = "none"; // Hide error by default

    // Check if password is at least 4 characters
    if (pass.length < 4) {
        errorMsg.innerText = "Password must be at least 6 characters.";
        errorMsg.style.display = "block";
        event.preventDefault(); // Stop the form from submitting
        return false;
    }
    
    return true; 
}