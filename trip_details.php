<?php

require_once 'db_connection.php';
session_start();

$tripId = intval($_GET['id'] ?? 0);

$stmt = $db->prepare("SELECT t.*, c.name as company_name 
                      FROM trips t 
                      JOIN companies c ON t.company_id = c.id 
                      WHERE t.id = ?");
$stmt->execute([$tripId]);
$trip = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$trip) {
    die('Sefer bulunamadı!');
}

$isLoggedIn = isset($_SESSION['user_id']);


$bookedSeats = [];
try {
    $bs = $db->prepare("SELECT seat_number FROM tickets WHERE trip_id = ? AND status = 'active'");
    $bs->execute([$tripId]);
    $bookedSeats = $bs->fetchAll(PDO::FETCH_COLUMN);
    
    $bookedSeats = array_map('intval', $bookedSeats);
} catch (Exception $e) {
    $bookedSeats = [];
}
$totalSeats = intval($trip['total_seats']);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Sefer Detayları - Koltuk Seçimi</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

    <style>
        *{box-sizing:border-box}
        body{
            margin:0;
            font-family:'Poppins',sans-serif;
            background: linear-gradient(135deg,#bfd7f3,#f7f9fc);
            min-height:100vh;
            color:#111827;
        }

        .navbar{
            background: linear-gradient(90deg,#4a63e7,#6b46ff);
            color:white;
            padding:14px 24px;
            box-shadow:0 2px 8px rgba(0,0,0,0.15);
        }

        .container{
            max-width:980px;
            margin:32px auto;
            padding:28px;
            background:white;
            border-radius:14px;
            box-shadow:0 8px 30px rgba(50,50,93,0.08);
        }

        .back-link{
            color:#4a63e7;
            text-decoration:none;
            font-weight:600;
            display:inline-block;
            margin-bottom:18px;
        }

        .detail-top{
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:16px;
            margin-bottom:18px;
        }
        .trip-title{
            font-size:20px;
            font-weight:600;
            color:#0f172a;
        }
        .trip-sub{ color:#6b7280; font-size:14px; }

        .price-box{
            background:#eef2ff;
            border:1px solid #dbe4ff;
            padding:12px 16px;
            border-radius:10px;
            text-align:center;
            min-width:140px;
        }
        .price-box .price{ color:#4a63e7; font-size:20px; font-weight:700; }

        .info-row{
            display:flex;
            justify-content:space-between;
            gap:12px;
            margin-top:8px;
            color:#374151;
            font-size:14px;
        }

        
        .bus-frame{
            margin-top:22px;
            padding:22px;
            border-radius:14px;
            background:linear-gradient(180deg,#ffffff,#f8fbff);
            border:3px solid rgba(74,99,231,0.15);
            box-shadow: inset 0 2px 10px rgba(0,0,0,0.02);
        }

        .driver-area{
            display:flex;
            justify-content:center;
            margin-bottom:14px;
        }
        .driver{
            width:92px;
            height:56px;
            border-radius:10px;
            background:#111827;
            color:white;
            display:flex;
            align-items:center;
            justify-content:center;
            font-weight:700;
            box-shadow:0 4px 12px rgba(0,0,0,0.08);
        }

        .seat-label{
            text-align:center;
            font-weight:700;
            margin-bottom:12px;
            color:#0f172a;
        }

        
        .seat-layout{
            display:grid;
            grid-template-columns: repeat(4, 70px);
            justify-content:center;
            gap:12px 18px;
            align-items:center;
        }

        .seat { 
            width:70px; height:70px; border-radius:10px;
            display:flex; align-items:center; justify-content:center;
            font-weight:700; cursor:pointer; user-select:none;
            transition:transform .12s ease, box-shadow .12s ease;
            border:2px solid transparent;
        }
        .seat.empty { background:transparent; cursor:default; box-shadow:none; }
        .seat.available { background:#10b981; color:white; box-shadow:0 6px 14px rgba(16,185,129,0.18); }
        .seat.available:hover{ transform:translateY(-4px); }
        .seat.booked { background:#d1d5db; color:#4b5563; cursor:not-allowed; box-shadow:none; }
        .seat.selected { background:#2563eb; color:white; border-color:#1e40af; box-shadow:0 8px 22px rgba(37,99,235,0.22); transform:translateY(-3px); }

    
        .aisle { width:18px; height:70px; background:transparent; }

        .legend{
            display:flex;
            gap:12px;
            align-items:center;
            justify-content:center;
            margin-top:16px;
        }
        .legend .item{ display:flex; gap:8px; align-items:center; font-size:13px; color:#374151; }
        .legend .box{ width:18px; height:18px; border-radius:4px; }

        
        .buy-wrap{ margin-top:20px; display:flex; gap:12px; align-items:center; justify-content:space-between; }
        .selected-info{ font-weight:600; color:#0f172a; }
        .btn-buy{
            background: linear-gradient(90deg,#4a63e7,#6b46ff);
            color:white; border:none; padding:12px 18px; border-radius:10px; font-weight:700;
            cursor:pointer; box-shadow:0 8px 24px rgba(75,40,255,0.12);
        }
        .btn-buy:disabled{ opacity:0.6; cursor:not-allowed; box-shadow:none; }

        @media (max-width:620px){
            .seat-layout{ grid-template-columns: repeat(4, 55px); gap:10px 10px; }
            .seat{ width:55px; height:55px; }
        }
    </style>
</head>
<body>
    <div class="navbar">🚌 Sefer Detayları</div>

    <div class="container">
        <a class="back-link" href="index.php">← Ana Sayfaya Dön</a>

        <div class="detail-top">
            <div>
                <div class="trip-title"><?= htmlspecialchars($trip['company_name']) ?></div>
                <div class="trip-sub"><?= htmlspecialchars($trip['departure']) ?> → <?= htmlspecialchars($trip['arrival']) ?></div>
                <div class="info-row">
                    <div>📅 <?= date('d.m.Y', strtotime($trip['departure_date'])) ?></div>
                    <div>🕐 <?= substr($trip['departure_time'],0,5) ?></div>
                    <div>💺 Müsait: <?= $trip['available_seats'] ?></div>
                </div>
            </div>
            <div class="price-box">
                <div style="font-size:13px;color:#374151">Bilet Fiyatı</div>
                <div class="price"><?= number_format($trip['price'],2) ?> ₺</div>
            </div>
        </div>

        <?php if (!$isLoggedIn): ?>
            <div style="margin-top:18px;padding:12px;border-radius:8px;background:#fff4d6;color:#7c2d12;border:1px solid #ffe8a8;">
                ⚠️ Bilet almak için giriş yapmanız gerekiyor. <a href="login.php" style="color:#4a63e7;font-weight:700;">Giriş yap</a>
            </div>
        <?php elseif ($trip['available_seats'] <= 0): ?>
            <div style="margin-top:18px;padding:12px;border-radius:8px;background:#fee2e2;color:#7f1d1d;border:1px solid #fecaca;">
                ❌ Bu seferde müsait koltuk kalmamıştır.
            </div>
        <?php else: ?>
            <div class="bus-frame">
                <div class="driver-area">
                    <div class="driver">👨‍✈️ Kaptan</div>
                </div>

                <div class="seat-label">🪑 Koltuk Seç (Solda: İkili, Sağda: Tekli — Ortada koridor)</div>

                <div class="seat-layout" id="seatLayout">
                    <?php
                    
                    $rows = (int)ceil($totalSeats / 3);
                    $seatNum = 1;
                    for ($r = 0; $r < $rows; $r++) {
                        
                        for ($c = 0; $c < 2; $c++) {
                            if ($seatNum > $totalSeats) {
                                echo "<div class='seat empty'></div>";
                            } else {
                                $cls = in_array($seatNum, $bookedSeats) ? 'booked' : 'available';
                                echo "<div class='seat {$cls}' data-seat='{$seatNum}'>".$seatNum."</div>";
                            }
                            $seatNum++;
                        }
                        
                        echo "<div class='aisle'></div>";
                    
                        if ($seatNum > $totalSeats) {
                            echo "<div class='seat empty'></div>";
                        } else {
                            $cls = in_array($seatNum, $bookedSeats) ? 'booked' : 'available';
                            echo "<div class='seat {$cls}' data-seat='{$seatNum}'>".$seatNum."</div>";
                        }
                        $seatNum++;
                    }
                    ?>
                </div>

                <div class="legend">
                    <div class="item"><span class="box" style="background:#10b981;border-radius:4px"></span>Boş</div>
                    <div class="item"><span class="box" style="background:#d1d5db;border-radius:4px"></span>Dolu</div>
                    <div class="item"><span class="box" style="background:#2563eb;border-radius:4px"></span>Seçili</div>
                </div>

                <form id="buyForm" method="get" action="buy_ticket.php" style="margin-top:18px;">
                    <input type="hidden" name="trip_id" value="<?= $tripId ?>">
                    <input type="hidden" name="seat" id="selectedSeat" value="">
                    <div class="buy-wrap">
                        <div class="selected-info" id="selectedInfo">Seçili koltuk: —</div>
                        <button type="submit" class="btn-buy" id="buyBtn" disabled>🎟️ Devam et</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <script>
        (function(){
            const seatLayout = document.getElementById('seatLayout');
            if (!seatLayout) return;
            const seats = seatLayout.querySelectorAll('.seat');
            const selectedSeatInput = document.getElementById('selectedSeat');
            const selectedInfo = document.getElementById('selectedInfo');
            const buyBtn = document.getElementById('buyBtn');

            function clearSelection(){
                seats.forEach(s => s.classList.remove('selected'));
                selectedSeatInput.value = '';
                selectedInfo.textContent = 'Seçili koltuk: —';
                buyBtn.disabled = true;
            }

            seats.forEach(s => {
                if (s.classList.contains('available')){
                    s.addEventListener('click', () => {
                    
                        seats.forEach(x => x.classList.remove('selected'));
                        s.classList.add('selected');
                        const num = s.getAttribute('data-seat');
                        selectedSeatInput.value = num;
                        selectedInfo.textContent = 'Seçili koltuk: ' + num;
                        buyBtn.disabled = false;
                    });
                }
            });

            
            const buyForm = document.getElementById('buyForm');
            if (buyForm){
                buyForm.addEventListener('submit', function(e){
                    if (!selectedSeatInput.value){
                        e.preventDefault();
                        alert('Lütfen bir koltuk seçin.');
                    }
                });
            }
        })();
    </script>
</body>
</html>
