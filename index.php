<?php
session_start();
$pageTitle = "EZ Books – Online Bookstore";
require 'db_connect.php';
include 'header.php';

// Fetch 4 newest books as "featured"
$sql = "SELECT b.book_id, b.title, b.category, b.list_price, b.image_file,
               GROUP_CONCAT(DISTINCT CONCAT(a.first_name, ' ', a.last_name) SEPARATOR ', ') AS authors
        FROM books b
        LEFT JOIN book_authors ba ON b.book_id = ba.book_id
        LEFT JOIN authors a ON ba.author_id = a.author_id
        GROUP BY b.book_id
        ORDER BY b.published_date DESC
        LIMIT 4";
$result = $conn->query($sql);
?>
<section class="hero-banner">
  <div class="hero-content">
    <h1>EZ Books Holiday Gift Guide</h1>
    <p>Find the perfect read for everyone on your list.</p>
    <a href="browse.php" class="btn-primary">Shop Gift Guide</a>
  </div>
</section>

<section class="promo-strip">
  <div class="promo-inner">
    <div class="promo-left">
      <span class="promo-tag">Black Friday Deals</span>
    </div>
    <div class="promo-center">
      <div class="promo-block">
        <p class="promo-label">Rewards Members</p>
        <p class="promo-big">2X</p>
        <p class="promo-small">Points on all books</p>
      </div>
      <div class="promo-divider"></div>
      <div class="promo-block">
        <p class="promo-label">Premium Members</p>
        <p class="promo-big">3X</p>
        <p class="promo-small">Points on all orders</p>
      </div>
    </div>
    <div class="promo-right">
      <a href="register.php" class="btn-outline">Join &amp; Save</a>
    </div>
  </div>
</section>

<section class="featured-section">
  <div class="section-header">
    <h2>Featured Books</h2>
    <a href="browse.php">View All &raquo;</a>
  </div>

  <div class="book-grid">
    <?php if ($result && $result->num_rows > 0): ?>
      <?php while ($row = $result->fetch_assoc()): ?>
        <article class="book-card">
          <?php if(!isset($row['image_file'])): ?>
            <div class="book-detail-cover placeholder-cover"> 
              <?php echo htmlspecialchars($row['title']); ?>
            </div>
          <?php else: ?>
            <div> 
              <img src = <?php echo htmlspecialchars($row['image_file'])?> class= "book-detail-cover placeholder-cover">
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
      <p>No featured books available.</p>
    <?php endif; ?>
  </div>
</section>

<?php include 'footer.php'; ?>
