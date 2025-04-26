<header>
    <div class="logo">
        <a href="index.php" style="text-decoration:none; color:inherit;">🎨 Art Auction</a>
    </div>

    <div class="user-menu">
        <a href="auction_market.php">Auction Market</a>
        <?php if (isset($_SESSION['user'])): ?>
            <span>Hi, <?= htmlspecialchars($_SESSION['user']['Username']) ?></span>
            | <a href="dashboard.php">Dashboard</a>
            | <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a> /
            <a href="register.php">Register</a>
        <?php endif; ?>
    </div>
</header>
