<?php
// ১. ডাটাবেস কানেকশন গ্লোবাল করা যাতে ড্যাশবোর্ড থেকে পায়
global $pdo;

if (!isset($pdo)) {
    require 'db.php';
}
?>

<div class="max-w-5xl mx-auto px-2 md:px-0">
    <h2 class="text-xl md:text-2xl font-black text-white mb-6 md:mb-8 tracking-tight">Requested Services</h2>

    <div class="dark-glass rounded-[1.5rem] md:rounded-[2rem] overflow-hidden border border-white/5 shadow-2xl bg-white/[0.02] backdrop-blur-md">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse min-w-[600px] md:min-w-full">
                <thead class="text-[10px] text-slate-500 uppercase tracking-[0.2em] bg-white/5">
                    <tr>
                        <th class="p-4 md:p-6 font-black">Service Information</th>
                        <th class="p-4 md:p-6 font-black">Current Status</th>
                        <th class="p-4 md:p-6 font-black text-right">Delivery Progress</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <?php
                    try {
                        $stmt = $pdo->prepare("
                            SELECT r.*, s.title 
                            FROM requests r 
                            JOIN services s ON r.service_id = s.id 
                            WHERE r.user_id = ? AND r.status != 'completed'
                            ORDER BY r.id DESC
                        ");
                        $stmt->execute([$_SESSION['user_id']]);
                        $requests = $stmt->fetchAll();

                        if (count($requests) > 0) {
                            foreach ($requests as $row) {
                                $created_at = isset($row['created_at']) ? $row['created_at'] : date('Y-m-d H:i:s', strtotime('-1 day'));
                                $start = strtotime($created_at); 
                                $end = strtotime($row['expiry_time']);  
                                $now = time();                                          
                                
                                $total = $end - $start;
                                $elapsed = $now - $start;
                                
                                $percent = ($total > 0) ? round(($elapsed / $total) * 100) : 0;
                                $percent = max(0, min(100, $percent)); 
                    ?>
                    <tr class="border-b border-white/5 hover:bg-white/[0.03] transition-colors group">
                        <td class="p-4 md:p-6"> 
                            <div class="flex flex-col min-w-0">
                                <span class="text-white font-bold text-sm md:text-base group-hover:text-blue-400 transition-colors truncate max-w-[200px] md:max-w-none">
                                    <?= htmlspecialchars($row['title']) ?>
                                </span>
                                <span class="text-[9px] text-slate-500 mt-1 font-mono uppercase tracking-widest">
                                    ID: #<?= $row['id'] ?>
                                </span>
                            </div>
                        </td>
                        <td class="p-4 md:p-6">
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-[9px] md:text-[10px] font-black uppercase tracking-widest border shrink-0
                                <?= $row['status']=='pending' 
                                    ? 'bg-orange-500/10 text-orange-400 border-orange-500/20' 
                                    : 'bg-blue-500/10 text-blue-400 border-blue-500/20' ?>">
                                <i class="fa-solid <?= $row['status']=='pending' ? 'fa-spinner fa-spin' : 'fa-bolt' ?> mr-2"></i>
                                <?= strtoupper($row['status']) ?>
                            </span>
                        </td>
                        <td class="p-4 md:p-6 text-right font-mono">
                            <?php if ($row['status'] == 'active' && $row['expiry_time']): ?>
                                <div class="flex flex-col items-end w-32 md:w-48 ml-auto">
                                    <span class="countdown text-blue-400 font-bold text-sm md:text-base drop-shadow-[0_0_10px_rgba(59,130,246,0.3)]" 
                                          data-time="<?= $row['expiry_time'] ?>">
                                        --:--:--
                                    </span>
                                    
                                    <div class="w-full h-1 bg-white/5 rounded-full mt-2 overflow-hidden border border-white/5">
                                        <div class="h-full bg-blue-500 rounded-full shadow-[0_0_8px_rgba(59,130,246,0.8)] transition-all duration-1000" 
                                             style="width: <?= $percent ?>%">
                                        </div>
                                    </div>

                                    <div class="flex justify-between w-full mt-1.5 hidden md:flex">
                                        <span class="text-[8px] text-slate-500 uppercase tracking-widest font-black"><?= $percent ?>%</span>
                                        <span class="text-[8px] text-slate-500 uppercase tracking-tighter">Est. Delivery</span>
                                    </div>
                                </div>
                            <?php else: ?>
                                <span class="text-slate-600 italic text-[10px] tracking-widest uppercase font-black opacity-50 shrink-0">
                                    Awaiting...
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php 
                            }
                        } else {
                            echo '<tr><td colspan="3" class="p-16 text-center text-slate-500">No active requests found.</td></tr>';
                        }
                    } catch (PDOException $e) {
                        echo '<tr><td colspan="3" class="p-6 text-red-400 text-center">Error: ' . $e->getMessage() . '</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    /* টেবিল স্ক্রলবারের জন্য কাস্টম ডিজাইন */
    .custom-scrollbar::-webkit-scrollbar { height: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: rgba(255, 255, 255, 0.02); }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(59, 130, 246, 0.2); border-radius: 10px; }
</style>

<script>
    function updateCountdowns() {
        document.querySelectorAll('.countdown').forEach(el => {
            const targetStr = el.dataset.time;
            if (!targetStr) return;

            const target = new Date(targetStr).getTime();
            const now = new Date().getTime();
            const diff = target - now;

            if (diff > 0) {
                const d = Math.floor(diff / (1000 * 60 * 60 * 24));
                const h = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const s = Math.floor((diff % (1000 * 60)) / 1000);

                let timerText = "";
                if (d > 0) timerText += `${d}d `;
                timerText += `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
                
                el.innerText = timerText;
            } else {
                el.innerText = "Finalizing...";
                el.classList.replace('text-blue-400', 'text-emerald-400');
                const progressBar = el.parentElement.querySelector('.bg-blue-500');
                if(progressBar) progressBar.style.width = '100%';
            }
        });
    }

    setInterval(updateCountdowns, 1000);
    updateCountdowns();
</script>