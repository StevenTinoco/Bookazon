<?php
session_start();
require 'db_connect.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Fetch book + authors
$sql = "SELECT b.*, 
               GROUP_CONCAT(DISTINCT CONCAT(a.first_name, ' ', a.last_name) SEPARATOR ', ') AS authors
        FROM books b
        LEFT JOIN book_authors ba ON b.book_id = ba.book_id
        LEFT JOIN authors a ON ba.author_id = a.author_id
        WHERE b.book_id = ?
        GROUP BY b.book_id";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$bookResult = $stmt->get_result();
$book = $bookResult->fetch_assoc();
$stmt->close();

if (!$book) {
  http_response_code(404);
  echo "Book not found.";
  exit;
}

// Handle review submission
$reviewMessage = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_review'])) {
  if (!isset($_SESSION['customer_id'])) {
    $reviewMessage = "You must be logged in to submit a review.";
  } else {
    $rating = (int) ($_POST['rating'] ?? 0);
    $title  = trim($_POST['title'] ?? '');
    $body   = trim($_POST['body'] ?? '');
    if ($rating < 1 || $rating > 5 || $title === '' || $body === '') {
      $reviewMessage = "Please provide a rating (1–5), title, and review text.";
    } else {
      $sql = "INSERT INTO reviews (book_id, customer_id, rating, title, body) VALUES (?, ?, ?, ?, ?)";
      $stmt = $conn->prepare($sql);
      $cid = $_SESSION['customer_id'];
      $stmt->bind_param("iiiss", $id, $cid, $rating, $title, $body);
      if ($stmt->execute()) {
        $reviewMessage = "Thank you! Your review has been submitted.";
      } else {
        $reviewMessage = "Error submitting review: " . $conn->error;
      }
      $stmt->close();
    }
  }
}

// Fetch reviews for this book
$sql = "SELECT r.rating, r.title, r.body, c.full_name
        FROM reviews r
        JOIN customers c ON r.customer_id = c.customer_id
        WHERE r.book_id = ?
        ORDER BY r.review_id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$reviewsResult = $stmt->get_result();
$stmt->close();

$pageTitle = $book['title'] . " – EZ Books";
include 'header.php';
?>

<article class="book-detail">
  
  <?php if(!isset($book['image_file'])): ?>
    <div class="book-detail-cover placeholder-cover"> 
      <?php echo htmlspecialchars($book['title']); ?>
    </div>
  <?php else: ?>
    <div> 
      <img src = <?php echo htmlspecialchars($book['image_file'])?> class= "book-detail-cover placeholder-cover">
    </div>
  <?php endif; ?>
    
  <div class="book-detail-info">
    <h1><?php echo htmlspecialchars($book['title']); ?></h1>
    <p class="book-author">
      By <?php echo htmlspecialchars($book['authors'] ?: 'Unknown Author'); ?>
    </p>
    <p class="book-price-large">$<?php echo number_format($book['list_price'], 2); ?></p>
    <p><strong>Category:</strong> <?php echo htmlspecialchars($book['category']); ?></p>
    <p><strong>In Stock:</strong> <?php echo (int)$book['stock_qty']; ?></p>
    <p><strong>Published:</strong> <?php echo htmlspecialchars($book['published_date']); ?></p>
    <?php if ((int)$book['stock_qty'] > 0): ?>
    <form method="post" action="cart.php">
      <input type="hidden" name="id" value="<?php echo (int)$book['book_id']; ?>">
      <label>Quantity:
        <input type="number" name="qty" value="1" min="1" max="<?php echo (int)$book['stock_qty']; ?>">
      </label>
      <button type="submit" name="add" class="btn-primary">Add to Cart</button>
    </form>
    <?php else: ?>
      <label style="color: #ff0000;"><strong>Sorry, currently out of stock.</strong></label>
    <?php endif; ?>
  </div>
</article>

<section class="reviews-section">
  <h2>Customer Reviews</h2>
  <?php if ($reviewMessage): ?>
    <p><?php echo htmlspecialchars($reviewMessage); ?></p>
  <?php endif; ?>

  <?php if (isset($_SESSION['customer_id'])): ?>
    <form method="post" class="review-form">
      <h3>Write a Review</h3>
      <label>Rating (1–5):
        <input type="number" name="rating" min="1" max="5" required>
      </label><br>
      <label>Title:<br>
        <input type="text" name="title" required>
      </label><br>
      <label>Review:<br>
        <textarea name="body" rows="4" required></textarea>
      </label><br>
      <button type="submit" name="add_review" class="btn-primary">Submit Review</button>
    </form>
  <?php else: ?>
    <p><a href="login.php">Log in</a> to write a review.</p>
  <?php endif; ?>

  <?php if ($reviewsResult && $reviewsResult->num_rows > 0): ?>
    <ul class="reviews-list">
      <?php while ($rev = $reviewsResult->fetch_assoc()): ?>
        <li class="review-item">
          <strong><?php echo htmlspecialchars($rev['title']); ?></strong>
          <span> – Rating: <?php echo (int)$rev['rating']; ?>/5</span><br>
          <em>by <?php echo htmlspecialchars($rev['full_name']); ?></em>
          <p><?php echo nl2br(htmlspecialchars($rev['body'])); ?></p>
        </li>
      <?php endwhile; ?>
    </ul>
  <?php else: ?>
    <p>No reviews yet. Be the first to review this book!</p>
  <?php endif; ?>
</section>

<?php include 'footer.php'; ?>
