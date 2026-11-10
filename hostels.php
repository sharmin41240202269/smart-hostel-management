<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
    header("Location: login.php?role=Admin");
    exit();
}

// নতুন হোস্টেল যোগ করার লজিক
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_hostel'])) {
    $name = trim($_POST["hostel_name"]);
    $type = $_POST["type"];
    $address = trim($_POST["address"]);

    $insert_sql = "INSERT INTO hostels (hostel_name, type, address) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insert_sql);
    mysqli_stmt_bind_param($stmt, "sss", $name, $type, $address);
    if (mysqli_stmt_execute($stmt)) {
        $message = "<div class='success-message'>Hostel added successfully!</div>";
    } else {
        $message = "<div class='error-message'>Something went wrong!</div>";
    }
}

// সকল হোস্টেলের তালিকা নিয়ে আসা
$query = "SELECT * FROM hostels ORDER BY hostel_id DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Hostels - Hostel Management System</title>
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
                <li><a href="hostels.php" class="active">🏨 Hostels</a></li>
                <li><a href="rooms.php">🛏️ Rooms</a></li>
                <li><a href="room_allocation.php">🔄 Room Allocation</a></li>
                <li><a href="payments.php">💰 Payments</a></li>
                <li><a href="complaints.php">📢 Complaints</a></li>
                <li><a href="notices.php">📢 Notices</a></li>
                <li><a href="room_applications.php">📄 Room Applications</a></li>
                <li><a href="reports.php">📋 Reports</a></li>
            </ul>
        </div>

        <div class="content">
            <h1>Hostel Management</h1>
            <?php echo $message; ?>

            <!-- নতুন হোস্টেল অ্যাড করার ফর্ম -->
            <div class="table-section" style="padding: 20px; margin-bottom: 30px;">
                <div style="font-weight: bold; margin-bottom: 15px;">Add New Hostel Block</div>
                <form method="POST" action="hostels.php" style="display: flex; gap: 15px; align-items: flex-end;">
                    <div style="flex: 2;">
                        <label style="display:block; margin-bottom:5px; font-size:14px;">Hostel Name</label>
                        <input type="text" name="hostel_name" required style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
                    </div>
                    <div style="flex: 1;">
                        <label style="display:block; margin-bottom:5px; font-size:14px;">Type</label>
                        <select name="type" required style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
                            <option value="Boys">Boys</option>
                            <option value="Girls">Girls</option>
                            <option value="Co-Ed">Co-Ed</option>
                        </select>
                    </div>
                    <div style="flex: 2;">
                        <label style="display:block; margin-bottom:5px; font-size:14px;">Address / Location</label>
                        <input type="text" name="address" required style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
                    </div>
                    <button type="submit" name="add_hostel" style="background:#198754; color:white; border:none; padding:9px 20px; border-radius:4px; cursor:pointer; font-weight:bold;">Add Hostel</button>
                </form>
            </div>

            <!-- হোস্টেল লিস্ট টেবিল -->
            <div class="table-section">
                <div class="table-header">All Hostel Blocks</div>
                <table>
                    <thead>
                        <tr>
                            <th>Hostel ID</th>
                            <th>Hostel Name</th>
                            <th>Type</th>
                            <th>Address Location</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['hostel_id']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['hostel_name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['type']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['address']) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' style='text-align:center;'>No hostels found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
