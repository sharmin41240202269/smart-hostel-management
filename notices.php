<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
    header("Location: login.php?role=Admin");
    exit();
}

$message = "";

// ১. নতুন নোটিশ পাবলিশ করার লজিক (Create Operation)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_notice'])) {
    $title = trim($_POST["title"]);
    $content = trim($_POST["content"]);

    if (!empty($title) && !empty($content)) {
        $stmt = mysqli_prepare($conn, "INSERT INTO notices (title, content) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "ss", $title, $content);
        if (mysqli_stmt_execute($stmt)) {
            $message = "<div style='background:#d1e7dd; color:#0f5132; padding:12px; border-radius:5px; margin-bottom:15px; font-weight:bold;'>✅ Notice published successfully! Students can see it now.</div>";
        } else {
            $message = "<div style='background:#f8d7da; color:#842029; padding:12px; border-radius:5px; margin-bottom:15px; font-weight:bold;'>❌ Failed to publish notice.</div>";
        }
        mysqli_stmt_close($stmt);
    }
}

// ২. নোটিশ ডিলিট করার লজিক (Delete Operation)
if (isset($_GET['delete_id'])) {
    $nid = intval($_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM notices WHERE notice_id = $nid");
    header("Location: notices.php?msg=deleted");
    exit();
}

if (isset($_GET['msg']) && $_GET['msg'] == 'deleted') {
    $message = "<div style='background:#d1e7dd; color:#0f5132; padding:12px; border-radius:5px; margin-bottom:15px; font-weight:bold;'>🗑️ Notice deleted successfully!</div>";
}

// ৩. সব নোটিশের তালিকা নিয়ে আসা (Read Operation)
$result = mysqli_query($conn, "SELECT * FROM notices ORDER BY notice_id DESC");

$rows_html = "";
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $nid = $row['notice_id'];
        $title = htmlspecialchars($row['title']);
        $content = htmlspecialchars($row['content']);
        $date = htmlspecialchars($row['created_at']);

        $rows_html .= "<tr style='border-bottom: 1px solid #ddd;'>
            <td style='padding: 12px; color:#000;'>#$nid</td>
            <td style='padding: 12px; color:#000;'><b>$title</b></td>
            <td style='padding: 12px; color:#000;'>$content</td>
            <td style='padding: 12px; color:#000;'>$date</td>
            <td style='padding: 12px;'>
                <a href='notices.php?delete_id=$nid' onclick='return confirm(\"Are you sure you want to delete this notice?\");' style='background:#dc3545; color:#ffffff; padding:5px 10px; text-decoration:none; border-radius:4px; font-size:12px; font-weight:bold; display:inline-block;'>Delete</a>
            </td>
        </tr>";
    }
} else {
    $rows_html = "<tr><td colspan='5' style='padding: 15px; text-align:center; color:#555;'>No notices published yet.</td></tr>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notice Board - Hostel Management System</title>
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
                <li><a href="notices.php" class="active">📢 Notices</a></li>
                <li><a href="room_applications.php">📄 Room Applications</a></li>
                <li><a href="reports.php">📋 Reports</a></li>
            </ul>
        </div>

        <div class="content">
            <h1 style="color:#000; margin-bottom:20px;">Notice Board Management</h1>
            
            <?php echo $message; ?>

            <!-- নতুন নোটিশ প্রকাশের ফর্ম -->
            <div style="background: #ffffff; padding: 20px; border-radius: 6px; border: 1px solid #ccc; margin-bottom: 25px;">
                <h3 style="margin-bottom: 15px; color: #000; font-size: 16px;">📢 Publish New Notice</h3>
                <form method="POST" action="notices.php">
                    <div style="margin-bottom: 15px;">
                        <label style="display:block; margin-bottom:5px; font-size:14px; font-weight:bold; color:#000;">Notice Title</label>
                        <input type="text" name="title" placeholder="e.g., Hostel Gate Closing Time, Monthly Meal Fee" required style="width:100%; padding:10px; border:1px solid #888; border-radius:4px; color:#000; background:#fff;">
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label style="display:block; margin-bottom:5px; font-size:14px; font-weight:bold; color:#000;">Notice Details / Content</label>
                        <textarea name="content" placeholder="Write full details of the announcement..." required style="width:100%; padding:10px; height:100px; border:1px solid #888; border-radius:4px; color:#000; background:#fff; resize:vertical;"></textarea>
                    </div>
                    <button type="submit" name="add_notice" style="background-color: #0d6efd; color: #ffffff; border: none; padding: 10px 25px; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 15px;">Publish Notice</button>
                </form>
            </div>

            <!-- প্রকাশিত নোটিশের তালিকা -->
            <div style="background: #ffffff; border-radius: 6px; border: 1px solid #ccc; overflow-x: auto;">
                <div style="background: #0d6efd; color: #ffffff; padding: 12px 20px; font-weight: bold;">Published Notices History</div>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #212529; color: #ffffff;">
                            <th style="padding: 12px; text-align: left;">ID</th>
                            <th style="padding: 12px; text-align: left;">Title</th>
                            <th style="padding: 12px; text-align: left;">Content</th>
                            <th style="padding: 12px; text-align: left;">Published Date</th>
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
