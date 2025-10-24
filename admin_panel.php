<?php
require_once 'db_connection.php';
require_once 'auth_helper.php';
requireRole(['admin']);

$message = '';
$error = '';


if (isset($_POST['add_company'])) {
    $name = trim($_POST['company_name']);
    try {
        $stmt = $db->prepare("INSERT INTO companies (name) VALUES (?)");
        $stmt->execute([$name]);
        $message = 'Firma başarıyla eklendi!';
    } catch (PDOException $e) {
        $error = 'Firma eklenirken hata oluştu!';
    }
}


if (isset($_GET['delete_company'])) {
    $stmt = $db->prepare("DELETE FROM companies WHERE id = ?");
    $stmt->execute([$_GET['delete_company']]);
    $message = 'Firma silindi!';
}


if (isset($_POST['add_firma_admin'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $fullName = trim($_POST['full_name']);
    $companyId = $_POST['company_id'];
    
    try {
        $stmt = $db->prepare("INSERT INTO users (username, email, password, full_name, role, company_id, credit) 
                              VALUES (?, ?, ?, ?, 'firma_admin', ?, 5000)");
        $stmt->execute([$username, $email, $password, $fullName, $companyId]);
        $message = 'Firma Admin başarıyla eklendi!';
    } catch (PDOException $e) {
        $error = 'Firma Admin eklenirken hata oluştu!';
    }
}


if (isset($_POST['add_coupon'])) {
    $code = strtoupper(trim($_POST['code']));
    $discountRate = $_POST['discount_rate'];
    $usageLimit = $_POST['usage_limit'];
    $expiryDate = $_POST['expiry_date'];
    
    try {
        $stmt = $db->prepare("INSERT INTO coupons (code, discount_rate, usage_limit, expiry_date) 
                              VALUES (?, ?, ?, ?)");
        $stmt->execute([$code, $discountRate, $usageLimit, $expiryDate]);
        $message = 'Kupon başarıyla eklendi!';
    } catch (PDOException $e) {
        $error = 'Kupon eklenirken hata oluştu!';
    }
}


if (isset($_GET['delete_coupon'])) {
    $stmt = $db->prepare("DELETE FROM coupons WHERE id = ?");
    $stmt->execute([$_GET['delete_coupon']]);
    $message = 'Kupon silindi!';
}


$companies = $db->query("SELECT * FROM companies ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$firmaAdmins = $db->query("SELECT u.*, c.name as company_name FROM users u 
                           LEFT JOIN companies c ON u.company_id = c.id 
                           WHERE u.role = 'firma_admin' ORDER BY u.username")->fetchAll(PDO::FETCH_ASSOC);
$coupons = $db->query("SELECT * FROM coupons ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Paneli</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: #f3f4f6;
            color: #333;
        }

    
        .navbar {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }

        .navbar h1 {
            font-size: 22px;
            font-weight: 600;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            margin-left: 15px;
            padding: 10px 18px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            background: rgba(255,255,255,0.1);
        }

        .navbar a:hover {
            background: white;
            color: #2575fc;
            transform: scale(1.05);
            box-shadow: 0 0 12px rgba(255,255,255,0.4);
        }

        
        .container { max-width: 1400px; margin: 40px auto; padding: 0 20px; }
        h2 { margin-bottom: 20px; color: #333; }

        
        .message, .error {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
            animation: fadeIn 0.5s ease-in;
        }
        .message { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }

          
        .section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            animation: fadeInUp 0.6s ease;
        }

        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; color: #444; }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            transition: 0.3s;
        }

        .form-group input:focus, .form-group select:focus {
            border-color: #2575fc;
            box-shadow: 0 0 5px rgba(37,117,252,0.3);
            outline: none;
        }

         
        button {
            padding: 12px 22px;
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        button:hover {
            transform: scale(1.05);
            background: linear-gradient(135deg, #5c0fc2, #1a63e2);
            box-shadow: 0 0 10px rgba(37,117,252,0.4);
        }

        .btn-delete {
            background: #dc3545;
            border-radius: 8px;
            padding: 6px 14px;
            font-size: 12px;
            text-decoration: none;
            color: white;
            transition: 0.3s;
        }

        .btn-delete:hover {
            background: #c82333;
            transform: scale(1.05);
        }

        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; color: #444; font-weight: 600; }
        tr:hover { background: #f1f7ff; transition: 0.2s; }

          
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Admin Paneli</h1>
        <div>
            <a href="index.php">Ana Sayfa</a>
            <a href="logout.php">Çıkış</a>
        </div>
    </div>

    <div class="container">
        <?php if ($message): ?><div class="message"><?= htmlspecialchars($message) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        
        <div class="section">
            <h2>🏢 Firma Yönetimi</h2>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Firma Adı</label>
                        <input type="text" name="company_name" required>
                    </div>
                    <div class="form-group" style="display: flex; align-items: flex-end;">
                        <button type="submit" name="add_company">Firma Ekle</button>
                    </div>
                </div>
            </form>

            <table>
                <thead>
                    <tr><th>ID</th><th>Firma Adı</th><th>Oluşturulma Tarihi</th><th>İşlem</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($companies as $company): ?>
                    <tr>
                        <td><?= $company['id'] ?></td>
                        <td><?= htmlspecialchars($company['name']) ?></td>
                        <td><?= date('d.m.Y H:i', strtotime($company['created_at'])) ?></td>
                        <td><a href="?delete_company=<?= $company['id'] ?>" class="btn-delete" onclick="return confirm('Bu firmayı silmek istediğinizden emin misiniz?')">Sil</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        
        <div class="section">
            <h2>👩‍💼 Firma Admin Yönetimi</h2>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group"><label>Kullanıcı Adı</label><input type="text" name="username" required></div>
                    <div class="form-group"><label>E-posta</label><input type="email" name="email" required></div>
                    <div class="form-group"><label>Ad Soyad</label><input type="text" name="full_name" required></div>
                    <div class="form-group"><label>Şifre</label><input type="password" name="password" required></div>
                    <div class="form-group">
                        <label>Firma</label>
                        <select name="company_id" required>
                            <option value="">Firma Seçin</option>
                            <?php foreach ($companies as $company): ?>
                                <option value="<?= $company['id'] ?>"><?= htmlspecialchars($company['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="display: flex; align-items: flex-end;">
                        <button type="submit" name="add_firma_admin">Firma Admin Ekle</button>
                    </div>
                </div>
            </form>

            <table>
                <thead>
                    <tr><th>ID</th><th>Kullanıcı Adı</th><th>Ad Soyad</th><th>E-posta</th><th>Firma</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($firmaAdmins as $admin): ?>
                    <tr>
                        <td><?= $admin['id'] ?></td>
                        <td><?= htmlspecialchars($admin['username']) ?></td>
                        <td><?= htmlspecialchars($admin['full_name']) ?></td>
                        <td><?= htmlspecialchars($admin['email']) ?></td>
                        <td><?= htmlspecialchars($admin['company_name']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        
        <div class="section">
            <h2>🎟️ Kupon Yönetimi</h2>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group"><label>Kupon Kodu</label><input type="text" name="code" required></div>
                    <div class="form-group"><label>İndirim Oranı (%)</label><input type="number" name="discount_rate" min="1" max="100" required></div>
                    <div class="form-group"><label>Kullanım Limiti</label><input type="number" name="usage_limit" min="1" required></div>
                    <div class="form-group"><label>Son Kullanma Tarihi</label><input type="date" name="expiry_date" required></div>
                    <div class="form-group" style="display: flex; align-items: flex-end;">
                        <button type="submit" name="add_coupon">Kupon Ekle</button>
                    </div>
                </div>
            </form>

            <table>
                <thead>
                    <tr><th>ID</th><th>Kod</th><th>İndirim (%)</th><th>Kullanım</th><th>Son Kullanma</th><th>İşlem</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($coupons as $coupon): ?>
                    <tr>
                        <td><?= $coupon['id'] ?></td>
                        <td><strong><?= htmlspecialchars($coupon['code']) ?></strong></td>
                        <td>%<?= $coupon['discount_rate'] ?></td>
                        <td><?= $coupon['used_count'] ?> / <?= $coupon['usage_limit'] ?></td>
                        <td><?= date('d.m.Y', strtotime($coupon['expiry_date'])) ?></td>
                        <td><a href="?delete_coupon=<?= $coupon['id'] ?>" class="btn-delete" onclick="return confirm('Bu kuponu silmek istediğinizden emin misiniz?')">Sil</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
