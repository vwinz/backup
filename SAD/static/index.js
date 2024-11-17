let unit = 'metric';  // Default to Celsius
let forecastDataCache = null;
let temperatureChart = null;
let currentlyDisplayedDayData = null; // Track currently displayed day data

async function getWeather() {
    const apiKey = "279922c56e2343fa988bf4d4a7863098";
    const lat = 14.035020;
    const lon = 120.652878;
    const forecastUrl = `https://api.openweathermap.org/data/2.5/forecast?lat=${lat}&lon=${lon}&units=${unit}&appid=${apiKey}`;

    try {
        const forecastResponse = await fetch(forecastUrl);
        const forecastData = await forecastResponse.json();
        forecastDataCache = forecastData;
        displayWeather(forecastData);
    } catch (error) {
        alert("Error fetching weather data.");
    }
}

function displayWeather(forecastData) {
    const weatherCards = document.getElementById("weather-cards");
    weatherCards.innerHTML = "";
    const dailyForecasts = {};
    forecastData.list.forEach(forecast => {
        const date = new Date(forecast.dt * 1000);
        const day = date.toLocaleDateString("en-US", { weekday: "long", month: "short", day: "numeric" });
        if (!dailyForecasts[day]) dailyForecasts[day] = [];
        dailyForecasts[day].push(forecast);
    });

    let count = 0;
    for (const day in dailyForecasts) {
        if (count++ === 5) break;
        const dayData = dailyForecasts[day];
        const temps = dayData.map(forecast => forecast.main.temp);
        const maxTemp = Math.max(...temps);
        const minTemp = Math.min(...temps);
        const weatherDescription = dayData[0].weather[0].description;
        const icon = `https://openweathermap.org/img/wn/${dayData[0].weather[0].icon}.png`;

        const card = document.createElement("div");
        card.classList.add("weather-card");
        card.innerHTML = `
            <h3>${day.split(',')[0]},</h3>
            <p>${day.split(',')[1].trim()}</p>
            <img src="${icon}" alt="${weatherDescription}" />
            <p>${weatherDescription}</p>
            <p>${maxTemp.toFixed(1)}°C</p>
            <p>${minTemp.toFixed(1)}°C</p>
        `;
        card.onclick = () => showDetails(dayData);
        weatherCards.appendChild(card);
    }
}

function showDetails(dayData) {
    const detailsDiv = document.getElementById("details");
    const chartContainer = document.getElementById("chartContainer");

    // Check if the clicked card is the same as the currently displayed one
    if (currentlyDisplayedDayData === dayData) {
        // If it's the same, hide details and clear the chart
        detailsDiv.style.display = "none";
        chartContainer.style.display = "none";
        if (temperatureChart) temperatureChart.destroy(); // Clear the chart
        currentlyDisplayedDayData = null; // Reset displayed data
        return; // Exit the function
    }

    // If it's a new card, update displayed data
    currentlyDisplayedDayData = dayData;
    
    detailsDiv.style.display = "block";
    chartContainer.style.display = "block";

    const labels = dayData.map(forecast => 
        new Date(forecast.dt * 1000).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
    );
    const data = dayData.map(forecast => forecast.main.temp);

    if (temperatureChart) temperatureChart.destroy();
    const ctx = document.getElementById("temperatureChart").getContext("2d");
    temperatureChart = new Chart(ctx, {
        type: "line",
        data: { 
            labels: labels, 
            datasets: [{
                label: "Temperature (°C)",
                data: data,
                borderColor: "#333333", // Line color
                backgroundColor: "#95999A",
                fill: true
            }]
        },
        options: {
            scales: { 
                y: { 
                    title: { display: true, text: "Temperature (°C)" }
                },
                x: { 
                    title: { display: true, text: "Time" } 
                } 
            },
            responsive: true
        }
    });
}

// Initialize weather on page load
window.onload = getWeather;
