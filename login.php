<?php
session_start();
require 'db_connect.php';

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  if ($email === '' || $password === '') {
    $message = "Please enter your email and password.";
  } else {
    $stmt = $conn->prepare("SELECT customer_id, full_name, password_hash FROM customers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($id, $name, $hash);
    if ($stmt->fetch() && password_verify($password, $hash)) {
      $_SESSION['customer_id'] = $id;
      $_SESSION['full_name'] = $name;
      header("Location: index.php");
      exit;
    } else {
      $message = "Invalid login.";
    }
    $stmt->close();
  }
}

$pageTitle = "Login – EZ Books";
include 'header.php';
?>

<h1>Login</h1>
<?php if ($message): ?>
  <p><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>

<form method="post" action="login.php" class="auth-form">
  <input type="email" name="email" placeholder="Email" required>
  <input type="password" name="password" placeholder="Password" required>
  <button type="submit" class="btn-primary">Login</button>
</form>

<p>Don&apos;t have an account? <a href="register.php">Register</a></p>

<?php include 'footer.php'; ?>
