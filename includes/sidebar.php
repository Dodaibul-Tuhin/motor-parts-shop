    <div class="sidebar">
        <h4 class="sidebar-title">মোটর পার্টস শপ</h4>
        <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link" href="/motor-parts-shop/index.php">📊 ড্যাশবোর্ড</a></li>
            <li class="nav-item"><a class="nav-link" href="/motor-parts-shop/brands/list.php">🏷️ ব্র্যান্ড</a></li>
            <li class="nav-item"><a class="nav-link" href="/motor-parts-shop/categories/list.php">📋 ক্যাটাগরি</a></li>
            <li class="nav-item"><a class="nav-link" href="/motor-parts-shop/products/list.php">📦 পণ্য</a></li>
            <li class="nav-item"><a class="nav-link" href="/motor-parts-shop/sales/list.php">🛒 বিক্রয়</a></li>
            <li class="nav-item"><a class="nav-link" href="/motor-parts-shop/expenses/list.php">💰 খরচ</a></li>
        </ul>
        <div class="mt-4 px-3">
            <a href="/motor-parts-shop/auth/logout.php" class="btn btn-danger w-100">লগআউট</a>
        </div>
    </div>
    <div class="main-content">
        <div class="topbar">
            স্বাগতম, <?= htmlspecialchars($_SESSION["user_name"] ?? "Administrator") ?>
        </div>
