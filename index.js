const menuIcon = document.getElementById("menu-icon");
    const navLinks = document.getElementById("nav-links");

    menuIcon.addEventListener("click", () => {
        navLinks.classList.toggle("active");

        // Change icon
        if(navLinks.classList.contains("active")){
            menuIcon.innerHTML = `<i class='bx bx-x'></i>`;
        } else{
            menuIcon.innerHTML = `<i class='bx bx-menu'></i>`;
        }
    });