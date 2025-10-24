<?php
require_once 'db_connection.php';
require_once 'auth_helper.php';


if (!file_exists(__DIR__ . '/fpdf.php')) {
    die("PDF oluşturmak için 'fpdf.php' bulunamadı. Lütfen proje köküne FPDF kütüphanesini ekleyin (fpdf.org).");
}
require_once __DIR__ . '/fpdf.php';

requireLogin();

$currentUser = getCurrentUser();
$userId = $currentUser['id'];
$userRole = $currentUser['role'];
$companyId = $currentUser['company_id'] ?? null;

$ticketId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($ticketId <= 0) {
    die('Geçersiz bilet id.');
}


$stmt = $db->prepare("
    SELECT t.id as ticket_id, t.trip_id, t.user_id, t.seat_number, t.price_paid, t.coupon_code, t.status, t.purchased_at,
           tr.departure, tr.arrival, tr.departure_date, tr.departure_time, tr.company_id, c.name as company_name, u.full_name as buyer_name
    FROM tickets t
    JOIN trips tr ON t.trip_id = tr.id
    JOIN companies c ON tr.company_id = c.id
    LEFT JOIN users u ON t.user_id = u.id
    WHERE t.id = :tid
");
$stmt->execute([':tid' => $ticketId]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
    die('Bilet bulunamadı.');
}


if ($userRole === 'user' && $ticket['user_id'] != $userId) {
    die('Bu bileti indirme yetkiniz yok.');
}
if ($userRole === 'firma_admin' && $ticket['company_id'] != $companyId) {
    die('Firma yetkiniz bu bileti indirmeye yetmiyor.');
}


$pdf = new FPDF('P','mm','A4');
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,12, 'OTOBÜS BİLETİ', 0,1,'C');
$pdf->Ln(4);

$pdf->SetFont('Arial','',12);
$pdf->Cell(45,8,'Bilet No:',0,0);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,8, $ticket['ticket_id'],0,1);

$pdf->SetFont('Arial','',12);
$pdf->Cell(45,8,'Yolcu:',0,0);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,8, $ticket['buyer_name'] ?? '-',0,1);

$pdf->SetFont('Arial','',12);
$pdf->Cell(45,8,'Firma:',0,0);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,8, $ticket['company_name'],0,1);

$pdf->SetFont('Arial','',12);
$pdf->Cell(45,8,'Güzergah:',0,0);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,8, $ticket['departure'] . ' → ' . $ticket['arrival'],0,1);

$pdf->SetFont('Arial','',12);
$pdf->Cell(45,8,'Tarih:',0,0);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,8, date('d.m.Y', strtotime($ticket['departure_date'])),0,1);

$pdf->SetFont('Arial','',12);
$pdf->Cell(45,8,'Saat:',0,0);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,8, substr($ticket['departure_time'],0,5),0,1);

$pdf->SetFont('Arial','',12);
$pdf->Cell(45,8,'Koltuk:',0,0);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,8, $ticket['seat_number'] ?? '-',0,1);

$pdf->SetFont('Arial','',12);
$pdf->Cell(45,8,'Ücret:',0,0);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,8, number_format($ticket['price_paid'],2) . ' ₺',0,1);

if (!empty($ticket['coupon_code'])) {
    $pdf->SetFont('Arial','',12);
    $pdf->Cell(45,8,'Kupon:',0,0);
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(0,8, $ticket['coupon_code'],0,1);
}

$pdf->Ln(10);
$pdf->SetFont('Arial','I',10);
$pdf->MultiCell(0,6, "Bu bilet elektronik olarak üretilmiştir. Lütfen yolculuk sırasında yanınızda gösterin.\nBilet durumu: " . ucfirst($ticket['status']) . "\nSatın alma tarihi: " . date('d.m.Y H:i', strtotime($ticket['purchased_at'] ?? 'now')));

$filename = 'bilet_' . $ticket['ticket_id'] . '.pdf';
$pdf->Output('I', $filename); // tarayıcıda göster / indir
exit;
