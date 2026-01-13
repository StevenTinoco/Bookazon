<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['customer_id'])) { // cannot access page while logged out
  header("Location: login.php");
  exit;
}

$pageTitle = "Order History - EZ Books";
include 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['view_details'])) {
  // if((int)$_POST['order_id'] = 0) {
    // echo $_POST['order_id'];
  // }
  if($_POST['order_id'] > 0) {

    $orderData = [];
    $stmt = $conn->prepare("SELECT book_id, book_title, list_price, quantity
                            FROM   books b, order_items o
                            WHERE  order_id = ? AND b.book_id = o.book_id");
    $stmt->bind_param("i", $oid);
    $stmt->execute();
    $order = $stmt->get_result();

    while($row = $order->fetch_assoc()) {
     $orderData[$row['book_id']] = $row;
    }

    $stmt->close();

  } else {
    echo "<p>lol";
  }

}

?>



<?php include 'footer.php'; ?>