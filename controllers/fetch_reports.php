<?php
require_once '../config/Database.php';

$db = new Database();
$conn = $db->connect();

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}




$user = $_SESSION['user']; // Assuming session stores user data
$user_role = $user['role'];
$user_outlet_id = $user['user_id'] ?? null; // Only available if the user is a manager





if ($user_role == 'manager') {

    // Step 1: Get the manager's assigned outlet_id
    $stmt = $conn->prepare("SELECT o.id AS outlet_id, gs.stock 
              FROM gas_stock gs JOIN outlets o ON gs.outlet_id = o.id  
              WHERE o.manager_id = :manager_id");
    $stmt->bindParam(':manager_id', $user_outlet_id, PDO::PARAM_INT);
    $stmt->execute();
    $outlet = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($outlet) {
        $assigned_outlet_id = $outlet['outlet_id'];
    }
}








$reportType = $_GET['report'] ?? '';

// Outlet Sales Report
if ($reportType == "outlet_sales") {
    $query = "SELECT o.name AS outlet_name, gr.gas_type, SUM(gr.qty) AS total_qty, SUM(gp.selling_price) AS total_sales 
              FROM gas_requests gr 
              JOIN outlets o ON gr.outlet_id = o.id 
              JOIN gas_prices gp ON gr.gas_type = gp.gas_type
              WHERE gr.status = 'completed'";

    // Restrict to manager's outlet if not admin
    if ($user_role != 'admin') {
        $query .= " AND gr.outlet_id = :outlet_id";
    }
    $query .= " GROUP BY gr.outlet_id, gr.gas_type";

    $stmt = $conn->prepare($query);
    if ($user_role != 'admin') {
        $stmt->bindParam(':outlet_id', $assigned_outlet_id, PDO::PARAM_INT);
    }
    $stmt->execute();
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}

// Customer Sales Report
if ($reportType == "customer_sales") {
    $query = "SELECT u.name AS customer_name, gr.gas_type, SUM(gr.qty) AS total_qty, SUM(gp.selling_price) AS total_spent 
              FROM gas_requests gr 
              JOIN users u ON gr.user_id = u.user_id 
             JOIN gas_prices gp ON gr.gas_type = gp.gas_type
              WHERE gr.status = 'completed'";

    // Restrict managers to their outlet sales only
    if ($user_role != 'admin') {
        $query .= " AND gr.outlet_id = :outlet_id";
    }
    $query .= " GROUP BY gr.user_id, gr.gas_type";

    $stmt = $conn->prepare($query);
    if ($user_role != 'admin') {
        $stmt->bindParam(':outlet_id', $assigned_outlet_id, PDO::PARAM_INT);
    }
    $stmt->execute();
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}

// Profit Report
if ($reportType == "profit") {
    $query = "SELECT gr.outlet_id, gr.gas_type, 
                     SUM(gp.selling_price) AS total_revenue, 
                     COUNT(gp.selling_price) AS total_QTY, 
                     SUM(gp.cost_price * gr.qty) AS total_cost, 
                     (SUM(gp.selling_price) - SUM(gp.cost_price * gr.qty)) AS profit 
                     FROM gas_requests gr 
                     JOIN gas_prices gp ON gr.gas_type = gp.gas_type";

    if ($user_role != 'admin') {
        $query .= " JOIN outlets o ON gr.outlet_id = o.id  WHERE gr.status = 'completed' AND gr.outlet_id = :outlet_id  GROUP BY  gr.gas_type ;  ";
    }
    $query .= "  WHERE gr.status = 'completed' GROUP BY gr.outlet_id, gr.gas_type ;";

    $stmt = $conn->prepare($query);
    if ($user_role != 'admin') {
        $stmt->bindParam(':outlet_id', $assigned_outlet_id, PDO::PARAM_INT);
    }
    $stmt->execute();
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}

// All Sales Report
if ($reportType == "all_sales") {
  

    if ($user_role != 'admin') {
        $query = "SELECT  o.name AS outlet_name , gr.gas_type, gp.selling_price  AS selling_price , gp.cost_price  AS cost_price,
        SUM(gr.qty) AS total_QTY,
        SUM(gp.selling_price) AS total_revenue,
        SUM(gp.cost_price * gr.qty) AS total_cost,
        (SUM(gp.selling_price) - SUM(gp.cost_price * gr.qty)) AS profit  
        FROM gas_requests gr 
        JOIN outlets o ON gr.outlet_id = o.id 
        JOIN users u ON gr.user_id = u.user_id 
        JOIN gas_prices gp ON gr.gas_type = gp.gas_type
        WHERE gr.status = 'completed'";
        $query .= " AND gr.outlet_id = :outlet_id";
        $query .= " GROUP BY  gr.gas_type,gr.outlet_id";


   
        //  (SUM(gp.selling_price) - SUM(gp.cost_price * SUM(gr.qty))) AS profit  








    }else{
        $query = "SELECT  o.name AS outlet_name ,gp.selling_price ,gp.cost_price  AS cost_price,
        SUM(gr.qty) AS total_QTY,
        SUM(gp.selling_price) AS total_revenue,
        SUM(gp.cost_price * gr.qty) AS total_cost,
        (SUM(gp.selling_price) - SUM(gp.cost_price * gr.qty)) AS profit  
        FROM gas_requests gr 
        JOIN outlets o ON gr.outlet_id = o.id 
        JOIN users u ON gr.user_id = u.user_id 
        JOIN gas_prices gp ON gr.gas_type = gp.gas_type
        WHERE gr.status = 'completed'";
        $query .= " GROUP BY gr.outlet_id";
    }
   

    $stmt = $conn->prepare($query);
    if ($user_role != 'admin') {
        $stmt->bindParam(':outlet_id', $assigned_outlet_id, PDO::PARAM_INT);
    }
    $stmt->execute();
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}


if ($reportType == "user_reports") {
    if ($user_role == 'admin') {
        // Admin: Load all users with outlet names
        $stmt = $conn->prepare("SELECT u.user_id, u.name, u.email, u.role, u.contact_number, u.registered_date, 
                                       o.name AS outlet_name 
                                FROM users u 
                                LEFT JOIN outlets o ON u.user_id = o.manager_id");
    } elseif ($user_role == 'manager') {

   

        if ($assigned_outlet_id) {
            $assigned_outlet_id =$assigned_outlet_id;

            // Step 2: Get gas requests filtered by the assigned outlet
            $stmt = $conn->prepare("SELECT u.user_id, u.name, u.email, u.role, u.contact_number, u.registered_date,
                                   g.gas_type, SUM(g.qty) AS total_qty 
                            FROM users u
                            JOIN gas_requests g ON u.user_id = g.user_id
                            WHERE  g.outlet_id = :outlet_id
                            GROUP BY u.user_id, g.gas_type");

            $stmt->bindParam(':outlet_id', $assigned_outlet_id, PDO::PARAM_INT);
            $stmt->execute();

     
        } else {
            echo json_encode(["error" => "Outlet not found for this user"]);
        }
    }
    $stmt->execute();
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}






// Stock Reports
if ($reportType == "stock_report") {


    $query = "SELECT o.name AS outlet_name, gs.stock 
              FROM gas_stock gs 
              JOIN outlets o ON gs.outlet_id = o.id";

    if ($user_role != 'admin') {
        $query .= " WHERE o.manager_id = :manager_id";
    }

    $stmt = $conn->prepare($query);
    if ($user_role != 'admin') {
        $stmt->bindParam(':manager_id', $user_outlet_id, PDO::PARAM_INT);
    }
    $stmt->execute();
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}
