<?php
require_once "../includes/auth_check.php";
require_once "../config/db.php";
$pageTitle = "ব্র্যান্ড এডিট";

$id = (int)($_GET["id"] ?? 0);
$error = "";

$stmt = $conn->prepare("SELECT * FROM brands WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$brand = $stmt->get_result()->fetch_assoc();

if (!$brand) {
    header("Location: list.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"]);
    if ($name === "") {
        $error = "নাম আবশ্যক।";
    } else {
        $upd = $conn->prepare("UPDATE brands SET name = ? WHERE id = ?");
        $upd->bind_param("si", $name, $id);
        $upd->execute();
        header("Location: list.php");
        exit;
    }
}

require_once "../includes/header.php";
require_once "../includes/sidebar.php";
?>
<div class="page-body">
    <h3 class="mb-3">ব্র্যান্ড এডিট করুন</h3>
    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <div class="card p-4" style="max-width:500px;">
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">ব্র্যান্ডের নাম</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($brand['name']) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">আপডেট করুন</button>
            <a href="list.php" class="btn btn-secondary">বাতিল</a>
        </form>
    </div>
</div>
<?php require_once "../includes/footer.php"; ?>
