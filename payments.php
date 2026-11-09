<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
    header("Location: login.php?role=Admin");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["user_id"]) && isset($_POST["amount"])) {
        $user_id = intval($_POST["user_id"]);
        $amount = floatval($_POST["amount"]);
        $pay_date = $_POST["payment_date"];
        $status = $_POST["status"];

        if ($user_id > 0 && $amount > 0) {
            $sql = "INSERT INTO payments (user_id, amount, payment_date, status) VALUES ('$user_id', '$amount', '$pay_date', '$status')";
            if (mysqli_query($conn, $sql)) {
                $message = "<div style='background:#d1e7dd; color:#0f5132; padding:12px; border-radius:5px; margin-bottom:15px; font-weight:bold;'>Record added successfully ($status)!</div>";
            } else {
                $err = mysqli_error($conn);
                $message = "<div style='background:#f8d7da; color:#842029; padding:12px; border-radius:5px; margin-bottom:15px; font-weight:bold;'>Error: $err</div>";
            }
        }
    }
}

if (isset($_GET['pay_now'])) {
    $pid = intval($_GET['pay_now']);
    mysqli_query($conn, "UPDATE payments SET status = 'Paid' WHERE payment_id = $pid");
    header("Location: payments.php?done=1");
    exit();
}

if (isset($_GET['del_id'])) {
    $pid = intval($_GET['del_id']);
    mysqli_query($conn, "DELETE FROM payments WHERE payment_id = $pid");
    header("Location: payments.php?done=2");
    exit();
}

if (isset($_GET['done'])) {
    if ($_GET['done'] == 1) {
        $message = "<div style='background:#d1e7dd; color:#0f5132; padding:12px; border-radius:5px; margin-bottom:15px; font-weight:bold;'>Payment updated to Paid!</div>";
    } elseif ($_GET['done'] == 2) {
        $message = "<div style='background:#d1e7dd; color:#0f5132; padding:12px; border-radius:5px; margin-bottom:15px; font-weight:bold;'>Record deleted!</div>";
    }
}

$q_paid = mysqli_query($conn, "SELECT SUM(amount) AS total FROM payments WHERE status = 'Paid'");
$total_paid = mysqli_fetch_assoc($q_paid)['total'] ?? 0;

$q_pending = mysqli_query($conn, "SELECT SUM(amount) AS total FROM payments WHERE status = 'Pending'");
$total_pending = mysqli_fetch_assoc($q_pending)['total'] ?? 0;

$student_list = mysqli_query($conn, "SELECT u.user_id, u.email, r.room_number 
                                     FROM users u 
                                     LEFT JOIN allocations a ON u.user_id = a.user_id AND a.status = 'Active'
                                     LEFT JOIN rooms r ON a.room_id = r.room_id
                                     WHERE u.role = 'Customer' 
                                     ORDER BY u.email ASC");

$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$where_clause = "";
if ($filter == 'Paid') {
    $where_clause = "WHERE p.status = 'Paid'";
} elseif ($filter == 'Pending') {
    $where_clause = "WHERE p.status = 'Pending'";
}

$records = mysqli_query($conn, "SELECT p.payment_id, u.email, r.room_number, p.amount, p.payment_date, p.status 
                                FROM payments p 
                                LEFT JOIN users u ON p.user_id = u.user_id 
                                LEFT JOIN allocations a ON u.user_id = a.user_id AND a.status = 'Active'
                                LEFT JOIN rooms r ON a.room_id = r.room_id
                                $where_clause
                                ORDER BY p.payment_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Management - Smart Hostel</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="top-navbar">
        <h2>Hostel Management System</h2>
        <div class="nav-right">
            <span>Welcome, Administrator</span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="main-container">
        <div class="sidebar">
            <h3>Hostel Management</h3>
            <ul>
                <li><a href="admin_dashboard.php">📊 Dashboard</a></li>
                <li><a href="students.php">👥 Students</a></li>
                <li><a href="hostels.php">🏨 Hostels</a></li>
                <li><a href="rooms.php">🛏️ Rooms</a></li>
                <li><a href="room_allocation.php">🔄 Room Allocation</a></li>
                <li><a href="payments.php" class="active">💰 Payments</a></li>
                <li><a href="complaints.php">📢 Complaints</a></li>
                <li><a href="notices.php">📢 Notices</a></li>
                <li><a href="room_applications.php">📄 Room Applications</a></li>
                <li><a href="reports.php">📋 Reports</a></li>
            </ul>
        </div>

        <div class="content">
            <h1 style="color:#000; margin-bottom:20px;">Payment & Dues Management</h1>
            
            <?php echo $message; ?>

            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 25px;">
                <div style="background-color: #198754; color: #ffffff; padding: 20px; border-radius: 6px; text-align: center;">
                    <h3 style="font-size: 16px; margin-bottom: 8px; color: #ffffff;">Total Collected (Paid)</h3>
                    <p style="font-size: 28px; font-weight: bold; color: #ffffff;">৳<?php echo number_format($total_paid, 2); ?></p>
                </div>
                <div style="background-color: #ffc107; color: #000000; padding: 20px; border-radius: 6px; text-align: center;">
                    <h3 style="font-size: 16px; margin-bottom: 8px; color: #000000;">Total Pending Dues</h3>
                    <p style="font-size: 28px; font-weight: bold; color: #000000;">৳<?php echo number_format($total_pending, 2); ?></p>
                </div>
            </div>
            <div style="background: #ffffff; padding: 20px; border-radius: 6px; border: 1px solid #ccc; margin-bottom: 25px;">
                <h3 style="margin-bottom: 15px; color: #000; font-size: 16px;">Add New Payment / Due Record</h3>
                <form method="POST" action="payments.php">
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 15px;">
                        <div>
                            <label style="display:block; margin-bottom:5px; font-size:14px; font-weight:bold; color:#000;">Select Student</label>
                            <select name="user_id" required style="width:100%; padding:9px; border:1px solid #888; border-radius:4px; color:#000; background:#fff;">
                                <option value="">-- Choose Student --</option>
                                <?php 
                                while ($st = mysqli_fetch_assoc($student_list)) {
                                    $rm = $st['room_number'] ? " (Room " . $st['room_number'] . ")" : " (No Room)";
                                    echo "<option value='" . $st['user_id'] . "'>" . htmlspecialchars($st['email']) . $rm . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div>
                            <label style="display:block; margin-bottom:5px; font-size:14px; font-weight:bold; color:#000;">Amount (৳)</label>
                            <input type="number" step="0.01" name="amount" placeholder="e.g. 5000" required style="width:100%; padding:9px; border:1px solid #888; border-radius:4px; color:#000; background:#fff;">
                        </div>
                        <div>
                            <label style="display:block; margin-bottom:5px; font-size:14px; font-weight:bold; color:#000;">Date</label>
                            <input type="date" name="payment_date" value="<?php echo date('Y-m-d'); ?>" required style="width:100%; padding:9px; border:1px solid #888; border-radius:4px; color:#000; background:#fff;">
                        </div>
                        <div>
                            <label style="display:block; margin-bottom:5px; font-size:14px; font-weight:bold; color:#000;">Status</label>
                            <select name="status" style="width:100%; padding:9px; border:1px solid #888; border-radius:4px; color:#000; background:#fff;">
                                <option value="Paid">Paid</option>
                                <option value="Pending">Pending</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" style="background-color: #0d6efd; color: #ffffff; border: none; padding: 10px 25px; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 15px;">Save Record</button>
                </form>
            </div>

            <div style="margin-bottom: 15px;">
                <a href="payments.php" style="background:#212529; color:#fff; padding:8px 15px; text-decoration:none; border-radius:4px; font-size:13px; font-weight:bold; margin-right:5px; display:inline-block;">All Records</a>
                <a href="payments.php?filter=Pending" style="background:#ffc107; color:#000; padding:8px 15px; text-decoration:none; border-radius:4px; font-size:13px; font-weight:bold; margin-right:5px; display:inline-block;">Show Pending Only</a>
                <a href="payments.php?filter=Paid" style="background:#198754; color:#fff; padding:8px 15px; text-decoration:none; border-radius:4px; font-size:13px; font-weight:bold; display:inline-block;">Show Paid Only</a>
            </div>

            <div style="background: #ffffff; border-radius: 6px; border: 1px solid #ccc; overflow-x: auto; margin-top: 15px;">
                <div style="background: #0d6efd; color: #ffffff; padding: 12px 20px; font-weight: bold;">Student Payment & Pending List</div>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #212529; color: #ffffff;">
                            <th style="padding: 12px; text-align: left;">ID</th>
                            <th style="padding: 12px; text-align: left;">Student Email</th>
                            <th style="padding: 12px; text-align: left;">Room</th>
                            <th style="padding: 12px; text-align: left;">Amount</th>
                            <th style="padding: 12px; text-align: left;">Date</th>
                            <th style="padding: 12px; text-align: left;">Status</th>
                            <th style="padding: 12px; text-align: left;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($records && mysqli_num_rows($records) > 0) {
                            while ($r = mysqli_fetch_assoc($records)) {
                                $id = $r['payment_id'];
                                $em = $r['email'] ? htmlspecialchars($r['email']) : "Unknown User";
                                $rm = $r['room_number'] ? "Room " . htmlspecialchars($r['room_number']) : "N/A";
                                $am = number_format($r['amount'], 2);
                                $dt = htmlspecialchars($r['payment_date']);
                                $st = $r['status'];

                                echo "<tr style='border-bottom: 1px solid #ddd;'>";
                                echo "<td style='padding: 12px; color:#000;'>#$id</td>";
                                echo "<td style='padding: 12px; color:#000;'><b>$em</b></td>";
                                echo "<td style='padding: 12px; color:#000;'>$rm</td>";
                                echo "<td style='padding: 12px; color:#000;'><b>৳$am</b></td>";
                                echo "<td style='padding: 12px; color:#000;'>$dt</td>";
                                
                                if ($st == 'Paid') {
                                    echo "<td style='padding: 12px;'><span style='background:#198754; color:#fff; padding:4px 10px; border-radius:4px; font-size:12px; font-weight:bold;'>Paid</span></td>";
                                    echo "<td style='padding: 12px;'><a href='payments.php?del_id=$id' onclick='return confirm(\"Delete?\");' style='background:#dc3545; color:#fff; padding:5px 10px; text-decoration:none; border-radius:4px; font-size:12px;'>Delete</a></td>";
                                } else {
                                    echo "<td style='padding: 12px;'><span style='background:#ffc107; color:#000; padding:4px 10px; border-radius:4px; font-size:12px; font-weight:bold;'>Pending</span></td>";
                                    echo "<td style='padding: 12px;'>
                                            <a href='payments.php?pay_now=$id' style='background:#0d6efd; color:#fff; padding:5px 10px; text-decoration:none; border-radius:4px; font-size:12px; font-weight:bold; margin-right:5px;'>Mark Paid</a>
                                            <a href='payments.php?del_id=$id' onclick='return confirm(\"Delete?\");' style='background:#dc3545; color:#fff; padding:5px 10px; text-decoration:none; border-radius:4px; font-size:12px;'>Delete</a>
                                          </td>";
                                }
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' style='padding: 15px; text-align:center; color:#555;'>No payment records found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</body>
</html>
