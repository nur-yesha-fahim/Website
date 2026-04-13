<?php
session_start();
require 'db.php';

// ১. চেক করা ইউজার লগইন আছে কি না এবং ডাটা পোস্ট হয়েছে কি না
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    
    // ২. ডাটা রিসিভ করা (নিশ্চিত করুন nameগুলো completed.php এর ফর্মের সাথে মিলছে)
    $user_id    = $_SESSION['user_id'];
    $request_id = $_POST['request_id'] ?? null;
    $service_id = $_POST['service_id'] ?? null;
    $rating     = $_POST['star'] ?? null; // completed.php তে name="star" আছে
    $comment    = isset($_POST['comment']) ? htmlspecialchars($_POST['comment']) : '';

    // ৩. ভ্যালিডেশন (যদি কোনো ডাটা মিসিং থাকে)
    if (!$request_id || !$service_id || !$rating) {
        die("Error: Missing required fields. Please select a star rating.");
    }

    try {
        // ৪. রিভিউ সেভ করা
        // আপনার টেবিলের কলামের নাম 'rating' নাকি 'stars' তা ডাটাবেসে চেক করে নিন
        $sql = "INSERT INTO reviews (request_id, service_id, user_id, rating, comment) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$request_id, $service_id, $user_id, $rating, $comment])) {
            // ৫. সফল হলে ড্যাশবোর্ডে ফেরত পাঠানো
            header("Location: dashboard.php?tab=completed&review=success");
            exit();
        } else {
            echo "Failed to save the review.";
        }
    } catch (PDOException $e) {
        // যদি ডাটাবেসে কোনো এরর হয় (যেমন: কলামের নাম ভুল)
        die("Database Error: " . $e->getMessage());
    }
} else {
    header("Location: login.php");
    exit();
}