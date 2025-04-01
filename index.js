document.addEventListener("DOMContentLoaded", function() {
    // Check login status from PHP sessions
    const profileContainer = document.getElementById("profile-container");
    const authButtons = document.querySelector(".navbar-right > div:not(#profile-container)");
    
    // Show/hide elements based on PHP-generated HTML structure
    if (profileContainer.style.display === "flex") {
        // User is logged in (PHP set this)
        authButtons.style.display = "none";
    } else {
        // User is not logged in
        profileContainer.style.display = "none";
    }

    loadRooms(); // Keep your room loading functionality
});

// Update logout to work with PHP sessions
function logoutUser() {
    fetch('logout.php')
        .then(response => {
            if (response.ok) {
                window.location.href = "index.php"; // Redirect after logout
            }
        })
        .catch(error => console.error('Logout failed:', error));
}

// Keep your existing loadRooms() function
function loadRooms() {
    fetch("rooms.php")
        .then(response => response.json())
        .then(data => {
            let roomHTML = "";
            data.forEach(room => {
                roomHTML += `
                    <div class="room-card">
                        <h3>${room.RoomType}</h3>
                        <p>Price: $${room.Price} per night</p>
                    </div>
                `;
            });
            document.getElementById("roomsContainer").innerHTML = roomHTML;
        })
        .catch(error => console.error("Error loading rooms:", error));
}