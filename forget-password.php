<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

// ডাটাবেস কানেকশন
$host = 'localhost'; $dbname = 'd_website'; $user = 'root'; $pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { die("Connection failed"); }

$message = ""; $status = ""; $view = "request_email"; 

// চেক করা হচ্ছে ইউজার কি ইমেইলের লিঙ্ক থেকে এসেছে?
if (isset($_GET['token'])) {
    $token_url = $_GET['token'];
    $session_data = $_SESSION['reset_data'] ?? null;

    if ($session_data && $token_url === $session_data['token'] && time() <= $session_data['expiry']) {
        $view = "reset_form";
    } else {
        $message = "Invalid or expired reset link!";
        $view = "request_email";
    }
}

// ধাপ ১: ইমেইল সাবমিট করলে লিঙ্ক পাঠানো
if (isset($_POST['send_link'])) {
    $email = $_POST['email'];
    $stmt = $pdo->prepare("SELECT u_name FROM user WHERE u_email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        $token = bin2hex(random_bytes(32));
        $expiry = time() + 1200; // ২০ মিনিট সময়
        $_SESSION['reset_data'] = ['email' => $email, 'token' => $token, 'expiry' => $expiry];

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'nurfahim58@gmail.com'; 
            $mail->Password = 'ufnp icbd fnod wulu'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('nurfahim58@gmail.com', 'Secure Portal');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            
            // ফাইলটি নিজের নামেই লিঙ্ক জেনারেট করবে
            $link = "http://localhost" . $_SERVER['PHP_SELF'] . "?token=$token";
            $mail->Body = "Click here to reset your password: <a href='$link'>$link</a><br>Expires in 20 mins.";

            $mail->send();
            $message = "Success! Reset link sent to your email.";
            $status = "success";
        } catch (Exception $e) { $message = "Mail error. Check credentials."; }
    } else {
        $message = "This email is not registered!";
    }
}

// ধাপ ২: নতুন পাসওয়ার্ড সাবমিট করলে আপডেট করা
if (isset($_POST['update_password'])) {
    $new_pass = $_POST['new_pass'];
    $conf_pass = $_POST['conf_pass'];
    $session_data = $_SESSION['reset_data'] ?? null;

    if ($session_data && $new_pass === $conf_pass) {
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE user SET u_pass = ? WHERE u_email = ?");
        $stmt->execute([$hashed, $session_data['email']]);
        
        unset($_SESSION['reset_data']);
        $view = "success_msg";
    } else {
        $message = "Passwords do not match!";
        $view = "reset_form";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Recovery | Portfolio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; background-image: radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), radial-gradient(at 50% 0%, hsla(225,39%,30%,1) 0, transparent 50%), radial-gradient(at 100% 0%, hsla(339,49%,30%,1) 0, transparent 50%); background-attachment: fixed; }
        .glass { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.1); }
    </style>
</head>
<body class="text-slate-200 min-h-screen flex items-center justify-center p-6 text-center">

    <div class="glass w-full max-w-md p-8 rounded-3xl">
        
        <?php if($message): ?>
            <div class="mb-4 p-2 text-xs rounded border <?= $status == 'success' ? 'bg-emerald-500/10 border-emerald-500/50 text-emerald-400' : 'bg-red-500/10 border-red-500/50 text-red-400' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <?php if($view == "request_email"): ?>
            <h2 class="text-2xl font-bold mb-2">Forgot Password?</h2>
            <p class="text-xs text-slate-400 mb-6">Enter email to get a secure reset link.</p>
            <form method="POST" class="space-y-4">
                <input type="email" name="email" required placeholder="Registered Email" class="w-full bg-slate-950 border border-slate-700 rounded-lg p-2.5 text-sm outline-none focus:border-blue-500 text-white">
                <button type="submit" name="send_link" class="w-full bg-blue-600 hover:bg-blue-500 text-white py-2.5 rounded-lg font-bold text-sm transition">Send Reset Link</button>
            </form>

        <?php elseif($view == "reset_form"): ?>
            <h2 class="text-2xl font-bold mb-2">Set New Password</h2>
            <p class="text-xs text-slate-400 mb-6">Create a strong password for your account.</p>
            <form method="POST" class="space-y-4 text-left">
                <div>
                    <label class="text-[10px] text-slate-400 ml-1">NEW PASSWORD</label>
                    <input type="password" name="new_pass" required class="w-full bg-slate-950 border border-slate-700 rounded-lg p-2.5 text-sm outline-none focus:border-blue-500 text-white">
                </div>
                <div>
                    <label class="text-[10px] text-slate-400 ml-1">CONFIRM PASSWORD</label>
                    <input type="password" name="conf_pass" required class="w-full bg-slate-950 border border-slate-700 rounded-lg p-2.5 text-sm outline-none focus:border-blue-500 text-white">
                </div>
                <button type="submit" name="update_password" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white py-2.5 rounded-lg font-bold text-sm transition mt-2">Update Password</button>
            </form>

        <?php elseif($view == "success_msg"): ?>
            <i class="fa-solid fa-circle-check text-5xl text-emerald-500 mb-4"></i>
            <h2 class="text-xl font-bold mb-2">Updated!</h2>
            <p class="text-sm text-slate-400 mb-6">Your password has been changed. You can login now.</p>
            <a href="login.php" class="inline-block w-full bg-white text-slate-950 py-2.5 rounded-lg font-bold text-sm">Login Now</a>
        <?php endif; ?>

        <div class="mt-6 text-xs text-slate-500">
            <a href="login.php" class="hover:text-blue-400"><i class="fa-solid fa-arrow-left mr-1"></i> Back to Login</a>
        </div>
    </div>

</body>
</html>