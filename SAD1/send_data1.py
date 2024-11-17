import mysql.connector

# Database connection
db = mysql.connector.connect(
    host="the-neil.com",# Replace with your GoDaddy MySQL host (e.g., mysql.example.com)
    user="admin",      # Replace with your GoDaddy DB username
    password="xdWSj@sO-sS]",  # Replace with your GoDaddy DB password
    database="ianvwinz"           # Your database name
)

cursor = db.cursor()

# Data to be inserted
water_level = 180 # Example data for water level, replace with your actual data

# SQL query to insert data
insert_query = "INSERT INTO sensor_data (water_level) VALUES (%s)"
cursor.execute(insert_query, (water_level,))

# Commit the transaction
db.commit()

print(f"Data {water_level} inserted successfully into sensor_data table.")

# Close the connection
cursor.close()
db.close()
