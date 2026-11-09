<?php
session_start();
include "db.php";

// ইউআরএল থেকে রোল নির্ধারণ করা (ডিফল্ট Customer)
$selected_role = "Customer";
if (isset($_GET["role"]) && !empty($_GET["role"])) {
    $selected_role = $_GET["role"];
} elseif (isset($_POST["role"]) && !empty($_POST["role"])) {
    $selected_role = $_POST["role"];
}

$error = "";

// লগইন ফর্ম সাবমিট হলে
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    // শুধু ইমেইল এবং পাসওয়ার্ড দিয়ে ডাটাবেজে ইউজার খোঁজা (যাতে রোলের বানানের কারণে না আটকায়)
    $sql = "SELECT user_id, email, password_hash, role, status FROM users WHERE email = ? AND password_hash = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $email, $password);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($result)) {
            if ($user["status"] !== "Active") {
                $error = "Your account is currently Inactive. Please contact Admin.";
            } else {
                // সেশন সেট করা
                $_SESSION["user_id"] = $user["user_id"];
                $_SESSION["email"] = $user["email"];
                $_SESSION["role"] = $user["role"];

                // রোল অনুযায়ী সঠিক ড্যাশবোর্ডে পাঠানো
                if ($user["role"] == "Admin") {
                    header("Location: admin_dashboard.php");
                    exit();
                } else {
                    // রোল Customer বা Student যাই হোক, স্টুডেন্ট ড্যাশবোর্ডে যাবে
                    $_SESSION["role"] = "Customer"; 
                    header("Location: student_dashboard.php");
                    exit();
                }
            }
        } else {
            $error = "Invalid Email or Password! Please check and try again.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $error = "Database connection error. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Smart Hostel</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="login-page">
    <div class="login-box">

        <?php if ($selected_role == "Admin") { ?>
            <div class="login-icon">👨‍💼</div>
            <h1>Admin Login</h1>
            <p>Login to manage the hostel system</p>
        <?php } else { ?>
            <div class="login-icon">👨‍🎓</div>
            <h1>Student Login</h1>
            <p>Login to access your hostel account</p>
        <?php } ?>

        <?php if (!empty($error)) { ?>
            <div class="error-message">
                <?php echo $error; ?>
            </div>
        <?php } ?>

        <form method="POST" action="login.php?role=<?php echo htmlspecialchars($selected_role); ?>">
            <input type="hidden" name="role" value="<?php echo htmlspecialchars($selected_role); ?>">

            <label>Email Address</label>
            <input type="email" name="email" placeholder="Enter your email" required>

            <label>Password</label>
            <input type="password" name="password" placeholder="Enter your password" required>

            <button type="submit" class="submit-button">LOGIN</button>
        </form>

        <a href="index.php" class="back-button">← Back to Home</a>

    </div>
</div>

</body>
</html>
