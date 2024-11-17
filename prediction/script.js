const ctx = document.getElementById('waterLevelChart').getContext('2d');
let waterLevelChart;

// Fetch data from the PHP script
async function fetchData() {
    const response = await fetch('index.php');
    const data = await response.json();
    return data;
}

// Update the graph and predictions
function updateGraphAndPredictions(data) {
    const actualData = data.data.map(point => ({
        x: new Date(point.time * 1000),
        y: point.water_level
    }));

    const predictionData = data.predictions.map(point => ({
        x: new Date(point.time * 1000),
        y: point.water_level
    }));

    // Update chart
    if (!waterLevelChart) {
        waterLevelChart = new Chart(ctx, {
            type: 'line',
            data: {
                datasets: [
                    {
                        label: 'Actual Water Levels',
                        data: actualData,
                        borderColor: 'blue',
                        fill: false
                    },
                    {
                        label: 'Predicted Water Levels',
                        data: predictionData,
                        borderColor: 'red',
                        borderDash: [5, 5],
                        fill: false
                    }
                ]
            },
            options: {
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'minute'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Water Level'
                        }
                    }
                }
            }
        });
    } else {
        // Update datasets
        waterLevelChart.data.datasets[0].data = actualData;
        waterLevelChart.data.datasets[1].data = predictionData;
        waterLevelChart.update();
    }

    // Update prediction list
    const predictionList = document.getElementById('predictionList');
    predictionList.innerHTML = '';
    data.predictions.forEach(prediction => {
        const li = document.createElement('li');
        li.textContent = `Time: ${new Date(prediction.time * 1000).toLocaleTimeString()}, Predicted Level: ${prediction.water_level.toFixed(2)}`;
        predictionList.appendChild(li);
    });
}

// Periodically fetch and update data
setInterval(async () => {
    const data = await fetchData();
    updateGraphAndPredictions(data);
}, 5000); // Update every 5 seconds
