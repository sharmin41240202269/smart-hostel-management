<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Customer") {
    header("Location: login.php?role=Customer");
    exit();
}

$user_id = $_SESSION["user_id"];
$user_email = $_SESSION["email"];
$message = "";

// নতুন কমপ্লেন সাবমিট করার লজিক
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_complaint'])) {
    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);

    if (!empty($title) && !empty($description)) {
        $insert_sql = "INSERT INTO complaints (user_id, title, description, status) VALUES (?, ?, ?, 'Pending')";
        $stmt = mysqli_prepare($conn, $insert_sql);
        mysqli_stmt_bind_param($stmt, "iss", $user_id, $title, $description);
        if (mysqli_stmt_execute($stmt)) {
            $message = "<div style='background:#d1e7dd; color:#0f5132; padding:12px; border-radius:5px; margin-bottom:15px; font-weight:bold;'>✅ Complaint submitted successfully! Admin will review it soon.</div>";
        } else {
            $err = mysqli_error($conn);
            $message = "<div style='background:#f8d7da; color:#842029; padding:12px; border-radius:5px; margin-bottom:15px; font-weight:bold;'>❌ Error: $err</div>";
        }
        mysqli_stmt_close($stmt);
    }
}

// এই স্টুডেন্টের করা আগের সব কমপ্লেন লিস্ট তুলে আনা
$query = "SELECT complaint_id, title, description, status, created_at FROM complaints WHERE user_id = ? ORDER BY complaint_id DESC";
$stmt2 = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt2, "i", $user_id);
mysqli_stmt_execute($stmt2);
$result = mysqli_stmt_get_result($stmt2);

$rows_html = "";
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $cid = $row['complaint_id'];
        $title = htmlspecialchars($row['title']);
        $desc = htmlspecialchars($row['description']);
        $date = htmlspecialchars($row['created_at']);
        
        if ($row['status'] == 'Pending') {
            $badge = "<span style='background:#dc3545; color:#fff; padding:4px 10px; border-radius:4px; font-size:12px; font-weight:bold;'>Pending</span>";
        } else {
            $badge = "<span style='background:#198754; color:#fff; padding:4px 10px; border-radius:4px; font-size:12px; font-weight:bold;'>Resolved</span>";
        }

        $rows_html .= "<tr style='border-bottom: 1px solid #ddd;'>
            <td style='padding: 12px; color:#000;'>#CMP-$cid</td>
            <td style='padding: 12px; color:#000;'><b>$title</b></td>
            <td style='padding: 12px; color:#000;'>$desc</td>
            <td style='padding: 12px;'>$badge</td>
            <td style='padding: 12px; color:#000;'>$date</td>
        </tr>";
    }
} else {
    $rows_html = "<tr><td colspan='5' style='padding: 15px; text-align:center; color:#555;'>You have not submitted any complaints yet.</td></tr>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Complaints - Student Portal</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="top-navbar" style="background-color: #0b6623;">
        <h2>Hostel Management System</h2>
        <div class="nav-right">
            <span style="color: white; font-size: 14px;">Welcome, <?php echo htmlspecialchars($user_email); ?></span>
            <a href="logout.php" class="logout-btn" style="color: #333; background: white; padding: 3px 10px; border-radius: 3px; font-size: 13px; margin-left: 10px;">Logout</a>
        </div>
    </div>

    <div class="main-container">
        <div class="sidebar" style="background-color: #1a1a1a; width: 220px;">
            <h3 style="background-color: #000; font-size: 16px; padding: 15px; color: #fff; text-align: left; border-bottom: none;">Student Panel</h3>
            <ul style="margin-top: 10px;">
                <li><a href="student_dashboard.php" style="padding: 10px 20px; font-size: 14px;">📋 Dashboard</a></li>
                <li><a href="student_profile.php" style="padding: 10px 20px; font-size: 14px;">👤 My Profile</a></li>
                <li><a href="student_room_apply.php" style="padding: 10px 20px; font-size: 14px;">➡️ Apply Room</a></li>
                <li><a href="student_payments.php" style="padding: 10px 20px; font-size: 14px;">💰 Payments</a></li>
                <li><a href="student_complaints.php" class="active" style="background-color: #0b6623; color: white; padding: 10px 20px; font-size: 14px;">📩 Complaints</a></li>
                <li style="margin-top: 20px;"><a href="logout.php" style="color: #ff4d4d; padding: 10px 20px; font-size: 14px;">🚪 Logout</a></li>
            </ul>
        </div>

        <div class="content" style="margin-left: 220px; padding: 20px; width: calc(100% - 220px);">
            <h1 style="color:#000; margin-bottom:20px;">Submit a Complaint</h1>
            
            <?php echo $message; ?>

            <div style="background: #ffffff; padding: 20px; border-radius: 6px; border: 1px solid #ccc; margin-bottom: 25px;">
                <h3 style="margin-bottom: 15px; color: #000; font-size: 16px;">Write Your Issue</h3>
                <form method="POST" action="student_complaints.php">
                    <div style="margin-bottom: 15px;">
                        <label style="display:block; margin-bottom:5px; font-size:14px; font-weight:bold; color:#000;">Issue Title</label>
                        <input type="text" name="title" placeholder="e.g., Light not working, Water problem" required style="width:100%; padding:9px; border:1px solid #888; border-radius:4px; color:#000; background:#fff;">
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label style="display:block; margin-bottom:5px; font-size:14px; font-weight:bold; color:#000;">Detailed Description</label>
                        <textarea name="description" placeholder="Explain your issue clearly..." required style="width:100%; padding:9px; height:90px; border:1px solid #888; border-radius:4px; color:#000; background:#fff; resize:vertical;"></textarea>
                    </div>
                    <button type="submit" name="submit_complaint" style="background-color: #dc3545; color: #ffffff; border: none; padding: 10px 25px; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 14px;">Submit Complaint</button>
                </form>
            </div>

            <div style="background: #ffffff; border-radius: 6px; border: 1px solid #ccc; overflow-x: auto;">
                <div style="background: #0b6623; color: #ffffff; padding: 12px 20px; font-weight: bold;">My Complaint History</div>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #212529; color: #ffffff;">
                            <th style="padding: 12px; text-align: left;">ID</th>
                            <th style="padding: 12px; text-align: left;">Title</th>
                            <th style="padding: 12px; text-align: left;">Description</th>
                            <th style="padding: 12px; text-align: left;">Status</th>
                            <th style="padding: 12px; text-align: left;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php echo $rows_html; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>
