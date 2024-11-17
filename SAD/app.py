from flask import Flask, render_template, request, redirect, url_for, session
import mysql.connector
import random
import vonage

app = Flask(__name__)
app.secret_key = 'your_secret_key'  # Replace with a strong secret key

# Database connection
def get_db_connection():
    return mysql.connector.connect(
        host='localhost',
        user='root',
        password='',
        database='sad'
    )

# Vonage client
def send_verification_code(phone_number, code):
    client = vonage.Client(key="eedf11e0", secret="VeFQIXINw3y7IddU")
    sms = vonage.Sms(client)
    responseData = sms.send_message(
        {
            "from": "Vonage APIs",
            "to": phone_number,
            "text": f"Your verification code is: {code}",
        }
    )
    return responseData

@app.route('/')
def index():
    username = session.get('username')  # Retrieve username from session
    return render_template('index.html', username=username)
@app.route('/login', methods=['GET', 'POST'])
def login():
    error = None  # Variable to hold error message
    if request.method == 'POST':
        email = request.form['email']
        password = request.form['password']

        # Check if the user exists in the database
        conn = get_db_connection()
        cursor = conn.cursor()
        cursor.execute('SELECT username FROM users WHERE email = %s AND password = %s', (email, password))
        user = cursor.fetchone()
        cursor.close()
        conn.close()

        if user:
            session['username'] = user[0]  # Store the username in the session
            return redirect(url_for('index'))  # Redirect to index page
        else:
            error = "Wrong email or password."  # Set the error message

    return render_template('login.html', error=error)  # Pass error message to the template



@app.route('/signup', methods=['GET'])
def signup():
    return render_template('signup.html')

@app.route('/register', methods=['POST'])
def register():
    username = request.form['username']
    password = request.form['password']
    email = request.form['email']
    phone_number = request.form['phone_number']
    
    verification_code = random.randint(1000, 10000)

    # Save user to database
    conn = get_db_connection()
    cursor = conn.cursor()
    cursor.execute('INSERT INTO users (username, password, email, phone_number, verification_code) VALUES (%s, %s, %s, %s, %s)',
                   (username, password, email, phone_number, verification_code))
    conn.commit()
    cursor.close()
    conn.close()

    # Send verification code via SMS
    send_verification_code(phone_number, verification_code)

    return redirect(url_for('verify', phone_number=phone_number))

@app.route('/verify', methods=['GET', 'POST'])
def verify():
    if request.method == 'POST':
        phone_number = request.form['phone_number']
        input_code = request.form['verification_code']

        # Check verification code
        conn = get_db_connection()
        cursor = conn.cursor()
        cursor.execute('SELECT verification_code FROM users WHERE phone_number = %s', (phone_number,))
        result = cursor.fetchone()
        cursor.close()
        conn.close()

        if result and str(result[0]) == input_code:
            return "Verification successful!"
        else:
            return "Verification failed. Please try again."

    return render_template('verify.html')

@app.route('/logout')
def logout():
    session.pop('username', None)  # Remove username from session
    return redirect(url_for('index'))  # Redirect to index page

if __name__ == '__main__':
    app.run(debug=True)
