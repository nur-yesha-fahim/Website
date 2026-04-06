<?php
// ১. ডাটাবেস কানেকশন গ্লোবাল করা যাতে ড্যাশবোর্ড থেকে পায়
global $pdo;

// যদি কোনো কারণে $pdo না পায়, তবে সরাসরি db.php থেকে কানেক্ট করবে
if (!isset($pdo)) {
    require 'db.php';
}
?>

<style>
    /* আপনার অরিজিনাল ডিজাইন অক্ষুণ্ণ রাখা হয়েছে */
    .dark-glass {
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(16px) saturate(180%);
        -webkit-backdrop-filter: blur(16px) saturate(180%);
        border: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.8);
    }

    .glass-float {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.15);
        box-shadow: 0 4px 15px 0 rgba(0, 0, 0, 0.4);
    }

    .service-card:hover {
        transform: translateY(-5px);
        border: 1px solid rgba(59, 130, 246, 0.4);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .modal-animate {
        animation: modalFade 0.3s ease-out;
    }

    @keyframes modalFade {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }
</style>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
    <?php
    // ডেটাবেস থেকে এভেইলঅ্যাবল সার্ভিসগুলো নিয়ে আসা
    try {
        $stmt = $pdo->query("SELECT * FROM services WHERE status = 'available' ORDER BY id DESC");
        $services = $stmt->fetchAll();

        if (count($services) > 0) {
            foreach ($services as $row) {
    ?>
    <div class="dark-glass service-card rounded-[2rem] overflow-hidden relative group p-2">
        
        <div class="absolute top-5 right-5 glass-float px-3 py-1 rounded-full text-[10px] font-bold text-emerald-400 z-10 tracking-widest uppercase">
            ● Available
        </div>
        
        <div class="relative h-48 w-full rounded-[1.5rem] overflow-hidden mb-4 bg-slate-800">
            <img src="<?= htmlspecialchars($row['image']) ?>" 
                 alt="Service Image" 
                 onerror="this.src='https://via.placeholder.com/400x300/0f172a/64748b?text=Service+Image'"
                 class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition duration-500">
            <div class="absolute inset-0 bg-gradient-to-t from-[#0f172a] to-transparent opacity-60"></div>
        </div>
        
        <div class="px-5 pb-6">
            <h3 class="text-xl font-bold text-white mb-2"><?= htmlspecialchars($row['title']) ?></h3>
            <p class="text-slate-400 text-sm mb-5 line-clamp-2 leading-relaxed">
                <?= htmlspecialchars($row['description']) ?>
            </p>
            
            <div class="flex justify-between items-center mb-6">
                <div class="text-xs font-medium text-slate-300">
                    <span class="block text-blue-400 font-bold text-lg">$<?= number_format($row['price'], 2) ?></span>
                    <span><i class="fa-solid fa-clock mr-1 text-slate-500"></i> <?= htmlspecialchars($row['delivery_time']) ?> Days</span>
                </div>
                
                <button onclick='showDetails(<?= json_encode($row) ?>)' 
                        class="glass-float px-5 py-2.5 rounded-xl text-xs font-bold text-white hover:bg-white/10 transition uppercase tracking-wider">
                    View More
                </button>
            </div>
        </div>
    </div>
    <?php 
            }
        } else {
            echo "<div class='col-span-full py-20 text-center text-slate-500'>No services available at the moment.</div>";
        }
    } catch (PDOException $e) {
        echo "<div class='col-span-full p-6 dark-glass text-red-400'>Error loading services: " . $e->getMessage() . "</div>";
    }
    ?>
</div>

<div id="serviceModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-6 bg-slate-950/90 backdrop-blur-md">
    <div class="modal-animate flex flex-col lg:flex-row gap-6 max-w-5xl w-full">
        
        <div class="dark-glass flex-[2.5] p-8 md:p-12 rounded-[2.5rem] relative border-blue-500/20">
            <button onclick="closeModal()" class="absolute top-8 right-8 text-slate-500 hover:text-white transition">
                <i class="fa-solid fa-circle-xmark text-3xl"></i>
            </button>

            <h2 id="mTitle" class="text-4xl font-black text-white mb-6 bg-gradient-to-r from-white to-slate-400 bg-clip-text text-transparent"></h2>
            <p id="mDesc" class="text-slate-400 text-lg mb-10 leading-relaxed"></p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-center text-slate-300 text-sm"><i class="fa-solid fa-circle-check text-blue-400 mr-3"></i> Premium Assets Included</div>
                <div class="flex items-center text-slate-300 text-sm"><i class="fa-solid fa-circle-check text-blue-400 mr-3"></i> Commercial Use License</div>
                <div class="flex items-center text-slate-300 text-sm"><i class="fa-solid fa-circle-check text-blue-400 mr-3"></i> 24/7 Priority Support</div>
                <div class="flex items-center text-slate-300 text-sm"><i class="fa-solid fa-circle-check text-blue-400 mr-3"></i> Multiple Revisions</div>
                <div class="flex items-center text-slate-300 text-sm"><i class="fa-solid fa-circle-check text-blue-400 mr-3"></i> Source Files Provided</div>
            </div>
        </div>

        <div class="dark-glass flex-1 p-8 rounded-[2.5rem] flex flex-col justify-center items-center text-center border-emerald-500/20">
            <div class="p-4 bg-emerald-500/10 rounded-full mb-4">
                <i class="fa-solid fa-wallet text-2xl text-emerald-400"></i>
            </div>
            <span class="text-slate-500 uppercase text-[11px] tracking-widest font-bold mb-1">Total Package</span>
            <div id="mPrice" class="text-5xl font-black text-white mb-2"></div>
            <div id="mTime" class="text-slate-400 text-xs mb-10 font-medium italic"></div>
            
            <form action="process_order.php" method="POST" class="w-full">
                <input type="hidden" name="service_id" id="mId">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white py-4 rounded-2xl font-black text-sm uppercase tracking-widest transition-all shadow-xl shadow-blue-900/40 hover:scale-[1.02] active:scale-[0.98]">
                    Order Now
                </button>
            </form>
            <p class="text-[10px] text-slate-500 mt-4 px-4 leading-tight">Securely order this service using our encrypted portal.</p>
        </div>

    </div>
</div>

<script>
    function showDetails(data) {
        document.getElementById('mTitle').innerText = data.title;
        document.getElementById('mDesc').innerText = data.description;
        // নম্বর ফরম্যাটিং সহ প্রাইস দেখানো
        document.getElementById('mPrice').innerText = '$' + parseFloat(data.price).toLocaleString();
        document.getElementById('mTime').innerText = 'Expected delivery in ' + data.delivery_time + ' days';
        document.getElementById('mId').value = data.id;
        
        const modal = document.getElementById('serviceModal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden'; // স্ক্রলিং বন্ধ
    }

    function closeModal() {
        const modal = document.getElementById('serviceModal');
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto'; // স্ক্রলিং চালু
    }

    // মোডালের বাইরে ক্লিক করলে বন্ধ হওয়া
    window.onclick = function(event) {
        const modal = document.getElementById('serviceModal');
        if (event.target == modal) {
            closeModal();
        }
    }
</script>