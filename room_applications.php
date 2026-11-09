<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
    header("Location: login.php?role=Admin");
    exit();
}

$message = "";

// ১. আবেদন অনুমোদন (Approve) ও পুরাতন রুম বদলে নতুন রুম বরাদ্দ
if (isset($_GET['approve_id'])) {
    $app_id = intval($_GET['approve_id']);
    $app_q = mysqli_query($conn, "SELECT * FROM room_applications WHERE application_id = $app_id");
    
    if ($app_q && $app_data = mysqli_fetch_assoc($app_q)) {
        $uid = $app_data['user_id'];
        $hid = $app_data['hostel_id'];
        $rtype = mysqli_real_escape_string($conn, $app_data['room_type']);

        $room_q = mysqli_query($conn, "SELECT r.room_id, r.capacity, 
                                       (SELECT COUNT(*) FROM allocations a WHERE a.room_id = r.room_id AND a.status = 'Active') as occupied 
                                       FROM rooms r 
                                       WHERE r.hostel_id = $hid AND r.room_type = '$rtype' 
                                       HAVING (r.capacity - occupied) > 0 
                                       LIMIT 1");

        if (mysqli_num_rows($room_q) == 0) {
            $room_q = mysqli_query($conn, "SELECT r.room_id, r.capacity, 
                                           (SELECT COUNT(*) FROM allocations a WHERE a.room_id = r.room_id AND a.status = 'Active') as occupied 
                                           FROM rooms r 
                                           WHERE r.hostel_id = $hid 
                                           HAVING (r.capacity - occupied) > 0 
                                           LIMIT 1");
        }

        if ($room_q && $room_data = mysqli_fetch_assoc($room_q)) {
            $assigned_room_id = $room_data['room_id'];
            mysqli_query($conn, "UPDATE allocations SET status = 'Terminated' WHERE user_id = $uid AND status = 'Active'");
            mysqli_query($conn, "INSERT INTO allocations (room_id, user_id, status) VALUES ($assigned_room_id, $uid, 'Active')");
            mysqli_query($conn, "UPDATE room_applications SET status = 'Approved' WHERE application_id = $app_id");
            
            header("Location: room_applications.php?msg=approved");
            exit();
        } else {
            header("Location: room_applications.php?msg=noseat");
            exit();
        }
    }
}

// ২. আবেদন বাতিল (Reject)
if (isset($_GET['reject_id'])) {
    $app_id = intval($_GET['reject_id']);
    mysqli_query($conn, "UPDATE room_applications SET status = 'Rejected' WHERE application_id = $app_id");
    header("Location: room_applications.php?msg=rejected");
    exit();
}

// ৩. আবেদন ডিলিট (Delete)
if (isset($_GET['delete_id'])) {
    $app_id = intval($_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM room_applications WHERE application_id = $app_id");
    header("Location: room_applications.php?msg=deleted");
    exit();
}

if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'approved') {
        $message = "<div style='background:#d1e7dd; color:#0f5132; padding:12px; border-radius:5px; margin-bottom:15px; font-weight:bold;'>✅ Application Approved & Room allocated!</div>";
    } elseif ($_GET['msg'] == 'noseat') {
        $message = "<div style='background:#f8d7da; color:#842029; padding:12px; border-radius:5px; margin-bottom:15px; font-weight:bold;'>⚠️ No empty seats available in this hostel!</div>";
    } elseif ($_GET['msg'] == 'rejected') {
        $message = "<div style='background:#f8d7da; color:#842029; padding:12px; border-radius:5px; margin-bottom:15px; font-weight:bold;'>❌ Application Rejected.</div>";
    } elseif ($_GET['msg'] == 'deleted') {
        $message = "<div style='background:#d1e7dd; color:#0f5132; padding:12px; border-radius:5px; margin-bottom:15px; font-weight:bold;'>🗑️ Record deleted.</div>";
    }
}

$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$where_clause = "";
if ($filter == 'Pending') {
    $where_clause = "WHERE a.status = 'Pending'";
} elseif ($filter == 'Approved') {
    $where_clause = "WHERE a.status = 'Approved'";
}

$query = "SELECT a.application_id, u.email, h.hostel_name, a.room_type, a.status, a.applied_at 
          FROM room_applications a 
          LEFT JOIN users u ON a.user_id = u.user_id 
          LEFT JOIN hostels h ON a.hostel_id = h.hostel_id 
          $where_clause
          ORDER BY a.application_id DESC";
$result = mysqli_query($conn, $query);

$rows_html = "";
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $aid = $row['application_id'];
        $email = $row['email'] ? htmlspecialchars($row['email']) : "Student #" . $aid;
        $hname = $row['hostel_name'] ? htmlspecialchars($row['hostel_name']) : "Main Hostel";
        $rtype = htmlspecialchars($row['room_type']);
        $date = htmlspecialchars($row['applied_at']);
        $st = $row['status'];

        if ($st == 'Pending') {
            $badge = "<span style='background:#ffc107; color:#000; padding:4px 10px; border-radius:4px; font-size:12px; font-weight:bold;'>Pending</span>";
            $btns = "<a href='room_applications.php?approve_id=$aid' style='background:#198754; color:#fff; padding:5px 10px; text-decoration:none; border-radius:4px; font-size:12px; font-weight:bold; margin-right:5px; display:inline-block;'>Approve</a>
                     <a href='room_applications.php?reject_id=$aid' style='background:#dc3545; color:#fff; padding:5px 10px; text-decoration:none; border-radius:4px; font-size:12px; font-weight:bold; display:inline-block;'>Reject</a>";
        } elseif ($st == 'Approved') {
            $badge = "<span style='background:#198754; color:#fff; padding:4px 10px; border-radius:4px; font-size:12px; font-weight:bold;'>Approved</span>";
            $btns = "<a href='room_applications.php?delete_id=$aid' onclick='return confirm(\"Delete?\");' style='background:#6c757d; color:#fff; padding:5px 10px; text-decoration:none; border-radius:4px; font-size:12px; font-weight:bold; display:inline-block;'>Delete</a>";
        } else {
            $badge = "<span style='background:#dc3545; color:#fff; padding:4px 10px; border-radius:4px; font-size:12px; font-weight:bold;'>Rejected</span>";
            $btns = "<a href='room_applications.php?delete_id=$aid' onclick='return confirm(\"Delete?\");' style='background:#6c757d; color:#fff; padding:5px 10px; text-decoration:none; border-radius:4px; font-size:12px; font-weight:bold; display:inline-block;'>Delete</a>";
        }

        $rows_html .= "<tr style='border-bottom: 1px solid #ddd;'>
            <td style='padding: 12px; color:#000;'>#REQ-$aid</td>
            <td style='padding: 12px; color:#000;'><b>$email</b></td>
            <td style='padding: 12px; color:#000;'>$hname</td>
            <td style='padding: 12px; color:#000;'>$rtype</td>
            <td style='padding: 12px; color:#000;'>$date</td>
            <td style='padding: 12px;'>$badge</td>
            <td style='padding: 12px;'>$btns</td>
        </tr>";
    }
} else {
    $rows_html = "<tr><td colspan='7' style='padding: 15px; text-align:center; color:#555;'>No room applications found.</td></tr>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Room Applications - Hostel Management System</title>
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
                <li><a href="complaints.php">📢 Complaints</a></li>
                <li><a href="notices.php">📢 Notices</a></li>
                <li><a href="room_applications.php" class="active">📄 Room Applications</a></li>
                <li><a href="reports.php">📋 Reports</a></li>
            </ul>
        </div>

        <div class="content">
            <h1 style="color:#000; margin-bottom:20px;">Room Applications</h1>
            
            <?php echo $message; ?>

            <div style="margin-bottom: 15px;">
                <a href="room_applications.php" style="background:#212529; color:#fff; padding:8px 15px; text-decoration:none; border-radius:4px; font-size:13px; font-weight:bold; margin-right:5px; display:inline-block;">All Requests</a>
                <a href="room_applications.php?filter=Pending" style="background:#ffc107; color:#000; padding:8px 15px; text-decoration:none; border-radius:4px; font-size:13px; font-weight:bold; margin-right:5px; display:inline-block;">Pending Only</a>
                <a href="room_applications.php?filter=Approved" style="background:#198754; color:#fff; padding:8px 15px; text-decoration:none; border-radius:4px; font-size:13px; font-weight:bold; display:inline-block;">Approved Only</a>
            </div>

            <div style="background: #ffffff; border-radius: 6px; border: 1px solid #ccc; overflow-x: auto;">
                <div style="background: #0d6efd; color: #ffffff; padding: 12px 20px; font-weight: bold;">Student Room Requests</div>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #212529; color: #ffffff;">
                            <th style="padding: 12px; text-align: left;">Req ID</th>
                            <th style="padding: 12px; text-align: left;">Student Email</th>
                            <th style="padding: 12px; text-align: left;">Requested Hostel</th>
                            <th style="padding: 12px; text-align: left;">Room Type</th>
                            <th style="padding: 12px; text-align: left;">Applied Date</th>
                            <th style="padding: 12px; text-align: left;">Status</th>
                            <th style="padding: 12px; text-align: left;">Actions</th>
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
