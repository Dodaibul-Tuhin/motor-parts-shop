<?php
require_once "../includes/auth_check.php";
require_once "../config/db.php";
$pageTitle = "নতুন বিক্রয়";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $customer_name = trim($_POST["customer_name"]);
    $sale_date = $_POST["sale_date"];
    $paid_amount = (float)$_POST["paid_amount"];
    $product_ids = $_POST["product_id"] ?? [];
    $quantities = $_POST["quantity"] ?? [];
    $prices = $_POST["unit_price"] ?? [];

    if (empty($product_ids)) {
        $error = "অন্তত একটি পণ্য যোগ করুন।";
    } else {
        $conn->begin_transaction();
        try {
            // Handle customer (create a simple walk-in record if name given, else null)
            $customer_id = null;
            if ($customer_name !== "") {
                $custStmt = $conn->prepare("INSERT INTO customers (name) VALUES (?)");
                $custStmt->bind_param("s", $customer_name);
                $custStmt->execute();
                $customer_id = $custStmt->insert_id;
            }

            // Calculate total
            $total = 0;
            foreach ($product_ids as $i => $pid) {
                $total += (float)$quantities[$i] * (float)$prices[$i];
            }
            $due = max(0, $total - $paid_amount);
            $status = $due <= 0 ? "Paid" : "Due";
            $invoice_no = "INV-" . date("Ymd") . "-" . str_pad(rand(1, 9999), 4, "0", STR_PAD_LEFT);

            $saleStmt = $conn->prepare("INSERT INTO sales
                (invoice_no, customer_id, sale_date, total_amount, paid_amount, due_amount, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)");
            $saleStmt->bind_param("sisddds", $invoice_no, $customer_id, $sale_date, $total, $paid_amount, $due, $status);
            $saleStmt->execute();
            $sale_id = $saleStmt->insert_id;

            $itemStmt = $conn->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, subtotal)
                VALUES (?, ?, ?, ?, ?)");
            $stockStmt = $conn->prepare("UPDATE products SET stock_qty = stock_qty - ? WHERE id = ?");

            foreach ($product_ids as $i => $pid) {
                $pid = (int)$pid;
                $qty = (int)$quantities[$i];
                $price = (float)$prices[$i];
                $subtotal = $qty * $price;

                $itemStmt->bind_param("iiidd", $sale_id, $pid, $qty, $price, $subtotal);
                $itemStmt->execute();

                $stockStmt->bind_param("ii", $qty, $pid);
                $stockStmt->execute();
            }

            $conn->commit();
            header("Location: view.php?id=" . $sale_id);
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $error = "সমস্যা হয়েছে: " . $e->getMessage();
        }
    }
}

$products = $conn->query("SELECT id, name, sell_price, stock_qty FROM products ORDER BY name");
$productList = [];
while ($p = $products->fetch_assoc()) { $productList[] = $p; }

require_once "../includes/header.php";
require_once "../includes/sidebar.php";
?>
<div class="page-body">
    <h3 class="mb-3">নতুন বিক্রয় (ইনভয়েস)</h3>
    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="card p-4">
        <form method="POST" id="saleForm">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">ক্রেতার নাম (ঐচ্ছিক)</label>
                    <input type="text" name="customer_name" class="form-control" placeholder="ওয়াক-ইন">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">তারিখ</label>
                    <input type="date" name="sale_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">প্রদত্ত পরিমাণ</label>
                    <input type="number" step="0.01" name="paid_amount" id="paidAmount" class="form-control" value="0" required>
                </div>
            </div>

            <hr>
            <h5>পণ্য যোগ করুন</h5>
            <table class="table" id="itemsTable">
                <thead>
                    <tr><th>পণ্য</th><th>মূল্য</th><th>পরিমাণ</th><th>সাবটোটাল</th><th></th></tr>
                </thead>
                <tbody></tbody>
            </table>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addRow()">+ পণ্য যোগ করুন</button>

            <div class="text-end mt-3">
                <h5>মোট: ৳<span id="grandTotal">0.00</span></h5>
            </div>

            <button type="submit" class="btn btn-primary mt-3">ইনভয়েস তৈরি করুন</button>
            <a href="list.php" class="btn btn-secondary mt-3">বাতিল</a>
        </form>
    </div>
</div>

<script>
const products = <?= json_encode($productList) ?>;
let rowCount = 0;

function addRow() {
    const tbody = document.querySelector("#itemsTable tbody");
    const rowId = rowCount++;
    const tr = document.createElement("tr");
    tr.id = "row-" + rowId;

    let options = '<option value="">-- নির্বাচন করুন --</option>';
    products.forEach(p => {
        options += `<option value="${p.id}" data-price="${p.sell_price}">${p.name} (স্টক: ${p.stock_qty})</option>`;
    });

    tr.innerHTML = `
        <td>
            <select class="form-select" name="product_id[]" onchange="updateRow(${rowId})" required>
                ${options}
            </select>
        </td>
        <td><input type="number" step="0.01" class="form-control" name="unit_price[]" id="price-${rowId}" onchange="calcRow(${rowId})" required></td>
        <td><input type="number" class="form-control" name="quantity[]" id="qty-${rowId}" value="1" min="1" onchange="calcRow(${rowId})" required></td>
        <td>৳<span id="subtotal-${rowId}">0.00</span></td>
        <td><button type="button" class="btn btn-sm btn-danger" onclick="removeRow(${rowId})">X</button></td>
    `;
    tbody.appendChild(tr);
}

function updateRow(rowId) {
    const select = document.querySelector(`#row-${rowId} select`);
    const price = select.options[select.selectedIndex]?.dataset.price || 0;
    document.getElementById(`price-${rowId}`).value = price;
    calcRow(rowId);
}

function calcRow(rowId) {
    const price = parseFloat(document.getElementById(`price-${rowId}`).value) || 0;
    const qty = parseFloat(document.getElementById(`qty-${rowId}`).value) || 0;
    const subtotal = price * qty;
    document.getElementById(`subtotal-${rowId}`).innerText = subtotal.toFixed(2);
    calcGrandTotal();
}

function removeRow(rowId) {
    document.getElementById(`row-${rowId}`).remove();
    calcGrandTotal();
}

function calcGrandTotal() {
    let total = 0;
    document.querySelectorAll('[id^="subtotal-"]').forEach(el => {
        total += parseFloat(el.innerText) || 0;
    });
    document.getElementById("grandTotal").innerText = total.toFixed(2);
}

// Start with one row
addRow();
</script>

<?php require_once "../includes/footer.php"; ?>
