<?php

require_once 'db_connection.php';
require_once 'auth_helper.php';
requireRole(['firma_admin']);

$message = '';
$error = '';
$companyId = $_SESSION['company_id'];


$stmt = $db->prepare("SELECT name FROM companies WHERE id = ?");
$stmt->execute([$companyId]);
$companyName = $stmt->fetchColumn();


if (isset($_POST['add_trip'])) {
    $departure = trim($_POST['departure']);
    $arrival = trim($_POST['arrival']);
    $date = $_POST['departure_date'];
    $time = $_POST['departure_time'];
    $price = $_POST['price'];
    $seats = $_POST['total_seats'];
    
    try {
        $stmt = $db->prepare("INSERT INTO trips (company_id, departure, arrival, departure_date, departure_time, price, total_seats, available_seats) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$companyId, $departure, $arrival, $date, $time, $price, $seats, $seats]);
        $message = 'Sefer başarıyla eklendi!';
    } catch (PDOException $e) {
        $error = 'Sefer eklenirken hata oluştu!';
    }
}


if (isset($_GET['delete_trip'])) {
    $stmt = $db->prepare("DELETE FROM trips WHERE id = ? AND company_id = ?");
    $stmt->execute([$_GET['delete_trip'], $companyId]);
    $message = 'Sefer silindi!';
}


if (isset($_POST['update_trip'])) {
    $tripId = $_POST['trip_id'];
    $departure = trim($_POST['departure']);
    $arrival = trim($_POST['arrival']);
    $date = $_POST['departure_date'];
    $time = $_POST['departure_time'];
    $price = $_POST['price'];
    $seats = $_POST['total_seats'];
    
    try {
        $stmt = $db->prepare("UPDATE trips SET departure = ?, arrival = ?, departure_date = ?, departure_time = ?, price = ?, total_seats = ? 
                              WHERE id = ? AND company_id = ?");
        $stmt->execute([$departure, $arrival, $date, $time, $price, $seats, $tripId, $companyId]);
        $message = 'Sefer güncellendi!';
    } catch (PDOException $e) {
        $error = 'Sefer güncellenirken hata oluştu!';
    }
}

$stmt = $db->prepare("SELECT * FROM trips WHERE company_id = ? ORDER BY departure_date DESC, departure_time DESC");
$stmt->execute([$companyId]);
$trips = $stmt->fetchAll(PDO::FETCH_ASSOC);


$editTrip = null;
if (isset($_GET['edit_trip'])) {
    $stmt = $db->prepare("SELECT * FROM trips WHERE id = ? AND company_id = ?");
    $stmt->execute([$_GET['edit_trip'], $companyId]);
    $editTrip = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firma Admin Paneli</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, #e0e7ff, #f5f7ff); color: #333; }

        .navbar {
            background: linear-gradient(90deg, #667eea, #764ba2);
            color: white; padding: 15px 30px;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .navbar a {
            color: white; text-decoration: none; margin-left: 20px;
            font-weight: 500; transition: all 0.3s;
        }
        .navbar a:hover { text-shadow: 0 0 5px #fff; }

        .container { max-width: 1400px; margin: 40px auto; padding: 0 20px; }
        .message { background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .section {
            background: white; padding: 30px; border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin-bottom: 30px;
            transition: transform 0.2s;
        }
        .section:hover { transform: scale(1.01); }

        h2 { color: #4a4a4a; margin-bottom: 20px; }

        .form-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px; margin-bottom: 20px;
        }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        .form-group input {
            width: 100%; padding: 10px;
            border: 1px solid #ccc; border-radius: 8px;
            transition: 0.3s;
        }
        .form-group input:focus {
            border-color: #667eea;
            box-shadow: 0 0 5px rgba(102,126,234,0.5);
            outline: none;
        }

        button {
            padding: 12px 25px; border: none; border-radius: 8px;
            cursor: pointer; font-weight: bold; font-size: 14px;
            color: white; background: linear-gradient(90deg, #667eea, #764ba2);
            transition: all 0.3s ease; box-shadow: 0 3px 10px rgba(0,0,0,0.15);
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(118,75,162,0.3);
        }
        .btn-success {
            background: linear-gradient(90deg, #28a745, #56d364);
        }
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40,167,69,0.3);
        }

        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table th, table td {
            padding: 12px; text-align: left; border-bottom: 1px solid #eee;
        }
        table th {
            background: #eef2ff; font-weight: bold; color: #444;
        }

        .btn-edit, .btn-delete {
            padding: 8px 15px; border-radius: 6px; text-decoration: none;
            font-weight: bold; font-size: 12px; transition: all 0.3s;
        }
        .btn-edit { background: #ffc107; color: #333; }
        .btn-edit:hover { background: #ffca2c; transform: scale(1.05); }
        .btn-delete { background: #dc3545; color: white; }
        .btn-delete:hover { background: #c82333; transform: scale(1.05); }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>🚌 Firma Admin Paneli - <?= htmlspecialchars($companyName) ?></h1>
        <div>
            <a href="index.php">Ana Sayfa</a>
            <a href="logout.php">Çıkış</a>
        </div>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="section">
            <h2><?= $editTrip ? '🛠 Sefer Güncelle' : '🆕 Yeni Sefer Ekle' ?></h2>
            <form method="POST">
                <?php if ($editTrip): ?>
                    <input type="hidden" name="trip_id" value="<?= $editTrip['id'] ?>">
                <?php endif; ?>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Kalkış</label>
                        <input type="text" name="departure" value="<?= $editTrip['departure'] ?? '' ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Varış</label>
                        <input type="text" name="arrival" value="<?= $editTrip['arrival'] ?? '' ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Tarih</label>
                        <input type="date" name="departure_date" value="<?= $editTrip['departure_date'] ?? '' ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Saat</label>
                        <input type="time" name="departure_time" value="<?= $editTrip['departure_time'] ?? '' ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Fiyat (₺)</label>
                        <input type="number" name="price" step="0.01" min="0" value="<?= $editTrip['price'] ?? '' ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Toplam Koltuk</label>
                        <input type="number" name="total_seats" min="1" value="<?= $editTrip['total_seats'] ?? '' ?>" required>
                    </div>
                    <div class="form-group" style="display: flex; align-items: flex-end; gap: 10px;">
                        <?php if ($editTrip): ?>
                            <button type="submit" name="update_trip" class="btn-success">Güncelle</button>
                            <a href="firma_admin_panel.php"><button type="button">İptal</button></a>
                        <?php else: ?>
                            <button type="submit" name="add_trip">Sefer Ekle</button>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>

        <div class="section">
            <h2>🧾 Sefer Listesi (<?= count($trips) ?>)</h2>
            <?php if (empty($trips)): ?>
                <p style="color: #999; text-align: center; padding: 40px;">Henüz sefer eklenmemiş.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Kalkış</th>
                            <th>Varış</th>
                            <th>Tarih</th>
                            <th>Saat</th>
                            <th>Fiyat</th>
                            <th>Koltuk</th>
                            <th>İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($trips as $trip): ?>
                            <tr>
                                <td><?= $trip['id'] ?></td>
                                <td><?= htmlspecialchars($trip['departure']) ?></td>
                                <td><?= htmlspecialchars($trip['arrival']) ?></td>
                                <td><?= date('d.m.Y', strtotime($trip['departure_date'])) ?></td>
                                <td><?= substr($trip['departure_time'], 0, 5) ?></td>
                                <td><?= number_format($trip['price'], 2) ?> ₺</td>
                                <td><?= $trip['available_seats'] ?> / <?= $trip['total_seats'] ?></td>
                                <td>
                                    <a href="?edit_trip=<?= $trip['id'] ?>" class="btn-edit">✏ Düzenle</a>
                                    <a href="?delete_trip=<?= $trip['id'] ?>" class="btn-delete" 
                                       onclick="return confirm('Bu seferi silmek istediğinizden emin misiniz?')">🗑 Sil</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
