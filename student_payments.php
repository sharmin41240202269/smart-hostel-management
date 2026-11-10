<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Customer") {
    header("Location: login.php?role=Customer");
    exit();
}

$user_id = $_SESSION["user_id"];
$display_name = $_SESSION["email"];
$message = "";

// ১. স্টুডেন্ট নিজে বকেয়া বিল পরিশোধ করলে তা Paid করার লজিক
if (isset($_GET['pay_id'])) {
    $pid = intval($_GET['pay_id']);
    mysqli_query($conn, "UPDATE payments SET status = 'Paid', payment_date = CURDATE() WHERE payment_id = $pid AND user_id = $user_id");
    header("Location: student_payments.php?msg=paid");
    exit();
}

if (isset($_GET['msg']) && $_GET['msg'] == 'paid') {
    $message = "<div style='background:#d1e7dd; color:#0f5132; padding:12px; border-radius:5px; margin-bottom:15px; font-weight:bold;'>✅ Payment successful! Your due bill has been marked as Paid.</div>";
}

// ২. মোট পরিশোধিত ও বকেয়া টাকার হিসাব করা
$paid_q = mysqli_query($conn, "SELECT IFNULL(SUM(amount), 0) as total FROM payments WHERE user_id = $user_id AND status = 'Paid'");
$total_paid = mysqli_fetch_assoc($paid_q)['total'];

$due_q = mysqli_query($conn, "SELECT IFNULL(SUM(amount), 0) as total FROM payments WHERE user_id = $user_id AND status = 'Pending'");
$total_due = mysqli_fetch_assoc($due_q)['total'];

// ৩. স্টুডেন্টের সব পেমেন্ট রেকর্ড নিয়ে আসা
$history_q = "SELECT payment_id, amount, payment_date, status FROM payments WHERE user_id = $user_id ORDER BY payment_id DESC";
$history_res = mysqli_query($conn, $history_q);

$rows_html = "";
if ($history_res && mysqli_num_rows($history_res) > 0) {
    while ($row = mysqli_fetch_assoc($history_res)) {
        $pid = $row['payment_id'];
        $amt = number_format($row['amount'], 2);
        $pdate = htmlspecialchars($row['payment_date']);
        $st = $row['status'];

        if ($st == 'Paid') {
            $badge = "<span style='background:#198754; color:#fff; padding:4px 10px; border-radius:4px; font-size:12px; font-weight:bold;'>Paid</span>";
            $action = "<span style='color:#198754; font-size:13px; font-weight:bold;'>✔ Cleared</span>";
        } else {
            $badge = "<span style='background:#ffc107; color:#000; padding:4px 10px; border-radius:4px; font-size:12px; font-weight:bold;'>Pending</span>";
            $action = "<a href='student_payments.php?pay_id=$pid' onclick='return confirm(\"Confirm payment of ৳$amt?\");' style='background:#0d6efd; color:#fff; padding:5px 12px; text-decoration:none; border-radius:4px; font-size:12px; font-weight:bold; display:inline-block;'>Pay Now</a>";
        }

        $rows_html .= "<tr style='border-bottom: 1px solid #ddd;'>
            <td style='padding: 12px; color:#000;'>#TXN-$pid</td>
            <td style='padding: 12px; color:#000;'><b>৳$amt</b></td>
            <td style='padding: 12px; color:#000;'>$pdate</td>
            <td style='padding: 12px;'>$badge</td>
            <td style='padding: 12px;'>$action</td>
        </tr>";
    }
} else {
    $rows_html = "<tr><td colspan='5' style='padding: 15px; text-align:center; color:#555;'>No payment history found for your account.</td></tr>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Payments - Hostel Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="top-navbar" style="background-color: #0b6623;">
        <h2>Hostel Management System</h2>
        <div class="nav-right">
            <span style="color: white; font-size: 14px;">Welcome, <?php echo htmlspecialchars($display_name); ?></span>
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
                <li><a href="student_payments.php" class="active" style="background-color: #0b6623; color: white; padding: 10px 20px; font-size: 14px;">💰 Payments</a></li>
                <li><a href="student_complaints.php" style="padding: 10px 20px; font-size: 14px;">📩 Complaints</a></li>
                <li style="margin-top: 20px;"><a href="logout.php" style="color: #ff4d4d; padding: 10px 20px; font-size: 14px;">🚪 Logout</a></li>
            </ul>
        </div>

        <div class="content" style="margin-left: 220px; padding: 20px; width: calc(100% - 220px);">
            <h1 style="color:#000; margin-bottom:20px;">My Payments & Dues</h1>
            
            <?php echo $message; ?>

            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 25px;">
                <div style="background-color: #198754; color: #fff; padding: 20px; border-radius: 6px; text-align: center;">
                    <h3 style="font-size: 16px; margin-bottom: 8px; color: #fff;">Total Paid Amount</h3>
                    <p style="font-size: 28px; font-weight: bold; color: #fff;">৳<?php echo number_format($total_paid, 2); ?></p>
                </div>
                <div style="background-color: #ffc107; color: #000; padding: 20px; border-radius: 6px; text-align: center;">
                    <h3 style="font-size: 16px; margin-bottom: 8px; color: #000;">Total Pending Due</h3>
                    <p style="font-size: 28px; font-weight: bold; color: #000;">৳<?php echo number_format($total_due, 2); ?></p>
                </div>
            </div>

            <div style="background: #ffffff; border-radius: 5px; border: 1px solid #ccc; overflow-x: auto;">
                <div style="background: #0b6623; color: #ffffff; padding: 12px 20px; font-weight: bold;">My Payment History</div>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #212529; color: #ffffff;">
                            <th style="padding: 12px; text-align: left;">Txn ID</th>
                            <th style="padding: 12px; text-align: left;">Amount</th>
                            <th style="padding: 12px; text-align: left;">Date</th>
                            <th style="padding: 12px; text-align: left;">Status</th>
                            <th style="padding: 12px; text-align: left;">Action</th>
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
