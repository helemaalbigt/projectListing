<?php
//include necessary files
include_once 'functions.inc.php';
include_once 'db.inc.php';
include_once 'project.inc.php';

//initialize session if none exists
if (session_id() == '' || !isset($_SESSION)) {
	// session isn't started
	session_set_cookie_params(0);
	session_start();
}

//helps interpret french accented characters. They have special needs
header('Content-type: text/html; charset=utf-8');

//perform verification of input and required values
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['submit'] == 'save project' && !empty($_POST['projectNmb']) && !empty($_POST['name']) && isset($_FILES['cover_image']['tmp_name']) && !empty($_POST['program_FR']) && !empty($_POST['program_NL']) && !empty($_POST['program_EN']) && !empty($_POST['date_start']) && !empty($_POST['date_end']) && !empty($_POST['country_code']) && !empty($_POST['location_city']) && !empty($_POST['client_type'])) {

	//instantiate the Project class
	$project = new Project();

	//clean post data
	$cleanedPost = cleanData($_POST);
	//update the project
	$id = $project -> updateProject($cleanedPost);
	if (!empty($id)) {
		header('Location:../review.php?id=' . $id);
		exit ;
	} else {
		exit('ERROR: problem updating project');
	}
}
//delete project
else if ($_GET['action'] == 'project_delete') {
	//check if logged in and logged in as admin or editor before deleting
	if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == 1 && isset($_SESSION['usertype']) && ($_SESSION['usertype'] == "admin" || $_SESSION['usertype'] == "editor")){	
		//instantiate the Project class
		$project = new Project();
	
		//Delete the comment and return to the entry
		if ($project -> deleteProject($_GET['id'])) {
			header('Location:../index.php');
			exit ;
		}
		//if deletion fails, output an error message
		else {
			exit('ERROR: Could not delete the project.');
		}
	
		exit ;
	} else{
		exit('ERROR: You are not authorized to delete projects.');
	};
} 
//if create user is pressed, create user
else if($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'create_user' && !empty($_POST['login_name']) && !empty($_POST['login_password']) && !empty($_POST['usertype'])){
		
	//clean post data
	$cleanedPost = cleanData($_POST);	

	include_once 'db.inc.php';
	//Open a database connection and store it
	$db = new PDO(DB_INFO, DB_USER, DB_PASS);
	
	//PROCESS PASSWORD source:http://alias.io/2010/01/store-passwords-safely-with-php-and-mysql/
	// A higher "cost" is more secure but consumes more processing power
	$cost = 10;

	// Create a random salt
	$salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');

	// Prefix information about the hash so PHP knows how to verify it later.
	// "$2a$" Means we're using the Blowfish algorithm. The following two digits are the cost parameter.
	$salt = sprintf("$2a$%02d$", $cost) . $salt;

	// Hash the password with the salt
	$hash = crypt($cleanedPost['login_password'], $salt);
	
	$sql= "INSERT INTO admin (username, password, usertype) VALUES(?, ?, ?)";
	$stmt = $db->prepare($sql);
	$stmt->execute(array($cleanedPost['login_name'], $hash, $cleanedPost['usertype']));
	$stmt -> closeCursor();
	
	header('Location:../index.php');
	exit;
}
//if login is pressed, log in
else if($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'login' && !empty($_POST['login_name']) && !empty($_POST['login_password'])){
		
	//clean post data
	$cleanedPost = cleanData($_POST);
		
	include_once 'db.inc.php';
	//Open a database connection and store it
	$db = new PDO(DB_INFO, DB_USER, DB_PASS);
	$sql = "SELECT COUNT(*) AS num_users, username, password, usertype FROM admin WHERE username=?";
	$stmt = $db->prepare($sql);
	$stmt->execute(array($cleanedPost['login_name']));
	$response = $stmt->fetch();
	
	//debug
	//echo md5($_POST['login_password'].$response['salt'])."  -  ".$response['password'];exit;
	
	if($response['num_users'] > 0 && crypt($cleanedPost['login_password'], $response['password']) == $response['password']){
		$_SESSION['loggedin'] = 1;
		$_SESSION['username'] = $response['username'];
		$_SESSION['usertype'] = $response['usertype'];
	} else{
		$_SESSION['loggedin'] = NULL;
	}
	header('Location:../index.php');
	exit;
} 
//if logout is pressed, log out
else if($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'logout'){
	//unset all login session variables
	if(isset($_SESSION['loggedin'])) unset($_SESSION['loggedin']);
	if(isset($_SESSION['username'])) unset($_SESSION['username']);
	if(isset($_SESSION['usertype'])) unset($_SESSION['usertype']);
	
	header('Location:../index.php');
	exit;
} 
//if update is pressed, send the submitted data via url to the homepage
else if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit']) && $_POST['submit'] == 'UPDATE'){
		
	//check for name
	$name = (isset($_POST['name'])) ? $_POST['name'] : "unnamed";
	//check for saved
	$saved= (isset($_POST['saved'])) ? $_POST['saved'] : FALSE;	
	//serialize the post data
	$query = serialize($_POST);

	//Open a database connection and store it
	$db = new PDO(DB_INFO, DB_USER, DB_PASS);
	//echo postToUrl($_POST, '../index.html');
	$sql = "INSERT INTO queries (IP, name, saved, query) VALUES (?, ?, ?, ?)";
	if ($stmt =  $db -> prepare($sql)) {
		$stmt -> execute(array($_SERVER['REMOTE_ADDR'], $name, $saved, $query));
		$stmt -> closeCursor();	
		
		//get the ID of the query that was just saved
		$id_obj = $db -> query("SELECT LAST_INSERT_ID()");
		//gets unique ID generated for last entry into database
		$new_id = $id_obj -> fetch();
		//pass data to the $id variable (array with the id in index [0])
		$id_obj -> closeCursor();
		$queryId = $new_id[0];
		
		//go back to homepage with the query id as a variable		
		header("Location:../index.php?q=".$queryId."\"");
		exit;		
	} else{
		exit("ERROR: problem processing query");
	}

}
//if no conditions met, go to homepage by default
else {
	header('Location:../index.php');
	exit ;
}
?>