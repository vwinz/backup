<?php
session_start(); // Start the session

// Include database connection details
include 'partials/connect.php';

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

    <style>
        :root {
            --card-height: 300px;
            --card-width: calc(var(--card-height) / 1.5);
        }
        * {
            box-sizing: border-box;
        }

          .row:nth-child(1) {
        margin-bottom: 50px; /* Adjust this value for gap */
    }
      
        .row {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
        }
     
        .card-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 20px;
        }
        .card {
            width: var(--card-width);
            height: var(--card-height);
            position: relative;
            display: flex;
            justify-content: center;
            align-items: flex-end;
            padding: 0 36px;
            perspective: 2500px;
            border-radius: 5px;
        }
        .cover-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 5px;
        }
        .wrapper {
            transition: all 0.5s;
            position: absolute;
            width: 100%;
            z-index: -1;
            border-radius: 5px;
        }
        .card:hover .wrapper {
            transform: perspective(900px) translateY(-5%) rotateX(25deg) translateZ(0);
            box-shadow: 2px 35px 32px -8px rgba(0, 0, 0, 0.75);
            -webkit-box-shadow: 2px 35px 32px -8px rgba(0, 0, 0, 0.75);
            -moz-box-shadow: 2px 35px 32px -8px rgba(0, 0, 0, 0.75);
            border-radius: 5px;
        }
        .character {
            width: 100%;
            opacity: 0;
            transition: all 0.5s;
            position: absolute;
            z-index: -1;
        }
        .card:hover .character {
            opacity: 1;
            transform: translate3d(0%, -30%, 100px);
        }
        .card-title {
            text-align: center;
            margin-top: 15px;
        }
        .card-title h2 {
            font-size: 1.5rem;
            font-weight: bold;
            margin: 0;
        }
        .card-title p {
            font-size: 1rem;
            color: #555;
            margin: 5px 0 0;
        }
    </style>

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
         <a href="evacuation.html">
            <img src="https://i.imgur.com/zaSAzER.png" alt="Evacuation Center Icon">
            <p>Evacuation Centers</p>
        </div>
        <div class="menu-item">
            <a href="donation.html">
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
        <button style="
        position: absolute;
        top: 105%;
        left: 50%;
        transform: translate(-50%, -50%);
        padding: 10px 20px;
        font-size: 16px;
        color: #fff;
        background-color: #333;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    ">
        Check Water Level History
    </button>
    </div>

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


    <main>
        <!-- About Us Section -->
        <section class="about-us-section">
            <h2 class="about-us-header">About Us</h2>
             <!-- Row 1: Francine -->
    <div class="row">
        <div class="card-container">
            <div class="card">
                <div class="wrapper">
                    <img src="https://i.imgur.com/umYIt9P.jpg" class="cover-image" />
                </div>
                <img src="https://i.imgur.com/JtMdoPQ.png" class="character" />
            </div>
            <div class="card-title">
                <h2>Francine Ysabel Jonson</h2>
               
                <p>System Analyst</p>
                 <h4>Project Leader</h4>
            </div>
        </div>
    </div>

    <!-- Row 2: Ian, Barias, MK, Vince -->
    <div class="row">
        <div class="card-container">
            <div class="card">
                <div class="wrapper">
                    <img src="https://i.imgur.com/vd4om38.jpg" class="cover-image" />
                </div>
                <img src="https://i.imgur.com/ihw10Fo.png" class="character" />
            </div>
            <div class="card-title">
                <h2>Ian Vince Romero</h2>
                <p>Software Engineer</p>
            </div>
        </div>

        <div class="card-container">
            <div class="card">
                <div class="wrapper">
                    <img src="https://i.imgur.com/fL5mwOi.jpg" class="cover-image" />
                </div>
                <img src="https://i.imgur.com/1HjTGbZ.png" class="character" />
            </div>
            <div class="card-title">
                <h2>Justine Barias</h2>
                <p>Librarian</p>
            </div>
        </div>

        <div class="card-container">
            <div class="card">
                <div class="wrapper">
                    <img src="https://i.imgur.com/TkSTODc.jpg" class="cover-image" />
                </div>
                <img src="https://i.imgur.com/6kbLIlB.png" class="character" />
            </div>
            <div class="card-title">
                <h2>Marc Kristian Pulido</h2>
                <p>UI/UX Designer</p>
            </div>
        </div>

        <div class="card-container">
            <div class="card">
                <div class="wrapper">
                    <img src="https://i.imgur.com/2UJ0GEI.jpg" class="cover-image" />
                </div>
                <img src="https://i.imgur.com/nMkI7tT.png" class="character" />
            </div>
            <div class="card-title">
                <h2>Vince Albert Alcaraz</h2>
                <p>Tester</p>
            </div>
        </div>
    </div>
            


            <br><br><br><br>

        </section>
    </main>


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
