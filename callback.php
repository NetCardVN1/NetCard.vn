<?php
session_start();
// --- THAY THÔNG TIN Ở ĐÂY ---
$partner_id = 'ID_CUA_NI'; 
$partner_key = 'KEY_CUA_NI'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type = $_POST['card_type'];
    $amount = $_POST['amount'];
    $serial = $_POST['serial'];
    $pin = $_POST['pin'];
    
    // Check khóa 20p
    if (isset($_SESSION['lock']) && time() < $_SESSION['lock']) {
        die("Tài khoản đang bị khóa!");
    }

    $sign = md5($partner_key . $pin . $serial);
    $url = "https://doithe.vn/chargingws/v2?sign=$sign&id=$partner_id&code=$pin&serial=$serial&telco=$type&amount=$amount";

    $res = json_decode(file_get_contents($url), true);

    if ($res['status'] == 1) {
        $_SESSION['fail'] = 0;
        echo "✅ Thẻ đúng! Đang xử lý...";
    } else {
        $_SESSION['fail'] = ($_SESSION['fail'] ?? 0) + 1;
        if ($_SESSION['fail'] >= 3) $_SESSION['lock'] = time() + 1200;
        echo "❌ Thẻ sai!";
    }
}
?>
