<?php
//include necessary files
include_once 'functions.inc.php';
include_once 'db.inc.php';
include_once 'project.inc.php';

//initialize session if none exists
if (session_id() == '' || !isset($_SESSION)) {
	// session isn't started
	session_start();
}

//helps interptre french accented characters. They have special needs
header('Content-type: text/html; charset=utf-8');

//perform verification of input and required values
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['submit'] == 'save project' && !empty($_POST['projectNmb']) && !empty($_POST['name']) && isset($_FILES['cover_image']['tmp_name']) && !empty($_POST['program_FR']) && !empty($_POST['program_NL']) && !empty($_POST['program_EN']) && !empty($_POST['date_start']) && !empty($_POST['date_end']) && !empty($_POST['country_code']) && !empty($_POST['location_city']) && !empty($_POST['client_type'])) {

	//instantiate the Project class
	$project = new Project();

	//update the project
	$id = $project -> updateProject($_POST);
	if (!empty($id)) {
		header('Location:/projectListing/review.php?id=' . $id);
		exit ;
	} else {
		exit('ERROR: problem updating project');
	}
}
//if no conditions met send user to main page
else if ($_GET['action'] == 'project_delete') {

	//instantiate the Project class
	$project = new Project();

	//Delete the comment and return to the entry
	if ($project -> deleteProject($_GET['id'])) {
		header('Location:/projectListing/index.php');
		exit ;
	}
	//if deletion fails, output an error message
	else {
		exit('ERROR: Could not delete the project.');
	}

	exit ;
} 
//if create user is pressed, create user
else if($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'create_user' && !empty($_POST['login_name']) && !empty($_POST['login_password']) && !empty($_POST['usertype'])){

	include_once 'db.inc.php';
	//Open a database connection and store it
	$db = new PDO(DB_INFO, DB_USER, DB_PASS);
	
	$sql= "INSERT INTO admin (username, password, usertype) VALUES(?, SHA1(?), ?)";
	$stmt = $db->prepare($sql);
	$stmt->execute(array($_POST['login_name'], $_POST['login_password'], $_POST['usertype']));
	$stmt -> closeCursor();
	
	header('Location:/projectListing/index.php');
	exit;
}
//if login is pressed, log in
else if($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'login' && !empty($_POST['login_name']) && !empty($_POST['login_password'])){
		
	include_once 'db.inc.php';
	//Open a database connection and store it
	$db = new PDO(DB_INFO, DB_USER, DB_PASS);
	$sql = "SELECT COUNT(*) AS num_users, username, usertype FROM admin WHERE username=? AND password=SHA1(?)";
	$stmt = $db->prepare($sql);
	$stmt->execute(array($_POST['login_name'],$_POST['login_password']));
	$response = $stmt->fetch();
	if($response['num_users'] > 0){
		$_SESSION['loggedin'] = 1;
		$_SESSION['username'] = $response['username'];
		$_SESSION['usertype'] = $response['usertype'];
	} else{
		$_SESSION['loggedin'] = NULL;
	}
	header('Location:/projectListing/index.php');
	exit;
		
} 
//if logout is pressed, log out
else if($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'logout'){
	//unset all login session variables
	if(isset($_SESSION['loggedin'])) unset($_SESSION['loggedin']);
	if(isset($_SESSION['username'])) unset($_SESSION['username']);
	if(isset($_SESSION['usertype'])) unset($_SESSION['usertype']);
	
	header('Location:/projectListing/index.php');
	exit;
} 
//if no conditions met, go to homepage by default
else {
	header('Location:/projectListing/index.php');
	exit ;
}
?>