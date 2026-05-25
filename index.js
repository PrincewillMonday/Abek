const menuIcon = document.getElementById("menu-icon");
const navLinks = document.getElementById("nav-links");

// MOBILE MENU TOGGLE
menuIcon.addEventListener("click", () => {
    navLinks.classList.toggle("active");

    if (navLinks.classList.contains("active")) {
        menuIcon.innerHTML = `<i class='bx bx-x'></i>`;
    } else {
        menuIcon.innerHTML = `<i class='bx bx-menu'></i>`;
    }
});

// CLOSE MENU ON LINK CLICK
document.querySelectorAll(".nav-links a").forEach(link => {
    link.addEventListener("click", () => {
        navLinks.classList.remove("active");
        menuIcon.innerHTML = `<i class='bx bx-menu'></i>`;
    });
});

// ================= TOAST =================
const toastContainer = document.getElementById("toast-container");

function showToast(message, type = "success") {
    const toast = document.createElement("div");
    toast.className = `toast ${type}`;

    const icon = type === "success"
        ? "<i class='bx bxs-check-circle'></i>"
        : "<i class='bx bxs-error-circle'></i>";

    toast.innerHTML = `${icon} <span>${message}</span>`;

    toastContainer.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = "fadeOut 0.5s ease forwards";
        setTimeout(() => toast.remove(), 500);
    }, 5000);
}

// ================= CONTACT FORM =================
const contactForm = document.getElementById("contactForm");

if (contactForm) {
    contactForm.addEventListener("submit", async (e) => {
        e.preventDefault();

        const formData = new FormData(contactForm);
        const submitBtn = contactForm.querySelector("button");

        submitBtn.disabled = true;
        submitBtn.innerText = "Sending...";

        try {
            const response = await fetch("https://api.abeksystems.com/contact.php", {
                method: "POST",
                body: formData
            });

            const text = await response.text();

            try {
                const result = JSON.parse(text);

                if (result.status === "success") {
                    showToast(result.message, "success");
                    contactForm.reset();
                } else {
                    showToast(result.message || "Error occurred", "error");
                }

            } catch {
                showToast("Server response error", "error");
            }

        } catch (err) {
            showToast("Network error", "error");
        }

        submitBtn.disabled = false;
        submitBtn.innerText = "Send Message";
    });
}

// ================= SCROLL REVEAL =================
const revealElements = document.querySelectorAll(
    ".grid-item, .about-content, .team-member, .contact-content"
);

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add("active");
        }
    });
}, { threshold: 0.15 });

revealElements.forEach((el, i) => {
    el.classList.add("reveal");
    el.style.transitionDelay = `${i * 0.1}s`;
    observer.observe(el);
});