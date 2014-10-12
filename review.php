<?php
//include necessary files
include_once './inc/functions.inc.php';
include_once './inc/db.inc.php';
include_once './inc/project.inc.php';

//helps interptre french accented characters. They have special needs
header('Content-type: text/html; charset=utf-8');

//open database connection
$db = new PDO(DB_INFO, DB_USER, DB_PASS);

//get id. If none exist, go back to index.php
if (isset($_GET['id'])) {
	$id = htmlentities(strip_tags($_GET['id']));
} else {
	echo("ERROR: didn't find a specific page id");
	exit ;
}

//get prepared data for this project id
$project = new Project(FALSE);
$project -> updateParameters($id);

//check if logged in and logged in as admin or editor. If not, don't render page
if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == 1 && isset($_SESSION['usertype']) && ($_SESSION['usertype'] == "admin" || $_SESSION['usertype'] == "editor")){
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" lang="en">

	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="icon" type="image/ico" href="images/up.ico">
		<title>input form</title>
		<meta name="author" content="Thomas" />
		<!-- CSS LINKS -->
		<link rel="stylesheet" type="text/css" media="all" href="css/style.css" />
		<!-- Date: 2014-07-26 -->
	</head>
	<body>

		<div id="review">
			<div id="content_wrapper">
				<div id="content">
					<div class="pagetitle">
						<h1 class="red">Review your new project's data before submitting</h1>
					</div>
					</br>
					
					<h2 class="red languagetitle">Français</h2>
					
					<?php
						$project -> setLanguage("FR");
						echo $project -> formatProjectData(TRUE,TRUE,FALSE,FALSE);
					?>
					
					<h2 class="red languagetitle">Nederlands</h2>
					
					<?php
						$project -> setLanguage("NL");
						echo $project -> formatProjectData(TRUE,FALSE,FALSE,FALSE);
					?>
					
					<h2 class="red languagetitle">English</h2>
					
					<?php
						$project -> setLanguage("EN");
						echo $project -> formatProjectData(TRUE,FALSE,FALSE,FALSE);
					?>
						
					</br>
					<div id="menu">
						<a class="button" href="./index.php">Submit</a>
						<a class="button" href="./admin.php?id=<?php echo $id ?>">Edit</a>
						<a class="button delete" href="./inc/update.inc.php?action=project_delete&id=<?php echo $id ?>" onclick="return confirm('You are about to DELETE a Project. \n This CANNOT BE UNDONE! \n \n  Do you want to continue?');">Delete</a>
					</div>
				</div>
			</div>
		</div>
		
	</body>
</html>
<?php } else{
	echo "ERROR: You are not authorized to see this page. <br> <br>Login on the home page with a valid admin or editor acount on the homepage";
} ?>