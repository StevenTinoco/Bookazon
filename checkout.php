<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['customer_id'])) { // cannot access page while logged out
  header("Location: login.php");
  exit;
}

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
  $error = "Your cart is empty.";
}

$orderMessage = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order']) && empty($error)) {
  $cid = $_SESSION['customer_id'];
  $name = trim($_POST['name'] ?? '');
  $address = trim($_POST['address'] ?? '');
  $city = trim($_POST['city'] ?? '');
  $zip = trim($_POST['zip'] ?? '');

  if ($name === '' || $address === '' || $city === '' || $zip === '') {
    $orderMessage = "Please fill out all shipping fields.";
  } else {
    $conn->begin_transaction();
    try {
      // Create order
      $status = "Processing";
      $stmt = $conn->prepare("INSERT INTO orders (customer_id, status) VALUES (?, ?)");
      $stmt->bind_param("is", $cid, $status);
      $stmt->execute();
      $orderId = $stmt->insert_id;
      $stmt->close();

      // Fetch book prices
      $items = $_SESSION['cart'];
      $ids = implode(",", array_map('intval', array_keys($items)));
      $sql = "SELECT book_id, list_price, stock_qty FROM books WHERE book_id IN ($ids) FOR UPDATE";
      $res = $conn->query($sql);
      $bookData = [];
      while ($row = $res->fetch_assoc()) {
        $bookData[$row['book_id']] = $row;
      }

      // // Insert order items and update stock
      $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, book_id, unit_price, quantity) VALUES (?, ?, ?, ?)");
      $stmtStock = $conn->prepare("UPDATE books SET stock_qty = stock_qty - ? WHERE book_id = ? AND stock_qty >= ?"); 


      foreach ($items as $item) {
        $bid = (int)$item['id'];   
        $qty = (int)$item['qty'];  

        
        if (!isset($bookData[$bid])) continue;  
        // safety check
        if($qty > $bookData[$bid]['stock_qty']) throw new Exception("Quantity of one of your items exceeds current available stock", 1);
        

        $price = (float)$bookData[$bid]['list_price'];
        // add order item
        $stmtItem->bind_param("iidi", $orderId, $bid, $price, $qty);  // 
        $stmtItem->execute();
        
        // update stock
        $stmtStock->bind_param("iii", $qty, $bid, $qty);
        $stmtStock->execute();
        
        
        
      }

      $stmtItem->close();
      $stmtStock->close();


      $conn->commit();
      $_SESSION['cart'] = [];
      $orderMessage = "Thank you! Your order #$orderId has been placed.";
    } catch (Exception $e) {
      $conn->rollback();
      $orderMessage = "Error placing order: " . $e->getMessage();
    }
  }
}

$pageTitle = "Checkout – EZ Books";
include 'header.php';
?>

<h1>Checkout</h1>

<?php if (!empty($error)): ?>
  <p><?php echo htmlspecialchars($error); ?></p>
<?php else: ?>
  <?php if ($orderMessage): ?>
    <p><?php echo htmlspecialchars($orderMessage); ?></p>
  <?php else: ?>
    <p>Please confirm your shipping information to place your order.</p>
    <form method="post" action="checkout.php">
      <label>Full Name:<br>
        <input type="text" name="name" required>
      </label><br>
      <label>Shipping Address:<br>
        <input type="text" name="address" required>
      </label><br>
      <label>City:<br>
        <input type="text" name="city" required>
      </label><br>
      <label>ZIP Code:<br>
        <input type="text" name="zip" required>
      </label><br><br>
      <button type="submit" name="place_order" class="btn-primary">Place Order</button>
    </form>
  <?php endif; ?>
<?php endif; ?>

<?php include 'footer.php'; ?>
