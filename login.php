<?php
session_start();

// ১. ডাটাবেস কনফিগারেশন
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
$message_type = "error"; // ডিফল্ট এরর টাইপ

// ২. লগইন লজিক
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $message = "Please fill in all fields.";
    } else {
        // ডাটাবেস থেকে ইউজার চেক
        $stmt = $pdo->prepare("SELECT * FROM user WHERE u_email = ?");
        $stmt->execute([$email]);
        $user_data = $stmt->fetch();

        if ($user_data) {
            // পাসওয়ার্ড ভেরিফিকেশন (BCRYPT Hash Check)
            if (password_verify($password, $user_data['u_pass'])) {
                // সেশন তৈরি
                $_SESSION['user_id'] = $user_data['id'];
                $_SESSION['user_name'] = $user_data['u_name'];
                
                // সফল লগইন হলে ড্যাশবোর্ডে রিডাইরেক্ট
                header("Location: dashboard.php");
                exit();
            } else {
                $message = "Invalid password. Please try again.";
            }
        } else {
            $message = "No account found with this email.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Secure Portal</title>
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
        
        <h2 class="text-2xl font-bold mb-2 text-center text-white">Welcome Back</h2>
        <p class="text-center text-xs text-slate-400 mb-8">Enter your credentials to access your account.</p>

        <?php if($message): ?>
            <div class="mb-4 p-2 text-center rounded bg-red-500/10 border border-red-500/50 text-red-400 text-xs">
                <i class="fa-solid fa-circle-exclamation mr-1"></i> <?= $message ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-xs text-slate-400 mb-1">Email Address</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500">
                        <i class="fa-solid fa-envelope text-xs"></i>
                    </span>
                    <input type="email" name="email" required placeholder="name@company.com"
                           class="w-full bg-slate-950 border border-slate-700 rounded-lg p-2.5 pl-10 text-sm focus:border-blue-500 outline-none transition text-white">
                </div>
            </div>

            <div>
                <div class="flex justify-between items-center mb-1">
                    <label class="block text-xs text-slate-400">Password</label>
                    <a href="forgot-password.php" class="text-[10px] text-blue-400 hover:underline">Forgot password?</a>
                </div>
                <div class="relative flex items-center">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500">
                        <i class="fa-solid fa-lock text-xs"></i>
                    </span>
                    <input type="password" id="password" name="password" required placeholder="••••••••"
                           class="w-full bg-slate-950 border border-slate-700 rounded-lg p-2.5 pl-10 pr-10 text-sm focus:border-blue-500 outline-none transition text-white">
                    <button type="button" onclick="togglePass('password', 'eye1')" class="absolute right-0 w-10 h-full flex items-center justify-center text-slate-500 hover:text-white transition">
                        <i id="eye1" class="fa-solid fa-eye text-xs"></i>
                    </button>
                </div>
            </div>

            <div class="flex items-center space-x-2 pb-2">
                <input type="checkbox" id="remember" class="w-3 h-3 rounded bg-slate-900 border-slate-700 text-blue-600 focus:ring-0">
                <label for="remember" class="text-[11px] text-slate-400">Remember this device</label>
            </div>

            <button type="submit" name="login" class="w-full bg-blue-600 hover:bg-blue-500 text-white py-2.5 rounded-lg font-bold text-sm transition shadow-lg shadow-blue-900/20">
                Sign In
            </button>
        </form>

        <p class="text-center text-xs text-slate-500 mt-8">
            New here? <a href="registerDesign.php" class="text-blue-400 hover:underline">Create an account</a>
        </p>
    </div>

    <script>
        // পাসওয়ার্ড দেখানো বা লুকানোর ফাংশন
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