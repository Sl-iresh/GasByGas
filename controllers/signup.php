<?php


require_once '../models/Crud.php';




if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	$name = isset($_POST['name']) ? htmlspecialchars(trim($_POST['name'])) : '';
	$email = isset($_POST['email']) ? htmlspecialchars(trim($_POST['email'])) : '';
	$Address = isset($_POST['Address']) ? htmlspecialchars(trim($_POST['Address'])) : '';
	$contact_number = isset($_POST['contact_number']) ? htmlspecialchars(trim($_POST['contact_number'])) : '';
	$customer_type = isset($_POST['customer_type']) ? htmlspecialchars(trim($_POST['customer_type'])) : '';
	$nic_or_registration_number = isset($_POST['nic_or_registration_number']) ? htmlspecialchars(trim($_POST['nic_or_registration_number'])) : '';

	//$Password = isset($_POST['password']) ? htmlspecialchars(trim($_POST['password'])) : '';
    // Hash the password before storing it
    $Password = isset($_POST['password']) ? password_hash(trim($_POST['password']), PASSWORD_BCRYPT) : '';

	$crud = new Crud();

	$table = 'customer';
	$columns = ['name', 'address','email','nic_or_registration_number','customer_type','contact_number','password'];
	$values = [$name,$Address,$email,$nic_or_registration_number,$customer_type,$contact_number,$Password];

	$insertId = $crud->insert($table, $columns, $values);
	if (is_numeric($insertId)) {
			echo "Inserted successfully with ID: $insertId";
			header('Location: ../public/login.php'); 
			
	} else {
			echo $insertId; // Error message
	}




//"Duplicate entry 'admin@gmail.com' for key 'email'"


	
}






?>