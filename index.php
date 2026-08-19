<?php
require __DIR__.'/config.php';
$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $stmt=$pdo->prepare("SELECT * FROM invoices WHERE customer_name LIKE ? OR invoice_no LIKE ? OR contact LIKE ? ORDER BY id DESC");
    $like="%$q%"; $stmt->execute([$like,$like,$like]);
} else $stmt=$pdo->query("SELECT * FROM invoices ORDER BY id DESC");
$rows=$stmt->fetchAll();
$sync = $_GET['sync'] ?? ''; $syncMsg = $_GET['msg'] ?? '';
?><!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Nepal Premium Store</title><link rel="stylesheet" href="assets/style.css"></head><body>
<div class="dash">
  <div class="dash-head"><div><div class="dash-title">NEPAL PREMIUM STORE</div><div>PHP + MySQL + Google Sheets Invoice Dashboard</div></div><div style="display:flex;gap:10px"><form method="post" action="sync_google_sheet.php"><button class="btn green" type="submit">↻ SYNC FROM GOOGLE SHEET</button></form><a class="btn purple" href="add_customer.php">＋ ADD NEW CUSTOMER</a></div></div>
  <?php if($syncMsg): ?><div style="margin-top:15px;padding:12px 15px;border-radius:9px;background:<?=$sync==='ok'?'#eaf9ee':'#fff0f0'?>;border:1px solid <?=$sync==='ok'?'#79c98a':'#ef9a9a'?>;color:<?=$sync==='ok'?'#14772b':'#b00000'?>;font-weight:700"><?=e($syncMsg)?></div><?php endif; ?>
  <div class="dash-card">
    <form class="toolbar" method="get"><h2>Invoices</h2><input name="q" value="<?=e($q)?>" placeholder="Search customer / invoice / contact"></form>
    <div style="overflow:auto"><table class="dash-table"><thead><tr><th>Invoice</th><th>Customer</th><th>Item</th><th>Price</th><th>Issue</th><th>Status</th><th>Action</th></tr></thead><tbody>
    <?php foreach($rows as $r): ?><tr><td><?=e($r['invoice_no'])?></td><td><?=e($r['customer_name'])?></td><td><?=e($r['item'])?></td><td><?=money($r['price'])?></td><td><?=pretty_date($r['issue_date'])?></td><td style="color:#0a9d2c;font-weight:800">✓ <?=e($r['status'])?></td><td><a class="btn purple" style="padding:7px 10px;font-size:12px" href="invoice.php?id=<?=$r['id']?>">VIEW</a></td></tr><?php endforeach; ?>
    </tbody></table></div>
  </div>
</div></body></html>
