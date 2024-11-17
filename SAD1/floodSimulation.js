// Initialize map centered on the target area
const map = L.map('map').setView([14.040653, 120.650343], 13);

// Add a tile layer from OpenStreetMap
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 18,
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

// Define boundaries for the simulation area and grid size
const bounds = [
    [14.038, 120.648],
    [14.042, 120.652]
];
const gridSize = 0.001; // Adjust grid density based on the area size

// River overflow level (in meters)
const riverFloodLevel = 30; // Overflow level of the river

// Generate random elevation data for the defined area
function generateRandomElevationGrid() {
    const elevationData = [];
    for (let lat = bounds[0][0]; lat <= bounds[1][0]; lat += gridSize) {
        for (let lng = bounds[0][1]; lng <= bounds[1][1]; lng += gridSize) {
            // Generate random elevation between 0 and 50 meters (for simplicity)
            const elevation = Math.random() * 50;
            elevationData.push({ lat, lng, elevation });
        }
    }
    console.log('Generated random elevation data:', elevationData);  // Debugging log
    return elevationData;
}

// Create polygons based on elevation and apply colors for flood risk levels
function createFloodPolygons(elevationData, rainfallLevel) {
    console.log('Creating flood polygons with rainfall level:', rainfallLevel);  // Debugging log
    elevationData.forEach(({ lat, lng, elevation }) => {
        let color;
        
        // Adjust flood risk zones based on elevation and rainfall level (flooding)
        const heightDifference = riverFloodLevel - elevation; // How much lower the area is compared to river overflow level
        if (heightDifference < 5) {
            color = 'green'; // Safe zone (not flooded)
        } else if (heightDifference >= 5 && heightDifference <= 10) {
            color = 'yellow'; // Moderate flood zone
        } else if (heightDifference > 10) {
            color = 'red'; // High flood zone
        } else {
            color = 'green'; // Default safe zone
        }

        // Create a polygon based on the elevation point and grid
        const polygon = L.polygon([
            [lat, lng],
            [lat + gridSize, lng],
            [lat + gridSize, lng + gridSize],
            [lat, lng + gridSize]
        ], {
            color: color,
            fillColor: color,
            fillOpacity: 0.5
        });
        polygon.addTo(map);
    });
}

// Control rainfall level simulation (adjusts based on selected rainfall level)
function toggleRainfallSimulation() {
    const statusLabel = document.getElementById('statusLabel');
    let rainfallLevel = 0;

    const selectedRainfall = document.querySelector('input[name="rainfall"]:checked');
    if (selectedRainfall) {
        const rainfallType = selectedRainfall.value;
        switch (rainfallType) {
            case 'low':
                rainfallLevel = 5; // Low rainfall effect
                statusLabel.textContent = "Status: Low Rain";
                break;
            case 'medium':
                rainfallLevel = 15; // Medium rainfall effect
                statusLabel.textContent = "Status: Medium Rain";
                break;
            case 'high':
                rainfallLevel = 30; // High rainfall effect
                statusLabel.textContent = "Status: High Rain";
                break;
            default:
                return;
        }

        // Generate random elevation data for the grid within the target bounds
        const elevationData = generateRandomElevationGrid();

        // Update flood polygons based on the selected rainfall level
        createFloodPolygons(elevationData, rainfallLevel);
    }
}

// Event listener for rainfall level radio buttons
document.querySelectorAll('input[name="rainfall"]').forEach((radio) => {
    radio.addEventListener('change', toggleRainfallSimulation);
});

// Check if a radio button is selected when the page loads and update the status
window.addEventListener('load', ()
