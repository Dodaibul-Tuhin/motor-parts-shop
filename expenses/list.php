<?php
require_once "../includes/auth_check.php";
require_once "../config/db.php";
$pageTitle = "খরচ";

if (isset($_GET["delete"])) {
    $id = (int)$_GET["delete"];
    $stmt = $conn->prepare("DELETE FROM expenses WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: list.php");
    exit;
}

$expenses = $conn->query("SELECT * FROM expenses ORDER BY expense_date DESC, id DESC");

require_once "../includes/header.php";
require_once "../includes/sidebar.php";
?>
<div class="page-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>খরচ</h3>
        <a href="add.php" class="btn btn-primary">+ নতুন খরচ</a>
    </div>
    <div class="card">
        <table class="table mb-0">
            <thead><tr><th>ক্যাটাগরি</th><th>পরিমাণ</th><th>তারিখ</th><th>নোট</th><th>একশন</th></tr></thead>
            <tbody>
            <?php if ($expenses->num_rows === 0): ?>
                <tr><td colspan="5" class="text-center py-3">কোনো খরচ পাওয়া যায়নি</td></tr>
            <?php else: while ($row = $expenses->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row["category"]) ?></td>
                    <td>৳<?= number_format($row["amount"], 2) ?></td>
                    <td><?= date("d M Y", strtotime($row["expense_date"])) ?></td>
                    <td><?= htmlspecialchars($row["note"]) ?></td>
                    <td>
                        <a href="list.php?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger"
                           onclick="return confirm('আপনি কি নিশ্চিত?')">ডিলিট</a>
                    </td>
                </tr>
            <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once "../includes/footer.php"; ?>
