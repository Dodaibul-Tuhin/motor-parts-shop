<?php
require_once "../includes/auth_check.php";
require_once "../config/db.php";
$pageTitle = "ক্যাটাগরি";

if (isset($_GET["delete"])) {
    $id = (int)$_GET["delete"];
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: list.php");
    exit;
}

$categories = $conn->query("SELECT * FROM categories ORDER BY id DESC");

require_once "../includes/header.php";
require_once "../includes/sidebar.php";
?>
<div class="page-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>ক্যাটাগরি</h3>
        <a href="add.php" class="btn btn-primary">+ নতুন ক্যাটাগরি</a>
    </div>
    <div class="card">
        <table class="table mb-0">
            <thead><tr><th>#</th><th>নাম</th><th>একশন</th></tr></thead>
            <tbody>
            <?php $i = 1; while ($row = $categories->fetch_assoc()): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($row["name"]) ?></td>
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
