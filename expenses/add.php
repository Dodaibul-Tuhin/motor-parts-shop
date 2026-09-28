<?php
require_once "../includes/auth_check.php";
require_once "../config/db.php";
$pageTitle = "নতুন খরচ";

$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $category = trim($_POST["category"]);
    $amount = (float)$_POST["amount"];
    $expense_date = $_POST["expense_date"];
    $note = trim($_POST["note"]);

    if ($category === "" || $amount <= 0) {
        $error = "ক্যাটাগরি ও পরিমাণ সঠিকভাবে দিন।";
    } else {
        $stmt = $conn->prepare("INSERT INTO expenses (category, amount, expense_date, note) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sdss", $category, $amount, $expense_date, $note);
        $stmt->execute();
        header("Location: list.php");
        exit;
    }
}

require_once "../includes/header.php";
require_once "../includes/sidebar.php";
?>
<div class="page-body">
    <h3 class="mb-3">নতুন খরচ যোগ করুন</h3>
    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <div class="card p-4" style="max-width:500px;">
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">খরচের ক্যাটাগরি</label>
                <input type="text" name="category" class="form-control" placeholder="যেমন: ভাড়া, বিদ্যুৎ বিল" required>
            </div>
            <div class="mb-3">
                <label class="form-label">পরিমাণ</label>
                <input type="number" step="0.01" name="amount" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">তারিখ</label>
                <input type="date" name="expense_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">নোট (ঐচ্ছিক)</label>
                <textarea name="note" class="form-control"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">সংরক্ষণ করুন</button>
            <a href="list.php" class="btn btn-secondary">বাতিল</a>
        </form>
    </div>
</div>
<?php require_once "../includes/footer.php"; ?>
