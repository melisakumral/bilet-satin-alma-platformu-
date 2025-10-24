<?php

require_once 'db_connection.php';
require_once 'auth_helper.php';
requireRole(['user']);

$tripId = $_GET['trip_id'] ?? 0;
$seatNumber = $_GET['seat'] ?? 0;
$error = '';
$success = '';


$stmt = $db->prepare("SELECT t.*, c.name as company_name 
                      FROM trips t 
                      JOIN companies c ON t.company_id = c.id 
                      WHERE t.id = ?");
$stmt->execute([$tripId]);
$trip = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$trip) {
    die('Sefer bulunamadı!');
}


$stmt = $db->prepare("SELECT seat_number FROM tickets WHERE trip_id = ? AND status = 'active'");
$stmt->execute([$tripId]);
$occupiedSeats = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (in_array($seatNumber, $occupiedSeats)) {
    die('Bu koltuk dolu! Lütfen başka bir koltuk seçin.');
}


$stmt = $db->prepare("SELECT credit FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$userCredit = $stmt->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $couponCode = trim($_POST['coupon_code'] ?? '');
    $finalPrice = $trip['price'];
    $couponUsed = null;

   
    if (!empty($couponCode)) {
        $stmt = $db->prepare("SELECT * FROM coupons WHERE code = ? AND expiry_date >= date('now') AND used_count < usage_limit");
        $stmt->execute([$couponCode]);
        $coupon = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($coupon) {
            $discount = ($finalPrice * $coupon['discount_rate']) / 100;
            $finalPrice -= $discount;
            $couponUsed = $couponCode;
        } else {
            $error = 'Geçersiz veya süresi dolmuş kupon kodu!';
        }
    }

    if (empty($error)) {
        if ($userCredit < $finalPrice) {
            $error = 'Yetersiz bakiye! Bakiyeniz: ' . number_format($userCredit, 2) . ' ₺';
        } else {
            try {
                $db->beginTransaction();
                
                
                $stmt = $db->prepare("INSERT INTO tickets (trip_id, user_id, seat_number, price_paid, coupon_code, status) 
                                      VALUES (?, ?, ?, ?, ?, 'active')");
                $stmt->execute([$tripId, $_SESSION['user_id'], $seatNumber, $finalPrice, $couponUsed]);
                
                
                $stmt = $db->prepare("UPDATE users SET credit = credit - ? WHERE id = ?");
                $stmt->execute([$finalPrice, $_SESSION['user_id']]);
                
                
                $stmt = $db->prepare("UPDATE trips SET available_seats = available_seats - 1 WHERE id = ?");
                $stmt->execute([$tripId]);
               
                if ($couponUsed) {
                    $stmt = $db->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE code = ?");
                    $stmt->execute([$couponUsed]);
                }
                
                $db->commit();
                header('Location: my_tickets.php');
                exit;
            } catch (PDOException $e) {
                $db->rollBack();
                $error = 'Bilet alımı sırasında hata oluştu!';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilet Satın Al</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg,#dbeafe,#fefeff);
            margin: 0;
            padding: 0;
            color: #1f2937;
        }
        .navbar {
            background: linear-gradient(90deg,#4f46e5,#6b21a8);
            color: white;
            padding: 15px 30px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.15);
        }
        .container {
            max-width: 800px;
            margin: 40px auto;
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 6px 25px rgba(0,0,0,0.08);
        }
        h1 { margin: 0; }
        h2 { margin-top: 0; color: #111827; }
        .back-link {
            display: inline-block;
            margin-bottom: 15px;
            color: #4f46e5;
            text-decoration: none;
            font-weight: 600;
        }
        .trip-summary {
            background: #f3f4f6;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            line-height: 1.5;
        }
        .credit-info {
            background: #e0f2fe;
            padding: 10px 15px;
            border-radius: 8px;
            color: #0369a1;
            font-weight: 500;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 15px;
        }
        .price-summary {
            background: #f9fafb;
            border-radius: 10px;
            padding: 15px 20px;
            margin: 25px 0;
        }
        .price-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
        }
        .total-price {
            border-top: 2px solid #e5e7eb;
            padding-top: 10px;
            font-size: 20px;
            font-weight: 700;
            color: #4f46e5;
        }
        .btn-buy {
            width: 100%;
            padding: 15px;
            background: linear-gradient(90deg,#4f46e5,#6b21a8);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 17px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s ease;
        }
        .btn-buy:hover {
            background: linear-gradient(90deg,#4338ca,#7e22ce);
        }
        .error {
            background: #fee2e2;
            color: #7f1d1d;
            border: 1px solid #fecaca;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>🎫 Bilet Satın Al</h1>
    </div>

    <div class="container">
        <a href="trip_details.php?id=<?= $tripId ?>" class="back-link">← Geri Dön</a>

        <div class="credit-info">
            💳 Mevcut Bakiyeniz: <strong><?= number_format($userCredit, 2) ?> ₺</strong>
        </div>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="trip-summary">
            <strong><?= htmlspecialchars($trip['company_name']) ?></strong><br>
            <?= htmlspecialchars($trip['departure']) ?> → <?= htmlspecialchars($trip['arrival']) ?><br>
            📅 <?= date('d.m.Y', strtotime($trip['departure_date'])) ?> | 🕐 <?= substr($trip['departure_time'], 0, 5) ?><br>
            💺 Seçilen Koltuk: <strong><?= htmlspecialchars($seatNumber) ?></strong>
        </div>

        <form method="POST">
            <div class="form-group">
                <label>İndirim Kuponu (Opsiyonel)</label>
                <input type="text" name="coupon_code" placeholder="Kupon kodunuzu girin">
            </div>

            <div class="price-summary">
                <div class="price-row">
                    <span>Bilet Fiyatı:</span>
                    <span><?= number_format($trip['price'], 2) ?> ₺</span>
                </div>
                <div class="price-row total-price">
                    <span>Ödenecek Tutar:</span>
                    <span><?= number_format($trip['price'], 2) ?> ₺</span>
                </div>
            </div>

            <button type="submit" class="btn-buy">Satın Al</button>
        </form>
    </div>
</body>
</html>
