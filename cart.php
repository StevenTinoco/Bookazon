<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}

// Handle add-to-cart from book.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
  $id = (int) ($_POST['id'] ?? 0);
  $qty = max(1, (int) ($_POST['qty'] ?? 1));
  if ($id > 0) {
    if (!isset($_SESSION['cart'][$id])) {
      $_SESSION['cart'][$id] = ['id' => $id, 'qty' => 0];
    }
    $_SESSION['cart'][$id]['qty'] += $qty;
  }
}

// Handle removal from cart
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item'])) {
  $id = (int) ($_POST['id'] ?? 0);
  if($id > 0) {
    unset($_SESSION['cart'][$id]);
  }
}

$pageTitle = "Shopping Cart – EZ Books";
include 'header.php';

$items = $_SESSION['cart'];
$total = 0.0;
$booksData = [];

if (!empty($items)) {
  $ids = implode(",", array_map('intval', array_keys($items)));
  $sql = "SELECT book_id, title, list_price FROM books WHERE book_id IN ($ids)";
  $res = $conn->query($sql);
  while ($row = $res->fetch_assoc()) {
    $booksData[$row['book_id']] = $row;
  }
}
?>

<h1>Your Cart</h1>

<?php if (empty($items)): ?>
  <p>Your cart is empty.</p>
<?php else: ?>
  <table class="cart-table">
    <tr>
      <th>Title</th>
      <th>Qty</th>
      <th>Price</th>
      <th>Subtotal</th>
      <th></th>
    </tr>
    <?php foreach ($items as $item):
      $id = $item['id'];
      $qty = (int)$item['qty'];
      if (!isset($booksData[$id])) continue;
      $book = $booksData[$id];
      $price = (float)$book['list_price'];
      $sub = $price * $qty;
      $total += $sub;
    ?>
      <tr>
        <td><?php echo htmlspecialchars($book['title']); ?></td>
        <td><?php echo $qty; ?></td>
        <td>$<?php echo number_format($price, 2); ?></td>
        <td>$<?php echo number_format($sub, 2); ?></td>
        <td>
          <form method="post" class="remove_item">
            <!-- keeps track of book id -->
            <input type="hidden" name="id" value="<?php echo $id; ?>">  
            <button style="border: double; background-color: #ff0000; color:#ffffff; padding:8px; border-radius: 12px" type="submit" name="remove_item">Delete</button>
          </form>
      </tr>
    <?php endforeach; ?>
    <tr>
      <td colspan="3" class="text-right"><strong>Total</strong></td>
      <td><strong>$<?php echo number_format($total, 2); ?></strong></td>
    </tr>
  </table>

  <p><a href="checkout.php" class="btn-primary">Proceed to Checkout</a></p>
<?php endif; ?>

<?php include 'footer.php'; ?>
