<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Customer") {
    header("Location: login.php?role=Customer");
    exit();
}

$user_id = $_SESSION["user_id"];
$message = "";

// পাসওয়ার্ড আপডেট করার লজিক (Update Operation)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $new_password = trim($_POST["new_password"]);
    if (!empty($new_password)) {
        $stmt_upd = mysqli_prepare($conn, "UPDATE users SET password_hash = ? WHERE user_id = ?");
        mysqli_stmt_bind_param($stmt_upd, "si", $new_password, $user_id);
        if (mysqli_stmt_execute($stmt_upd)) {
            $message = "<div style='background:#d1e7dd; color:#0f5132; padding:12px; border-radius:5px; margin-bottom:15px; font-weight:bold;'>✅ Password updated successfully!</div>";
        } else {
            $message = "<div style='background:#f8d7da; color:#842029; padding:12px; border-radius:5px; margin-bottom:15px; font-weight:bold;'>❌ Failed to update password.</div>";
        }
        mysqli_stmt_close($stmt_upd);
    } else {
        $message = "<div style='background:#f8d7da; color:#842029; padding:12px; border-radius:5px; margin-bottom:15px; font-weight:bold;'>⚠️ Please enter a new password first.</div>";
    }
}

// স্টুডেন্টের প্রোফাইল ও বরাদ্দকৃত হোস্টেল/রুমের তথ্য নিয়ে আসা
$query = "SELECT u.user_id, u.email, u.status, u.created_at, r.room_number, r.room_type, r.price_per_month, h.hostel_name 
          FROM users u 
          LEFT JOIN allocations a ON u.user_id = a.user_id AND a.status = 'Active'
          LEFT JOIN rooms r ON a.room_id = r.room_id 
          LEFT JOIN hostels h ON r.hostel_id = h.hostel_id
          WHERE u.user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$user_info = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$room_display = $user_info['room_number'] ? htmlspecialchars($user_info['hostel_name'] . " - Room " . $user_info['room_number'] . " (" . $user_info['room_type'] . ")") : "Not Allocated Yet";
$rent_display = $user_info['price_per_month'] ? "৳" . number_format($user_info['price_per_month'], 2) . " / month" : "N/A";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - Hostel Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="top-navbar" style="background-color: #0b6623;">
        <h2>Hostel Management System</h2>
        <div class="nav-right">
            <span style="color: white; font-size: 14px;">Welcome, <?php echo htmlspecialchars($user_info['email']); ?></span>
            <a href="logout.php" class="logout-btn" style="color: #333; background: white; padding: 3px 10px; border-radius: 3px; font-size: 13px; margin-left: 10px;">Logout</a>
        </div>
    </div>

    <div class="main-container">
        <div class="sidebar" style="background-color: #1a1a1a; width: 220px;">
            <h3 style="background-color: #000; font-size: 16px; padding: 15px; color: #fff; text-align: left; border-bottom: none;">Student Panel</h3>
            <ul style="margin-top: 10px;">
                <li><a href="student_dashboard.php" style="padding: 10px 20px; font-size: 14px;">📋 Dashboard</a></li>
                <li><a href="student_profile.php" class="active" style="background-color: #0b6623; color: white; padding: 10px 20px; font-size: 14px;">👤 My Profile</a></li>
                <li><a href="student_room_apply.php" style="padding: 10px 20px; font-size: 14px;">➡️ Apply Room</a></li>
                <li><a href="student_payments.php" style="padding: 10px 20px; font-size: 14px;">💰 Payments</a></li>
                <li><a href="student_complaints.php" style="padding: 10px 20px; font-size: 14px;">📩 Complaints</a></li>
                <li style="margin-top: 20px;"><a href="logout.php" style="color: #ff4d4d; padding: 10px 20px; font-size: 14px;">🚪 Logout</a></li>
            </ul>
        </div>

        <div class="content" style="margin-left: 220px; padding: 20px; width: calc(100% - 220px);">
            
            <?php echo $message; ?>
            
            <div style="background: #ffffff; border: 1px solid #dee2e6; border-radius: 5px; overflow: hidden;">
                <div style="background-color: #0b6623; color: white; padding: 12px 20px; font-size: 18px; font-weight: bold;">
                    My Profile
                </div>
                
                <form method="POST" action="student_profile.php" style="padding: 25px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    
                    <div>
                        <label style="display:block; margin-bottom: 6px; font-size: 13px; font-weight:bold; color: #333;">Student ID</label>
                        <input type="text" value="#STU-<?php echo $user_info['user_id']; ?>" disabled style="width:100%; padding:9px; background:#f4f6f9; border:1px solid #ccc; border-radius:4px; color:#000;">
                    </div>
                    
                    <div>
                        <label style="display:block; margin-bottom: 6px; font-size: 13px; font-weight:bold; color: #333;">Account Status</label>
                        <input type="text" value="<?php echo htmlspecialchars($user_info['status']); ?>" disabled style="width:100%; padding:9px; background:#e8f5e9; border:1px solid #a5d6a7; border-radius:4px; color:#1b5e20; font-weight:bold;">
                    </div>
                    
                    <div>
                        <label style="display:block; margin-bottom: 6px; font-size: 13px; font-weight:bold; color: #333;">Email Address</label>
                        <input type="email" value="<?php echo htmlspecialchars($user_info['email']); ?>" disabled style="width:100%; padding:9px; background:#f4f6f9; border:1px solid #ccc; border-radius:4px; color:#000;">
                    </div>
                    
                    <div>
                        <label style="display:block; margin-bottom: 6px; font-size: 13px; font-weight:bold; color: #333;">Registration Date</label>
                        <input type="text" value="<?php echo htmlspecialchars($user_info['created_at']); ?>" disabled style="width:100%; padding:9px; background:#f4f6f9; border:1px solid #ccc; border-radius:4px; color:#000;">
                    </div>

                    <div>
                        <label style="display:block; margin-bottom: 6px; font-size: 13px; font-weight:bold; color: #333;">Assigned Hostel & Room</label>
                        <input type="text" value="<?php echo $room_display; ?>" disabled style="width:100%; padding:9px; background:#f4f6f9; border:1px solid #ccc; border-radius:4px; color:#000; font-weight:bold;">
                    </div>

                    <div>
                        <label style="display:block; margin-bottom: 6px; font-size: 13px; font-weight:bold; color: #333;">Monthly Room Rent</label>
                        <input type="text" value="<?php echo $rent_display; ?>" disabled style="width:100%; padding:9px; background:#f4f6f9; border:1px solid #ccc; border-radius:4px; color:#000;">
                    </div>

                    <div style="grid-column: span 2; border-top: 1px solid #eee; padding-top: 15px;">
                        <label style="display:block; margin-bottom: 6px; font-size: 13px; font-weight:bold; color: #0b6623;">Change Password (Enter new password below to update)</label>
                        <input type="password" name="new_password" placeholder="Enter new password" style="width:100%; padding:9px; border:1px solid #999; border-radius:4px; color:#000;">
                    </div>
                    
                    <div style="grid-column: span 2;">
                        <button type="submit" name="update_profile" style="background-color: #0b6623; color: white; border: none; padding: 10px 25px; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 14px;">
                            Update Profile
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

</body>
</html>
