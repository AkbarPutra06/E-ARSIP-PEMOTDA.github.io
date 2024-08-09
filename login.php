<?php
session_start();

// Koneksi ke database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "form_login";

$conn = new mysqli($servername, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $conn->real_escape_string($_POST['password']);

    // Query untuk memeriksa apakah username ada di database
    $sql = "SELECT users.*, roles.role_name FROM users 
            JOIN roles ON users.role_id = roles.id 
            WHERE username='$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Jika username ada, periksa passwordnya
        $user = $result->fetch_assoc();
        if ($user['password'] === $password) {
            // Jika password cocok, simpan informasi pengguna ke session
            $_SESSION['session_username'] = $username;
            $_SESSION['session_role'] = $user['role_name']; // Menyimpan nama peran dalam sesi
            // Redirect ke dashboard dengan alert berhasil
            echo '<script>alert("Anda telah berhasil Login, Selamat datang ' . $username . '!"); window.location.href = "dashboard.php";</script>';
            exit();
        } else {
            // Jika password salah
            $_SESSION['login_error'] = "Password salah.";
        }
    } else {
        // Jika username tidak ditemukan
        $_SESSION['login_error'] = "Username tidak ditemukan.";
    }

    // Redirect ke login page
    header("Location: login.php");
    exit();
}

$conn->close();
?>
    


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Arsip Pemotda Sulawesi Utara</title>
    <link rel="icon" href="img/provsulut.png" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    
            <!-- Custom styles for this template-->
            <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700&display=swap');

        * {
            margin: 0;
            border: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            background: linear-gradient(45deg, #E50000, #ff9a8b, #E50000, #ff4b5c);
            background-size: 500% 500%;
            -webkit-animation: Gradient 15s ease infinite;
            -moz-animation: Gradient 15s ease infinite;
            animation: Gradient 15s ease infinite;
            min-height: 100vh;
            min-width: 100vw;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column; /* Tambahkan ini untuk menampilkan footer di bawah */
        }

        @-webkit-keyframes Gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @-moz-keyframes Gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes Gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .container {
            width: 400px;
            min-height: 400px;
            background: #FFF;
            border-radius: 5px;
            box-shadow: 0 0 5px rgba(0, 0, 0, .3);
            padding: 40px 30px;
            animation: bouncedown 4s ease 1 forwards; /* Increase duration to 4s */
            text-align: center; /* Center align text and logo */
        }

        @keyframes bouncedown {
            0% { opacity: 0; transform: translateY(-2000px); }
            60% { opacity: 1; transform: translateY(30px); }
            80% { transform: translateY(-10px); }
            100% { transform: translateY(0); }
        }

        .login-logo {
            width: 80px; /* Adjust the size of the logo */
            height: auto;
            margin-bottom: 10px; /* Space between the logo and the text */
        }

        .brand-text {
            font-size: 1.5rem;
            font-weight: 600;
            color: #E50000;
            margin-bottom: 20px; /* Space between the brand text and the login form */
        }

        .login-text {
            color: #111;
            font-weight: 500;
            font-size: 1.1rem;
            margin-bottom: 20px;
            text-transform: capitalize;
        }

        .login-text:hover {
            animation: rubberBand 2s ease reverse;
        }

        @keyframes rubberBand {
            from { transform: scale3d(1, 1, 1); }
            30% { transform: scale3d(1.25, 0.75, 1); }
            40% { transform: scale3d(0.75, 1.25, 1); }
            50% { transform: scale3d(1.15, 0.85, 1); }
            65% { transform: scale3d(.95, 1.05, 1); }
            75% { transform: scale3d(1.05, .95, 1); }
            to { transform: scale3d(1, 1, 1); }
        }

        .container .input-group {
            width: 100%;
            height: 50px;
            margin-bottom: 25px;
        }

        .container .input-group input {
            width: 100%;
            height: 100%;
            border: 2px solid #e7e7e7;
            padding: 15px 20px;
            font-size: 1rem;
            border-radius: 30px;
            background: transparent;
            outline: none;
            transition: .3s;
        }

        .container .input-group input:focus,
        .container .input-group input:valid {
            border-color: #a29bfe;
        }

        .container .input-group .btn {
            display: block;
            width: 100%;
            padding: 15px 20px;
            text-align: center;
            border: none;
            background: #a29bfe;
            outline: none;
            border-radius: 30px;
            font-size: 1.2rem;
            color: #FFF;
            cursor: pointer;
            transition: .3s;
        }

        .container .input-group .btn:hover {
            transform: translateY(-5px);
            background: #6c5ce7;
        }

        .login-register-text {
            color: #111;
            font-weight: 600;
        }

        .login-register-text a {
            text-decoration: none;
            color: #6c5ce7;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            transition: background-color 0.3s, border-color 0.3s;
        }
        .btn-primary:hover {
            background-color: red;
            border-color: white;
        }
        .btn-primary:focus, .btn-primary.focus {
            box-shadow: 0 0 0 0.2rem rgba(38, 143, 255, 0.5);
        }
        .btn-primary:active, .btn-primary.active {
            background-color: #004085;
            border-color: #003768;
        }

        footer {
            margin-top:30px;
            color: #FFF;
            text-align: center;
        }

        @media (max-width: 430px) {
            .container {
                width: 300px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="img/provsulut.png" alt="Logo" class="login-logo"><br>
        <div class="brand-text">E-Arsip Pemotda</div>
        <div class="login-text">Silahkan Login Terlebih Dahulu</div>

       
        <?php
        if (isset($_SESSION['login_error'])) {
            echo '<div class="alert alert-danger" role="alert">' . $_SESSION['login_error'] . '</div>';
            unset($_SESSION['login_error']);
        }

        if (isset($_GET['logout']) && $_GET['logout'] == 'success') {
            echo '<script>alert("Anda telah berhasil logout. Sampai jumpa!");</script>';
        }
        ?>

<form method="post" action="login.php">
    <div class="input-group mb-4">
        <input type="text" id="username" name="username" class="form-control form-control-lg" placeholder="Username" required>
    </div>
    <div class="input-group mb-4 position-relative">
        <input type="password" id="password" name="password" class="form-control form-control-lg" placeholder="Password" required>
        <span id="password-toggle-icon" class="fa fa-eye position-absolute" style="top: 50%; right: 25px; transform: translateY(-50%); cursor: pointer;" onclick="togglePasswordVisibility()"></span>

    </div>
    <button class="btn btn-primary btn-block" type="submit">Masuk</button>
</form>

    </div>
    <footer>
        <p>&copy; 2024 E-Arsip Pemotda Sulut.</p>
    </footer>
    <script>
            function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const passwordToggleIcon = document.getElementById('password-toggle-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordToggleIcon.classList.remove('fa-eye');
                passwordToggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                passwordToggleIcon.classList.remove('fa-eye-slash');
                passwordToggleIcon.classList.add('fa-eye');
            }
        }
</script>



</body>
</html>
