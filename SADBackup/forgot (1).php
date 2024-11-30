<?php
session_start();

$statusMessage = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Step 1: Handle email submission and sending verification code
    if (isset($_POST['email']) && !isset($_POST['verification_code']) && !isset($_POST['new_password'])) {
        include 'partials/connect.php';
        $email = $_POST['email'];
        $stmt = $con->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $verificationCode = mt_rand(1000, 10000);
            $_SESSION['verification_code'] = $verificationCode;

            $updateStmt = $con->prepare("UPDATE users SET verification_code = ? WHERE email = ?");
            $updateStmt->bind_param("is", $verificationCode, $email);
            $updateStmt->execute();

            $statusMessage = "A verification code has been sent to your email. Please check your inbox.";
            $_SESSION['email'] = $email;

            // Send the verification code via EmailJS
            echo "<script>
                window.onload = function() {
                    emailjs.init('q6mrbPROZKMOvff2T'); // Initialize EmailJS with your user ID
                    
                    // Send the verification code via EmailJS
                    emailjs.send('service_soc43sm', 'template_asq5e1i', {
                        to_email: '$email',
                        verification_code: '$verificationCode'
                    }).then(function(response) {
                        console.log('Success:', response);
                    }, function(error) {
                        console.log('Error:', error);
                    });
                };
            </script>";
        } else {
            $statusMessage = "No account found with that email address.";
        }
    }

    // Step 2: Handle verification code validation
    if (isset($_POST['verification_code']) && isset($_SESSION['verification_code'])) {
        $userCode = $_POST['verification_code'];
        if ($userCode == $_SESSION['verification_code']) {
            $statusMessage = "Verification successful! Please enter your new password.";
            $_SESSION['verification_valid'] = true;
        } else {
            $statusMessage = "Invalid verification code. Please try again.";
        }
    }

    // Step 3: Handle new password update
    if (isset($_POST['new_password']) && isset($_POST['verify_password']) && isset($_SESSION['verification_valid']) && $_SESSION['verification_valid'] == true) {
        $newPassword = $_POST['new_password'];
        $verifyPassword = $_POST['verify_password'];

        if ($newPassword == $verifyPassword) {
            include 'partials/connect.php';
            $email = $_SESSION['email'];
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            $updateStmt = $con->prepare("UPDATE users SET password = ? WHERE email = ?");
            $updateStmt->bind_param("ss", $hashedPassword, $email);
            $updateStmt->execute();

            $statusMessage = "Your password has been successfully updated! Redirecting to login...";

            session_destroy(); // Clear the session after password change

            // Redirect to login page after successful password update
            header("Location: login.php");
            exit(); // Stop further script execution
        } else {
            $statusMessage = "Passwords do not match. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/@emailjs/browser@4/dist/email.min.js"></script>
    <script>
        console.log(emailjs);  // Check if emailjs object is loaded
    </script>
    <style>
        /* forgot.css */
        body {
            background: url('https://i.imgur.com/kLDKeZP.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(51, 51, 51, 0.8); /* Dark background with 80% opacity */
            z-index: -1;
        }
        .container {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.1);
            padding: 2rem;
            border-radius: 15px;
            width: 300px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        h1 {
            color: #fff;
            font-size: 24px;
            margin-bottom: 20px;
        }
        label {
            color: #fff;
            display: block;
            font-size: 14px;
            margin-bottom: 5px;
        }
        input[type="email"], input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 7px 0;
            border: none;
            border-radius: 5px;
            background: rgba(176, 174, 174, 0.2);
            color: #2d2b2b;
        }
        input::placeholder {
            color: #ccc;
        }
        button {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: #333333;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
        }
        button:hover {
            background-color: #4e4d4d;
        }
        #status {
            margin-top: 10px;
            color: #fff;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Forgot Password</h1>

        <?php if (!isset($_SESSION['verification_valid'])): ?>
            <?php if (!isset($_SESSION['verification_code'])): ?>
                <form action="forgot.php" method="POST">
                    <label for="email">Enter Your Email:</label>
                    <input type="email" name="email" placeholder="Enter your email" required>
                    <button type="submit">Send Verification Code</button>
                </form>
            <?php else: ?>
                <form action="forgot.php" method="POST">
                    <label for="verification_code">Enter Verification Code:</label>
                    <input type="text" name="verification_code" placeholder="Enter your verification code" required>
                    <button type="submit">Verify Code</button>
                </form>
            <?php endif; ?>
        <?php else: ?>
            <form action="forgot.php" method="POST">
                <label for="new_password">Enter New Password:</label>
                <input type="password" name="new_password" placeholder="Enter new password" required>
                <label for="verify_password">Confirm New Password:</label>
                <input type="password" name="verify_password" placeholder="Confirm new password" required>
                <button type="submit">Update Password</button>
            </form>
        <?php endif; ?>

        <p id="status"><?php echo $statusMessage; ?></p>
    </div>
</body>
</html>
