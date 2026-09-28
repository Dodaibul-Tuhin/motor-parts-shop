<?php
require_once "../includes/auth_check.php";
require_once "../config/db.php";
$pageTitle = "ইনভয়েস দেখুন";

$id = (int)($_GET["id"] ?? 0);

$stmt = $conn->prepare("
    SELECT s.*, COALESCE(c.name,'ওয়াক-ইন') AS customer_name, c.phone
    FROM sales s LEFT JOIN customers c ON s.customer_id = c.id
    WHERE s.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$sale = $stmt->get_result()->fetch_assoc();

if (!$sale) {
    header("Location: list.php");
    exit;
}

$itemsStmt = $conn->prepare("
    SELECT si.*, p.name AS product_name
    FROM sale_items si JOIN products p ON si.product_id = p.id
    WHERE si.sale_id = ?
");
$itemsStmt->bind_param("i", $id);
$itemsStmt->execute();
$items = $itemsStmt->get_result();

require_once "../includes/header.php";
require_once "../includes/sidebar.php";
?>
<div class="page-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>ইনভয়েস: <?= htmlspecialchars($sale["invoice_no"]) ?></h3>
        <a href="list.php" class="btn btn-secondary">তালিকায় ফিরুন</a>
    </div>

    <div class="card p-4 mb-3">
        <p><strong>ক্রেতা:</strong> <?= htmlspecialchars($sale["customer_name"]) ?></p>
        <p><strong>তারিখ:</strong> <?= date("d M Y", strtotime($sale["sale_date"])) ?></p>
        <p><strong>স্ট্যাটাস:</strong>
            <span class="badge bg-<?= $sale['status'] === 'Paid' ? 'success' : 'danger' ?>">
                <?= htmlspecialchars($sale["status"]) ?>
            </span>
        </p>
    </div>

    <div class="card">
        <table class="table mb-0">
            <thead><tr><th>পণ্য</th><th>পরিমাণ</th><th>একক মূল্য</th><th>সাবটোটাল</th></tr></thead>
            <tbody>
            <?php while ($item = $items->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($item["product_name"]) ?></td>
                    <td><?= (int)$item["quantity"] ?></td>
                    <td>৳<?= number_format($item["unit_price"], 2) ?></td>
                    <td>৳<?= number_format($item["subtotal"], 2) ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="card p-4 mt-3" style="max-width:400px; margin-left:auto;">
        <p><strong>মোট:</strong> ৳<?= number_format($sale["total_amount"], 2) ?></p>
        <p><strong>প্রদত্ত:</strong> ৳<?= number_format($sale["paid_amount"], 2) ?></p>
        <p><strong>বাকি:</strong> ৳<?= number_format($sale["due_amount"], 2) ?></p>
    </div>
</div>
<?php require_once "../includes/footer.php"; ?>
