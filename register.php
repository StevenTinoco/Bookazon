<?php
session_start();
require 'db_connect.php';

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['full_name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $address = trim($_POST['address'] ?? '');
  $password = $_POST['password'] ?? '';

  if ($name === '' || $email === '' || $address === '' || $password === '') {
    $message = "Please fill out all fields.";
  } else {
    // $hash = password_hash($password, PASSWORD_DEFAULT);
    // $stmt = $conn->prepare("INSERT INTO customers (email, password_hash, full_name, address) VALUES (?, ?, ?, ?)");
    // $stmt->bind_param("ssss", $email, $hash, $name, $address);
    // if ($stmt->execute()) {
    //   $message = "Registration successful. You can now log in.";
    // } else {
    //   if ($conn->errno == 1062) {
    //     $message = "That email is already registered.";
    //   } else {
    //     $message = "Error creating account: " . $conn->error;
    //   }
    // }
    // $stmt->close();

    try {
      $hash = password_hash($password, PASSWORD_DEFAULT);

      $mailch = $conn->prepare("SELECT email FROM customers WHERE email = ?");
      $mailch->bind_param("s", $email);
      $mailch->execute();
      $ch = $mailch->get_result();
      if($row = $ch->fetch_assoc())
        throw new Exception("Email is registered with another account.", 1);
      $mailch->close();

      $stmt = $conn->prepare("INSERT INTO customers (email, password_hash, full_name, address) VALUES (?, ?, ?, ?)");
      $stmt->bind_param("ssss", $email, $hash, $name, $address);
      $stmt->execute();
      $message = "Registration successful. You can now log in.";
      $stmt->close();
    
    } catch(Exception $e) {
      $message = "Error creating an account: " . $e->getMessage();
    }
  }
}

$pageTitle = "Register – EZ Books";
include 'header.php';
?>

<h1>Create an Account</h1>
<?php if ($message): ?>
  <p><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>

<form method="post" action="register.php" class="auth-form">
  <input type="text" name="full_name" placeholder="Full Name" required>
  <input type="email" name="email" placeholder="Email" required>
  <input type="text" name="address" placeholder="Address" required>
  <input type="password" name="password" placeholder="Password" required>
  <button type="submit" class="btn-primary">Register</button>
</form>

<p>Already have an account? <a href="login.php">Login</a></p>

<?php include 'footer.php'; ?>
