const menuIcon = document.getElementById("menu-icon");
const navLinks = document.getElementById("nav-links");

menuIcon.addEventListener("click", () => {
    navLinks.classList.toggle("active");

    // Change icon
    if (navLinks.classList.contains("active")) {
        menuIcon.innerHTML = `<i class='bx bx-x'></i>`;
    } else {
        menuIcon.innerHTML = `<i class='bx bx-menu'></i>`;
    }
});

const contactForm = document.getElementById("contactForm");
const toastContainer = document.getElementById("toast-container");

function showToast(message, type = "success") {
    const toast = document.createElement("div");
    toast.className = `toast ${type}`;
    
    const icon = type === "success" ? "<i class='bx bxs-check-circle'></i>" : "<i class='bx bxs-error-circle'></i>";
    
    toast.innerHTML = `${icon} <span>${message}</span>`;
    
    toastContainer.appendChild(toast);
    
    // Remove toast after 5 seconds
    setTimeout(() => {
        toast.style.animation = "fadeOut 0.5s ease-in forwards";
        setTimeout(() => {
            toast.remove();
        }, 500);
    }, 5000);
}

if (contactForm) {
    contactForm.addEventListener("submit", async (e) => {
        e.preventDefault();

        const formData = new FormData(contactForm);
        const submitBtn = contactForm.querySelector('button');
        const originalBtnText = submitBtn.innerText;

        submitBtn.innerText = "Sending...";
        submitBtn.disabled = true;

        try {
            const response = await fetch('https://api.abeksystems.com/contact.php', {
                method: 'POST',
                body: formData
            });

            const responseText = await response.text();
            
            try {
                const result = JSON.parse(responseText);
                if (result.status === 'success') {
                    showToast(result.message, "success");
                    contactForm.reset();
                } else {
                    showToast("Error: " + result.message, "error");
                }
            } catch (jsonError) {
                console.error("Server returned non-JSON response:", responseText);
                showToast("Server Error: Unexpected response from server.", "error");
            }

        } catch (error) {
            console.error("Fetch error:", error);
            showToast("Network Error: Could not connect to the server.", "error");
        } finally {
            submitBtn.innerText = originalBtnText;
            submitBtn.disabled = false;
        }
    });
}