// Register service worker
// Bulma mobile navigation bar
var navbarBurger = document.querySelector('.navbar-burger');
var navbarMenu = document.querySelector('.navbar-menu');
if (navbarBurger != undefined && navbarMenu != undefined) {
    navbarBurger.addEventListener('click', function (event) {
        event.preventDefault();
        navbarBurger.classList.toggle('is-active');
        navbarMenu.classList.toggle('is-active');
    });
}

// Bulma alerts close button
var notificationDeleteButtons = document.querySelectorAll('.notification .delete');
for (var i = 0; i < notificationDeleteButtons.length; i++) {
    notificationDeleteButtons[i].addEventListener('click', function (event) {
        event.preventDefault();
        this.parentNode.parentNode.removeChild(this.parentNode);
    });
}
