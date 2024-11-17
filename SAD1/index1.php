<?php
session_start(); // Start the session

// Include database connection details
include 'connect.php';

// Check for successful connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
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
                <a href="login.php">
                    <img src="https://i.imgur.com/RgJwhYe.png" alt="Account Icon" style="max-width: 100%; height: auto;">
                </a>

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
          <button class="explore-button" onclick="window.location.href='simulation2.html'">Explore</button>

        </div>
    </header>

    <div class="menu">
        <div class="menu-item">
        <a href="https://guileless-hamster-f55e39.netlify.app/">
            <img src="https://i.imgur.com/oz6Rm2M.png" alt="Map Icon">
            <p>Maps</p>
        </a>
        </div>
        <div class="menu-item">
            <img src="https://i.imgur.com/zaSAzER.png" alt="Evacuation Center Icon">
            <p>Evacuation Centers</p>
        </div>
        <div class="menu-item">
            <img src="https://i.imgur.com/yEE7RqG.png" alt="Donate Icon">
            <p>Donate Here!</p>
        </div>
        <div class="menu-item">
            <a href="upload.php">
                <img src="https://i.imgur.com/07nLoij.png" alt="Updates Icon">
                <p>Updates</p>
            </a>
        </div>
    </div>

    <br><br>
    <h2 class="forecast-header">Real-time Weather</h2>
    <br>
    <div class="container">
        <div class="weather-cards" id="weather-cards"></div>
        <div class="details" id="details"></div>
        <div id="chartContainer">
            <canvas id="temperatureChart"></canvas>
        </div>
    </div>

    <!-- Section for Water Level Monitoring Chart -->
    <br><br><br>
    
    <h2 class="forecast-header">Lian River's Real-Time Water Level</h2>
    <div style="position: relative;">
        <canvas id="waterLevelChart" width="500" height="300"></canvas>
    </div>

    <button class="river-button" onclick="window.location.href='river.html'">Explore Water Level</button>



    <br><br><br><br>

    <!-- Emergency Hotlines Section -->
    <h2 class="emergency-header">Emergency Hotlines</h2>
    <div class="hotlines-container">
        <div class="hotline">
            <h3>MDRRMO RESCUE</h3>
            <p>0965-210-5769<br>(043)-233-1241</p>
        </div>
        <div class="hotline">
            <h3>BATELEC I</h3>
            <p>0917-714-3862<br>0908-814-2144<br>0917-627-7847</p>
        </div>
        <div class="hotline">
            <h3>MDRRMO RESCUE</h3>
            <p>0956-182-4272<br>(043)-727-6745</p>
        </div>
        <div class="hotline">
            <h3>PHILIPPINE RED CROSS (LIAN)</h3>
            <p>0917-133-1427<br>(043)-740-2356</p>
        </div>
        <div class="hotline">
            <h3>PHILIPPINE COASTGUARD (LIAN)</h3>
            <p>0966-496-6796</p>
        </div>
        <div class="hotline">
            <h3>LIAN POLICE STATION</h3>
            <p>0917-551-2292<br>0998-598-5692</p>
        </div>
        <div class="hotline">
            <h3>BRGY 3 RESCUE</h3>
            <p>0966-496-6796</p>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('waterLevelChart').getContext('2d');

        // Custom plugin to draw the background color bands
        const backgroundBandsPlugin = {
            id: 'backgroundBands',
            beforeDraw(chart) {
                const { ctx } = chart;
                const { top, bottom, left, right } = chart.chartArea;
                const sectionHeight = (bottom - top) / 3;

                // Draw green band (bottom third)
                ctx.fillStyle = 'rgba(0, 255, 0, 0.5)';
                ctx.fillRect(left, bottom - sectionHeight, right - left, sectionHeight);

                // Draw yellow band (middle third)
                ctx.fillStyle = 'rgba(255, 255, 0, 0.5)';
                ctx.fillRect(left, bottom - 2 * sectionHeight, right - left, sectionHeight);

                // Draw red band (top third)
                ctx.fillStyle = 'rgba(255, 0, 0, 0.5)';
                ctx.fillRect(left, top, right - left, sectionHeight);
            }
        };

        const waterLevelChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [], // No x-axis labels
                datasets: [{
                    label: 'Water Level',
                    data: [], // To be filled dynamically
                    borderColor: 'rgba(0, 123, 255, 1)',
                    backgroundColor: 'rgba(0, 123, 255, 0.2)', // Fill color below the line
                    tension: 0.4,
                    fill: true // Enable filling
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    backgroundBands: {} // Add the custom plugin
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 180, // Set max to 180 for equal thirds
                        title: {
                            display: true,
                            text: 'Green\nYellow\nRed', // Simplified y-axis labels
                            align: 'center', // Center the title
                            padding: 20 // Add padding for better visibility
                        },
                        ticks: {
                            callback: function(value) {
                                return ''; // Hide y-axis tick marks
                            }
                        }
                    },
                    x: {
                        display: false // Hide the x-axis completely
                    }
                }
            },
            plugins: [backgroundBandsPlugin] // Register the plugin
        });

        // Function to add new water level reading
        function addData(label, dataPoint) {
            if (waterLevelChart.data.labels.length > 20) {
                waterLevelChart.data.labels.shift(); // Remove the first label if over 20
                waterLevelChart.data.datasets[0].data.shift(); // Remove the first data point if over 20
            }
            waterLevelChart.data.labels.push(label);
            waterLevelChart.data.datasets[0].data.push(dataPoint);
            waterLevelChart.update();
        }

        // Simulate real-time data for demonstration (Replace with your Arduino data)
        setInterval(() => {
            const simulatedWaterLevel = Math.random() * 180; // Replace with actual reading
            addData('', simulatedWaterLevel); // No label for x-axis
        }, 2000); // Update every 2 seconds
    </script>

</body>
</html>
