<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
    header("Location: login.php?role=Admin");
    exit();
}

// ড্রপডাউনের জন্য সব হোস্টেলের তালিকা নিয়ে আসা
$hostels_list = mysqli_query($conn, "SELECT hostel_id, hostel_name, type FROM hostels ORDER BY hostel_name ASC");
$hostels_array = [];
while ($h = mysqli_fetch_assoc($hostels_list)) {
    $hostels_array[] = $h;
}

// কোন হোস্টেলটি বর্তমানে সিলেক্ট করা আছে তা নির্ধারণ করা (URL অথবা POST থেকে)
$selected_hostel_id = 0;
if (isset($_GET['hostel_id'])) {
    $selected_hostel_id = intval($_GET['hostel_id']);
} elseif (isset($_POST['hostel_id'])) {
    $selected_hostel_id = intval($_POST['hostel_id']);
} elseif (!empty($hostels_array)) {
    $selected_hostel_id = $hostels_array[0]['hostel_id']; // ডিফল্টভাবে প্রথম হোস্টেলটি সিলেক্ট থাকবে
}

$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_room'])) {
    $hostel_id = intval($_POST["hostel_id"]);
    $room_no = trim($_POST["room_number"]);
    $type = $_POST["room_type"];
    $capacity = intval($_POST["capacity"]);
    $price = floatval($_POST["price_per_month"]);

    // ওই নির্দিষ্ট হোস্টেলে রুম নম্বরটি ইতিমধ্যে আছে কি না চেক করা
    $check_sql = "SELECT room_id FROM rooms WHERE hostel_id = ? AND room_number = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "is", $hostel_id, $room_no);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);

    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        $message = "<div class='error-message'>⚠️ Room No '$room_no' already exists in this hostel!</div>";
    } else {
        $insert_sql = "INSERT INTO rooms (hostel_id, room_number, room_type, capacity, price_per_month, status) VALUES (?, ?, ?, ?, ?, 'Available')";
        $stmt = mysqli_prepare($conn, $insert_sql);
        mysqli_stmt_bind_param($stmt, "issid", $hostel_id, $room_no, $type, $capacity, $price);
        
        if (mysqli_stmt_execute($stmt)) {
            $message = "<div class='success-message'>✅ Room No '$room_no' added successfully!</div>";
        } else {
            $message = "<div class='error-message'>❌ Something went wrong. Please try again.</div>";
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_stmt_close($check_stmt);
}

// শুধুমাত্র সিলেক্ট করা হোস্টেলের রুমগুলো নিয়ে আসার কোয়েরি (WHERE ফিল্টারসহ)
$query = "SELECT r.*, h.hostel_name 
          FROM rooms r 
          LEFT JOIN hostels h ON r.hostel_id = h.hostel_id 
          WHERE r.hostel_id = ? 
          ORDER BY r.room_number ASC";
$stmt_rooms = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt_rooms, "i", $selected_hostel_id);
mysqli_stmt_execute($stmt_rooms);
$result = mysqli_stmt_get_result($stmt_rooms);

// সিলেক্ট করা হোস্টেলের নাম বের করা (টেবিলের হেডারে দেখানোর জন্য)
$current_hostel_name = "Selected Hostel";
foreach ($hostels_array as $ha) {
    if ($ha['hostel_id'] == $selected_hostel_id) {
        $current_hostel_name = $ha['hostel_name'];
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Rooms - Hostel Management System</title>
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
                <li><a href="rooms.php" class="active">🛏️ Rooms</a></li>
                <li><a href="room_allocation.php">🔄 Room Allocation</a></li>
                <li><a href="payments.php">💰 Payments</a></li>
                <li><a href="complaints.php">📢 Complaints</a></li>
                <li><a href="notices.php">📢 Notices</a></li>
                <li><a href="room_applications.php">📄 Room Applications</a></li>
                <li><a href="reports.php">📋 Reports</a></li>
            </ul>
        </div>
        <div class="content">
            <h1>Room Management</h1>
            <?php echo $message; ?>
            
            <!-- নতুন রুম অ্যাড করার ফর্ম -->
            <div class="table-section" style="padding: 20px; margin-bottom: 30px;">
                <div style="font-weight: bold; margin-bottom: 15px;">Add New Room</div>
                <form method="POST" action="rooms.php?hostel_id=<?php echo $selected_hostel_id; ?>" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
                    <div>
                        <label style="display:block; margin-bottom:5px; font-size:14px;">Select Hostel</label>
                        <!-- হোস্টেল পরিবর্তন করার সাথে সাথে পেজ রিলোড হয়ে ওই হোস্টেলের রুম দেখাবে -->
                        <select name="hostel_id" onchange="window.location.href='rooms.php?hostel_id=' + this.value;" required style="padding:8px; border:1px solid #ddd; border-radius:4px; width:180px; font-weight:bold;">
                            <?php foreach($hostels_array as $h) {
                                $selected = ($h['hostel_id'] == $selected_hostel_id) ? "selected" : "";
                                echo "<option value='".$h['hostel_id']."' $selected>".$h['hostel_name']."</option>";
                            } ?>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; margin-bottom:5px; font-size:14px;">Room No</label>
                        <input type="text" name="room_number" placeholder="e.g. 101" required style="padding:8px; border:1px solid #ddd; border-radius:4px; width:100px;">
                    </div>
                    <div>
                        <label style="display:block; margin-bottom:5px; font-size:14px;">Room Type</label>
                        <select name="room_type" style="padding:8px; border:1px solid #ddd; border-radius:4px; width:130px;">
                            <option value="Single AC">Single AC</option>
                            <option value="Double Non-AC">Double Non-AC</option>
                            <option value="4-Bed Share">4-Bed Share</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; margin-bottom:5px; font-size:14px;">Total Seats</label>
                        <input type="number" name="capacity" min="1" required style="padding:8px; border:1px solid #ddd; border-radius:4px; width:85px;">
                    </div>
                    <div>
                        <label style="display:block; margin-bottom:5px; font-size:14px;">Rent / Month (৳)</label>
                        <input type="number" step="0.01" name="price_per_month" required style="padding:8px; border:1px solid #ddd; border-radius:4px; width:120px;">
                    </div>
                    <button type="submit" name="add_room" style="background:#ffc107; color:#333; border:none; padding:9px 18px; border-radius:4px; cursor:pointer; font-weight:bold;">Add Room</button>
                </form>
            </div>

            <!-- শুধুমাত্র সিলেক্ট করা হোস্টেলের রুম লিস্ট টেবিল -->
            <div class="table-section">
                <div class="table-header">Rooms in <?php echo htmlspecialchars($current_hostel_name); ?></div>
                <table>
                    <thead>
                        <tr>
                            <th>Room No</th>
                            <th>Type</th>
                            <th>Total Seats</th>
                            <th>Monthly Rent</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>
                                    <td><b>".$row['room_number']."</b></td>
                                    <td>".$row['room_type']."</td>
                                    <td>".$row['capacity']."</td>
                                    <td>৳".number_format($row['price_per_month'], 2)."</td>
                                    <td><span class='status-active'>".$row['status']."</span></td>
                                </tr>";
                            }
                        } else { echo "<tr><td colspan='5' style='text-align:center;'>No rooms found in this hostel.</td></tr>"; }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
