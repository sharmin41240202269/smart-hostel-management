<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Customer") {
    header("Location: login.php?role=Customer");
    exit();
}

$user_id = $_SESSION["user_id"];
$user_email = $_SESSION["email"];

// ১. স্টুডেন্টের বরাদ্দকৃত রুম ও হোস্টেলের নাম নিয়ে আসা (JOIN ব্যবহার করে)
$alloc_query = "SELECT a.allocation_id, r.room_number, r.room_type, r.price_per_month, h.hostel_name 
                FROM allocations a 
                JOIN rooms r ON a.room_id = r.room_id 
                LEFT JOIN hostels h ON r.hostel_id = h.hostel_id
                WHERE a.user_id = ? AND a.status = 'Active'";
$stmt = mysqli_prepare($conn, $alloc_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$room_info = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// ২. স্টুডেন্টের মোট পরিশোধিত (Paid) এবং বকেয়া (Pending) টাকা হিসাব করা
$paid_q = mysqli_query($conn, "SELECT IFNULL(SUM(amount), 0) as total FROM payments WHERE user_id = $user_id AND status = 'Paid'");
$total_paid = mysqli_fetch_assoc($paid_q)['total'];

$due_q = mysqli_query($conn, "SELECT IFNULL(SUM(amount), 0) as total FROM payments WHERE user_id = $user_id AND status = 'Pending'");
$total_due = mysqli_fetch_assoc($due_q)['total'];

// ৩. স্টুডেন্টের মোট অভিযোগের সংখ্যা গণনা
$comp_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM complaints WHERE user_id = $user_id");
$total_complaints = mysqli_fetch_assoc($comp_q)['total'];

// ৪. সর্বশেষ নোটিশগুলো তুলে আনা
$notices = mysqli_query($conn, "SELECT * FROM notices ORDER BY notice_id DESC LIMIT 4");

$notices_html = "";
if ($notices && mysqli_num_rows($notices) > 0) {
    while ($n = mysqli_fetch_assoc($notices)) {
        $ntitle = htmlspecialchars($n['title']);
        $ncontent = htmlspecialchars($n['content']);
        $ndate = htmlspecialchars($n['created_at']);
        $notices_html .= "<div style='margin-bottom: 12px; border-bottom: 1px solid #eee; padding-bottom: 10px;'>
            <h4 style='color:#0b6623; margin-bottom:4px; font-size:15px;'>📌 $ntitle</h4>
            <p style='font-size:13px; color:#333; margin-bottom:4px;'>$ncontent</p>
            <small style='color:#777;'>Published: $ndate</small>
        </div>";
    }
} else {
    $notices_html = "<p style='text-align:center; color:#777;'>No notices published yet.</p>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard - Hostel Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- টপ নেভিগেশন বার -->
    <div class="top-navbar" style="background-color: #0b6623;">
        <h2>Hostel Management System</h2>
        <div class="nav-right">
            <span style="color: white; font-size: 14px;">Welcome, <?php echo htmlspecialchars($user_email); ?></span>
            <a href="logout.php" class="logout-btn" style="color: #333; background: white; padding: 3px 10px; border-radius: 3px; font-size: 13px; margin-left: 10px;">Logout</a>
        </div>
    </div>

    <div class="main-container">
        
        <!-- সাইডবার মেনু (সবগুলো সঠিক লিংকসহ) -->
        <div class="sidebar" style="background-color: #1a1a1a; width: 220px;">
            <h3 style="background-color: #000; font-size: 16px; padding: 15px; color: #fff; text-align: left; border-bottom: none;">Student Panel</h3>
            <ul style="margin-top: 10px;">
                <li><a href="student_dashboard.php" class="active" style="background-color: #0b6623; color: white; padding: 10px 20px; font-size: 14px;">📋 Dashboard</a></li>
                <li><a href="student_profile.php" style="padding: 10px 20px; font-size: 14px;">👤 My Profile</a></li>
                <li><a href="student_room_apply.php" style="padding: 10px 20px; font-size: 14px;">➡️ Apply Room</a></li>
                <li><a href="student_payments.php" style="padding: 10px 20px; font-size: 14px;">💰 Payments</a></li>
                <li><a href="student_complaints.php" style="padding: 10px 20px; font-size: 14px;">📩 Complaints</a></li>
                <li style="margin-top: 20px;"><a href="logout.php" style="color: #ff4d4d; padding: 10px 20px; font-size: 14px;">🚪 Logout</a></li>
            </ul>
        </div>

        <!-- মেইন কন্টেন্ট এরিয়া -->
        <div class="content" style="margin-left: 220px; padding: 20px; width: calc(100% - 220px);">
            <h1 style="color:#000; margin-bottom:20px;">Student Dashboard</h1>
            
            <!-- ৪টি পরিসংখ্যান কার্ড -->
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 25px;">
                <div style="background-color: #0b6623; color: #fff; padding: 18px; border-radius: 6px; text-align: center;">
                    <h3 style="font-size: 14px; margin-bottom: 6px; color: #fff;">My Room</h3>
                    <p style="font-size: 22px; font-weight: bold; color: #fff;"><?php echo $room_info ? "Room " . htmlspecialchars($room_info['room_number']) : "Unassigned"; ?></p>
                </div>
                <div style="background-color: #0d6efd; color: #fff; padding: 18px; border-radius: 6px; text-align: center;">
                    <h3 style="font-size: 14px; margin-bottom: 6px; color: #fff;">Total Paid</h3>
                    <p style="font-size: 22px; font-weight: bold; color: #fff;">৳<?php echo number_format($total_paid, 2); ?></p>
                </div>
                <div style="background-color: #ffc107; color: #000; padding: 18px; border-radius: 6px; text-align: center;">
                    <h3 style="font-size: 14px; margin-bottom: 6px; color: #000;">Pending Due</h3>
                    <p style="font-size: 22px; font-weight: bold; color: #000;">৳<?php echo number_format($total_due, 2); ?></p>
                </div>
                <div style="background-color: #dc3545; color: #fff; padding: 18px; border-radius: 6px; text-align: center;">
                    <h3 style="font-size: 14px; margin-bottom: 6px; color: #fff;">My Complaints</h3>
                    <p style="font-size: 22px; font-weight: bold; color: #fff;"><?php echo $total_complaints; ?></p>
                </div>
            </div>

            <!-- নিচের দুটি বক্স: রুমের বিবরণ ও নোটিশ বোর্ড -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                
                <!-- রুমের বিবরণ বক্স -->
                <div style="background: #ffffff; padding: 20px; border-radius: 6px; border: 1px solid #ccc; border-top: 4px solid #0b6623;">
                    <h3 style="color: #0b6623; margin-bottom: 15px; font-size: 17px;">🛏️ My Room Details</h3>
                    <?php if ($room_info) { ?>
                        <p style="margin-bottom: 10px; color:#000;"><b>Hostel Block:</b> <?php echo htmlspecialchars($room_info['hostel_name'] ?? 'Main Hostel'); ?></p>
                        <p style="margin-bottom: 10px; color:#000;"><b>Room Number:</b> Room <?php echo htmlspecialchars($room_info['room_number']); ?></p>
                        <p style="margin-bottom: 10px; color:#000;"><b>Room Type:</b> <?php echo htmlspecialchars($room_info['room_type']); ?></p>
                        <p style="margin-bottom: 10px; color:#000;"><b>Monthly Rent:</b> ৳<?php echo number_format($room_info['price_per_month'], 2); ?></p>
                        <p style="color: #198754; font-weight: bold; margin-top: 12px;">✅ Status: Active Resident</p>
                    <?php } else { ?>
                        <p style="color: #dc3545; font-weight: bold; margin-bottom: 15px;">You have not been allocated to any room yet.</p>
                        <a href="student_room_apply.php" style="background: #0d6efd; color: #fff; padding: 8px 15px; text-decoration: none; border-radius: 4px; font-size: 13px; font-weight: bold;">Apply For Room Now</a>
                    <?php } ?>
                </div>

                <!-- নোটিশ বোর্ড বক্স -->
                <div style="background: #ffffff; border-radius: 6px; border: 1px solid #ccc; overflow: hidden;">
                    <div style="background-color: #ffc107; color: #000; padding: 12px 15px; font-weight: bold; font-size: 16px;">📢 Hostel Notice Board</div>
                    <div style="padding: 15px;">
                        <?php echo $notices_html; ?>
                    </div>
                </div>

            </div>

        </div>
    </div>

</body>
</html>
