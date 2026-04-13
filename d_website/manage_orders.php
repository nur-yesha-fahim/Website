<?php
session_start();
require 'db.php';

$orders = []; 

try {
    // Database Query - Corrected for your 'user', 'services', and 'requests' tables
    $query = "SELECT 
                requests.id, 
                requests.status, 
                requests.order_date, 
                requests.delivery_link,
                services.title as service_name, 
                services.price,
                user.u_name as username, 
                user.u_email as email 
              FROM requests
              INNER JOIN services ON requests.service_id = services.id 
              INNER JOIN user ON requests.user_id = user.id 
              ORDER BY requests.order_date DESC";
    
    $stmt = $pdo->query($query);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Manager | Studio Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { 
            font-family: 'Inter', sans-serif;
            background-color: #0f172a;
            background-image: 
                radial-gradient(at 0% 0%, hsla(253, 16%, 7%, 1) 0, transparent 50%),
                radial-gradient(at 100% 0%, hsla(225, 39%, 30%, 1) 0, transparent 50%);
            background-attachment: fixed;
            color: #f1f5f9;
        }
        .glass {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }
        /* Custom styling for the select dropdown to match glassmorphism */
        select option { background: #0f172a; color: white; }
    </style>
</head>
<body class="min-h-screen p-4 md:p-8">

    <div class="max-w-7xl mx-auto space-y-8">
        
        <div class="glass rounded-[2.5rem] p-8 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-6">
                <a href="admin.php" class="w-12 h-12 glass rounded-2xl flex items-center justify-center text-slate-400 hover:text-blue-400 transition-all">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <div>
                    <h2 class="text-3xl font-black text-white italic tracking-tight">Request Manager</h2>
                    <p class="text-blue-500 text-[10px] uppercase tracking-[0.4em] font-bold mt-1">Live Database Pipeline</p>
                </div>
            </div>
            
            <div class="glass px-6 py-3 rounded-2xl border-white/10 text-right">
                <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest">Active Entries</p>
                <p class="text-sm font-bold text-white italic" id="orderCount"><?= count($orders) ?> Requests</p>
            </div>
        </div>

        <div class="glass rounded-[2.5rem] overflow-hidden border-white/5 shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-slate-500 text-[10px] uppercase tracking-[0.4em] font-black bg-white/[0.02]">
                            <th class="px-10 py-6">ID</th>
                            <th class="px-10 py-6">Client Identity</th>
                            <th class="px-10 py-6">Service Detail</th>
                            <th class="px-10 py-6">Change Status</th>
                            <th class="px-10 py-6">Timestamp</th>
                            <th class="px-10 py-6 text-right">Operations</th>
                        </tr>
                    </thead>
                    <tbody class="text-[13px] border-t border-white/5">
                        <?php if (!empty($orders)): ?>
                            <?php foreach ($orders as $row): ?>
                                <?php 
                                    $st = strtolower($row['status']); 
                                    $statusColor = ($st == 'active') ? 'text-blue-400' : (($st == 'completed') ? 'text-emerald-400' : 'text-orange-400');
                                ?>
                                <tr class="hover:bg-white/[0.03] transition-colors group border-b border-white/5 last:border-0">
                                    <td class="px-10 py-6 font-mono text-slate-500 text-xs">#REQ-<?= $row['id'] ?></td>
                                    <td class="px-10 py-6">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-white italic"><?= htmlspecialchars($row['username']) ?></span>
                                            <span class="text-[10px] text-slate-500"><?= htmlspecialchars($row['email']) ?></span>
                                        </div>
                                    </td>
                                    <td class="px-10 py-6">
                                        <div class="flex flex-col">
                                            <span class="text-slate-200 font-semibold"><?= htmlspecialchars($row['service_name']) ?></span>
                                            <span class="text-blue-400 font-black">$<?= number_format($row['price'], 2) ?></span>
                                        </div>
                                    </td>
                                    <td class="px-10 py-6">
                                        <select onchange="updateStatus(<?= $row['id'] ?>, this.value)" 
                                                class="bg-transparent <?= $statusColor ?> text-[10px] font-black uppercase tracking-widest border border-white/10 rounded-full px-4 py-2 cursor-pointer focus:outline-none focus:border-blue-500 transition-all outline-none">
                                            <option value="pending" <?= $st == 'pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="active" <?= $st == 'active' ? 'selected' : '' ?>>Active</option>
                                            <option value="completed" <?= $st == 'completed' ? 'selected' : '' ?>>Completed</option>
                                        </select>
                                    </td>
                                    <td class="px-10 py-6 text-slate-500 text-xs italic">
                                        <?= date('M d, Y', strtotime($row['order_date'])) ?>
                                    </td>
                                    <td class="px-10 py-6 text-right">
                                        <div class="flex justify-end gap-3">
                                            <?php if(!empty($row['delivery_link'])): ?>
                                                <a href="<?= htmlspecialchars($row['delivery_link']) ?>" target="_blank" class="w-9 h-9 glass rounded-xl flex items-center justify-center text-emerald-400 hover:bg-emerald-500 hover:text-white transition-all shadow-lg">
                                                    <i class="fa-solid fa-link text-[12px]"></i>
                                                </a>
                                            <?php endif; ?>
                                            <button class="w-9 h-9 glass rounded-xl flex items-center justify-center text-slate-500 hover:text-white transition-all">
                                                <i class="fa-solid fa-ellipsis-vertical"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-10 py-24 text-center">
                                    <span class="text-slate-600 italic tracking-[0.3em] text-[10px] uppercase font-bold">No Records Found</span>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    function updateStatus(requestId, newStatus) {
        // Simple loading state
        const formData = new FormData();
        formData.append('request_id', requestId);
        formData.append('status', newStatus);

        fetch('update_status.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if (data.trim() === "Success") {
                // Flash effect or refresh to update colors
                location.reload();
            } else {
                alert("Operation failed: " + data);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("Matrix Connection Error.");
        });
    }
    </script>

</body>
</html>