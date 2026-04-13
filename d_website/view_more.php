<?php
// ১. সেশন স্টার্ট করা
session_start();

require 'db.php'; 

// ২. ডাটা রিটার্নিং লজিক
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$service = null;

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ? AND status = 'available'");
    $stmt->execute([$id]);
    $service = $stmt->fetch();
}

if (!$service) { 
    header("Location: index.php"); 
    exit; 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($service['title']) ?> - Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #020617; color: white; font-family: 'Inter', sans-serif; }
        .dark-glass {
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.5);
        }
    </style>
</head>
<body class="min-h-screen p-6 md:p-12">

    <div class="max-w-4xl mx-auto">
        
        <a href="javascript:history.back()" class="inline-flex items-center text-slate-500 hover:text-blue-400 mb-5 text-[12px] uppercase tracking-widest font-bold transition group">
            <i class="fa-solid fa-chevron-left mr-2 group-hover:-translate-x-1 transition-transform"></i>
            Back
        </a>

        <div class="flex flex-col md:flex-row gap-6 items-start">
            
            <div class="dark-glass flex-[2] p-6 rounded-3xl border-white/5">
                <div class="relative h-[200px] md:h-[280px] w-full rounded-2xl overflow-hidden mb-6">
                    <img src="<?= htmlspecialchars($service['image']) ?>" 
                         onerror="this.src='https://via.placeholder.com/800x450/0f172a/64748b?text=Service'"
                         class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-[#020617]/60 to-transparent"></div>
                </div>

                <h2 class="text-2xl md:text-3xl font-bold text-white mb-3 tracking-tight">
                    <?= htmlspecialchars($service['title']) ?>
                </h2>
                
                <p class="text-slate-400 text-[13px] md:text-sm mb-8 leading-relaxed">
                    <?= nl2br(htmlspecialchars($service['description'])) ?>
                </p>
                
                <div class="pt-6 border-t border-white/5">
                    <h4 class="text-[10px] font-bold uppercase tracking-[0.2em] text-blue-500 mb-4">Included with this service:</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3">
                        <?php 
                        if (!empty($service['features'])) {
                            $features = explode(',', $service['features']); 
                            foreach ($features as $feature) {
                                if (trim($feature) !== "") {
                        ?>
                            <div class="flex items-center text-slate-300 text-[12px] group/item">
                                <div class="w-5 h-5 rounded-full bg-blue-500/10 flex items-center justify-center mr-3 shrink-0">
                                    <i class="fa-solid fa-check text-blue-400 text-[10px]"></i> 
                                </div>
                                <span class="truncate"><?= htmlspecialchars(trim($feature)) ?></span>
                            </div>
                        <?php 
                                }
                            }
                        } 
                        ?>
                    </div>
                </div>
            </div>

            <div class="dark-glass w-full md:w-[280px] p-8 rounded-3xl text-center border-white/5 md:sticky md:top-10">
                <p class="text-slate-500 uppercase text-[9px] tracking-[0.2em] font-bold mb-1">Total Investment</p>
                <div class="text-4xl font-bold text-white mb-1 tracking-tighter">
                    $<?= number_format($service['price'], 2) ?>
                </div>
                <div class="text-slate-500 text-[11px] mb-8">
                    <i class="fa-regular fa-clock mr-1"></i> <?= htmlspecialchars($service['delivery_time']) ?> Day Turnaround
                </div>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <form action="process_order.php" method="POST" class="w-full">
                        <input type="hidden" name="service_id" value="<?= $service['id'] ?>">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white py-4 rounded-xl text-[12px] font-bold uppercase tracking-widest transition-all active:scale-95 shadow-xl shadow-blue-900/40">
                            Order Now
                        </button>
                    </form>
                <?php else: ?>
                    <a href="login.php" class="inline-block w-full bg-blue-600 hover:bg-blue-500 text-white py-4 rounded-xl text-[12px] font-bold uppercase tracking-widest transition-all active:scale-95 shadow-xl shadow-blue-900/40 text-center">
                        Order Now
                    </a>
                <?php endif; ?>
                
                <div class="mt-6 pt-6 border-t border-white/5 text-slate-600">
                    <div class="flex items-center justify-center gap-2">
                        <i class="fa-solid fa-shield-check text-[10px]"></i>
                        <span class="text-[9px] uppercase tracking-tighter font-bold">Secure Portal</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

</body>
</html>