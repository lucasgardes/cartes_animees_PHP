const userIcon = document.getElementById('userIcon');
const dropdown = document.getElementById('userDropdown');

userIcon.addEventListener('click', () => {
    dropdown.classList.toggle('show');
});

// Fermer le menu si on clique ailleurs
window.addEventListener('click', (e) => {
    if (!userIcon.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.classList.remove('show');
    }
});
