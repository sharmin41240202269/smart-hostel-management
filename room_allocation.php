<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
    header("Location: login.php?role=Admin");
    exit();
}

$message = "";

// ১. রুম বরাদ্দ দেওয়ার লজিক (ডুপ্লিকেট এলোকেশন চেকসহ)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['allocate'])) {
    $room_id = intval($_POST["room_id"]);
    $user_id = intval($_POST["user_id"]);

    // চেক করা: এই স্টুডেন্টের ইতিমধ্যে কোনো একটিভ রুম আছে কি না
    $check_user = mysqli_query($conn, "SELECT allocation_id FROM allocations WHERE user_id = $user_id AND status = 'Active'");
    if (mysqli_num_rows($check_user) > 0) {
        $message = "<div class='error-message'>⚠️ This student already has an active room allocated!</div>";
    } else {
        $insert_sql = "INSERT INTO allocations (room_id, user_id, status) VALUES (?, ?, 'Active')";
        $stmt = mysqli_prepare($conn, $insert_sql);
        mysqli_stmt_bind_param($stmt, "ii", $room_id, $user_id);
        if (mysqli_stmt_execute($stmt)) {
            $message = "<div class='success-message'>✅ Room allocated successfully!</div>";
        } else {
            $message = "<div class='error-message'>❌ Failed to allocate room. Please check again.</div>";
        }
        mysqli_stmt_close($stmt);
    }
}

// ২. রুম বরাদ্দ বাতিল করার লজিক (Terminate Allocation)
if (isset($_GET['terminate_id'])) {
    $alloc_id = intval($_GET['terminate_id']);
    mysqli_query($conn, "UPDATE allocations SET status = 'Terminated' WHERE allocation_id = $alloc_id");
    header("Location: room_allocation.php?msg=terminated");
    exit();
}

if (isset($_GET['msg']) && $_GET['msg'] == 'terminated') {
    $message = "<div class='success-message'>🔄 Room allocation terminated! Seat is now available.</div>";
}

// ড্রপডাউনের জন্য শুধুমাত্র সেই স্টুডেন্টদের আনা যাদের এখনো রুম নেই (Subquery ব্যবহার করে)
$students = mysqli_query($conn, "SELECT user_id, email FROM users WHERE role = 'Customer' AND status = 'Active' AND user_id NOT IN (SELECT user_id FROM allocations WHERE status = 'Active') ORDER BY email ASC");

// ড্রপডাউনের জন্য হোস্টেলের নাম ও খালি সিটসহ রুমগুলো আনা
$rooms = mysqli_query($conn, "SELECT r.room_id, r.room_number, r.capacity, h.hostel_name,
                             (SELECT COUNT(*) FROM allocations a WHERE a.room_id = r.room_id AND a.status = 'Active') as occupied
                             FROM rooms r 
                             LEFT JOIN hostels h ON r.hostel_id = h.hostel_id
                             HAVING (r.capacity - occupied) > 0
                             ORDER BY h.hostel_name ASC, r.room_number ASC");

// এলোকেশন হিস্ট্রি টেবিল (JOIN ব্যবহার করে)
$history_query = "SELECT a.allocation_id, r.room_number, h.hostel_name, u.email, a.status, a.allocated_at 
                  FROM allocations a 
                  JOIN rooms r ON a.room_id = r.room_id 
                  LEFT JOIN hostels h ON r.hostel_id = h.hostel_id
                  JOIN users u ON a.user_id = u.user_id 
                  ORDER BY a.allocation_id DESC";
$history = mysqli_query($conn, $history_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Room Allocation - Hostel Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="top-navbar">
        <h2>Hostel Management System</h2>
        <div class="nav-right"><a href="logout.php" class="logout-btn">Logout</a></div>
    </div>
    <div class="main-container">
        <div class="sidebar">
            <h3>Hostel Management</h3>
            <ul>
                <li><a href="admin_dashboard.php">📊 Dashboard</a></li>
                <li><a href="students.php">👥 Students</a></li>
                <li><a href="hostels.php">🏨 Hostels</a></li>
                <li><a href="rooms.php">🛏️ Rooms</a></li>
                <li><a href="room_allocation.php" class="active">🔄 Room Allocation</a></li>
                <li><a href="payments.php">💰 Payments</a></li>
                <li><a href="complaints.php">📢 Complaints</a></li>
                <li><a href="notices.php">📢 Notices</a></li>
                <li><a href="room_applications.php">📄 Room Applications</a></li>
                <li><a href="reports.php">📋 Reports</a></li>
            </ul>
        </div>
        <div class="content">
            <h1>Room Allocation</h1>
            <?php echo $message; ?>
            
            <div class="table-section" style="padding: 20px; margin-bottom: 30px;">
                <div style="font-weight: bold; margin-bottom: 15px;">Allocate Room to Student</div>
                <form method="POST" action="room_allocation.php" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
                    <div style="flex:1; min-width: 220px;">
                        <label style="display:block; margin-bottom:5px; font-size:14px;">Select Unassigned Student</label>
                        <select name="user_id" required style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
                            <option value="">-- Choose Student --</option>
                            <?php while($row = mysqli_fetch_assoc($students)) { 
                                echo "<option value='".$row['user_id']."'>".$row['email']."</option>"; 
                            } ?>
                        </select>
                    </div>
                    <div style="flex:1; min-width: 220px;">
                        <label style="display:block; margin-bottom:5px; font-size:14px;">Select Available Room</label>
                        <select name="room_id" required style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
                            <option value="">-- Choose Room --</option>
                            <?php while($row = mysqli_fetch_assoc($rooms)) { 
                                $left = $row['capacity'] - $row['occupied'];
                                $h_name = $row['hostel_name'] ?? 'Main Hostel';
                                echo "<option value='".$row['room_id']."'>".$h_name." - Room ".$row['room_number']." (Seats Left: ".$left.")</option>"; 
                            } ?>
                        </select>
                    </div>
                    <button type="submit" name="allocate" style="background:#0dcaf0; color:white; border:none; padding:9px 20px; border-radius:4px; cursor:pointer; font-weight:bold;">Allocate</button>
                </form>
            </div>

            <div class="table-section">
                <div class="table-header">Allocation History</div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Hostel Name</th>
                            <th>Room No</th>
                            <th>Student Email</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if (mysqli_num_rows($history) > 0) {
                            while ($row = mysqli_fetch_assoc($history)) {
                                $h_name = $row['hostel_name'] ?? 'Main Hostel';
                                $status_badge = ($row['status'] == 'Active') ? 'status-active' : 'bg-grey';
                                $status_style = ($row['status'] != 'Active') ? "style='padding:3px 8px; border-radius:4px; color:white; font-size:12px;'" : "";

                                echo "<tr>
                                    <td>".$row['allocation_id']."</td>
                                    <td><b>".$h_name."</b></td>
                                    <td>".$row['room_number']."</td>
                                    <td>".$row['email']."</td>
                                    <td><span class='$status_badge' $status_style>".$row['status']."</span></td>
                                    <td>";
                                if ($row['status'] == 'Active') {
                                    echo "<a href='room_allocation.php?terminate_id=".$row['allocation_id']."' onclick='return confirm(\"Release this student from the room?\");' style='background:#dc3545; color:white; padding:4px 8px; text-decoration:none; border-radius:4px; font-size:12px;'>Vacate / Release</a>";
                                } else {
                                    echo "<span style='color:#888; font-size:12px;'>Released</span>";
                                }
                                echo "</td></tr>";
                            }
                        } else { echo "<tr><td colspan='6' style='text-align:center;'>No active allocations found</td></tr>"; }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
