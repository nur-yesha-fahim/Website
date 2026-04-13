<?php 
require 'db.php'; 
error_reporting(E_ALL & ~E_NOTICE); 
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Services | Digital Portfolio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0f172a;
            background-image: 
                radial-gradient(at 0% 0%, hsla(253, 16%, 7%, 1) 0, transparent 50%),
                radial-gradient(at 50% 0%, hsla(225, 39%, 30%, 1) 0, transparent 50%),
                radial-gradient(at 100% 0%, hsla(339, 49%, 30%, 1) 0, transparent 50%);
            background-attachment: fixed;
            color: #f1f5f9;
        }

        .glass {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        /* Fixed the corner bug by ensuring the transition doesn't leak */
        .service-card {
            isolation: isolate;
            backface-visibility: hidden;
        }
    </style>
</head>

<body class="min-h-screen">

    <nav class="fixed top-0 w-full z-50 px-6 py-4">
        <div class="max-w-6xl mx-auto glass rounded-full px-8 py-3 flex justify-between items-center text-sm font-semibold border-white/10">
            <a href="#" class="text-xl font-bold tracking-tight">PORTFOLIO</a>
            <div class="hidden md:flex items-center space-x-8">
                <a href="home.php" class="hover:text-blue-400 transition">Home</a>
                <a href="#services" class="hover:text-blue-400 transition">Services</a>
                <a href="#developers" class="hover:text-blue-400 transition">Team</a>
                <a href="#reviews" class="hover:text-blue-400 transition">Reviews</a>
                <a href="login.php" class="border border-white/20 px-6 py-2 rounded-full hover:bg-blue-600 hover:text-white transition">Sign In</a>
            </div>
            <button class="md:hidden text-2xl">☰</button>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-6 pt-32 pb-20">
        
        <div class="mb-12">
            <h1 class="text-5xl md:text-6xl lg:text-7xl font-black mb-4 tracking-tighter italic leading-[1.1] py-2 overflow-visible">
                Full <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-500 px-2 pb-2 mt-1 inline-block overflow-visible" style="vertical-align: top;">Solutions</span>
            </h1>
            <p class="text-slate-400 max-w-2xl text-lg leading-relaxed">
                Browse our complete list of technical and creative services.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php
            try {
                $stmt = $pdo->query("SELECT * FROM services ORDER BY id DESC");
                while ($row = $stmt->fetch()) { 
                ?>
                
                <div class="service-card group flex flex-col rounded-[2.5rem] overflow-hidden transition-all duration-500 hover:scale-[1.03] border border-white/5 bg-[#111827]/50">
                    
                    <div class="h-48 relative bg-[#1e293b] overflow-hidden">
                        <img src="<?= htmlspecialchars($row['image']) ?>" 
                             class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" 
                             onerror="this.src='https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=500&q=80'">
                        
                        <div class="absolute top-5 right-5 bg-black/40 backdrop-blur-md px-3 py-1 rounded-full border border-white/10 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span class="text-[9px] font-bold text-emerald-400 uppercase tracking-widest">Available</span>
                        </div>
                    </div>

                    <div class="glass p-8 flex flex-col flex-grow border-none backdrop-blur-none bg-transparent">
                        <h3 class="text-xl font-bold text-white mb-2 group-hover:text-blue-400 transition-colors tracking-tight">
                            <?= htmlspecialchars($row['title']) ?>
                        </h3>
                        
                        <p class="text-slate-400 text-[13px] leading-relaxed mb-8 line-clamp-2">
                            <?= htmlspecialchars($row['description']) ?>
                        </p>
                        
                        <div class="mt-auto flex justify-between items-center">
                            <div>
                                <p class="text-blue-500 text-xl font-black mb-0.5">
                                    $<?= number_format($row['price'], 2) ?>
                                </p>
                                <p class="text-slate-500 text-[10px] uppercase tracking-widest flex items-center gap-1">
                                    <i class="fa-regular fa-clock"></i> <?= htmlspecialchars($row['days'] ?? 5) ?> Days
                                </p>
                            </div>

                            <a href="view_more.php?id=<?= $row['id'] ?>" 
                               class="bg-[#1e293b] hover:bg-blue-600 text-white px-6 py-3 rounded-xl text-[11px] font-bold uppercase tracking-widest border border-white/5 transition-all">
                                View More
                            </a>
                        </div>
                    </div>
                </div>

            <?php } } catch (PDOException $e) { } ?>
        </div>
    </main>

    <footer class="border-t border-white/5 py-12 text-center text-slate-600 text-[10px] tracking-[0.4em] uppercase font-bold">
        &copy; 2026 DIGITAL PORTFOLIO. ALL RIGHTS RESERVED.
    </footer>

</body>
</html>