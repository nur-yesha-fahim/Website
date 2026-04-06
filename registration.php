<?php
session_start();

// 1. IMPORT PHPMAILER
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

// 2. DATABASE CONFIGURATION
$host = 'localhost';
$dbname = 'd_website';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$message = "";
$view = "register"; 

// --- BRANCH 1: User Submits Registration (With Email Pre-Check) ---
if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Security Check 1: Passwords Match
    if ($password !== $confirm_password) {
        $message = "Error: Passwords do not match!";
    } else {
        // Security Check 2: Email Uniqueness (Before sending email)
        $stmt = $pdo->prepare("SELECT u_email FROM user WHERE u_email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $message = "Error: This email is already registered!";
        } else {
            // Proceed to generate OTP
            $otp = rand(100000, 999999);
            $_SESSION['pending'] = [
                'u_name'  => $username,
                'u_email' => $email,
                'u_pass'  => password_hash($password, PASSWORD_DEFAULT),
                'otp'     => $otp,
                'time'    => time()
            ];

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'nurfahim58@gmail.com'; 
                $mail->Password   = 'ufnp icbd fnod wulu'; 
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('nurfahim58@gmail.com', 'Secure Portal');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Your Verification Code';
                $mail->Body    = "Your 6-digit verification code is: <b style='font-size: 20px;'>$otp</b>. <br>It will expire in 2 minutes.";

                $mail->send();
                $view = "verify"; 
            } catch (Exception $e) {
                $message = "Error: Email could not be sent. " . $mail->ErrorInfo;
            }
        }
    }
}

// --- BRANCH 2: User Submits Verification ---
if (isset($_POST['verify'])) {
    $pending = $_SESSION['pending'] ?? null;
    $user_otp = $_POST['otp_code'];

    if ($pending && ($user_otp == $pending['otp'])) {
        // Security Check 3: 2-Minute Expiry (120 seconds)
        if ((time() - $pending['time']) <= 120) {
            try {
                $sql = "INSERT INTO user (u_name, u_email, u_pass) VALUES (?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$pending['u_name'], $pending['u_email'], $pending['u_pass']]);
                
                unset($_SESSION['pending']);
                $view = "success";
            } catch (PDOException $e) {
                // Secondary check for race conditions
                if ($e->errorInfo[1] == 1062) {
                    $message = "Error: Email already exists!";
                } else {
                    $message = "Error: Database problem.";
                }
                $view = "register";
                unset($_SESSION['pending']);
            }
        } else {
            $message = "Error: Time expired! Please register again.";
            $view = "register";
            unset($_SESSION['pending']);
        }
    } else {
        $message = "Error: Invalid verification code!";
        $view = "verify";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Register | Portfolio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: #0f172a; 
            background-image: radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), 
                              radial-gradient(at 50% 0%, hsla(225,39%,30%,1) 0, transparent 50%), 
                              radial-gradient(at 100% 0%, hsla(339,49%,30%,1) 0, transparent 50%); 
            background-attachment: fixed; 
        }
        .glass { 
            background: rgba(255, 255, 255, 0.03); 
            backdrop-filter: blur(12px); 
            -webkit-backdrop-filter: blur(12px); 
            border: 1px solid rgba(255, 255, 255, 0.1); 
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37); 
        }
    </style>
</head>
<body class="text-slate-200 min-h-screen flex items-center justify-center p-6">

    <div class="glass w-full max-w-md p-8 rounded-3xl">
        
        <?php if($message): ?>
            <div class="mb-4 p-2 text-center rounded bg-red-500/10 border border-red-500/50 text-red-400 text-xs">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <?php if($view == "register"): ?>
            <h2 class="text-2xl font-bold mb-6 text-center">Create Account</h2>
            
            <form method="POST" class="space-y-3">
                <div>
                    <label class="block text-xs text-slate-400 mb-1">User Name</label>
                    <input type="text" name="username" required class="w-full bg-slate-950 border border-slate-700 rounded-lg p-2 text-sm focus:border-blue-500 outline-none transition text-white">
                </div>

                <div>
                    <label class="block text-xs text-slate-400 mb-1">Email Address</label>
                    <input type="email" name="email" required class="w-full bg-slate-950 border border-slate-700 rounded-lg p-2 text-sm focus:border-blue-500 outline-none transition text-white">
                </div>

                <div class="relative">
                    <label class="block text-xs text-slate-400 mb-1">Password</label>
                    <div class="relative flex items-center">
                        <input type="password" id="password" name="password" required class="w-full bg-slate-950 border border-slate-700 rounded-lg p-2 text-sm pr-10 focus:border-blue-500 outline-none transition text-white">
                        <button type="button" onclick="togglePass('password', 'eye1')" class="absolute right-0 w-10 h-full flex items-center justify-center text-slate-500 hover:text-white">
                            <i id="eye1" class="fa-solid fa-eye text-sm"></i>
                        </button>
                    </div>
                </div>

                <div class="relative">
                    <label class="block text-xs text-slate-400 mb-1">Confirm Password</label>
                    <div class="relative flex items-center">
                        <input type="password" id="confirm-password" name="confirm_password" required class="w-full bg-slate-950 border border-slate-700 rounded-lg p-2 text-sm pr-10 focus:border-blue-500 outline-none transition text-white">
                        <button type="button" onclick="togglePass('confirm-password', 'eye2')" class="absolute right-0 w-10 h-full flex items-center justify-center text-slate-500 hover:text-white">
                            <i id="eye2" class="fa-solid fa-eye text-sm"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" name="register" class="w-full bg-blue-600 hover:bg-blue-500 text-white py-2.5 rounded-lg font-bold text-sm transition shadow-lg shadow-blue-900/20 mt-3">
                    Register Now
                </button>
            </form>

            <p class="text-center text-xs text-slate-500 mt-5">
                Already have an account? <a href="login.php" class="text-blue-400 hover:underline">Log in</a>
            </p>

        <?php elseif($view == "verify"): ?>
            <h2 class="text-2xl font-bold mb-2 text-center">Verify Email</h2>
            <p class="text-xs text-slate-400 text-center mb-2">Enter the code sent to your inbox.</p>
            
            <div class="text-center mb-6">
                <span class="text-[10px] uppercase tracking-wider text-slate-500">Code expires in:</span>
                <div id="timer" class="text-xl font-mono font-bold text-blue-400">02:00</div>
            </div>
            
            <form method="POST" class="space-y-4">
                <input type="text" name="otp_code" placeholder="000000" maxlength="6" autofocus required 
                       class="w-full bg-slate-950 border border-slate-700 rounded-lg p-3 text-center text-2xl tracking-[0.3em] outline-none focus:border-blue-500 font-mono text-white">
                <button type="submit" name="verify" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white py-2.5 rounded-lg font-bold text-sm transition">
                    Verify Code
                </button>
            </form>

            <script>
                const startTime = <?= $_SESSION['pending']['time'] ?>;
                const timerDisplay = document.getElementById('timer');

                function updateTimer() {
                    const now = Math.floor(Date.now() / 1000);
                    const diff = 120 - (now - startTime);

                    if (diff <= 0) {
                        timerDisplay.innerText = "00:00";
                        timerDisplay.classList.replace('text-blue-400', 'text-red-500');
                        alert("Time expired! Redirecting to registration.");
                        window.location.href = window.location.pathname; // রিডাইরেক্ট টু ফ্রেশ রেজিস্ট্রেশন
                    } else {
                        const mins = Math.floor(diff / 60).toString().padStart(2, '0');
                        const secs = (diff % 60).toString().padStart(2, '0');
                        timerDisplay.innerText = `${mins}:${secs}`;
                        if(diff < 30) timerDisplay.classList.add('text-red-400');
                    }
                }
                setInterval(updateTimer, 1000);
                updateTimer();
            </script>

        <?php elseif($view == "success"): ?>
            <div class="text-center py-6">
                <div class="text-5xl mb-4 text-emerald-500">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <h2 class="text-xl font-bold mb-2 text-white">Success!</h2>
                <p class="text-sm text-slate-400 mb-6">Your account has been verified and created successfully.</p>
                <a href="login.php" class="inline-block w-full bg-white text-slate-950 py-2.5 rounded-lg font-bold text-sm hover:bg-slate-200 transition">Login Now</a>
            </div>
        <?php endif; ?>

    </div>

    <script>
        function togglePass(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === "password") {
                input.type = "text";
                icon.classList.replace("fa-eye", "fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.replace("fa-eye-slash", "fa-eye");
            }
        }
    </script>
</body>
</html>