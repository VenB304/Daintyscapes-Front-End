<?php
// management.php
$sellers = [
    ['name' => 'Seller1', 'date' => '2025-04-28'],
    ['name' => 'Seller2', 'date' => '2025-04-29'],
    ['name' => 'Seller3', 'date' => '2025-04-30'],
    ['name' => 'Seller4', 'date' => '2025-05-01'],
    ['name' => 'Seller5', 'date' => '2025-05-02'],
    ['name' => 'Seller6', 'date' => '2025-05-03'],
    ['name' => 'Seller7', 'date' => '2025-05-04'],
    ['name' => 'Seller8', 'date' => '2025-05-05'],
    ['name' => 'Seller9', 'date' => '2025-05-06'],
    ['name' => 'Seller10', 'date' => '2025-05-07'],
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Seller Management</title>
    <link rel="stylesheet" href="../../styles.css">
    <script>
    let sellers = <?php echo json_encode($sellers); ?>;

    function renderTable(filtered = null) {
        const tableBody = document.getElementById("seller-body");
        tableBody.innerHTML = "";
        const data = filtered || sellers;
        data.forEach((seller, index) => {
            const row = document.createElement("tr");
            row.className = "seller-row";
            row.innerHTML = `
                <td>${seller.name}</td>
                <td>${seller.date}</td>
                <td class="actions">
                    <button onclick="openModifyPopup(${index})">Modify</button>
                    <button onclick="removeSeller(${index})">Remove</button>
                </td>
            `;
            tableBody.appendChild(row);
        });
    }

    function removeSeller(index) {
        sellers.splice(index, 1);
        renderTable();
    }

    function searchSeller() {
        const query = document.getElementById("search").value.toLowerCase();
        const filtered = sellers.filter(s => s.name.toLowerCase().includes(query));
        renderTable(filtered);
    }

    function openModifyPopup(index) {
        const popup = document.getElementById("popup");
        document.getElementById("popupTitle").textContent = "Modify Seller";
        document.getElementById("sellerIndex").value = index;
        document.getElementById("editName").value = sellers[index].name;
        document.getElementById("editUsername").value = "";
        document.getElementById("editPassword").value = "";
        document.getElementById("popup").style.display = "block";
    }

    function openAddPopup() {
        const popup = document.getElementById("popup");
        document.getElementById("popupTitle").textContent = "Add Seller";
        document.getElementById("sellerIndex").value = ""; // No index
        document.getElementById("editName").value = "";
        document.getElementById("editUsername").value = "";
        document.getElementById("editPassword").value = "";
        popup.style.display = "block";
    }

    function closePopup() {
        document.getElementById("popup").style.display = "none";
    }

    function saveChanges() {
        const index = document.getElementById("sellerIndex").value;
        const name = document.getElementById("editName").value;
        const username = document.getElementById("editUsername").value;
        const password = document.getElementById("editPassword").value;

        if (!name || !username || !password) {
            alert("Please fill in all fields.");
            return;
        }

        if (index === "") {
            // Add mode
            const today = new Date().toISOString().split("T")[0];
            sellers.push({ name: name, date: today });
        } else {
            // Modify mode
            sellers[index].name = name; // For demo, only name updates
        }

        closePopup();
        renderTable();
    }

    window.onload = function () {
        renderTable();
        document.getElementById("search").addEventListener("input", searchSeller);
    };
    </script>
</head>
<body class="page-container">
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/daintyscapes/includes/header.php'); ?>

    <div class="management-container">
        <h1>Welcome Daintyscapes admin</h1>

        <div class="management-header">
            <input type="text" id="search" placeholder="Search seller...">
            <button onclick="openAddPopup()">Add Seller</button>
        </div>

        <table class="seller-table">
            <thead>
                <tr>
                    <th>Seller Name</th>
                    <th>Registration Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="seller-body"></tbody>
        </table>
    </div>

    <!-- Add/Modify Popup -->
    <div class="popup" id="popup">
        <h3 id="popupTitle">Modify Seller</h3>
        <input type="hidden" id="sellerIndex">
        <label>Seller Name:</label>
        <input type="text" id="editName" placeholder="Enter seller name">
        <label>Username:</label>
        <input type="text" id="editUsername" placeholder="Enter username (demo only)">
        <label>Password:</label>
        <input type="password" id="editPassword" placeholder="Enter password (demo only)">
        <div style="margin-top: 10px;">
            <button type="submit" onclick="saveChanges()">Save</button>
            <button type="button" onclick="closePopup()">Cancel</button>
        </div>
    </div>
</body>
</html>
