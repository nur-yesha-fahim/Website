<?php
session_start();

// ১. সব সেশন ডাটা রিমুভ করা
session_unset();

// ২. সেশনটি পুরোপুরি ধ্বংস করা
session_destroy();

// ৩. লগআউট হওয়ার পর ইউজারকে হোম পেজ বা লগইন পেজে পাঠিয়ে দেয়া
header("Location: login.php");
exit();
?>