const signUpButton = document.getElementById('signUp');
const signInButton = document.getElementById('signIn');
const overlaContainer = document.getElementById('overlay-container');

signUpButton.addEventListener('click', () => {
    overlaContainer.classList.add("changed");
});

signInButton.addEventListener('click', () => {
    overlaContainer.classList.remove("changed");
});