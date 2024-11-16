<?php
session_start();

// ตรวจสอบสิทธิ์ (สำหรับ admin.page-report.php)
if (basename($_SERVER['PHP_SELF']) === 'admin.page-report.php' && $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

include('../config.php');

// ตรวจสอบว่าผู้ใช้ล็อกอินอยู่หรือไม่
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// ตรวจสอบ session hospital_name
if (!isset($_SESSION['hospital_name'])) {
    $stmt = $con->prepare("SELECT hospital_name FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['hospital_name'] = $user['hospital_name'];
    } else {
        echo '<p class="text-center text-red-500">ไม่พบข้อมูลหน่วยเบิกในเซสชัน</p>';
        exit;
    }
}

$hospital_name = $_SESSION['hospital_name'];
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body>

        <!-- รวม Navbar -->
    <?php include('navbar.php'); ?>

<div class="container mx-auto mt-8 flex space-x-6">
    <!-- คอนเทนเนอร์ด้านซ้าย (แดชบอร์ดเดิม) -->
    <div class="w-1/2 bg-gray-50 shadow-lg rounded-lg p-6">
        <h2 class="text-2xl font-bold mb-6 text-center text-gray-700">แดชบอร์ดใบเบิกของหน่วยงาน</h2>
        <div class="bg-white shadow-md rounded-lg p-6 overflow-y-auto max-h-96">
            <?php if (!empty($withdrawRecords)): ?>
                <table class="min-w-full bg-white border border-gray-300">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="py-3 px-6 border-b text-left text-sm text-gray-600">เลขที่ใบเบิก</th>
                            <th class="py-3 px-6 border-b text-left text-sm text-gray-600">วันที่</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($withdrawRecords as $record): ?>
                            <tr class="hover:bg-gray-100 cursor-pointer" onclick="openModal('<?php echo htmlspecialchars($record['po_number']); ?>')">
                                <td class="py-3 px-6 border-b text-sm"><?php echo htmlspecialchars($record['po_number']); ?></td>
                                <td class="py-3 px-6 border-b text-sm"><?php echo htmlspecialchars($record['date']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-gray-500 mt-4 text-center">ไม่พบข้อมูลใบเบิก</p>
            <?php endif; ?>
        </div>
    </div>


    <!-- คอนเทนเนอร์ด้านขวา (ตัวเลือกการรายงาน) -->
    <div class="w-1/2 bg-gray-50 shadow-lg rounded-lg p-6 space-y-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-4 text-center">เลือกประเภทการรายงาน</h3>

        <form action="../report/generate_report.php" method="GET" class="space-y-4">
            <!-- รายงานจัดซื้อทั้งหมด -->
            <div class="flex items-center space-x-2">
                <input type="radio" name="reportType" value="allPurchases" id="allPurchases" class="form-radio h-5 w-5 text-indigo-600" required>
                <label for="allPurchases" class="text-sm text-gray-700">รายงานจัดซื้อทั้งหมด</label>
            </div>


    <!-- รายงานตามเลขที่ใบเบิก -->
<div class="space-y-2">
    <div class="flex items-center space-x-2">
        <input type="radio" name="reportType" value="poNumber" id="poNumberReport" class="form-radio h-5 w-5 text-indigo-600">
        <label for="poNumberReport" class="text-sm text-gray-700">รายงานตามเลขที่ใบเบิก</label>
    </div>
    <input type="text" name="poNumber" id="poNumberInput" class="form-input block w-full mt-1 p-2 border border-gray-300 rounded text-sm" placeholder="กรอกเลขที่ใบเบิก" disabled>
</div>

    <!-- รายงานที่ยกเลิกแล้ว -->
    <div class="flex items-center space-x-2">
        <input type="radio" name="reportType" value="cancelledReports" id="cancelledReports" class="form-radio h-5 w-5 text-indigo-600">
        <label for="cancelledReports" class="text-sm text-gray-700">รายงานที่ยกเลิกแล้ว</label>
    </div>

    <!-- รายงานตามช่วงวันที่ -->
    <div class="space-y-2">
        <div class="flex items-center space-x-2">
            <input type="radio" name="reportType" value="dateRange" id="dateRangeReport" class="form-radio h-5 w-5 text-indigo-600">
            <label for="dateRangeReport" class="text-sm text-gray-700">รายงานตามช่วงวันที่</label>
        </div>
        <div class="flex space-x-2">
            <input type="date" name="startDate" id="startDate" class="form-input p-2 border border-gray-300 rounded w-1/2 text-sm">
            <input type="date" name="endDate" id="endDate" class="form-input p-2 border border-gray-300 rounded w-1/2 text-sm">
        </div>
    </div>
                
                
                <!-- รายงานตามชื่อยา -->
            <div class="space-y-2">
                <div class="flex items-center space-x-2">
                    <input type="checkbox" class="form-checkbox h-5 w-5 text-indigo-600" id="medicineNameReport" onclick="toggleInputs()">
                    <label for="medicineNameReport" class="text-sm text-gray-700">รายงานตามชื่อยา</label>
                </div>
                <div class="relative">
                    <input type="text" class="form-input p-2 border border-gray-300 rounded w-full text-sm" id="medicineNameInput" placeholder="กรอกชื่อยา" oninput="searchMedicine()">
                <div id="medicineDropdown" class="absolute bg-white border border-gray-300 rounded shadow-lg mt-1 max-h-40 w-full overflow-y-auto hidden">
                        <!-- รายการดรอปดาวน์ของชื่อยาจะถูกสร้างที่นี่ -->
                    </div>
                </div>
            </div>

            <!-- ปุ่ม Generate Report -->
            <button type="submit" class="w-full bg-indigo-500 text-white font-semibold py-2 px-4 rounded hover:bg-indigo-600">
                Generate Report
            </button>
        </form>



<div id="modal" class="fixed inset-0 hidden bg-gray-800 bg-opacity-50 flex items-center justify-center z-50 box-border">
    <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-3xl">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold">รายละเอียดใบเบิก</h3>
            <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">&times;</button>
        </div>
        <div id="modalContent" class="overflow-y-auto" style="max-height: 400px;">
        </div>
    </div>
</div>

<script>
    function openModal(poNumber) {
        document.getElementById('modal').classList.remove('hidden');
        fetch('get_po_details.php?po_number=' + encodeURIComponent(poNumber))
            .then(response => response.text())
            .then(data => {
                document.getElementById('modalContent').innerHTML = data;
            })
            .catch(error => {
                document.getElementById('modalContent').innerHTML = '<p class="text-red-500">เกิดข้อผิดพลาดในการดึงข้อมูล</p>';
            });
    }

    function closeModal() {
        document.getElementById('modal').classList.add('hidden');
    }
</script>

<!-- JavaScript -->
<script>
    // เปิดใช้งานฟิลด์วันที่เมื่อเลือกช่วงวันที่
    document.getElementById("dateRangeReport").addEventListener("change", function() {
        const isEnabled = this.checked;
        document.getElementById("startDate").disabled = !isEnabled;
        document.getElementById("endDate").disabled = !isEnabled;
    });

    // ตรวจสอบให้แน่ใจว่าเลือกเช็คบ็อกซ์ใดเช็คบ็อกซ์หนึ่ง
    function generateReport() {
        const allPurchases = document.getElementById("allPurchases").checked;
        const poNumberReport = document.getElementById("poNumberReport").checked;
        const cancelledReports = document.getElementById("cancelledReports").checked;
        const dateRangeReport = document.getElementById("dateRangeReport").checked;
        const medicineNameReport = document.getElementById("medicineNameReport").checked;

        const poNumber = document.getElementById("poNumberInput").value.trim();
        const startDate = document.getElementById("startDate").value;
        const endDate = document.getElementById("endDate").value;

        // ตรวจสอบว่าเลือกเช็คบ็อกซ์ใดเช็คบ็อกซ์หนึ่ง
        if (allPurchases) {
            window.location.href = 'report/all_purchases_report.php';
        } else if (poNumberReport && poNumber) {
            window.location.href = 'report/po_number_report.php?po_number=' + encodeURIComponent(poNumber);
        } else if (cancelledReports) {
            window.location.href = 'report/cancelled_report.php';
        } else if (dateRangeReport && startDate && endDate) {
            window.location.href = 'report/date_range_report.php?start_date=' + encodeURIComponent(startDate) + '&end_date=' + encodeURIComponent(endDate);
        } else if (medicineNameReport && selectedWorkingCode) {
            window.location.href = `report/medicine_report.php?working_code=${encodeURIComponent(selectedWorkingCode)}`;
        } else {
            alert("กรุณาเลือกตัวเลือกและกรอกข้อมูลที่จำเป็นก่อนกด Generate Report");
        }
    }

    // ฟังก์ชันค้นหารายการยาในดรอปดาวน์
    let selectedWorkingCode = null;
    function searchMedicine() {
        const query = document.getElementById("medicineNameInput").value;
        if (query.length > 0) {
            fetch(`report/search_drug_list.php?query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    const dropdown = document.getElementById("medicineDropdown");
                    dropdown.innerHTML = "";
                    
                    if (Array.isArray(data) && data.length > 0) {
                        dropdown.classList.remove("hidden");
                        data.forEach(item => {
                            const option = document.createElement("div");
                            option.classList.add("p-2", "hover:bg-gray-200", "cursor-pointer");
                            option.textContent = item.name_item_code;
                            option.onclick = () => selectMedicine(item.working_code, item.name_item_code);
                            dropdown.appendChild(option);
                        });
                    } else {
                        dropdown.classList.add("hidden");
                    }
                })
                .catch(error => {
                    console.error("Error fetching medicine list:", error);
                });
        } else {
            document.getElementById("medicineDropdown").classList.add("hidden");
        }
    }

    // ฟังก์ชันเลือกยาและเก็บค่า working_code
function selectMedicine(workingCode, name) {
    if (workingCode && name) {
        selectedWorkingCode = workingCode;
        document.getElementById("medicineNameInput").value = name; // แสดงชื่อยาใน input
        document.getElementById("medicineDropdown").classList.add("hidden");
    } else {
        console.error("Invalid medicine selection.");
    }
}

// ฟังก์ชันจัดการการเปิด/ปิดการใช้งาน input
function toggleInputs() {
    const poNumberReport = document.getElementById("poNumberReport").checked;
    const dateRangeReport = document.getElementById("dateRangeReport").checked;
    const medicineNameReport = document.getElementById("medicineNameReport")?.checked;

    // เปิดหรือปิดการใช้งานฟิลด์
    document.getElementById("poNumberInput").disabled = !poNumberReport;
    document.getElementById("startDate").disabled = !dateRangeReport;
    document.getElementById("endDate").disabled = !dateRangeReport;

    // ล้างค่าในฟิลด์ที่ปิดการใช้งาน
    if (!poNumberReport) {
        document.getElementById("poNumberInput").value = "";
    }
    if (!dateRangeReport) {
        document.getElementById("startDate").value = "";
        document.getElementById("endDate").value = "";
    }
    if (medicineNameReport === false && document.getElementById("medicineNameInput")) {
        document.getElementById("medicineNameInput").value = "";
        document.getElementById("medicineNameInput").disabled = true;
    } else if (document.getElementById("medicineNameInput")) {
        document.getElementById("medicineNameInput").disabled = false;
    }
}

// เพิ่ม Event Listener ให้ radio และ checkbox
document.querySelectorAll('input[type="radio"], input[type="checkbox"]').forEach(input => {
    input.addEventListener('change', toggleInputs);
});

// เรียกใช้งาน toggleInputs เมื่อโหลดหน้า
document.addEventListener("DOMContentLoaded", () => {
    toggleInputs();
});

<script>
    // ฟังก์ชันตรวจสอบฟอร์มก่อนส่ง
    function validateForm(event) {
        const poNumberReport = document.getElementById("poNumberReport").checked;
        const poNumberInput = document.getElementById("poNumberInput").value.trim();

        if (poNumberReport && !poNumberInput) {
            alert("กรุณาระบุเลขที่ใบเบิก");
            event.preventDefault();
            return false;
        }

        const dateRangeReport = document.getElementById("dateRangeReport").checked;
        const startDate = document.getElementById("startDate").value;
        const endDate = document.getElementById("endDate").value;

        if (dateRangeReport && (!startDate || !endDate)) {
            alert("กรุณาระบุวันที่เริ่มต้นและสิ้นสุด");
            event.preventDefault();
            return false;
        }

        const medicineNameReport = document.getElementById("medicineNameReport").checked;
        const medicineNameInput = document.getElementById("medicineNameInput").value.trim();

        if (medicineNameReport && !medicineNameInput) {
            alert("กรุณาระบุชื่อยา");
            event.preventDefault();
            return false;
        }

        const cancelledReports = document.getElementById("cancelledReports").checked;

        // ตรวจสอบว่ามีประเภทใดประเภทหนึ่งถูกเลือก
        if (!(poNumberReport || dateRangeReport || medicineNameReport || cancelledReports)) {
            alert("กรุณาเลือกประเภทรายงาน");
            event.preventDefault();
            return false;
        }

        return true; // ผ่านการตรวจสอบ
    }

    // เพิ่ม Event Listener ให้ฟอร์มเมื่อโหลด DOM
    document.addEventListener("DOMContentLoaded", () => {
        const form = document.querySelector("form");
        form.addEventListener("submit", validateForm);

        // ฟังก์ชันจัดการ dropdown menu
        const profileButton = document.getElementById("user-menu-button");
        const dropdownMenu = document.getElementById("dropdown-menu");

        profileButton.addEventListener("click", function (event) {
            event.preventDefault();
            dropdownMenu.classList.toggle("hidden");
        });

        document.addEventListener("click", function (event) {
            if (!profileButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
                dropdownMenu.classList.add("hidden");
            }
        });
    });
</script>
            
            
<!-- Script to toggle dropdown menu เมนู profile-->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const profileButton = document.getElementById("user-menu-button");
        const dropdownMenu = document.getElementById("dropdown-menu");

        // Toggle dropdown menu on profile button click
        profileButton.addEventListener("click", function (event) {
            event.preventDefault();
            dropdownMenu.classList.toggle("hidden");
        });

        // Close dropdown menu on outside click
        document.addEventListener("click", function (event) {
            if (!profileButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
                dropdownMenu.classList.add("hidden");
            }
        });
    });
</script>


<!-- Script to toggle dropdown menu เมนู profile-->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const profileButton = document.getElementById('user-menu-button');
        const dropdownMenu = document.getElementById('dropdown-menu');

        // Toggle dropdown menu on profile button click
        profileButton.addEventListener('click', function(event) {
            event.preventDefault();
            dropdownMenu.classList.toggle('hidden');
        });

        // Close dropdown menu on outside click
        document.addEventListener('click', function(event) {
            if (!profileButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
                dropdownMenu.classList.add('hidden');
            }
        });
    });
</script>
</body>
</html>
