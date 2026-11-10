<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
    header("Location: login.php?role=Admin");
    exit();
}

$message = "";

// ১. নতুন স্টুডেন্ট যোগ করার লজিক
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_student'])) {
    $email = strtolower(trim($_POST["email"]));
    $password = trim($_POST["password"]);
    
    $check_sql = "SELECT user_id FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if (mysqli_stmt_num_rows($stmt) > 0) {
        $message = "<div class='error-message'>⚠️ Email '$email' is already registered!</div>";
    } else {
        $insert_sql = "INSERT INTO users (email, password_hash, role, status) VALUES (?, ?, 'Customer', 'Active')";
        $ins_stmt = mysqli_prepare($conn, $insert_sql);
        mysqli_stmt_bind_param($ins_stmt, "ss", $email, $password);
        if (mysqli_stmt_execute($ins_stmt)) {
            $message = "<div class='success-message'>✅ New student '$email' added successfully!</div>";
        } else {
            $message = "<div class='error-message'>❌ Something went wrong.</div>";
        }
        mysqli_stmt_close($ins_stmt);
    }
    mysqli_stmt_close($stmt);
}

// ২. স্ট্যাটাস পরিবর্তন
if (isset($_GET['toggle_id'])) {
    $uid = intval($_GET['toggle_id']);
    $curr_status = $_GET['current'];
    $new_status = ($curr_status == 'Active') ? 'Inactive' : 'Active';
    mysqli_query($conn, "UPDATE users SET status = '$new_status' WHERE user_id = $uid AND role = 'Customer'");
    header("Location: students.php");
    exit();
}

// ৩. স্টুডেন্ট ডিলিট করা
if (isset($_GET['delete_id'])) {
    $uid = intval($_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM users WHERE user_id = $uid AND role = 'Customer'");
    header("Location: students.php?msg=deleted");
    exit();
}

if (isset($_GET['msg']) && $_GET['msg'] == 'deleted') {
    $message = "<div class='success-message'>🗑️ Student account removed successfully!</div>";
}

// ৪. পাসওয়ার্ডসহ স্টুডেন্ট তালিকা ও সার্চ ফিল্টারিং
$search_query = "";
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_term = "%" . trim($_GET['search']) . "%";
    $query = "SELECT u.user_id, u.email, u.password_hash, u.status, r.room_number 
              FROM users u 
              LEFT JOIN allocations a ON u.user_id = a.user_id AND a.status = 'Active'
              LEFT JOIN rooms r ON a.room_id = r.room_id
              WHERE u.role = 'Customer' AND u.email LIKE ? 
              ORDER BY u.user_id DESC";
    $stmt_search = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt_search, "s", $search_term);
    mysqli_stmt_execute($stmt_search);
    $result = mysqli_stmt_get_result($stmt_search);
    $search_query = htmlspecialchars(trim($_GET['search']));
} else {
    $query = "SELECT u.user_id, u.email, u.password_hash, u.status, r.room_number 
              FROM users u 
              LEFT JOIN allocations a ON u.user_id = a.user_id AND a.status = 'Active'
              LEFT JOIN rooms r ON a.room_id = r.room_id
              WHERE u.role = 'Customer' 
              ORDER BY u.user_id DESC";
    $result = mysqli_query($conn, $query);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Students - Hostel Management System</title>
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
                <li><a href="students.php" class="active">👥 Students</a></li>
                <li><a href="hostels.php">🏨 Hostels</a></li>
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
            <h1>Student Management</h1>
            <?php echo $message; ?>

            <div class="table-section" style="padding: 20px; margin-bottom: 25px;">
                <div style="font-weight: bold; margin-bottom: 15px;">Add New Student</div>
                <form method="POST" action="students.php" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 200px;">
                        <label style="display:block; margin-bottom:5px; font-size:14px;">Email Address</label>
                        <input type="email" name="email" placeholder="student@gmail.com" required style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
                    </div>
                    <div style="flex: 1; min-width: 200px;">
                        <label style="display:block; margin-bottom:5px; font-size:14px;">Password</label>
                        <input type="text" name="password" placeholder="Enter password" required style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
                    </div>
                    <button type="submit" name="add_student" style="background:#007bff; color:white; border:none; padding:9px 20px; border-radius:4px; cursor:pointer; font-weight:bold;">Add Student</button>
                </form>
            </div>

            <div style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
                <form method="GET" action="students.php" style="display: flex; gap: 8px;">
                    <input type="text" name="search" value="<?php echo $search_query; ?>" placeholder="Search student by email..." style="padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; width: 250px;">
                    <button type="submit" style="background: #212529; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer;">🔍 Search</button>
                    <?php if(!empty($search_query)) { ?>
                        <a href="students.php" style="background: #6c757d; color: white; padding: 8px 12px; text-decoration: none; border-radius: 4px; font-size: 14px;">Reset</a>
                    <?php } ?>
                </form>
            </div>

            <div class="table-section">
                <div class="table-header">All Registered Students (With Credentials)</div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Email Account</th>
                            <th>Password</th>
                            <th>Assigned Room</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $room_badge = $row['room_number'] ? "<b>Room ".$row['room_number']."</b>" : "<span style='color:#888;'>Not Assigned</span>";
                                $pass_text = htmlspecialchars($row['password_hash']);
                                $status_bg = ($row['status'] == 'Active') ? 'status-active' : 'bg-red';
                                $status_style = ($row['status'] == 'Inactive') ? "style='padding:3px 8px; border-radius:4px; color:white; font-size:12px;'" : "";

                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
                                echo "<td><b>" . htmlspecialchars($row['email']) . "</b></td>";
                                echo "<td><code style='background:#f1f3f5; padding:3px 6px; border-radius:3px; color:#d63384; font-weight:bold;'>$pass_text</code></td>";
                                echo "<td>" . $room_badge . "</td>";
                                echo "<td><span class='$status_bg' $status_style>" . htmlspecialchars($row['status']) . "</span></td>";
                                echo "<td>
                                        <a href='students.php?toggle_id=".$row['user_id']."&current=".$row['status']."' style='background:#ffc107; color:#333; padding:4px 8px; text-decoration:none; border-radius:4px; font-size:12px; margin-right:5px;'>Toggle Status</a>
                                        <a href='students.php?delete_id=".$row['user_id']."' onclick='return confirm(\"Delete this student?\");' style='background:#dc3545; color:white; padding:4px 8px; text-decoration:none; border-radius:4px; font-size:12px;'>Delete</a>
                                      </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' style='text-align:center;'>No students found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
