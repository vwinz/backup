import serial
import mysql.connector
from datetime import datetime
import time  # Import time module for delay
import re  # Import regex module for parsing

# Arduino serial port and baud rate
arduino_port = "COM3"  # Change this to your Arduino's serial port
baud_rate = 9600

# MySQL database credentials
db_config = {
    'host': 'localhost',
    'user': 'root',
    'password': '',  # Your MySQL password if any
    'database': 'water_level'
}

# Connect to Arduino
try:
    arduino = serial.Serial(arduino_port, baud_rate)
    print("Connected to Arduino on port:", arduino_port)
except Exception as e:
    print("Error connecting to Arduino:", e)
    exit()

# Connect to MySQL database
try:
    conn = mysql.connector.connect(**db_config)
    cursor = conn.cursor()
    print("Connected to MySQL database.")
except mysql.connector.Error as e:
    print("Error connecting to MySQL database:", e)
    exit()

# Insert data into the database
def insert_data(water_level):
    try:
        timestamp = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        query = "INSERT INTO sensor_data (water_level, timestamp) VALUES (%s, %s)"
        cursor.execute(query, (water_level, timestamp))
        conn.commit()
        print(f"Inserted data - Water Level: {water_level}, Timestamp: {timestamp}")
    except mysql.connector.Error as e:
        print("Error inserting data:", e)

# Parse and extract numeric value from Arduino data
def parse_water_level(data):
    match = re.search(r"Air Quality \(PPM\): ([\d.]+)", data)
    if match:
        return float(match.group(1))
    else:
        return None

# Read data from Arduino and send to database
try:
    while True:
        if arduino.in_waiting > 0:
            line = arduino.readline().decode('utf-8').strip()
            print("Received data from Arduino:", line)
            water_level = parse_water_level(line)
            if water_level is not None:
                insert_data(water_level)
            else:
                print("Invalid data received:", line)
        # Delay for 5 seconds
        time.sleep(5)
except KeyboardInterrupt:
    print("Exiting program.")

# Close connections
finally:
    if arduino.is_open:
        arduino.close()
    if conn.is_connected():
        cursor.close()
        conn.close()
    print("Connections closed.")
