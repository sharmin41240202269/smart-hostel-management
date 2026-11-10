<?php
session_start();
include "db.php";

// নিরাপত্তা চেক: ইউজার লগইন না থাকলে বা অ্যাডমিন না হলে লগইন পেজে পাঠিয়ে দেবে
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Admin") {
    header("Location: login.php?role=Admin");
    exit();
}

// ১. মোট স্টুডেন্ট সংখ্যা গণনা (Role = Customer)
$student_query = "SELECT COUNT(*) as total FROM users WHERE role = 'Customer'";
$student_result = mysqli_query($conn, $student_query);
$total_students = mysqli_fetch_assoc($student_result)['total'] ?? 0;

// ২. মোট হোস্টেল সংখ্যা গণনা
$hostel_query = "SELECT COUNT(*) as total FROM hostels";
$hostel_result = mysqli_query($conn, $hostel_query);
$total_hostels = mysqli_fetch_assoc($hostel_result)['total'] ?? 0;

// ৩. মোট রুম সংখ্যা গণনা
$room_query = "SELECT COUNT(*) as total FROM rooms";
$room_result = mysqli_query($conn, $room_query);
$total_rooms = mysqli_fetch_assoc($room_result)['total'] ?? 0;

// ৪. একটিভ এলোকেশন সংখ্যা গণনা
$alloc_query = "SELECT COUNT(*) as total FROM allocations WHERE status = 'Active'";
$alloc_result = mysqli_query($conn, $alloc_query);
$total_allocations = mysqli_fetch_assoc($alloc_result)['total'] ?? 0;

// ৫. এভেইলেবল সিট গণনা (মোট রুমের ক্যাপাসিটি থেকে একটিভ এলোকেশন বিয়োগ)
$seats_query = "SELECT SUM(capacity) as total FROM rooms";
$seats_result = mysqli_query($conn, $seats_query);
$total_seats = mysqli_fetch_assoc($seats_result)['total'] ?? 0;
$available_seats = ($total_seats - $total_allocations < 0) ? 0 : ($total_seats - $total_allocations);

// ৬. টোটাল ইনকাম বা আয় গণনা
$income_query = "SELECT SUM(amount) as total FROM payments WHERE status = 'Paid'";
$income_result = mysqli_query($conn, $income_query);
$total_income = mysqli_fetch_assoc($income_result)['total'] ?? 0;

// ৭. পেন্ডিং কমপ্লেন সংখ্যা গণনা
$complaint_query = "SELECT COUNT(*) as total FROM complaints WHERE status = 'Pending'";
$complaint_result = mysqli_query($conn, $complaint_query);
$total_complaints = mysqli_fetch_assoc($complaint_result)['total'] ?? 0;

// সাম্প্রতিক স্টুডেন্টদের তালিকা নিয়ে আসা (সর্বশেষ ৫ জন)
$recent_students_query = "SELECT user_id, email, status FROM users WHERE role = 'Customer' ORDER BY user_id DESC LIMIT 5";
$recent_students_result = mysqli_query($conn, $recent_students_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Hostel Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- টপ নেভিগেশন বার -->
    <div class="top-navbar">
        <h2>Hostel Management System</h2>
        <div class="nav-right">
            <span>Welcome, Administrator</span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="main-container">
        
        <!-- বামদিকের সাইডবার মেনু লিস্ট -->
        <div class="sidebar">
            <h3>Hostel Management</h3>
            <ul>
                <li><a href="admin_dashboard.php" class="active">📊 Dashboard</a></li>
                <li><a href="students.php">👥 Students</a></li>
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

        <!-- ডানদিকের মেইন কন্টেন্ট এরিয়া -->
        <div class="content">
            <h1>Dashboard</h1>
            
            <!-- ড্যাশবোর্ড কার্ড গ্রিড (৭টি কার্ডের প্রোপার সেটআপ) -->
            <div class="dashboard-cards">
                <div class="card bg-blue">
                    <h3>Total Students</h3>
                    <p class="card-number"><?php echo $total_students; ?></p>
                </div>
                <div class="card bg-green">
                    <h3>Total Hostels</h3>
                    <p class="card-number"><?php echo $total_hostels; ?></p>
                </div>
                <div class="card bg-yellow">
                    <h3>Total Rooms</h3>
                    <p class="card-number"><?php echo $total_rooms; ?></p>
                </div>
                <div class="card bg-cyan">
                    <h3>Active Allocations</h3>
                    <p class="card-number"><?php echo $total_allocations; ?></p>
                </div>
                <div class="card bg-grey">
                    <h3>Available Seats</h3>
                    <p class="card-number"><?php echo $available_seats; ?></p>
                </div>
                <div class="card bg-dark">
                    <h3>Total Income</h3>
                    <p class="card-number">৳<?php echo number_format($total_income, 2); ?></p>
                </div>
                <div class="card bg-red">
                    <h3>Pending Complaints</h3>
                    <p class="card-number"><?php echo $total_complaints; ?></p>
                </div>
            </div>

            <!-- Recent Students টেবিল সেকশন -->
            <div class="table-section">
                <div class="table-header">Recent Students</div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Email / Account</th>
                            <th>Role</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if (mysqli_num_rows($recent_students_result) > 0) {
                            while ($row = mysqli_fetch_assoc($recent_students_result)) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                echo "<td>Student</td>";
                                echo "<td><span class='status-active'>" . htmlspecialchars($row['status']) . "</span></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' style='text-align:center;'>No recent students found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</body>
</html>
