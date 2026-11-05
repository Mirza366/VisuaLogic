
const menuToggle = document.getElementById("menuToggle");
const navMenu = document.getElementById("navMenu");

// Membuka dan menutup menu mobile
if (menuToggle && navMenu) {
    menuToggle.addEventListener("click", function () {
        navMenu.classList.toggle("show");

        const icon = menuToggle.querySelector("i");

        if (navMenu.classList.contains("show")) {
            icon.classList.remove("fa-bars");
            icon.classList.add("fa-xmark");
        } else {
            icon.classList.remove("fa-xmark");
            icon.classList.add("fa-bars");
        }
    });
}

// Menutup menu setelah memilih navigasi
document.querySelectorAll(".nav-menu a").forEach(function (link) {
    link.addEventListener("click", function () {
        navMenu.classList.remove("show");

        const icon = menuToggle.querySelector("i");

        if (icon) {
            icon.classList.remove("fa-xmark");
            icon.classList.add("fa-bars");
        }
    });
});

// Navigasi aktif berdasarkan bagian halaman
const sections = document.querySelectorAll("section[id]");
const navLinks = document.querySelectorAll(".nav-menu a");

window.addEventListener("scroll", function () {
    let currentSection = "";

    sections.forEach(function (section) {
        const sectionTop = section.offsetTop - 120;

        if (window.scrollY >= sectionTop) {
            currentSection = section.getAttribute("id");
        }
    });

    navLinks.forEach(function (link) {
        link.classList.remove("active");

        if (link.getAttribute("href") === "#" + currentSection) {
            link.classList.add("active");
        }
    });
});
