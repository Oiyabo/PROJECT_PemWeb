document.addEventListener('DOMContentLoaded', () => {
    const signUpButton = document.getElementById('signUp');
    const signInButton = document.getElementById('signIn');
    const overlayContainer = document.getElementById('overlay-container');

    // Toggle to Sign Up
    if (signUpButton && overlayContainer) {
        signUpButton.addEventListener('click', (e) => {
            e.preventDefault();
            overlayContainer.classList.add('changed');
        });
    }

    // Toggle to Sign In
    if (signInButton && overlayContainer) {
        signInButton.addEventListener('click', (e) => {
            e.preventDefault();
            overlayContainer.classList.remove('changed');
        });
    }

    // Auto-dismiss flash messages
    const flashes = document.querySelectorAll('.flash-auth');
    flashes.forEach(el => {
        setTimeout(() => {
            el.style.transition = 'opacity 0.5s';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 500);
        }, 4000);
    });
});
