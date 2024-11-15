<?php
session_start();
include('../config.php');

// รับค่าช่วงวันที่จาก URL
$start_date = $_GET['start_date'];
$end_date = $_GET['end_date'];

try {
    // ดึงข้อมูลโดยใช้ DATE() เพื่อดึงเฉพาะวันที่ ไม่สนใจเวลา
    $stmt = $con->prepare("
        SELECT po_number, DATE(date) as date, working_code, item_code, format_item_code, 
               quantity, price, remarks, packing_size, total_value
        FROM po 
        WHERE DATE(date) BETWEEN :start_date AND :end_date AND status = 'อนุมัติ'
        ORDER BY DATE(date) DESC
    ");
    $stmt->bindParam(':start_date', $start_date);
    $stmt->bindParam(':end_date', $end_date);
    $stmt->execute();
    $date_range_records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // คำนวณจำนวนรายการทั้งหมดและมูลค่ารวม
    $total_items = count($date_range_records);
    $grand_total = array_sum(array_column($date_range_records, 'total_value'));
} catch (PDOException $e) {
    echo '<p class="text-red-500">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>รายงานตามช่วงวันที่</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body>
<div class="container mx-auto mt-8 px-4">
    <h2 class="text-2xl font-bold mb-6 text-center text-gray-700">รายงานตามช่วงวันที่: <?php echo htmlspecialchars($start_date); ?> ถึง <?php echo htmlspecialchars($end_date); ?></h2>
    
    <?php if ($date_range_records): ?>
        <table class="min-w-full bg-white border border-gray-300 text-sm">
            <thead>
                <tr class="bg-gray-200 text-gray-700 text-left text-sm">
                    <th class="py-2 px-4 border-b">เลขที่ใบเบิก</th>
                    <th class="py-2 px-4 border-b">วันที่</th>
                    <th class="py-2 px-4 border-b">รหัสงาน</th>
                    <th class="py-2 px-4 border-b">รหัสสินค้า</th>
                    <th class="py-2 px-4 border-b">รูปแบบสินค้า</th>
                    <th class="py-2 px-4 border-b">จำนวน</th>
                    <th class="py-2 px-4 border-b">ราคา</th>
                    <th class="py-2 px-4 border-b">หมายเหตุ</th>
                    <th class="py-2 px-4 border-b">ขนาดบรรจุ</th>
                    <th class="py-2 px-4 border-b">มูลค่ารวม</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                <?php foreach ($date_range_records as $record): ?>
                    <tr class="hover:bg-gray-100">
                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($record['po_number']); ?></td>
                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($record['date']); ?></td>
                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($record['working_code']); ?></td>
                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($record['item_code']); ?></td>
                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($record['format_item_code']); ?></td>
                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($record['quantity']); ?></td>
                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($record['price']); ?></td>
                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($record['remarks']); ?></td>
                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($record['packing_size']); ?></td>
                        <td class="py-2 px-4 border-b"><?php echo number_format($record['total_value'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="bg-gray-200">
                    <td colspan="8" class="py-2 px-4 border-t text-left text-gray-700 font-semibold">จำนวนรายการทั้งหมด</td>
                    <td colspan="2" class="py-2 px-4 border-t text-right text-gray-700 font-semibold"><?php echo $total_items; ?></td>
                </tr>
                <tr class="bg-gray-200">
                    <td colspan="8" class="py-2 px-4 border-t text-left text-gray-700 font-semibold">มูลค่ารวมทั้งหมด</td>
                    <td colspan="2" class="py-2 px-4 border-t text-right text-gray-700 font-semibold"><?php echo number_format($grand_total, 2); ?> บาท</td>
                </tr>
            </tfoot>
        </table>
    <?php else: ?>
        <p class="text-gray-500 mt-4 text-center">ไม่พบข้อมูลในช่วงวันที่ที่กำหนด</p>
    <?php endif; ?>
</div>
</body>
</html>
