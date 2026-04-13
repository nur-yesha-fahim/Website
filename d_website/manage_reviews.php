<?php
session_start();
require 'db.php';

// --- ১. ডিলিট লজিক (Single and Multiple) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $response = ['status' => 'error', 'message' => 'Invalid action'];

    if ($_POST['action'] === 'delete_reviews' && isset($_POST['ids'])) {
        $ids = is_array($_POST['ids']) ? $_POST['ids'] : [$_POST['ids']];
        $ids = array_map('intval', $ids); // Security: integer casting

        if (!empty($ids)) {
            try {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $stmt = $pdo->prepare("DELETE FROM reviews WHERE id IN ($placeholders)");
                if ($stmt->execute($ids)) {
                    $response = ['status' => 'success'];
                }
            } catch (PDOException $e) {
                $response = ['status' => 'error', 'message' => $e->getMessage()];
            }
        }
    }
    
    // AJAX রিকোয়েস্ট হলে JSON রিটার্ন করবে এবং স্ক্রিপ্ট বন্ধ হবে
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// --- ২. ডাটা রিড লজিক ---
try {
    $stmt = $pdo->query("SELECT * FROM reviews ORDER BY id DESC");
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Matrix Sync Failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Control Center</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; background-image: radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), radial-gradient(at 100% 0%, hsla(225,39%,30%,1) 0, transparent 50%); background-attachment: fixed; }
        .glass { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.08); }
        .custom-check { width: 18px; height: 18px; cursor: pointer; accent-color: #ef4444; }
    </style>
</head>
<body class="text-slate-200 min-h-screen p-6 md:p-12">

    <div class="max-w-5xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-center mb-10 gap-6">
            <div class="flex items-center gap-4">
                <a href="admin.php" class="w-10 h-10 glass rounded-xl flex items-center justify-center text-slate-500 hover:text-white transition"><i class="fa-solid fa-arrow-left"></i></a>
                <h2 class="text-3xl font-black text-white italic tracking-tighter">Review <span class="text-red-500">Purge</span></h2>
            </div>
            
            <button id="batchDeleteBtn" onclick="openModal('multiple')" class="hidden bg-red-600 hover:bg-red-500 text-white px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all shadow-lg shadow-red-900/40">
                Delete all (<span id="countDisplay">0</span>)
            </button>
        </div>

        <div class="glass rounded-[2.5rem] overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-slate-500 text-[10px] uppercase tracking-[0.3em] font-black bg-white/[0.02]">
                        <th class="px-8 py-6 w-10"><input type="checkbox" id="selectAll" class="custom-check"></th>
                        <th class="px-8 py-6">Identity</th>
                        <th class="px-8 py-6">Message</th>
                        <th class="px-8 py-6 text-right">Operation</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <?php if (empty($reviews)): ?>
                        <tr><td colspan="4" class="px-8 py-20 text-center text-slate-600 font-bold uppercase tracking-widest text-[10px]">Matrix is empty</td></tr>
                    <?php else: ?>
                        <?php foreach ($reviews as $row): ?>
                        <tr class="border-t border-white/5 hover:bg-white/[0.02] transition-colors">
                            <td class="px-8 py-6"><input type="checkbox" value="<?= $row['id'] ?>" class="review-id-cb custom-check"></td>
                            <td class="px-8 py-6 flex items-center gap-3">
                                <img src="<?= $row['client_image'] ?>" class="w-8 h-8 rounded-full border border-white/10">
                                <span class="font-bold text-white italic"><?= htmlspecialchars($row['client_name']) ?></span>
                            </td>
                            <td class="px-8 py-6 text-slate-400 text-xs italic truncate max-w-xs"><?= htmlspecialchars($row['comment']) ?></td>
                            <td class="px-8 py-6 text-right">
                                <button onclick="openModal(<?= $row['id'] ?>)" class="text-slate-600 hover:text-red-500 transition-colors"><i class="fa-solid fa-trash-can"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="confirmModal" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 hidden items-center justify-center p-6">
        <div class="glass max-w-sm w-full p-10 rounded-[3rem] text-center border-red-500/20 shadow-2xl">
            <div class="w-20 h-20 bg-red-500/10 rounded-full flex items-center justify-center text-red-500 text-3xl mx-auto mb-6"><i class="fa-solid fa-skull-crossbones"></i></div>
            <h3 class="text-xl font-black text-white italic mb-2">Do you want to Delete ?</h3>
            <p class="text-slate-400 text-xs leading-relaxed mb-8">This data will be deleted from the all records permanently.</p>
            <div class="flex gap-4">
                <button onclick="closeModal()" class="flex-1 px-6 py-4 rounded-2xl glass text-[10px] font-bold uppercase tracking-widest text-slate-400 hover:text-white transition">Cancel</button>
                <button id="confirmBtn" class="flex-1 px-6 py-4 rounded-2xl bg-red-600 text-white text-[10px] font-bold uppercase tracking-widest shadow-lg shadow-red-900/40">Delete</button>
            </div>
        </div>
    </div>

    <script>
    const selectAll = document.getElementById('selectAll');
    const cbs = document.querySelectorAll('.review-id-cb');
    const batchBtn = document.getElementById('batchDeleteBtn');
    let currentTarget = null;

    // ১. চেকিবক্স কন্ট্রোল
    selectAll.onchange = (e) => {
        cbs.forEach(cb => cb.checked = e.target.checked);
        updateUI();
    };
    cbs.forEach(cb => cb.onchange = updateUI);

    function updateUI() {
        const count = document.querySelectorAll('.review-id-cb:checked').length;
        document.getElementById('countDisplay').innerText = count;
        batchBtn.classList.toggle('hidden', count === 0);
    }

    // ২. মোডাল কন্ট্রোল
    function openModal(id) {
        currentTarget = id;
        document.getElementById('confirmModal').classList.replace('hidden', 'flex');
        document.getElementById('confirmBtn').onclick = processDelete;
    }

    function closeModal() {
        document.getElementById('confirmModal').classList.replace('flex', 'hidden');
    }

    // ৩. ডিলিট প্রসেসিং (AJAX to same file)
    function processDelete() {
        let ids = (currentTarget === 'multiple') 
                  ? Array.from(document.querySelectorAll('.review-id-cb:checked')).map(cb => cb.value)
                  : [currentTarget];

        let formData = new URLSearchParams();
        formData.append('action', 'delete_reviews');
        ids.forEach(id => formData.append('ids[]', id));

        fetch('manage_reviews.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') location.reload();
            else alert(data.message);
        });
    }
    </script>
</body>
</html>