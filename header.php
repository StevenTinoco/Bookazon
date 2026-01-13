<?php
if (!isset($pageTitle)) {
  $pageTitle = "EZ Books – Online Bookstore";
}
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$cartCount = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
  foreach ($_SESSION['cart'] as $item) {
    $cartCount += (int)$item['qty'];
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($pageTitle); ?></title>
  <link rel="stylesheet" href="styles.css">
</head>
<body class="home-body">

<div class="top-banner">
  <button class="banner-arrow">&#10094;</button>
  <span>HOLIDAY SHIPPING INFORMATION</span>
  <button class="banner-arrow">&#10095;</button>
</div>

<div class="bn-mini-nav">
  <div class="bn-mini-left">
    <span>Stores &amp; Events</span>
    <span>Membership</span>
    <span>Blog</span>
    <span>Gift Cards</span>
  </div>
  <div class="bn-mini-right">
    <?php if (isset($_SESSION['customer_id'])): ?>
      <span>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
      <a href="logout.php">Logout</a>
    <?php else: ?>
      <a href="login.php">My Account</a>
    <?php endif; ?>
    <a href="cart.php">Wishlist</a>
  </div>
</div>

<header class="bn-main-header">
  <div class="bn-header-inner">
    <a href="index.php"> <div class="bn-logo">EZ<span>Books</span></div> </a>

    <form class="bn-search-wrap" action="browse.php" method="get">
      <select class="bn-search-filter" name="field">
        <option value="all">All</option>
        <option value="title">Title</option>
        <option value="author">Author</option>
        <option value="isbn">ISBN</option>
      </select>
      <input type="text" name="q" class="bn-search-input" placeholder="Search by Title, Author, Keyword or ISBN">
      <button type="submit" class="bn-search-btn">🔍</button>
    </form>

    <div class="bn-header-icons">
      <a href="orderhistory.php">📋</a>
      <!-- <a href="account.php">👤</a> -->
      <a href="cart.php" class="bn-cart">
        🛒 <span class="bn-cart-count"><?php echo $cartCount; ?></span>
      </a>
      <a href="alterbook.php">📚</a>
    </div>
  </div>
</header>

<nav class="category-nav">
  <a href="browse.php?cat=all">Books</a>
  <a href="browse.php?cat=Fiction">Fiction</a>
  <a href="browse.php?cat=Nonfiction">Nonfiction</a>
  <a href="browse.php?cat=Fantasy">Fantasy</a>
  <a href="browse.php?cat=Kids">Kids</a>
</nav>

<main class="home-main">
