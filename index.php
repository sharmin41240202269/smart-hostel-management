<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Hostel Management System - Home</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="login-page">

    <div class="login-box" style="max-width: 600px;">
        
        <!-- লোগো ও হেডার -->
        <div class="login-icon">🏠</div>
        <h1>Smart Hostel Management System</h1>
        <p>Welcome! Please choose your login portal to proceed.</p>

        <!-- পোর্টাল লিংকস -->
        <div style="margin-top: 20px;">
            
            <a href="login.php?role=Admin" class="portal-link">
                👨‍💼 Admin Login Portal
                <span style="display:block; font-size:12px; font-weight:normal; color:#666; margin-top:4px;">
                    Manage Students, Rooms, Payments, Facilities, Complaints and Reports.
                </span>
            </a>

            <a href="login.php?role=Customer" class="portal-link" style="margin-top: 15px;">
                👨‍🎓 Student Login Portal
                <span style="display:block; font-size:12px; font-weight:normal; color:#666; margin-top:4px;">
                    Login to view Profile, Room allocations, Payments, Facilities and Complaints.
                </span>
            </a>

        </div>

        <!-- ফুটার কপিরাইট -->
        <div style="margin-top: 40px; font-size: 12px; color: #888;">
            &copy; <?php echo date("Y"); ?> Smart Hostel Management System. All Rights Reserved.
        </div>

    </div>

</div>

</body>
</html>
