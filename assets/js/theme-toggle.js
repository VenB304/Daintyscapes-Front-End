// Theme Toggle Script
document.addEventListener("DOMContentLoaded", function() {
    const themeToggleButton = document.getElementById("theme-toggle");

    // Load saved theme from localStorage
    if (localStorage.getItem("theme") === "dark") {
        document.body.classList.add("dark-mode");
    }

    themeToggleButton.addEventListener("click", function() {
        document.body.classList.toggle("dark-mode");

        // Save preference
        if (document.body.classList.contains("dark-mode")) {
            localStorage.setItem("theme", "dark");
        } else {
            localStorage.setItem("theme", "light");
        }
    });
});
