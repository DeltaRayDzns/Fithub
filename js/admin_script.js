const sidebar = document.querySelector('.sidebar');
const sidebarToggle = document.querySelector('.sidebar-toggle');
const mainContent = document.querySelector('.main-content');

sidebarToggle.addEventListener('click', () => {
    sidebar.classList.toggle('active');
});

if (sidebar.classList.contains('active')) {
        mainContent.style.marginLeft = '0';
    } else {
        mainContent.style.marginLeft = getComputedStyle(document.documentElement).getPropertyValue('--sidebar-width');
    }

const profileToggle = document.querySelector('.profile-dropdown .profile-toggle');
const profileMenu = document.querySelector('.profile-dropdown .dropdown-menu');

profileToggle.addEventListener('click', (e) => {
    e.stopPropagation(); 
    profileMenu.classList.toggle('show');
});

document.addEventListener('click', (e) => {
    if (!profileToggle.contains(e.target)) {
        profileMenu.classList.remove('show');
    }
});

document.addEventListener('click', (e) => {
    if (window.innerWidth <= 991 && !sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
        sidebar.classList.add('active'); 
    }
});

window.addEventListener('resize', () => {
    if (window.innerWidth > 991) {
        sidebar.classList.remove('active');
    }
});
