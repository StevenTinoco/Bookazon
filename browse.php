<?php
session_start();
$pageTitle = "Browse Books – EZ Books";
require 'db_connect.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$field = isset($_GET['field']) ? $_GET['field'] : 'all';
$cat = isset($_GET['cat']) ? trim($_GET['cat']) : 'all';

$sql = "SELECT b.book_id, b.title, b.category, b.list_price, b.image_file,
               GROUP_CONCAT(DISTINCT CONCAT(a.first_name, ' ', a.last_name) SEPARATOR ', ') AS authors
        FROM books b
        LEFT JOIN book_authors ba ON b.book_id = ba.book_id
        LEFT JOIN authors a ON ba.author_id = a.author_id
        WHERE 1=1";
$params = [];
$types  = "";

// Category filter
if ($cat !== 'all') {
  $sql .= " AND b.category = ?";
  $params[] = $cat;
  $types   .= "s";
}

// Search filter
if ($q !== '') {
  if ($field === 'title') {
    $sql .= " AND b.title LIKE ?";
    $params[] = "%" . $q . "%";
    $types   .= "s";
  } elseif ($field === 'author') {
    $sql .= " AND CONCAT(a.first_name, ' ', a.last_name) LIKE ?";
    $params[] = "%" . $q . "%";
    $types   .= "s";
  } else { // all
    $sql .= " AND (b.title LIKE ? OR CONCAT(a.first_name, ' ', a.last_name) LIKE ?)";
    $params[] = "%" . $q . "%";
    $params[] = "%" . $q . "%";
    $types   .= "ss";
  }
}

$sql .= " GROUP BY b.book_id ORDER BY b.title ASC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

include 'header.php';
?>

<h1>Browse Books</h1>

<?php if ($q !== ''): ?>
  <p>Showing results for: <strong><?php echo htmlspecialchars($q); ?></strong></p>
<?php endif; ?>

<div class="book-grid">
  <?php if ($result && $result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
      <article class="book-card">
        <?php if(!isset($row['image_file'])): ?>
          <div class="book-detail-cover placeholder-cover"> 
            <?php echo htmlspecialchars($row['title']); ?>
          </div>
        <?php else: ?>
          <div class="book-card">
            <img src = <?php echo htmlspecialchars($row['image_file'])?> class = "book-detail-cover placeholder-cover" style = "display: block; margin_left: auto; margin_right: auto;">
          </div>
        <?php endif; ?>
        <h3><?php echo htmlspecialchars($row['title']); ?></h3>
        <p class="book-author">
          <?php echo htmlspecialchars($row['authors'] ?: 'Unknown Author'); ?>
        </p>
        <p class="book-price">$<?php echo number_format($row['list_price'], 2); ?></p>
        <a href="book.php?id=<?php echo (int)$row['book_id']; ?>" class="btn-sm">View Details</a>
      </article>
    <?php endwhile; ?>
  <?php else: ?>
    <p>No books found.</p>
  <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
