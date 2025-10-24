<?php
require_once 'db_connection.php';
session_start();

$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['full_name'] ?? '';
$userRole = $_SESSION['role'] ?? '';

$stmt = $db->prepare("SELECT * FROM coupons 
                      WHERE expiry_date >= date('now') 
                      AND used_count < usage_limit 
                      ORDER BY discount_rate DESC");
$stmt->execute();
$activeCoupons = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare("SELECT * FROM coupons 
                      WHERE expiry_date < date('now') 
                      OR used_count >= usage_limit 
                      ORDER BY created_at DESC 
                      LIMIT 10");
$stmt->execute();
$expiredCoupons = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🎟️ Jet Bilet - Kuponlar</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Montserrat', sans-serif; background: #f0f4f8; }

        
        .navbar { 
            background: linear-gradient(135deg, #667eea, #5568d3);
            color: white; 
            padding: 15px 30px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .navbar h1 { font-size: 24px; font-weight: bold; }
        .navbar-links a, .navbar-links span { 
            font-weight: 600;
            color: white; 
            text-decoration: none; 
            margin-left: 15px; 
            padding: 8px 16px;
            border-radius: 10px;
            transition: all 0.3s; 
            display: inline-block;
        }
        .navbar-links a:hover { 
            background: linear-gradient(135deg, #ff6b6b, #ff4757);
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            transform: translateY(-2px);
        }
        .coupon-highlight {
            background: linear-gradient(135deg, #ffcc33 0%, #ff9900 100%);
            color: white;
            font-weight: bold;
            padding: 8px 18px;
            border-radius: 8px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .coupon-highlight:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.2);
        }

        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }

        .page-header {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 30px;
            text-align: center;
            margin-bottom: 30px;
        }

        .coupons-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .coupon-card {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.15);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .coupon-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }

        .coupon-discount {
            font-size: 42px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .coupon-code {
            border: 2px dashed rgba(255,255,255,0.6);
            padding: 15px;
            border-radius: 10px;
            background: rgba(255,255,255,0.15);
            font-family: 'Courier New', monospace;
            text-align: center;
            letter-spacing: 3px;
            font-size: 18px;
            margin-bottom: 10px;
        }

        .copy-button {
            background: white;
            color: #667eea;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
            width: 100%;
        }
        .copy-button:hover {
            background: #f0f0f0;
            transform: translateY(-2px);
        }

        .expired-section {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin-top: 40px;
        }

        .expired-coupon {
            display: flex;
            justify-content: space-between;
            padding: 15px;
            border-bottom: 1px solid #eee;
            color: #999;
        }

        .toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #28a745;
            color: white;
            padding: 15px 25px;
            border-radius: 8px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>🎫 JET BİLET</h1>
        <div class="navbar-links">
            <a href="index.php">Ana Sayfa</a>
            <a href="coupons.php" class="coupon-highlight">Kuponlar</a>
            <?php if ($isLoggedIn): ?>
                <span>Hoşgeldin, <?= htmlspecialchars($userName) ?></span>
                <?php if ($userRole == 'user'): ?>
                    <a href="my_tickets.php">Biletlerim</a>
                <?php elseif ($userRole == 'admin'): ?>
                    <a href="admin_panel.php">Admin Panel</a>
                <?php elseif ($userRole == 'firma_admin'): ?>
                    <a href="firma_admin_panel.php">Firma Panel</a>
                <?php endif; ?>
                <a href="logout.php">Çıkış</a>
            <?php else: ?>
                <a href="login.php">Giriş Yap</a>
                <a href="register.php">Kayıt Ol</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="container">
        <div class="page-header">
            <h2>🎟️ Aktif Kuponlar</h2>
            <p>Bilet alırken bu kodlarla indirim kazanabilirsiniz!</p>
        </div>

        <?php if (empty($activeCoupons)): ?>
            <div class="no-results" style="text-align:center; background:white; border-radius:15px; padding:50px; color:#999;">
                <h3>😕 Şu anda aktif kupon bulunmuyor.</h3>
            </div>
        <?php else: ?>
            <div class="coupons-grid">
                <?php foreach ($activeCoupons as $coupon): ?>
                    <div class="coupon-card">
                        <div class="coupon-discount">%<?= htmlspecialchars($coupon['discount_rate']) ?></div>
                        <div>İndirim Kuponu</div>
                        <div class="coupon-code"><?= htmlspecialchars($coupon['code']) ?></div>
                        <button class="copy-button" onclick="copyCode('<?= htmlspecialchars($coupon['code']) ?>')">📋 Kodu Kopyala</button>
                        <p style="margin-top:10px; font-size:12px;">Son Kullanma: <?= date('d.m.Y', strtotime($coupon['expiry_date'])) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($expiredCoupons)): ?>
            <div class="expired-section">
                <h3>⏰ Süresi Dolan Kuponlar</h3>
                <?php foreach ($expiredCoupons as $coupon): ?>
                    <div class="expired-coupon">
                        <span><strong><?= htmlspecialchars($coupon['code']) ?></strong> - %<?= $coupon['discount_rate'] ?> indirim</span>
                        <span>Geçersiz</span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="toast" id="toast">✅ Kupon kodu kopyalandı!</div>

    <script>
        function copyCode(code) {
            navigator.clipboard.writeText(code);
            const toast = document.getElementById('toast');
            toast.style.display = 'block';
            setTimeout(() => toast.style.display = 'none', 3000);
        }
    </script>
</body>
</html>
