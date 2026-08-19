<?php
require __DIR__.'/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php'); exit; }
$url = trim(GOOGLE_SHEET_WEBAPP_URL);
if ($url === '' || strpos($url, 'script.google.com/macros/s/') === false) {
    header('Location: index.php?sync=error&msg='.rawurlencode('Google Sheet Web App URL is not configured in config.php.')); exit;
}

$endpoint = $url;

$ch = curl_init($endpoint);
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_CONNECTTIMEOUT=>10,CURLOPT_TIMEOUT=>30,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_HTTPHEADER=>['Accept: application/json']]);
$response = curl_exec($ch); $curlError=curl_error($ch); $httpCode=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch);
if ($response===false || $curlError) { header('Location: index.php?sync=error&msg='.rawurlencode('Could not connect to Google Sheets: '.$curlError)); exit; }
$data=json_decode($response,true);
if (!is_array($data) || empty($data['ok'])) { $msg=is_array($data)&&isset($data['error'])?$data['error']:'Invalid response from Google Sheets.'; header('Location: index.php?sync=error&msg='.rawurlencode($msg)); exit; }

$rows=$data['rows']??[]; $created=0; $updated=0; $skipped=0; $assignments=[];
$find=$pdo->prepare('SELECT id FROM invoices WHERE invoice_no=? LIMIT 1');
$insert=$pdo->prepare('INSERT INTO invoices (invoice_no,customer_name,contact,user_id,account_password,profile,pin,item,plan,duration,price,issue_date,expiry_date,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
$update=$pdo->prepare('UPDATE invoices SET customer_name=?,contact=?,user_id=?,account_password=?,profile=?,pin=?,item=?,plan=?,duration=?,price=?,issue_date=?,expiry_date=?,status=? WHERE invoice_no=?');
function sheet_date($v){$v=trim((string)$v);if($v==='')return null;$ts=strtotime($v);return $ts?date('Y-m-d',$ts):null;}
function sheet_price($v){$v=str_replace([',','NPR','npr',' '],'',(string)$v);return is_numeric($v)?(float)$v:0;}

foreach($rows as $r){
    if(!is_array($r)){ $skipped++; continue; }
    $name=trim((string)($r['customer_name']??'')); if($name===''){ $skipped++; continue; }
    $invoice=trim((string)($r['invoice_no']??''));
    $wasBlank=($invoice==='');
    if($invoice==='') $invoice=next_invoice_no($pdo);
    $contact=trim((string)($r['contact']??'')); $userId=trim((string)($r['user_id']??'')); $password=trim((string)($r['account_password']??''));
    $profile=trim((string)($r['profile']??'Main Profile'))?:'Main Profile'; $pin=trim((string)($r['pin']??'')); $item=trim((string)($r['item']??'')); $plan=trim((string)($r['plan']??'')); $duration=trim((string)($r['duration']??''));
    $price=sheet_price($r['price']??0); $issue=sheet_date($r['issue_date']??''); $expiry=sheet_date($r['expiry_date']??''); $status=strtoupper(trim((string)($r['status']??'ACTIVE')))?:'ACTIVE';
    $find->execute([$invoice]); $id=$find->fetchColumn();
    if($id){ $update->execute([$name,$contact,$userId,$password,$profile,$pin,$item,$plan,$duration,$price,$issue,$expiry,$status,$invoice]); $updated++; }
    else { $insert->execute([$invoice,$name,$contact,$userId,$password,$profile,$pin,$item,$plan,$duration,$price,$issue,$expiry,$status]); $created++; }
    if($wasBlank && isset($r['sheet_row']) && (int)$r['sheet_row']>1) $assignments[]=['sheet_row'=>(int)$r['sheet_row'],'invoice_no'=>$invoice];
}

// Push newly generated invoice numbers back into Google Sheet.
if(count($assignments)>0){
    $payload=json_encode(['updates'=>$assignments], JSON_UNESCAPED_UNICODE);
    $ch=curl_init($url);
    curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$payload,CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>true,CURLOPT_CONNECTTIMEOUT=>10,CURLOPT_TIMEOUT=>30,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_HTTPHEADER=>['Content-Type: application/json','Accept: application/json']]);
    $push=curl_exec($ch); $pushErr=curl_error($ch); curl_close($ch);
    $pushData=json_decode($push,true);
    if($pushErr || !is_array($pushData) || empty($pushData['ok'])){
        $detail=is_array($pushData)&&isset($pushData['error'])?$pushData['error']:($pushErr?:'Unknown Google Sheet update error');
        $msg="Sync complete: {$created} new, {$updated} updated, {$skipped} skipped. Invoice number push-back failed: {$detail}";
        header('Location: index.php?sync=error&msg='.rawurlencode($msg)); exit;
    }
}
$msg="Sync complete: {$created} new, {$updated} updated, {$skipped} skipped. " . count($assignments) . " invoice number(s) written back to Google Sheet.";
header('Location: index.php?sync=ok&msg='.rawurlencode($msg)); exit;
