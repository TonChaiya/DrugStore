<?php
session_start();
include_once 'config.php';

// สร้าง token สำหรับ CSRF protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ตรวจสอบ token CSRF
    if (hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $username = $_POST['username_account'];
        $password = $_POST['password'];

        // ดึงข้อมูลผู้ใช้พร้อมกับ dept_id
        $stmt = $con->prepare("SELECT * FROM users WHERE username_account = :username_account");
        $stmt->bindParam(':username_account', $username);
        $stmt->execute();
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username_account'] = $user['username_account'];
            $_SESSION['hospital_name'] = $user['hospital_name']; // เก็บ hospital_name ในเซสชัน

            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Invalid request.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <form method="POST" class="bg-white p-6 rounded-lg shadow-lg w-full max-w-sm">
        <h2 class="text-2xl font-bold mb-4 text-center text-gray-800">Login</h2>
        <?php if (!empty($error)) : ?>
            <p class="text-red-500 mb-3 text-center"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
        <input type="text" name="username_account" placeholder="Username" required class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400">
        <input type="password" name="password" placeholder="Password" required class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-3 focus:outline-none focus:ring-2 focus:ring-blue-400">
        <button type="submit" class="w-full bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition">Login</button>
    </form>
</body>
</html>
