import requests
import time

# URL of your PHP script on InfinityFree
url = "https://vwinz.infinityfreeapp.com/send_data.php"

# Dummy sensor value (you can replace this with actual sensor data from Arduino)
sensor_value = 1

# Set headers if required (optional)
headers = {
    'Content-Type': 'application/x-www-form-urlencoded',
}

# Loop to send data continuously
while True:
    try:
        # Send data using POST method with SSL verification disabled
        response = requests.post(
            url,
            data={'sensor_value': sensor_value},
            headers=headers,
            timeout=60,  # Timeout in seconds
            verify=False  # Disable SSL certificate verification
        )

        # Check if the request was successful
        if response.status_code == 200:
            print(f"Data sent successfully: Sensor Value = {sensor_value}")
        else:
            print(f"Failed to send data: {response.status_code}")

    except requests.exceptions.RequestException as e:
        print(f"An error occurred: {e}")

    # Simulate new sensor data every 10 seconds (or change this as needed)
    time.sleep(10)
    sensor_value += 1  # Increment sensor value for demonstration
