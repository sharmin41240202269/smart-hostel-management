<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
    header("Location: login.php?role=Admin");
    exit();
}

// ১. সামগ্রিক পরিসংখ্যান (Aggregation Queries: COUNT & SUM)
$total_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'Customer'"))['total'] ?? 0;
$total_hostels = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM hostels"))['total'] ?? 0;
$total_rooms = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM rooms"))['total'] ?? 0;
$total_seats = mysqli_fetch_assoc(mysqli_query($conn, "SELECT IFNULL(SUM(capacity), 0) as total FROM rooms"))['total'] ?? 0;
$occupied_seats = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM allocations WHERE status = 'Active'"))['total'] ?? 0;
$available_seats = max(0, $total_seats - $occupied_seats);

$total_income = mysqli_fetch_assoc(mysqli_query($conn, "SELECT IFNULL(SUM(amount), 0) as total FROM payments WHERE status = 'Paid'"))['total'] ?? 0;
$total_pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT IFNULL(SUM(amount), 0) as total FROM payments WHERE status = 'Pending'"))['total'] ?? 0;

$pending_complaints = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM complaints WHERE status = 'Pending'"))['total'] ?? 0;
$resolved_complaints = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM complaints WHERE status = 'Resolved'"))['total'] ?? 0;

// ২. হোস্টেল অনুযায়ী বিস্তারিত রিপোর্ট (LEFT JOIN & GROUP BY)
$hostel_report_q = "SELECT h.hostel_name, h.type, 
                    COUNT(DISTINCT r.room_id) as room_count, 
                    IFNULL(SUM(r.capacity), 0) as seat_capacity,
                    (SELECT COUNT(*) FROM allocations a JOIN rooms rm ON a.room_id = rm.room_id WHERE rm.hostel_id = h.hostel_id AND a.status = 'Active') as occupied
                    FROM hostels h 
                    LEFT JOIN rooms r ON h.hostel_id = r.hostel_id 
                    GROUP BY h.hostel_id";
$hostel_report_res = mysqli_query($conn, $hostel_report_q);

$hostel_rows = "";
if ($hostel_report_res && mysqli_num_rows($hostel_report_res) > 0) {
    while ($h = mysqli_fetch_assoc($hostel_report_res)) {
        $hname = htmlspecialchars($h['hostel_name']);
        $htype = htmlspecialchars($h['type']);
        $rcount = $h['room_count'];
        $scap = $h['seat_capacity'];
        $occ = $h['occupied'];
        $avail = max(0, $scap - $occ);

        $hostel_rows .= "<tr style='border-bottom: 1px solid #ddd;'>
            <td style='padding: 10px; color:#000;'><b>$hname</b></td>
            <td style='padding: 10px; color:#000;'>$htype</td>
            <td style='padding: 10px; color:#000;'>$rcount Rooms</td>
            <td style='padding: 10px; color:#000;'>$scap Seats</td>
            <td style='padding: 10px; color:#198754;'><b>$occ Occupied</b></td>
            <td style='padding: 10px; color:#0d6efd;'><b>$avail Available</b></td>
        </tr>";
    }
} else {
    $hostel_rows = "<tr><td colspan='6' style='padding: 12px; text-align:center;'>No hostel data found.</td></tr>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Reports - Hostel Management System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* প্রিন্ট করার সময় সাইডবার ও বাটন লুকানোর জন্য CSS */
        @media print {
            .top-navbar, .sidebar, .no-print {
                display: none !important;
            }
            .content {
                margin-left: 0 !important;
                width: 100% !important;
                padding: 0 !important;
            }
        }
    </style>
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
                <li><a href="room_applications.php">📄 Room Applications</a></li>
                <li><a href="reports.php" class="active">📋 Reports</a></li>
            </ul>
        </div>

        <div class="content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h1 style="color:#000; margin:0;">System Analytical Report</h1>
                <button class="no-print" onclick="window.print()" style="background:#212529; color:#fff; border:none; padding:10px 20px; border-radius:5px; cursor:pointer; font-weight:bold; font-size:14px;">🖨️ Print / Save PDF</button>
            </div>

            <p style="color:#555; margin-bottom:20px; font-size:14px;">Report Generated on: <b><?php echo date("d M, Y - h:i A"); ?></b></p>

            <!-- ১. সামগ্রিক সারসংক্ষেপ বক্স -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 25px;">
                <div style="background:#fff; padding:18px; border-radius:6px; border:1px solid #ccc; border-left: 5px solid #0d6efd;">
                    <h4 style="color:#555; font-size:13px; margin-bottom:5px;">STUDENT & SEAT SUMMARY</h4>
                    <p style="color:#000; font-size:15px; margin:4px 0;">Registered Students: <b><?php echo $total_students; ?></b></p>
                    <p style="color:#198754; font-size:15px; margin:4px 0;">Allocated Seats: <b><?php echo $occupied_seats; ?> / <?php echo $total_seats; ?></b></p>
                    <p style="color:#0d6efd; font-size:15px; margin:4px 0;">Available Seats: <b><?php echo $available_seats; ?></b></p>
                </div>

                <div style="background:#fff; padding:18px; border-radius:6px; border:1px solid #ccc; border-left: 5px solid #198754;">
                    <h4 style="color:#555; font-size:13px; margin-bottom:5px;">FINANCIAL SUMMARY</h4>
                    <p style="color:#198754; font-size:15px; margin:4px 0;">Total Collected (Paid): <b>৳<?php echo number_format($total_income, 2); ?></b></p>
                    <p style="color:#d97706; font-size:15px; margin:4px 0;">Pending Dues: <b>৳<?php echo number_format($total_pending, 2); ?></b></p>
                    <p style="color:#000; font-size:15px; margin:4px 0;">Total Expected: <b>৳<?php echo number_format($total_income + $total_pending, 2); ?></b></p>
                </div>

                <div style="background:#fff; padding:18px; border-radius:6px; border:1px solid #ccc; border-left: 5px solid #dc3545;">
                    <h4 style="color:#555; font-size:13px; margin-bottom:5px;">INFRASTRUCTURE & ISSUES</h4>
                    <p style="color:#000; font-size:15px; margin:4px 0;">Total Hostels: <b><?php echo $total_hostels; ?> Blocks</b></p>
                    <p style="color:#000; font-size:15px; margin:4px 0;">Total Rooms: <b><?php echo $total_rooms; ?> Rooms</b></p>
                    <p style="color:#dc3545; font-size:15px; margin:4px 0;">Pending Complaints: <b><?php echo $pending_complaints; ?></b> (Resolved: <?php echo $resolved_complaints; ?>)</p>
                </div>
            </div>

            <!-- ২. হোস্টেল অনুযায়ী বিস্তারিত টেবিল -->
            <div style="background: #ffffff; border-radius: 6px; border: 1px solid #ccc; overflow-x: auto;">
                <div style="background: #212529; color: #ffffff; padding: 12px 20px; font-weight: bold;">Hostel-wise Capacity & Occupancy Breakdown</div>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                            <th style="padding: 12px; text-align: left; color:#000;">Hostel Name</th>
                            <th style="padding: 12px; text-align: left; color:#000;">Category</th>
                            <th style="padding: 12px; text-align: left; color:#000;">Total Rooms</th>
                            <th style="padding: 12px; text-align: left; color:#000;">Total Capacity</th>
                            <th style="padding: 12px; text-align: left; color:#000;">Occupied Seats</th>
                            <th style="padding: 12px; text-align: left; color:#000;">Empty Seats</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php echo $hostel_rows; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</body>
</html>
