<?php
require_once "../includes/auth_check.php";
require_once "../config/db.php";
$pageTitle = "নতুন ব্র্যান্ড";

$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"]);
    if ($name === "") {
        $error = "নাম আবশ্যক।";
    } else {
        $stmt = $conn->prepare("INSERT INTO brands (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        header("Location: list.php");
        exit;
    }
}

require_once "../includes/header.php";
require_once "../includes/sidebar.php";
?>
<div class="page-body">
    <h3 class="mb-3">নতুন ব্র্যান্ড যোগ করুন</h3>
    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <div class="card p-4" style="max-width:500px;">
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">ব্র্যান্ডের নাম</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">সংরক্ষণ করুন</button>
            <a href="list.php" class="btn btn-secondary">বাতিল</a>
        </form>
    </div>
</div>
<?php require_once "../includes/footer.php"; ?>
