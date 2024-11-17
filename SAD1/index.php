<?php
session_start(); // Start the session

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FloodGuard</title>
    <link rel="stylesheet" href="index.css">
    <script src="index.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <header>
        <div class="header-container">
            <h1>FloodGuard</h1>
            <nav>
                <a href="#">Home</a>
                <a href="#">Services</a>
                <a href="#">Contact</a>
                <a href="login.php"><img src="pics/acc.png" alt="Account Icon"></a>
                <?php if (isset($_SESSION['username'])): ?>
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <button onclick="confirmLogout()">Logout</button>
                <?php else: ?>
                    <span></span>
                <?php endif; ?>
            </nav>
        </div>
        <div class="banner">
            <p>Welcome to</p>
            <h2>FloodGuard: Real-Time Water Level Monitoring and Evacuation Network</h2>
            <p>This website is designed to keep the community informed about flood management in Brgy 3, Lian, Batangas, located near the river. By signing up, you will receive automated messages and updates specific to our community's needs. Stay informed and prepared!</p>
        </div>
    </header>

    <div class="menu">
        <div class="menu-item">
            <img src="pics/maps.png" alt="Map Icon">
            <p>Maps</p>
        </div>
        <div class="menu-item">
            <img src="pics/home.png" alt="Evacuation Center Icon">
            <p>Evacuation Centers</p>
        </div>
        <div class="menu-item">
            <img src="pics/donation.png" alt="Donate Icon">
            <p>Donate Here!</p>
        </div>
        <div class="menu-item">
            <img src="pics/update.png" alt="Updates Icon">
            <p>Updates</p>
        </div>
    </div>

    <br>
    <br>
    <h2 class="forecast-header">Real-time Weather</h2>
    <br>
    <div class="container">
        <div class="weather-cards" id="weather-cards"></div>
        <div class="details" id="details"></div>
        <div id="chartContainer">
            <canvas id="temperatureChart"></canvas>
        </div>
    </div>

    <footer>&copy; 2024 FloodGuard. All rights reserved.</footer>

    <script>
    function confirmLogout() {
        if (confirm("Are you sure you want to log out?")) {
            window.location.href = "logout.php"; // Redirect to logout
        }
    }
    </script>
</body>
</html>
