<?php
require_once "../includes/auth_check.php";
require_once "../config/db.php";
$pageTitle = "পণ্য এডিট";

$id = (int)($_GET["id"] ?? 0);
$error = "";

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header("Location: list.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"]);
    $brand_id = (int)$_POST["brand_id"];
    $category_id = (int)$_POST["category_id"];
    $cost_price = (float)$_POST["cost_price"];
    $sell_price = (float)$_POST["sell_price"];
    $stock_qty = (int)$_POST["stock_qty"];
    $low_stock_threshold = (int)$_POST["low_stock_threshold"];

    if ($name === "") {
        $error = "পণ্যের নাম আবশ্যক।";
    } else {
        $upd = $conn->prepare("UPDATE products SET
            name=?, brand_id=?, category_id=?, cost_price=?, sell_price=?, stock_qty=?, low_stock_threshold=?
            WHERE id=?");
        $upd->bind_param("siiddiii", $name, $brand_id, $category_id, $cost_price, $sell_price, $stock_qty, $low_stock_threshold, $id);
        $upd->execute();
        header("Location: list.php");
        exit;
    }
}

$brands = $conn->query("SELECT * FROM brands ORDER BY name");
$categories = $conn->query("SELECT * FROM categories ORDER BY name");

require_once "../includes/header.php";
require_once "../includes/sidebar.php";
?>
<div class="page-body">
    <h3 class="mb-3">পণ্য এডিট করুন</h3>
    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <div class="card p-4" style="max-width:600px;">
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">পণ্যের নাম</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">ব্র্যান্ড</label>
                    <select name="brand_id" class="form-select">
                        <option value="">-- নির্বাচন করুন --</option>
                        <?php while ($b = $brands->fetch_assoc()): ?>
                            <option value="<?= $b['id'] ?>" <?= $b['id'] == $product['brand_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($b['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">ক্যাটাগরি</label>
                    <select name="category_id" class="form-select">
                        <option value="">-- নির্বাচন করুন --</option>
                        <?php while ($c = $categories->fetch_assoc()): ?>
                            <option value="<?= $c['id'] ?>" <?= $c['id'] == $product['category_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">ক্রয় মূল্য</label>
                    <input type="number" step="0.01" name="cost_price" class="form-control" value="<?= $product['cost_price'] ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">বিক্রয় মূল্য</label>
                    <input type="number" step="0.01" name="sell_price" class="form-control" value="<?= $product['sell_price'] ?>" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">স্টক পরিমাণ</label>
                    <input type="number" name="stock_qty" class="form-control" value="<?= $product['stock_qty'] ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">লো স্টক সীমা</label>
                    <input type="number" name="low_stock_threshold" class="form-control" value="<?= $product['low_stock_threshold'] ?>" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">আপডেট করুন</button>
            <a href="list.php" class="btn btn-secondary">বাতিল</a>
        </form>
    </div>
</div>
<?php require_once "../includes/footer.php"; ?>
