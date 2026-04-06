<?php
global $pdo;
if (!isset($pdo)) { require 'db.php'; }
$user_id = $_SESSION['user_id'] ?? 0;
?>

<style>
    .completed-card {
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(20px) saturate(180%);
        border: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow: 0 10px 40px 0 rgba(0, 0, 0, 0.7);
    }
    .glass-modal {
        background: rgba(15, 23, 42, 0.9);
        backdrop-filter: blur(30px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .star-rating-modal {
        display: flex;
        flex-direction: row-reverse;
        justify-content: center;
        gap: 8px;
    }
    .star-rating-modal input { display: none; }
    .star-rating-modal label { font-size: 24px; color: #334155; cursor: pointer; transition: 0.2s; }
    .star-rating-modal input:checked ~ label,
    .star-rating-modal label:hover,
    .star-rating-modal label:hover ~ label { color: #fbbf24; }
</style>

<div class="max-w-4xl mx-auto">
    <h2 class="text-2xl font-black text-white mb-8 tracking-tight">Completed Projects</h2>

    <div class="space-y-4"> 
        <?php
        try {
            $stmt = $pdo->prepare("SELECT r.*, s.title, s.id as s_id FROM requests r JOIN services s ON r.service_id = s.id WHERE r.user_id = ? AND r.status = 'completed' ORDER BY r.id DESC");
            $stmt->execute([$user_id]);
            $results = $stmt->fetchAll();

            if (count($results) > 0) {
                foreach ($results as $row) {
                    $checkRev = $pdo->prepare("SELECT id FROM reviews WHERE request_id = ?");
                    $checkRev->execute([$row['id']]);
                    $reviewed = $checkRev->fetch();
        ?>
        
        <div class="completed-card p-5 rounded-[2rem] flex flex-col md:flex-row justify-between items-center gap-6 group transition-all hover:border-emerald-500/30">
            <div class="flex-1 text-center md:text-left">
                <div class="flex items-center justify-center md:justify-start gap-3 mb-2"> 
                    <span class="bg-emerald-500/10 text-emerald-400 text-[9px] font-black px-2.5 py-0.5 rounded-full uppercase tracking-widest border border-emerald-500/20">Finished</span>
                    <span class="text-slate-500 text-[10px] font-mono">#<?= $row['id'] ?></span>
                </div>
                <h3 class="text-lg font-bold text-white mb-3"><?= htmlspecialchars($row['title']) ?></h3> 
                <div class="flex justify-center md:justify-start">
                    <?php if (!empty($row['delivery_link'])): ?>
                    <a href="<?= $row['delivery_link'] ?>" target="_blank" class="px-4 py-2 rounded-xl bg-blue-500/10 text-blue-400 text-[10px] font-bold border border-blue-500/20 hover:bg-blue-600 hover:text-white transition-all">
                        <i class="fa-solid fa-download mr-2"></i> Download
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="w-full md:w-auto flex justify-center">
                <?php if (!$reviewed): ?>
                    <button onclick="openReviewModal('<?= $row['id'] ?>', '<?= $row['s_id'] ?>', '<?= htmlspecialchars($row['title']) ?>')" 
                            class="px-6 py-3 rounded-xl bg-emerald-600 text-white text-[11px] font-black uppercase tracking-widest hover:bg-emerald-500 transition-all active:scale-95 shadow-lg shadow-emerald-900/20">
                        Give Review
                    </button>
                <?php else: ?>
                    <div class="flex items-center gap-2 px-5 py-2.5 bg-white/5 rounded-xl border border-white/5">
                        <i class="fa-solid fa-heart text-emerald-500 text-[10px]"></i>
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Reviewed</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php } } } catch (PDOException $e) { echo "Error: " . $e->getMessage(); } ?>
    </div>
</div>

<div id="review-modal" class="fixed inset-0 z-[999] hidden items-center justify-center bg-slate-950/80 backdrop-blur-md p-4">
    <div class="glass-modal w-full max-w-sm p-8 rounded-[2.5rem] relative animate-in fade-in zoom-in duration-300">
        <button onclick="closeReviewModal()" class="absolute top-6 right-6 text-slate-500 hover:text-white transition-colors">
            <i class="fa-solid fa-xmark text-xl"></i>
        </button>

        <div class="text-center mb-6">
            <h4 id="m-title" class="text-white font-bold text-xl mb-1">Service Name</h4>
            <p class="text-[10px] text-slate-500 uppercase tracking-widest font-black">Your Feedback</p>
        </div>

        <form action="submit_review.php" method="POST" class="space-y-5">
            <input type="hidden" name="request_id" id="m-req-id">
            <input type="hidden" name="service_id" id="m-serv-id">
            
            <div class="star-rating-modal">
                <?php for($i=5; $i>=1; $i--): ?>
                    <input type="radio" id="ms<?= $i ?>" name="star" value="<?= $i ?>" required/>
                    <label for="ms<?= $i ?>"><i class="fa-solid fa-star"></i></label>
                <?php endfor; ?>
            </div>

            <textarea name="comment" required class="w-full bg-slate-900/50 border border-white/10 rounded-2xl p-4 text-xs text-slate-300 focus:border-emerald-500 outline-none transition" rows="3" placeholder="How was the service?"></textarea>
            
            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white py-4 rounded-2xl text-[11px] font-black uppercase tracking-widest transition-all active:scale-95 shadow-xl shadow-emerald-900/40">
                Submit Review
            </button>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById('review-modal');
    
    function openReviewModal(reqId, servId, title) {
        document.getElementById('m-req-id').value = reqId;
        document.getElementById('m-serv-id').value = servId;
        document.getElementById('m-title').innerText = title;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeReviewModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // পপআপের বাইরে ক্লিক করলে ক্লোজ হবে
    window.onclick = function(event) {
        if (event.target == modal) {
            closeReviewModal();
        }
    }
</script>