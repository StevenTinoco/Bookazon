<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['customer_id'])) { // cannot access page while logged out
  header("Location: login.php");
  exit;
}


if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
  $oid = (int) ($_POST['order_id'] ?? 0);
  if($oid > 0) {
    $stmtOrder = $conn->prepare("UPDATE orders SET status = 'Canceled' WHERE order_id = ?");
    $stmtOrder->bind_param("i", $oid);
    $stmtOrder->execute();
    $stmtOrder->close();

    $stmtStock = $conn->prepare("SELECT book_id, quantity FROM order_items WHERE order_id = ?");
    $stmtStock->bind_param("i", $oid);
    $stmtStock->execute();
    $rsltStock = $stmtStock->get_result();
    
    $bid = 0;
    $qty = 0;

    $stmtBooks = $conn->prepare("UPDATE books SET stock_qty = stock_qty + ? WHERE book_id = ?");
	$stmtBooks->bind_param("ii", $qty, $bid);
    
    while($row = $rsltStock->fetch_assoc()) {
    	$bid = $row['book_id'];
    	$qty = $row['quantity'];
    	
    	$stmtBooks->execute();
	}


    $stmtStock->close();
    $stmtBooks->close();
  } 
}


$pageTitle = "Order History - EZ Books";
include 'header.php';

$cid = (int)$_SESSION["customer_id"];
$orderData = [];

$sql = "SELECT	order_id, order_date, status
		FROM	orders
		WHERE	customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $cid);
$stmt->execute();
$rslt = $stmt->get_result();

while ($row = $rslt->fetch_assoc()) {
	$orderData[$row['order_id']] = $row;
} 

$stmt->close();

?>

<h1>Your Order History</h1>

<?php if(empty($orderData)): ?>
  <p>You have not ordered anything yet.</p>
<?php else: ?>
  <table class="cart-table">
    <tr>
      <th>Order ID</th>
      <th>Date</th>
      <th>Status</th>
      <th></th>
      <th></th>
    </tr>
    <?php foreach ($orderData as $item):
      $id = $item['order_id'];
      $date = $item['order_date'];
      $status = $item['status'];
      // echo $id;
    ?>
    <tr>
        <td><?php echo number_format($id); ?></td>
        <td>$<?php echo htmlspecialchars($date); ?></td>
        <td>$<?php echo $status; ?></td>
        <td>
          <form action = "orderinfo.php" method="post" class="view_details">
            <input type="hidden" name="order_id" value="<?php echo $id; ?>">
            <button style="border: double; background-color: #ffff00; color:#000000; padding:8px; border-radius: 12px" type="submit" name="view_details">Order Details</button>
          </form>
        <td>
        	<?php if(strcmp("Processing", $status) == 0): ?>
	          <form method="post">
	          <input type="hidden" name="order_id" value="<?php echo $id; ?>">  
	          <button style="border: double; background-color: #ff0000; color:#ffffff; padding:8px; border-radius: 12px" type="submit" name="cancel_order">Cancel Order</button>
	          </form>
            <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
<?php endif; ?>



<?php include 'footer.php'; ?>