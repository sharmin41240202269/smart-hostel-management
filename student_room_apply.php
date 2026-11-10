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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_application'])) {
    $room_id = intval($_POST["room_id"]);

    if ($room_id <= 0) {
        $message = "<div style='background:#f8d7da; color:#842029; padding:12px; border-radius:5px; margin-bottom:15px; font-weight:bold;'>Please select a room first!</div>";
    } else {
        $check_app = mysqli_query($conn, "SELECT application_id FROM room_applications WHERE user_id = $user_id AND status = 'Pending'");
        
        if ($check_app && mysqli_num_rows($check_app) > 0) {
            $message = "<div style='background:#f8d7da; color:#842029; padding:12px; border-radius:5px; margin-bottom:15px; font-weight:bold;'>You already have a pending request! Please wait for Admin approval.</div>";
        } else {
            $room_info_q = mysqli_query($conn, "SELECT hostel_id, room_type FROM rooms WHERE room_id = $room_id LIMIT 1");
            $room_info_data = mysqli_fetch_assoc($room_info_q);
            $hostel_id = $room_info_data['hostel_id'] ?? 1;
            $room_type = $room_info_data['room_type'] ?? 'Single AC';

            $insert_sql = "INSERT INTO room_applications (user_id, hostel_id, room_type, status) VALUES (?, ?, ?, 'Pending')";
            $stmt = mysqli_prepare($conn, $insert_sql);
            mysqli_stmt_bind_param($stmt, "iis", $user_id, $hostel_id, $room_type);
            if (mysqli_stmt_execute($stmt)) {
                $message = "<div style='background:#d1e7dd; color:#0f5132; padding:12px; border-radius:5px; margin-bottom:15px; font-weight:bold;'>Room application submitted successfully! Admin will review it soon.</div>";
            } else {
                $err = mysqli_error($conn);
                $message = "<div style='background:#f8d7da; color:#842029; padding:12px; border-radius:5px; margin-bottom:15px; font-weight:bold;'>Error: $err</div>";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

$rooms_query = "SELECT r.room_id, r.room_number, r.room_type, r.capacity, r.price_per_month, h.hostel_name, h.type AS hostel_type,
                (SELECT COUNT(*) FROM allocations a WHERE a.room_id = r.room_id AND a.status = 'Active') as occupied
                FROM rooms r
                LEFT JOIN hostels h ON r.hostel_id = h.hostel_id
                ORDER BY h.type ASC, h.hostel_name ASC, r.room_number ASC";
$rooms_result = mysqli_query($conn, $rooms_query);

$room_options_html = "";
if ($rooms_result) {
    while ($row = mysqli_fetch_assoc($rooms_result)) {
        $seats_left = max(0, $row['capacity'] - $row['occupied']);
        if ($seats_left > 0) {
            $rid = $row['room_id'];
            $hname = htmlspecialchars($row['hostel_name'] ?? 'Main Hostel');
            $htype = htmlspecialchars($row['hostel_type'] ?? 'General');
            $rnum = htmlspecialchars($row['room_number']);
            $rtype = htmlspecialchars($row['room_type']);
            $rent = $row['price_per_month'];
            $room_options_html .= "<option value='$rid'>$hname [$htype] - Room $rnum ($rtype | Rent: ৳$rent) - Seats Left: $seats_left</option>";
        }
    }
}

$history_q = "SELECT a.application_id, h.hostel_name, h.type AS hostel_type, a.room_type, a.status, a.applied_at 
              FROM room_applications a 
              LEFT JOIN hostels h ON a.hostel_id = h.hostel_id 
              WHERE a.user_id = $user_id 
              ORDER BY a.application_id DESC";
$history_res = mysqli_query($conn, $history_q);

$history_rows_html = "";
if ($history_res && mysqli_num_rows($history_res) > 0) {
    while ($hrow = mysqli_fetch_assoc($history_res)) {
        $aid = $hrow['application_id'];
        $hname = htmlspecialchars($hrow['hostel_name'] ?? 'Main Hostel');
        $htype = htmlspecialchars($hrow['hostel_type'] ?? '');
        $rtype = htmlspecialchars($hrow['room_type']);
        $adate = htmlspecialchars($hrow['applied_at']);
        $st = $hrow['status'];

        $hostel_label = $htype ? "<b>$hname</b> <small style='color:#555;'>($htype)</small>" : "<b>$hname</b>";

        if ($st == 'Pending') {
            $badge = "<span style='background:#ffc107; color:#000; padding:4px 10px; border-radius:4px; font-size:12px; font-weight:bold;'>Pending</span>";
        } elseif ($st == 'Approved') {
            $badge = "<span style='background:#198754; color:#fff; padding:4px 10px; border-radius:4px; font-size:12px; font-weight:bold;'>Approved</span>";
        } else {
            $badge = "<span style='background:#dc3545; color:#fff; padding:4px 10px; border-radius:4px; font-size:12px; font-weight:bold;'>Rejected</span>";
        }

        $history_rows_html .= "<tr style='border-bottom: 1px solid #ddd;'>
            <td style='padding: 12px; color:#000;'>#REQ-$aid</td>
            <td style='padding: 12px; color:#000;'>$hostel_label</td>
            <td style='padding: 12px; color:#000;'>$rtype</td>
            <td style='padding: 12px;'>$badge</td>
            <td style='padding: 12px; color:#000;'>$adate</td>
        </tr>";
    }
} else {
    $history_rows_html = "<tr><td colspan='5' style='padding: 15px; text-align:center; color:#555;'>You have not submitted any room requests yet.</td></tr>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Apply Room - Hostel Management System</title>
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
                <li><a href="student_room_apply.php" class="active" style="background-color: #0b6623; color: white; padding: 10px 20px; font-size: 14px;">➡️ Apply Room</a></li>
                <li><a href="student_payments.php" style="padding: 10px 20px; font-size: 14px;">💰 Payments</a></li>
                <li><a href="student_complaints.php" style="padding: 10px 20px; font-size: 14px;">📩 Complaints</a></li>
                <li style="margin-top: 20px;"><a href="logout.php" style="color: #ff4d4d; padding: 10px 20px; font-size: 14px;">🚪 Logout</a></li>
            </ul>
        </div>

        <div class="content" style="margin-left: 220px; padding: 20px; width: calc(100% - 220px);">
            
            <?php echo $message; ?>

            <div style="background: #ffffff; border: 1px solid #dee2e6; border-radius: 5px; overflow: hidden; margin-bottom: 25px;">
                <div style="background-color: #0d6efd; color: white; padding: 12px 20px; font-size: 18px; font-weight: bold;">
                    Apply For New Room / Room Change
                </div>
                
                <form method="POST" action="student_room_apply.php" style="padding: 20px;">
                    <div style="margin-bottom: 20px;">
                        <label style="display:block; margin-bottom:8px; font-size:14px; font-weight:bold; color:#000;">Select Available Room (Check Boys / Girls Hostel carefully)</label>
                        <select name="room_id" required style="width:100%; padding:10px; border:1px solid #999; border-radius:4px; font-size: 14px; background-color: #fff; color:#000;">
                            <option value="">-- Choose Room (Hostel [Boys/Girls] - Room No - Seats Left) --</option>
                            <?php echo $room_options_html; ?>
                        </select>
                    </div>
                    
                    <button type="submit" name="submit_application" style="background-color: #0d6efd; color: white; border: none; padding: 10px 25px; font-size: 14px; font-weight: bold; border-radius: 4px; cursor: pointer;">
                        Submit Request
                    </button>
                </form>
            </div>

            <div style="background: #ffffff; border-radius: 5px; border: 1px solid #ccc; overflow-x: auto;">
                <div style="background: #0b6623; color: #ffffff; padding: 12px 20px; font-weight: bold;">My Room Application History</div>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #212529; color: #ffffff;">
                            <th style="padding: 12px; text-align: left;">Req ID</th>
                            <th style="padding: 12px; text-align: left;">Hostel Name & Category</th>
                            <th style="padding: 12px; text-align: left;">Room Type</th>
                            <th style="padding: 12px; text-align: left;">Status</th>
                            <th style="padding: 12px; text-align: left;">Applied Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php echo $history_rows_html; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</body>
</html>
