<?php
require_once "../includes/auth_check.php";
require_once "../config/db.php";
$pageTitle = "বিক্রয়";

$sales = $conn->query("
    SELECT s.*, COALESCE(c.name, 'ওয়াক-ইন') AS customer_name
    FROM sales s
    LEFT JOIN customers c ON s.customer_id = c.id
    ORDER BY s.sale_date DESC, s.id DESC
");

require_once "../includes/header.php";
require_once "../includes/sidebar.php";
?>
<div class="page-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>বিক্রয়</h3>
        <a href="add.php" class="btn btn-primary">+ নতুন বিক্রয়</a>
    </div>
    <div class="card">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>ইনভয়েস</th><th>ক্রেতা</th><th>তারিখ</th>
                    <th>প্রদেয়</th><th>প্রদত্ত</th><th>বাকি</th><th>স্ট্যাটাস</th><th>একশন</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($sales->num_rows === 0): ?>
                <tr><td colspan="8" class="text-center py-3">কোনো বিক্রয় পাওয়া যায়নি</td></tr>
            <?php else: while ($row = $sales->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row["invoice_no"]) ?></td>
                    <td><?= htmlspecialchars($row["customer_name"]) ?></td>
                    <td><?= date("d M Y", strtotime($row["sale_date"])) ?></td>
                    <td>৳<?= number_format($row["total_amount"], 2) ?></td>
                    <td>৳<?= number_format($row["paid_amount"], 2) ?></td>
                    <td>৳<?= number_format($row["due_amount"], 2) ?></td>
                    <td>
                        <span class="badge bg-<?= $row['status'] === 'Paid' ? 'success' : 'danger' ?>">
                            <?= htmlspecialchars($row["status"]) ?>
                        </span>
                    </td>
                    <td><a href="view.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info">দেখুন</a></td>
                </tr>
            <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once "../includes/footer.php"; ?>
