<?php
$title = "Dashboard | Lpgas ";
include_once '../../includes/header.php';

// if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'manager') {
//     header("Location: ../../public/login.php");
//     exit();
// }

$user = $_SESSION['user'];
$manager_id = $user['user_id'];

?>



<style>
    body {
        background-color: rgb(225, 226, 228);
        font-family: 'Roboto', sans-serif;
    }

    .card {
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .btn-primary {
        background-color: #007bff;
        border: none;
    }

    .btn-primary:hover {
        background-color: #0056b3;
    }

    .gas-card {
        transition: transform 0.3s, box-shadow 0.3s;
        border-radius: 15px;
        overflow: hidden;
        background: white;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .gas-card:hover {
        transform: scale(1.05);
        box-shadow: 0 8px 12px rgba(0, 0, 0, 0.2);
    }

    .order-btn {
        background-color: #28a745;
        border: none;
        font-size: 16px;
        font-weight: bold;
        transition: background-color 0.3s ease;
    }

    .order-btn:hover {
        background-color: #218838;
        color: #fff;
    }

    @media (max-width: 768px) {
        .gas-card {
            margin-bottom: 20px;
        }
    }
</style>

<header>
    <?php
    $page = basename($_SERVER['PHP_SELF'], ".php");
    include_once '../../includes/navbar.php'; ?>
</header>

<main>


    <div class="container mt-4">
        <h2 class="text-center">Reports</h2>
        <select id="reportType" class="form-select mb-3" onchange="fetchReport(this.value)">
            <option>Select</option>
            <option value="all_sales">All Sales Report</option>
            <option value="customer_sales">Customer Sales Report</option>

            <option value="outlet_sales">Outlet Sales Report</option>
            <option value="profit">Profit Report</option>
            <?php

            if ($user['role']== 'admin') {
            ?>
                <option value="user_reports">User Reports</option>
            <?php
            }
            ?>
            <option value="stock_report">Stock Report</option>
        </select>



        <section id="all-orders" class="mt-5" style="background-color:#fff; padding: 10px; border-radius:12px;">
            <table id="reportTable" class="table table-striped">
                <thead>
                    <tr id="tableHeaders"></tr>
                </thead>
                <tbody></tbody>
            </table>

        </section>
    </div>

</main>
<br>
<br><br>






<script>
    function fetchReport(reportType) {
        $.ajax({
            url: '../../controllers/fetch_reports.php',
            type: 'GET',
            data: {
                report: reportType
            },
            dataType: 'json',
            success: function(data) {
                let tableHeaders = $("#tableHeaders");
                let tableBody = $("#reportTable tbody");

                // **Destroy existing DataTable if initialized**
                if ($.fn.DataTable.isDataTable("#reportTable")) {
                    $("#reportTable").DataTable().clear().destroy();
                }

                // **Clear old table data**
                tableHeaders.empty();
                tableBody.empty();

                if (data.length > 0) {
                    let headers = Object.keys(data[0]);

                    // **Check if 'stock' column exists and parse JSON**
                    let stockKeys = [];
                    if (headers.includes('stock')) {
                        data.forEach(row => {
                            let stockData = row.stock ? JSON.parse(row.stock) : {};
                            Object.keys(stockData).forEach(key => {
                                if (!stockKeys.includes(key)) {
                                    stockKeys.push(key);
                                }
                            });
                        });

                        // Remove 'stock' from headers and add individual stock types
                        headers = headers.filter(key => key !== 'stock');
                        headers.push(...stockKeys);
                    }

                    // **Generate table headers dynamically**
                    headers.forEach(key => {
                        tableHeaders.append(`<th>${key.replace("_", " ").toUpperCase()}</th>`);
                    });

                    // **Populate table body**
                    data.forEach(row => {
                        let rowHTML = "<tr>";
                        headers.forEach(key => {
                            if (stockKeys.includes(key)) {
                                // Display stock value
                                let stockData = row.stock ? JSON.parse(row.stock) : {};
                                let cellData = stockData[key] !== undefined ? stockData[key] : '0';
                                rowHTML += `<td>${cellData}</td>`;
                            } else {
                                let cellData = row[key] === null || row[key] === '' ? 'N/A' : row[key];
                                rowHTML += `<td>${cellData}</td>`;
                            }
                        });
                        rowHTML += "</tr>";
                        tableBody.append(rowHTML);
                    });

                    // **Reinitialize DataTable**
                    $("#reportTable").DataTable({
                        destroy: true,
                        dom: 'Bfrtip',
                        buttons: [{
                                extend: 'csvHtml5',
                                text: 'Export CSV',
                                className: 'btn btn-primary'
                            },
                            {
                                extend: 'excelHtml5',
                                text: 'Export Excel',
                                className: 'btn btn-success'
                            },
                            {
                                extend: 'pdfHtml5',
                                text: 'Export PDF',
                                className: 'btn btn-danger'
                            },
                            {
                                extend: 'print',
                                text: 'Print',
                                className: 'btn btn-info'
                            }
                        ],
                        responsive: true
                    });
                } else {
                    tableHeaders.append(`<th>No Data Available</th>`);
                    tableBody.append("<tr><td colspan='100%'>No records found.</td></tr>");
                }
            },
            error: function(xhr, status, error) {
                console.error("Error fetching report:", error);
            }
        });
    }
</script>

<?php include_once '../../includes/footer.php'; ?>





<?php include_once '../../includes/end.php'; ?>

<script>
    $(document).ready(function() {
        let dataTable;



        // Handle report selection change
        // $("#reportType").change(function() {
        //     let selectedReport = $(this).val();
        //     fetchReport(selectedReport);  // Fetch new report based on selection
        // });

        // Load default report on page load
        //fetchReport("all_sales");
    });
</script>