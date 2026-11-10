<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
    header("Location: login.php?role=Admin");
    exit();
}

$message = "";

if (isset($_GET['resolve_id'])) {
    $c_id = intval($_GET['resolve_id']);
    mysqli_query($conn, "UPDATE complaints SET status = 'Resolved' WHERE complaint_id = $c_id");
    header("Location: complaints.php?msg=resolved");
    exit();
}

if (isset($_GET['delete_id'])) {
    $c_id = intval($_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM complaints WHERE complaint_id = $c_id");
    header("Location: complaints.php?msg=deleted");
    exit();
}

if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'resolved') {
        $message = "<div style='background:#d1e7dd; color:#0f5132; padding:12px; border-radius:5px; margin-bottom:15px; font-weight:bold;'>✅ Complaint marked as Resolved!</div>";
    } elseif ($_GET['msg'] == 'deleted') {
        $message = "<div style='background:#d1e7dd; color:#0f5132; padding:12px; border-radius:5px; margin-bottom:15px; font-weight:bold;'>🗑️ Complaint deleted successfully!</div>";
    }
}

$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$where_clause = "";
if ($filter == 'Pending') {
    $where_clause = "WHERE c.status = 'Pending'";
} elseif ($filter == 'Resolved') {
    $where_clause = "WHERE c.status = 'Resolved'";
}

$query = "SELECT c.complaint_id, u.email, r.room_number, h.hostel_name, c.title, c.description, c.status, c.created_at 
          FROM complaints c 
          JOIN users u ON c.user_id = u.user_id 
          LEFT JOIN allocations a ON u.user_id = a.user_id AND a.status = 'Active'
          LEFT JOIN rooms r ON a.room_id = r.room_id
          LEFT JOIN hostels h ON r.hostel_id = h.hostel_id
          $where_clause
          ORDER BY c.complaint_id DESC";
$result = mysqli_query($conn, $query);

$table_rows = "";
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $cid = $row['complaint_id'];
        $email = htmlspecialchars($row['email']);
        $title = htmlspecialchars($row['title']);
        $desc = htmlspecialchars($row['description']);
        $status = $row['status'];

        if (!empty($row['room_number'])) {
            $h_name = $row['hostel_name'] ? htmlspecialchars($row['hostel_name']) : "Main Hostel";
            $r_num = htmlspecialchars($row['room_number']);
            $location_badge = "<span style='background:#e2e8f0; color:#1e293b; padding:3px 8px; border-radius:4px; font-size:12px; font-weight:bold;'>$h_name - Room $r_num</span>";
        } else {
            $location_badge = "<span style='color:#888; font-size:12px;'>No Room Assigned</span>";
        }

        if ($status == 'Pending') {
            $status_badge = "<span style='background:#dc3545; color:#ffffff; padding:4px 10px; border-radius:4px; font-size:12px; font-weight:bold; display:inline-block;'>Pending</span>";
            $action_btns = "<a href='complaints.php?resolve_id=$cid' style='background:#198754; color:#ffffff; padding:5px 10px; text-decoration:none; border-radius:4px; font-size:12px; font-weight:bold; margin-right:5px; display:inline-block;'>Resolve</a>
                            <a href='complaints.php?delete_id=$cid' onclick='return confirm(\"Delete this complaint?\");' style='background:#6c757d; color:#ffffff; padding:5px 10px; text-decoration:none; border-radius:4px; font-size:12px; font-weight:bold; display:inline-block;'>Delete</a>";
        } else {
            $status_badge = "<span style='background:#198754; color:#ffffff; padding:4px 10px; border-radius:4px; font-size:12px; font-weight:bold; display:inline-block;'>Resolved</span>";
            $action_btns = "<a href='complaints.php?delete_id=$cid' onclick='return confirm(\"Delete this complaint?\");' style='background:#dc3545; color:#ffffff; padding:5px 10px; text-decoration:none; border-radius:4px; font-size:12px; font-weight:bold; display:inline-block;'>Delete</a>";
        }

        $table_rows .= "<tr style='border-bottom: 1px solid #ddd;'>
            <td style='padding: 12px; color:#000;'>#$cid</td>
            <td style='padding: 12px; color:#000;'><b>$email</b></td>
            <td style='padding: 12px;'>$location_badge</td>
            <td style='padding: 12px; color:#000;'><b>$title</b></td>
            <td style='padding: 12px; color:#000;'>$desc</td>
            <td style='padding: 12px;'>$status_badge</td>
            <td style='padding: 12px;'>$action_btns</td>
        </tr>";
    }
} else {
    $table_rows = "<tr><td colspan='7' style='padding: 15px; text-align:center; color:#555;'>No complaints found.</td></tr>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Complaints - Hostel Management System</title>
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
                <li><a href="payments.php">💰 Payments</a></li>
                <li><a href="complaints.php" class="active">📢 Complaints</a></li>
                <li><a href="notices.php">📢 Notices</a></li>
                <li><a href="room_applications.php">📄 Room Applications</a></li>
                <li><a href="reports.php">📋 Reports</a></li>
            </ul>
        </div>

        <div class="content">
            <h1 style="color:#000; margin-bottom:20px;">Student Complaints</h1>
            
            <?php echo $message; ?>

            <div style="margin-bottom: 15px;">
                <a href="complaints.php" style="background:#212529; color:#fff; padding:8px 15px; text-decoration:none; border-radius:4px; font-size:13px; font-weight:bold; margin-right:5px; display:inline-block;">All Complaints</a>
                <a href="complaints.php?filter=Pending" style="background:#dc3545; color:#fff; padding:8px 15px; text-decoration:none; border-radius:4px; font-size:13px; font-weight:bold; margin-right:5px; display:inline-block;">Pending Only</a>
                <a href="complaints.php?filter=Resolved" style="background:#198754; color:#fff; padding:8px 15px; text-decoration:none; border-radius:4px; font-size:13px; font-weight:bold; display:inline-block;">Resolved Only</a>
            </div>

            <div style="background: #ffffff; border-radius: 6px; border: 1px solid #ccc; overflow-x: auto;">
                <div style="background: #0d6efd; color: #ffffff; padding: 12px 20px; font-weight: bold;">All Registered Complaints</div>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #212529; color: #ffffff;">
                            <th style="padding: 12px; text-align: left;">ID</th>
                            <th style="padding: 12px; text-align: left;">Student Email</th>
                            <th style="padding: 12px; text-align: left;">Hostel & Room</th>
                            <th style="padding: 12px; text-align: left;">Title</th>
                            <th style="padding: 12px; text-align: left;">Description</th>
                            <th style="padding: 12px; text-align: left;">Status</th>
                            <th style="padding: 12px; text-align: left;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php echo $table_rows; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
