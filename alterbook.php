<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['customer_id'])) { // cannot access page while logged out
  header("Location: login.php");
  exit;
}

// 

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload'])) {
  $target_dir = "img/";
  $filename = $_FILES['filename']['tmp_name'];
  $target_file = $target_dir . basename($filename);
  
  // print_r($target_file);  // filename for pictures of 

  // if(is_uploaded_file($filename)) 
  //   echo "should work ";

  if(!file_exists($target_file)) {
    move_uploaded_file($_FILES["filename"]["tmp_name"], $target_file);
    $id = (int)$_POST['book_id'];
    $stmt = $conn->prepare("UPDATE books
                            SET image_file = ?
                            WHERE book_id = ?");
    $stmt->bind_param("si", $target_file, $id);
    $stmt->execute();
    $stmt->close();
  }
  
  // } else {
  //   echo "didn't work :(" . $target_file;
  // }

}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_book'])) {
  $sql = "INSERT INTO books (title, category, list_price, stock_qty, published_date) VALUES (?, ?, ?, ?, ?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ssdis", $_POST['title'], $_POST['category'], $_POST['list_price'], $_POST['stock_qty'], $_POST['published_date']);
  $stmt->execute();
  $stmt->close();
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_book'])) {
  $sql = "UPDATE books
          SET title = ?,
              category = ?,
              list_price = ?,
              stock_qty = ?,
              published_date = ?
          WHERE book_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ssdisi", $_POST['title'], $_POST['category'], $_POST['list_price'], $_POST['stock_qty'], $_POST['published_date'], $_POST['book_id']);
  $stmt->execute();
  $stmt->close();
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_book'])) {
  $sql = "DELETE FROM books
          WHERE book_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $_POST['book_id']);
  $stmt->execute();
  $stmt->close();
}


$pageTitle = "Book Publishing - EZ Books";
include 'header.php';

$cid = (int)$_SESSION["customer_id"];
$bookData = [];

$sql = "SELECT	*
		   FROM	books";
$stmt = $conn->prepare($sql);
// $stmt->bind_param("i", $cid);
$stmt->execute();
$rslt = $stmt->get_result();

while ($row = $rslt->fetch_assoc()) {
	$bookData[$row['book_id']] = $row;
} 

$stmt->close();

?>

<h1>Published Books</h1>

<?php if(empty($bookData)): ?>
  <p>No books added yet.</p>
<?php else: ?>
  <table class="cart-table">
    <tr>
      <th>Book ID:</th>
      <th>Title:</th>
      <th>Category:</th>
      <th>List Price:</th>
      <th>Stock:</th>
      <th>Publish Date:</th>
      <th></th>
      <th></th>
      <th>Image Upload?</th>
    </tr> 
      <form method="post">
          <td></td>
          <td><input type = "text" name = "title" required></td>
          <td><input type = "text" name = "category" required></td>
          <td><input type = "number" id = "decimal_input" step = "0.01" name = "list_price" required></td>
          <td><input type = "number" name = "stock_qty" required></td>
          <td><input type = "date" name = "published_date" required></td>
          <td><button style="border: double; background-color: #1065e6; color:#ffffff; padding:8px; border-radius: 12px" type = "submit" name = "add_book">Add</button></td>
          <td></td>
          <td></td>
        </form>

    <?php foreach ($bookData as $book):
      $id = $book['book_id'];
      $title = $book['title'];
      $genre = $book['category'];
      $price = $book['list_price'];
      $stock = $book['stock_qty'];
      $date = $book['published_date'];
    ?>
    <tr>
        <form method="post">
          <td><input type = "hidden" name = "book_id" value="<?php echo (int)$id?>"><label><?php echo $id ?></label></td>
          <td><input type = "text" name = "title" value = <?php echo $book['title']?> required></td>
          <td><input type = "text" name = "category" value = <?php echo $genre?> required></td>
          <td><input type = "number" id = "decimal_input" step = "0.01" name = "list_price" value = <?php echo $price?> required></td>
          <td><input type = "number" name = "stock_qty" value = <?php echo $stock?> required></td>
          <td><input type = "date" name = "published_date" value = <?php echo $date?> required></td>
          <td><button style="border: double; background-color: #f6cf4b; color:#222222; padding:8px; border-radius: 12px" type = "submit" name = "edit_book">Edit</button></td>
          <td><button style="border: double; background-color: #ff0000; color:#ffffff; padding:8px; border-radius: 12px" type="submit" name="delete_book">Delete</button></td>
        </form>
        <form method="post" enctype="multipart/form-data">
          <td>
          <input type="hidden" name="book_id" value="<?php echo (int)$id?>">
          <input type="file" id = "myFile" name="filename">
          <input type = "submit" name = "upload">
          </td>
        </form>
      </tr>
    <?php endforeach; ?>
<?php endif; ?>



<?php include 'footer.php'; ?>