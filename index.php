<?php
require_once "includes/auth_check.php";
require_once "config/db.php";

$pageTitle = "ড্যাশবোর্ড";

// --- Total products ---
$totalProducts = $conn->query("SELECT COUNT(*) AS c FROM products")->fetch_assoc()["c"];

// --- Today's sales ---
$today = date("Y-m-d");
$stmt = $conn->prepare("SELECT COALESCE(SUM(total_amount),0) AS total FROM sales WHERE sale_date = ?");
$stmt->bind_param("s", $today);
$stmt->execute();
$todaySales = $stmt->get_result()->fetch_assoc()["total"];

// --- Low stock products ---
$lowStock = $conn->query("SELECT COUNT(*) AS c FROM products WHERE stock_qty <= low_stock_threshold")->fetch_assoc()["c"];

// --- Total sales (all time) ---
$totalSales = $conn->query("SELECT COALESCE(SUM(total_amount),0) AS total FROM sales")->fetch_assoc()["total"];

// --- Total brands / categories ---
$totalBrands = $conn->query("SELECT COUNT(*) AS c FROM brands")->fetch_assoc()["c"];
$totalCategories = $conn->query("SELECT COUNT(*) AS c FROM categories")->fetch_assoc()["c"];

// --- Total expense ---
$totalExpense = $conn->query("SELECT COALESCE(SUM(amount),0) AS total FROM expenses")->fetch_assoc()["total"];

// --- Total due ---
$totalDue = $conn->query("SELECT COALESCE(SUM(due_amount),0) AS total FROM sales")->fetch_assoc()["total"];

// --- Recent sales (last 5) ---
$recentSales = $conn->query("
    SELECT s.invoice_no, COALESCE(c.name, 'Walk-in') AS customer_name, s.sale_date,
           s.total_amount, s.paid_amount, s.due_amount, s.status
    FROM sales s
    LEFT JOIN customers c ON s.customer_id = c.id
    ORDER BY s.sale_date DESC, s.id DESC
    LIMIT 5
");

require_once "includes/header.php";
require_once "includes/sidebar.php";
?>

<div class="page-body">
    <h3 class="mb-4">ড্যাশবোর্ড</h3>

    <div class="row">
        <div class="col-md-3">
            <div class="stat-card bg-blue">
                <h6>মোট পণ্য</h6>
                <h2><?= (int)$totalProducts ?></h2>
                <a href="products/list.php">বিস্তারিত দেখুন &raquo;</a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-green">
                <h6>আজকের বিক্রয়</h6>
                <h2>৳<?= number_format($todaySales, 2) ?></h2>
                <a href="sales/list.php">বিস্তারিত দেখুন &raquo;</a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-yellow">
                <h6>কম স্টক পণ্য</h6>
                <h2><?= (int)$lowStock ?></h2>
                <a href="products/list.php">বিস্তারিত দেখুন &raquo;</a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-red">
                <h6>মোট বিক্রয়</h6>
                <h2>৳<?= number_format($totalSales, 2) ?></h2>
                <a href="sales/list.php">বিস্তারিত দেখুন &raquo;</a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="stat-card bg-cyan">
                <h6>মোট ব্র্যান্ড</h6>
                <h2><?= (int)$totalBrands ?></h2>
                <a href="brands/list.php">বিস্তারিত দেখুন &raquo;</a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-purple">
                <h6>মোট ক্যাটাগরি</h6>
                <h2><?= (int)$totalCategories ?></h2>
                <a href="categories/list.php">বিস্তারিত দেখুন &raquo;</a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-pink">
                <h6>মোট খরচ</h6>
                <h2>৳<?= number_format($totalExpense, 2) ?></h2>
                <a href="expenses/list.php">বিস্তারিত দেখুন &raquo;</a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-orange">
                <h6>মোট বাকি</h6>
                <h2>৳<?= number_format($totalDue, 2) ?></h2>
                <a href="sales/list.php">বিস্তারিত দেখুন &raquo;</a>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>সাম্প্রতিক বিক্রয়</span>
            <a href="sales/list.php" class="btn btn-primary btn-sm">সব দেখুন</a>
        </div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>ইনভয়েস</th>
                        <th>ক্রেতা</th>
                        <th>তারিখ</th>
                        <th>প্রদেয়</th>
                        <th>প্রদত্ত</th>
                        <th>বাকি</th>
                        <th>স্ট্যাটাস</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($recentSales->num_rows === 0): ?>
                    <tr><td colspan="7" class="text-center py-3">কোনো বিক্রয় পাওয়া যায়নি</td></tr>
                <?php else: ?>
                    <?php while ($row = $recentSales->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row["invoice_no"]) ?></td>
                            <td><?= htmlspecialchars($row["customer_name"]) ?></td>
                            <td><?= date("d M Y", strtotime($row["sale_date"])) ?></td>
                            <td>৳<?= number_format($row["total_amount"], 2) ?></td>
                            <td>৳<?= number_format($row["paid_amount"], 2) ?></td>
                            <td>৳<?= number_format($row["due_amount"], 2) ?></td>
                            <td><?= htmlspecialchars($row["status"]) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once "includes/footer.php"; ?>
