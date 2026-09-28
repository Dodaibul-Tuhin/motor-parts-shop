<?php
require_once "../includes/auth_check.php";
require_once "../config/db.php";
$pageTitle = "পণ্য";

if (isset($_GET["delete"])) {
    $id = (int)$_GET["delete"];
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: list.php");
    exit;
}

$products = $conn->query("
    SELECT p.*, b.name AS brand_name, c.name AS category_name
    FROM products p
    LEFT JOIN brands b ON p.brand_id = b.id
    LEFT JOIN categories c ON p.category_id = c.id
    ORDER BY p.id DESC
");

require_once "../includes/header.php";
require_once "../includes/sidebar.php";
?>
<div class="page-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>পণ্য</h3>
        <a href="add.php" class="btn btn-primary">+ নতুন পণ্য</a>
    </div>
    <div class="card">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>#</th><th>নাম</th><th>ব্র্যান্ড</th><th>ক্যাটাগরি</th>
                    <th>ক্রয় মূল্য</th><th>বিক্রয় মূল্য</th><th>স্টক</th><th>একশন</th>
                </tr>
            </thead>
            <tbody>
            <?php $i = 1; while ($row = $products->fetch_assoc()): ?>
                <tr class="<?= $row['stock_qty'] <= $row['low_stock_threshold'] ? 'table-warning' : '' ?>">
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($row["name"]) ?></td>
                    <td><?= htmlspecialchars($row["brand_name"] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row["category_name"] ?? '-') ?></td>
                    <td>৳<?= number_format($row["cost_price"], 2) ?></td>
                    <td>৳<?= number_format($row["sell_price"], 2) ?></td>
                    <td><?= (int)$row["stock_qty"] ?></td>
                    <td>
                        <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">এডিট</a>
                        <a href="list.php?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger"
                           onclick="return confirm('আপনি কি নিশ্চিত?')">ডিলিট</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once "../includes/footer.php"; ?>
