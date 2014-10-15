<?php

//initialize session if none exists
if(session_id() == '' || !isset($_SESSION)) {
    // session isn't started
    session_start();
}


//include necessary files
include_once './inc/functions.inc.php';
include_once './inc/db.inc.php';
include_once './inc/interface_values.inc.php';
include_once './inc/parameter_values.inc.php';
include_once './inc/label_values.inc.php';

//helps interpret french accented characters. They have special needs
header('Content-type: text/html; charset=utf-8');

//Open a database connection and store it
$db = new PDO(DB_INFO, DB_USER, DB_PASS);

//declare globals
//menu
global $expand_globals, $expand_visibility, $expand_filters;
//visible
global $hiddenFields;
//filter
global $show_empty, $filter_by_pt, $filter_ptypes, $filter_by_category, $filter_categories, $filter_by_client, $filter_clients, $filter_by_date, $filter_startdate, $filter_enddate, $filter_by_ps, $filter_statusses, $filter_by_eel, $filter_eels, $filter_by_eev, $filter_eev_min, $filter_eev_max, $filter_by_budget_e, $filter_budget_estimate_min, $filter_budget_estimate_max, $filter_by_budget_f, $filter_budget_final_min, $filter_budget_final_max ;
//global
global $listtitle,$display_mode, $language, $projects_offset, $projects_current_offset, $filter_sql, $number_of_projects; 

//sitewide login or open access?
$sitewide = TRUE;
//how many projects are displayed in one time
$projects_pp = 10;

/*
 * Sets all variables and defaults for the project list query
 */
function setDefaults(){
	//MENU
	$GLOBALS['expand_globals'] = NULL;
	$GLOBALS['expand_visibility'] = NULL;
	$GLOBALS['expand_filters'] = NULL;
	
	//VISIBLE
	include './inc/label_values.inc.php';
	include './inc/parameter_values.inc.php';
	include './inc/interface_values.inc.php';
	$GLOBALS['hiddenFields'] = joinData(array(joinData($pnumber_labels), joinData($address_labels), joinData($pnumber_labels), joinData($pt_labels), joinData($sc_labels), joinData($eev_labels), joinData($cons_labels), joinData($it_labels), joinData($tup_labels), joinData($awards_labels), joinData($publications_labels)));
	
	//FILTER
	//showempty
	$GLOBALS['show_empty'] = "";
	/**/
	//project type
	$GLOBALS['filter_by_pt'] = "";
	$GLOBALS['filter_ptypes'] = $projecttypes;
	//category
	$GLOBALS['filter_by_category'] = "";
	$GLOBALS['filter_categories'] = $categories;
	//client
	$GLOBALS['filter_by_client'] = "";
	$GLOBALS['filter_clients'] = $clienttypes;
	/**/
	//date
	$GLOBALS['filter_by_date'] = "";
	$GLOBALS['filter_startdate'] = "2000";
	$GLOBALS['filter_enddate'] = date("Y");
	/**/
	//status
	$GLOBALS['filter_by_ps'] = "";
	$GLOBALS['filter_statusses'] = $statusses;
	/**/
	//eelevel
	$GLOBALS['filter_by_eel'] = "";
	$GLOBALS['filter_eels'] = $eelevels;
	/**/
	//eevalue
	$GLOBALS['filter_by_eev'] = "";
	$GLOBALS['filter_eev_min'] = "";
	$GLOBALS['filter_eev_max'] = "";
	/**/
	//budget_e
	$GLOBALS['filter_by_budget_e'] = "";
	$GLOBALS['filter_budget_estimate_min'] = "";
	$GLOBALS['filter_budget_estimate_max'] = "";
	/**/
	//budget_f
	$GLOBALS['filter_by_budget_f'] = "";
	$GLOBALS['filter_budget_final_min'] = "";
	$GLOBALS['filter_budget_final_max'] = "";
	/**/
	
	//INCLUDE
	//create array with all ids as keys, save for each id whether the project is included (=1 - default) or not (=0)
	$_SESSION['project_inclusion'] = (isset($_SESSION['project_inclusion'])) ? $_SESSION['project_inclusion'] : array_fill_keys(retrieveIDs(), 1);
	//if a project was deleted or added, reset the 'project_inclusion' array
	$_SESSION['project_inclusion'] = (count(retrieveIDs())!= count($_SESSION['project_inclusion'])) ? array_fill_keys(retrieveIDs(), 1) : $_SESSION['project_inclusion'];
	
	//GLOBALS
	$GLOBALS['listtitle'] = "";
	$GLOBALS['language'] = $languages[0];
	$GLOBALS['display_mode'] = (isset($_POST['display_mode'])) ? $_POST['display_mode'] : $displaymodes[0];
	$GLOBALS['projects_offset'] = 0;
	$GLOBALS['filter_sql'] = "";
	$GLOBALS['number_of_projects'] = count($_SESSION['project_inclusion']);

}

/*
 * read passed variables in the url and setup the filter query
 */
if (isset($_GET['var'])) {
	$post = urlToPost($_GET['var']);
	//print_r($post);
	//echo $post['input_globals_checkbox'];
	/*
	 * MENU
	 */
	//expand global tab?
	$expand_globals = (isset($post['input_globals_checkbox'])) ? $post['input_globals_checkbox'] : NULL;
	//expand visibility tab?
	$expand_visibility = (isset($post['input_visibility_checkbox'])) ? $post['input_visibility_checkbox'] : NULL;
	//expand filter tab?
	$expand_filters = (isset($post['input_filters_checkbox'])) ? $post['input_filters_checkbox'] : NULL;
	
	/*
	 * VISIBLE
	 */
	//string of elements to show
	$hiddenFields= (isset($post['visibilityHidden'])) ? joinData($post['visibilityHidden']) : $hiddenFields;
	
	/*
	 * FILTER  update filter variables and construct sql query
	 */
	//start the query
	$filter_sql = "WHERE (";
	//showempty selected?
	$show_empty = (isset($post['show_empty'])) ? $post['show_empty'] : "";
	
	//project type selected?
	$filter_by_pt = (isset($post['filter_by_pt'])) ? $post['filter_by_pt'] : "";
	$filter_ptypes = (isset($post['project_type'])) ? ($post['project_type']) : $filter_ptypes;
	if($filter_by_pt != "" && isset($post['project_type'])){
		//loop through all selected
		foreach ($filter_ptypes as $key => $value) {
			$filter_sql .= " projecttype='".$value."' OR";
			//in case of filter by "projects", include competitions that have been won
			if($key==$projecttypes[0]){
				$filter_sql .= " ( projecttype='".$projecttypes[1]."' AND competitionwon='yes') OR";
			}
		}
		//if something was added, remove last "OR"
		$filter_sql = ($filter_sql!="WHERE") ?  rtrim($filter_sql, "OR") : $filter_sql;
		$filter_sql .= ") AND (";
	}
	
	//category
	$filter_by_category = (isset($post['filter_by_category'])) ? $post['filter_by_category'] : "";
	$filter_categories = (isset($post['category'])) ? $post['category'] : "";
	if($filter_by_category != "" && isset($post['category'])){
		//bad, bad code, I know. Mea Culpa
		$anyCfound = false;
		
		$c1Found = false;
		$v1Found = 0;
		
		$c2Found = false;
		$v2Found = 0;
		
		$c3Found = false;
		$v3Found = 0;
		
		$c4Found = false;
		$v4Found = 0;
		
		$c5Found = false;
		$v5Found = 0;
		
		foreach ($filter_categories as $key => $value) {
			//urbanism
			if($value == $categories[0]){
				$anyCfound = true;
				$c1Found = true;
				$filter_sql .= " (category LIKE '%".$value."%' AND ( category LIKE '%'";
			}
			if($c1Found == true){
				if(in_array($value, array_slice($categories, 1, 6))){
					$filter_sql = rtrim($filter_sql, "category LIKE '%'");
					$filter_sql .= " category LIKE '%".$value."%' OR";
					$v1Found ++;
					if($v1Found==6){
						$filter_sql .= " category LIKE '%' OR";
					}
				}
			}
			//residential
			if($value == $categories[7]){
				$filter_sql = rtrim($filter_sql, "OR");
				$filter_sql =($anyCfound == true) ? $filter_sql.=")) OR" : $filter_sql;
				
				$anyCfound = true;
				$c2Found = true;
				$filter_sql .= " (category LIKE '%".$value."%' AND ( category LIKE '%'";
			}
			if($c2Found == true){
				if(in_array($value, array_slice($categories, 8, 2))){
					$filter_sql = rtrim($filter_sql, "category LIKE '%'");
					$filter_sql .= " category LIKE '%".$value."%' OR";
					$v2Found ++;
					if($v2Found==2){
						$filter_sql .= " category LIKE '%' OR";
					}
				}
			}
			//public
			if($value == $categories[10]){
				$filter_sql = rtrim($filter_sql, "OR");
				$filter_sql =($anyCfound == true) ? $filter_sql.=")) OR" : $filter_sql;
				
				$anyCfound = true;
				$c3Found = true;
				$filter_sql .= " (category LIKE '%".$value."%' AND ( category LIKE '%'";
			}
			if($c3Found == true){
				if(in_array($value, array_slice($categories, 11, 3))){
					$filter_sql = rtrim($filter_sql, "category LIKE '%'");
					$filter_sql .= " category LIKE '%".$value."%' OR";
					$v3Found ++;
					if($v3Found==3){
						$filter_sql .= " category LIKE '%' OR";
					}
				}
			}
			//offices
			if($value == $categories[14]){
				$filter_sql = rtrim($filter_sql, "OR");
				$filter_sql =($anyCfound == true) ? $filter_sql.=")) OR" : $filter_sql;	
				
				$anyCfound = true;
				$c4Found = true;
				$filter_sql .= " (category LIKE '%".$value."%' AND ( category LIKE '%'";
			}
			if($c4Found == true){
				if(in_array($value, array_slice($categories, 15, 3))){
					$filter_sql = rtrim($filter_sql, "category LIKE '%'");
					$filter_sql .= " category LIKE '%".$value."%' OR";
					$v4Found ++;
					if($v4Found==3){
						$filter_sql .= " category LIKE '%' OR";
					}
				}
			}
			//other
			if($value == $categories[18]){
				$filter_sql = rtrim($filter_sql, "OR");
				$filter_sql =($anyCfound == true) ? $filter_sql.=")) OR" : $filter_sql;
				
				$anyCfound = true;
				$c5Found = true;
				$filter_sql .= " (category LIKE '%".$value."%' AND ( category LIKE '%'";
			}
			if($c5Found == true){
				if(in_array($value, array_slice($categories, 19, 5))){
					$filter_sql = rtrim($filter_sql, "category LIKE '%'");
					$filter_sql .= " category LIKE '%".$value."%' OR";
					$v5Found ++;
					if($v5Found==5){
						$filter_sql .= " category LIKE '%' OR";
					}
				}
			}
		}
		if($anyCfound){
			//if something was added, remove last "OR"
			$filter_sql = rtrim($filter_sql, "OR");
			$filter_sql.= "))";
			$filter_sql .= ") AND (";
		}
	}
	
	//client
	$filter_by_client = (isset($post['filter_by_client'])) ? $post['filter_by_client'] : "";
	$filter_clients = (isset($post['client'])) ? ($post['client']) : $filter_clients;
	if($filter_by_client != "" && isset($post['client'])){
		foreach ($filter_clients as $key => $value) {
			$filter_sql .= " clienttype='".$value."' OR";
		}
		//if something was added, remove last "OR"
		$filter_sql = ($filter_sql!="WHERE") ?  rtrim($filter_sql, "OR") : $filter_sql;
		$filter_sql .= ") AND (";
	}
	
	//date
	$filter_by_date = (isset($post['filter_by_date'])) ? $post['filter_by_date'] : "";
	$filter_startdate = (isset($post['start_date'])) ? ($post['start_date']) : $filter_startdate;
	$filter_enddate = (isset($post['end_date'])) ? ($post['end_date']) : $filter_enddate;
	if($filter_by_date != ""){
		$filter_sql .= " ((startdate >= ".$filter_startdate." AND startdate <= ".$filter_enddate.") OR (enddate >= ".$filter_startdate." AND enddate <= ".$filter_enddate.") OR (startdate <= ".$filter_startdate." AND enddate >= ".$filter_enddate."))";
		//$filter_sql .= " startdate >= ".$filter_startdate." AND enddate <= ".$filter_enddate;
		//in case enddate is smaller than the current year, we dont want 'ongoing' projects to be displayed
		if($filter_enddate < date("Y")){//|| $filter_startdate > date("Y")
			$filter_sql .=" AND enddate NOT LIKE '%lopend%ongoing%'";
		} else if($filter_enddate >= date("Y") && $filter_startdate <= date("Y")){
			$filter_sql .=" OR enddate LIKE '%lopend%ongoing%'";
		}
		$filter_sql .= ") AND (";
	}
	
	//status
	$filter_by_ps = (isset($post['filter_by_ps'])) ? $post['filter_by_ps'] : "";
	$filter_statusses = (isset($post['PSL'])) ? $post['PSL'] : $filter_statusses;
	if($filter_by_ps != "" && isset($post['PSL'])){
		foreach ($filter_statusses as $key => $value) {
			$filter_sql .= " status= &quot;".$value."&quot; OR";
		}
		//if something was added, remove last "OR"
		$filter_sql = ($filter_sql!="WHERE") ?  rtrim($filter_sql, "OR") : $filter_sql;
		$filter_sql .= ") AND (";
	}
	
	//eelevel
	$filter_by_eel = (isset($post['filter_by_eel'])) ? $post['filter_by_eel'] : "";
	$filter_eels = (isset($post['EFL'])) ? $post['EFL'] : $filter_eels;
	if($filter_by_eel != "" && isset($post['EFL'])){
		foreach ($filter_eels as $key => $value) {
			$filter_sql .= " eelevel= &quot;".$value."&quot; OR";
		}
		//if something was added, remove last "OR"
		$filter_sql = ($filter_sql!="WHERE") ?  rtrim($filter_sql, "OR") : $filter_sql;
		$filter_sql .= ") AND (";
	}
	
	//eevalue
	$filter_by_eev = (isset($post['filter_by_eev'])) ? $post['filter_by_eev'] : "";
	$filter_eev_min = (isset($post['min_eev'])) ? ($post['min_eev']) : $filter_eev_min;
	$filter_eev_max = (isset($post['max_eev'])) ? ($post['max_eev']) : $filter_eev_max;
	if($filter_by_eev != ""){
		$filter_sql .= " eevalue >= ".$filter_eev_min." AND eevalue <= ".$filter_eev_max;
		if(!$show_empty){
			$filter_sql.=" AND eevalue NOT LIKE '0'";
		}
		$filter_sql .= ") AND (";
	}
	
	//budget_e
	$filter_by_budget_e = (isset($post['filter_by_budget_e'])) ? $post['filter_by_budget_e'] : "";
	$filter_budget_estimate_min = (isset($post['budget_e_min'])) ? ($post['budget_e_min']) : $filter_budget_estimate_min;
	$filter_budget_estimate_max = (isset($post['budget_e_max'])) ? ($post['budget_e_max']) : $filter_budget_estimate_max;
	if($filter_by_budget_e != ""){
		$filter_sql .= " budget_estimate >= ".$filter_budget_estimate_min." AND budget_estimate <= ".$filter_budget_estimate_max;
		if(!$show_empty){
			$filter_sql.=" AND budget_estimate NOT LIKE '0'";
		}
		$filter_sql .= ") AND (";
	}

	//budget_f
	$filter_by_budget_f = (isset($post['filter_by_budget_f'])) ? $post['filter_by_budget_f'] : "";
	$filter_budget_final_min = (isset($post['budget_f_min'])) ? ($post['budget_f_min']) : $filter_budget_final_min;
	$filter_budget_final_max = (isset($post['budget_f_max'])) ? ($post['budget_f_max']) : $filter_budget_final_max;
	if($filter_by_budget_f != ""){
		$filter_sql .= " budget_final >= ".$filter_budget_final_min." AND budget_final <= ".$filter_budget_final_max;
		if(!$show_empty){
			$filter_sql.=" AND budget_final NOT LIKE '0'";
		}
		$filter_sql .= ") AND (";
	}
	
	//remove WHERE from $filter_sql if no conditiones where stated
	$filter_sql = rtrim($filter_sql , "WHERE (");
	//remove last AND
	$filter_sql = rtrim($filter_sql , " AND (");
	//debug
	//echo $filter_sql;
	
	/*
	 * INCLUDE
	 */
	//if we just came from the data view mode, update the checked and unchecked project include checkboxes
	if($post['previouspage']==$displaymodes[0]){
			
		//turn on all selected projects	
		if (isset($post['include_checkbox'])){
			foreach ($post['include_checkbox'] as $key => $value) {
				if (array_key_exists($value, $_SESSION['project_inclusion'])){
					$_SESSION['project_inclusion'][$value] = 1;
				}
			}
		}
		//turn off all unselected projects
		if (isset($post['include_checkboxHidden'])){
			foreach ($post['include_checkboxHidden'] as $key => $value) {
				if (array_key_exists($value, $_SESSION['project_inclusion'])){
					$_SESSION['project_inclusion'][$value] = 0;
				}
			}
		}
	}
				
	/*
	 * GLOBALS
	 */
	//check title
	$listtitle = (isset($post['listtitle'])) ? $post['listtitle'] : $listtitle;
	//check language
	$language = (isset($post['language'])) ? $post['language'] : $language;
	//check display mode
	$display_mode = (isset($post['display_mode'])) ? $post['display_mode'] : $display_mode;
	// get number of projects
	$number_of_projects = retrieveNumberOfProjects($filter_sql);	
	$projects_offset = 0;
} 
//process passed variables and setup the filter query
else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (isset($_POST['reset'])){
		if ($_POST['reset'] == 'RESET') {
			//Reset Session parameters
			if(isset($_SESSION['project_inclusion'])) unset($_SESSION['project_inclusion']);
		}
		setDefaults();
	}
}
//if we're not updating, reset parameters to default 
else{
	setDefaults();
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
	<head profile="http://www.w3.org/2005/10/profile">
		<link rel="icon" type="image/ico" href="images/up.ico">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>Project Listing</title>
		<meta name="author" content="Thomas" />
		<!-- CSS LINKS -->
		<link rel="stylesheet" type="text/css" media="all" href="css/style.css" />
		<!-- JAVASCRIPT LINKS -->
		<script src="js/jquery-1.11.1.min.js" type="text/javascript"></script>
		<script type="text/javascript" src="js/script.js"></script>
		<script type="text/javascript" src="js/charcount.js"></script>
		<script type="text/javascript">
			/**
			 *function for collapsable menu
			 *
			 * @param string checkbox Checkbox to check
			 * @param string id Element to chenge state
			 * @param string state
			 * @param string id_img id of the expand/hide image button
			 */
			function expand(checkbox, id, state, id_img) {
				swap(checkbox, id, state);

				var E = document.getElementById(id);
				var img = document.getElementById(id_img);
				if (E.style.display == 'none') {
					img.style.backgroundPosition = "0px -24px";
				} else {
					img.style.backgroundPosition = "0px -36px";
				}
			}

			/**
			 *Calls the validateForm function and passes local variables
			 *
			 * @param
			 * @return
			 */
			function checkInputForm() {
				/*
				 * For the visibility filter checkboxes, disable the 'hidden' inputfield if checkbox checked
				 */
				var visibility_checkboxes = document.getElementsByClassName("reversecheckbox");
				var visibility_hidden_checkboxes = document.getElementsByClassName("reversecheckboxHidden");
				for(var i=1; i < visibility_checkboxes.length; i++){
					if(visibility_checkboxes[i].checked){
						//alert("ok");
  						visibility_hidden_checkboxes[i].disabled = true;
					}
				}
				
								//array of all inputs to check for 'empty' errors [id,name]
				//added debugfield bc function was crashing with empty array
				var checkEmpty = [["debug_field", "debug field"]];

				// array of all inputs to check whether they are numerical errors [id,human readable name]
				var checkNumerical = [["budget_e_min", "Estimated Minimal Budget"], ["budget_e_max", "Estimated Maximal Budget"], ["budget_f_min", "Final Minimal Budget"], ["budget_f_max", "Final Maximal Budget"]];

				// array of all inputs to check whether they are bigger than the other [id1,id2, error message]
				var checkBiggerThan = [['min_eev','max_eev',"The minimal Energy Efficiency should be smaller than the maximum Energy Efficiency"], ["budget_f_min", "budget_f_max", "The minimal final budget should be smaller than the maximal final budget"], ["budget_e_min", "budget_e_max", "The minimal estimated budget should be smaller than the maximal estimated budget"], ["start_date", "end_date", "The enddate should be later the the starting date"]];

				//execute form check
				return validateForm(false, [], checkNumerical, checkBiggerThan, [], [], [], [], []);
				
			}

			/**
			 *Adds an event listener to all delete buttons and promots the user to confirm deletion
			 *
			 * @param
			 * @return
			 */
			window.onload = function() {
				formatCheckboxes();
				updateFields();
			}				
				
			function updateFields(){
				var buttons = document.getElementsByClassName("delete");

				for (var i = 0; i < buttons.length; i++) {
					buttons[i].addEventListener('click', function() {

						if (confirm('You are about to DELETE a Project. \n This CANNOT BE UNDONE! \n \n  Do you want to continue?')) {
							return true;
						} else {
							event.preventDefault();
						};
					}, false);
				}
				//onclick="return confirm('You are about to DELETE a Project. \n This CANNOT BE UNDONE! \n \n  Do you want to continue?');

				/*
				 *Execute any JS code in checked checkboxes on windowload
				 */
				var inputs = document.getElementsByTagName("input");

				for (var i = 0; i < inputs.length; i++) {
					if (inputs[i].getAttribute('type') == 'checkbox') {
						if (inputs[i].hasAttribute('onchange')) {
							inputs[i].onchange();
						}
					}
				}	
			}
			
			function formatCheckboxes(){
				/*
				* calls swap to hide/show elements based on selection
				*/

				//define variables for each swap

				//urbanism
				var cb1 = '<?php echo $categories[0] ?>';
				var cb2 = '<?php echo $categories[1] ?>';
				var cb3 = '<?php echo $categories[2] ?>';
				var cb4 = '<?php echo $categories[3] ?>';
				var cb5 = '<?php echo $categories[4] ?>';
				var cb6 = '<?php echo $categories[5] ?>';
				var cb7 = '<?php echo $categories[6] ?>';
				var cb1_string = joinData([cb2+"_wrapper",cb3+"_wrapper",cb4+"_wrapper",cb5+"_wrapper",cb6+"_wrapper",cb7+"_wrapper"]);
				//checkbox to check, joined string of checkboxes to hide/show, desired state
				var s1 = ["category-"+cb1,cb1_string,'block'];
			
				//residential
				var cb8 = '<?php echo $categories[7] ?>';
				var cb9 = '<?php echo $categories[8] ?>';
				var cb10 = '<?php echo $categories[9] ?>';
				var cb8_string = joinData([cb9+"_wrapper",cb10+"_wrapper"]);
				//checkbox to check, joined string of checkboxes to hide/show, desired state
				var s2 = ["category-"+cb8,cb8_string,'block'];
			
				//public
				var cb11 = '<?php echo $categories[10] ?>';
				var cb12 = '<?php echo $categories[11] ?>';
				var cb13 = '<?php echo $categories[12] ?>';
				var cb14 = '<?php echo $categories[13] ?>';
				var cb11_string = joinData([cb12+"_wrapper",cb13+"_wrapper", cb14+"_wrapper"]);
				//checkbox to check, joined string of checkboxes to hide/show, desired state
				var s3 = ["category-"+cb11,cb11_string,'block'];
			
				//office
				var cb15 = '<?php echo $categories[14] ?>';
				var cb16 = '<?php echo $categories[15] ?>';
				var cb17 = '<?php echo $categories[16] ?>';
				var cb18 = '<?php echo $categories[17] ?>';
				var cb15_string = joinData([cb16+"_wrapper",cb17+"_wrapper", cb18+"_wrapper"]);
				//checkbox to check, joined string of checkboxes to hide/show, desired state
				var s4 = ["category-"+cb15,cb15_string,'block'];
			
				//other
				var cb19 = '<?php echo $categories[18] ?>';
				var cb20 = '<?php echo $categories[19] ?>';
				var cb21 = '<?php echo $categories[20] ?>';
				var cb22 = '<?php echo $categories[21] ?>';
				var cb23 = '<?php echo $categories[22] ?>';
				var cb24 = '<?php echo $categories[23] ?>';
				var cb19_string = joinData([cb20+"_wrapper",cb21+"_wrapper", cb22+"_wrapper",cb23+"_wrapper", cb24+"_wrapper"]);
				//checkbox to check, joined string of checkboxes to hide/show, desired state
				var s5 = ["category-"+cb19,cb19_string,'block'];
			
				//join all these variables in an array
				var list = [s1, s2, s3, s4, s5];
				//loop through the array and call swapSelect for each value
				for (var i = 0; i < list.length; i++) {
			
				var s = document.getElementById(list[i][0]);
				var e1 = list[i][0];
				var e2 = list[i][1];
				var e3 = list[i][2];
			
				s.setAttribute('onchange',"swap('"+e1+"','"+e2+"','"+e3+"')");
				}
			}
			
			
			/**
			 *Dynamically load more projects
			 */
			$(document).ready(function() {
				var language = "<?php echo $language ?>";
				var projects_pp = "<?php echo $projects_pp ?>";
				var projects_offset = "<?php echo $projects_offset ?>";
				var no_projects = "<?php echo $number_of_projects ?>";
				var filter_sql = "<?php echo $filter_sql ?>";
				
				
				$("#load_more").click(function() {
					
					//check if there are still projects to load
					if(parseInt(projects_pp)+parseInt(projects_offset) <= parseInt(no_projects)){
						//update variables
						projects_offset = parseInt(projects_offset)+parseInt(projects_pp);
						//create joined array of arguments to pass to function
						var arguments = joinData([language, projects_pp.toString(), projects_offset.toString(), filter_sql.toString()],"joiner");
						arguments = arguments.toString()
						//alert(arguments);
						$.get("inc/functions.inc.php?arguments="+arguments+"&offset="+projects_offset, function(data) {
							$(data).appendTo("#content");
							updateFields();
						});
						
					} else{
						$("#load_more").html("ALL PROJECTS LOADED");
					}
				});
			});
			
			function swapDisplay(element, state){
				document.getElementById(element).style.display = state;
			}
			

		</script>
		<!-- Date: 2014-08-06 -->
	</head>
	<body>
		<div id="home">
			
			<!--LOGIN-->
			<div class="none" id="login">
				<?php 
				//only show login button if logged in, or if login isen't sitewide
				if(!$sitewide || ($sitewide && (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == 1) )){ 
				?>
				<a id="show_login" onclick="swapDisplay('login_form_wrapper', 'block')"> 
						<?php $loginlink = (isset($_SESSION['username'])) ?  "user: ".$_SESSION['username'] : "LOG IN"; 
						echo $loginlink ?>
				</a>
				<?php } ?>

				<div id="login_form_wrapper" style="display: <?php echo ($sitewide && !(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == 1)) ? "block": "none"?>">
					<?php 
					$loggedin = (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == 1) ? TRUE : FALSE;
					$admin = (isset($_SESSION['usertype']) && $_SESSION['usertype'] == "admin") ? TRUE : FALSE;
					printLogin($loggedin, $admin, $sitewide); 
					?>
				</div>
			</div>
			
			<!--SIDEBAR-->
			<!-- <form class="inputform" method="post" action="./index.php" enctype="multipart/form-data" onsubmit="return checkInputForm()"> -->
			<form class="inputform" method="post" action="./inc/update.inc.php" enctype="multipart/form-data" onsubmit="return checkInputForm()">
			<fieldset>
			<div id="sidebar_wrapper">
				<div id="sidebar">
					<div id="application_title">
						<img src="images/logoUP_small.png"/><span><h1>Project Listing</h1></span>
					</div>
					<!--START UI-->
					<div id="interface" class="filterform">

								<div id="buttons">
									<!--submit-->
									<div>
										<?php
										if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == 1 && isset($_SESSION['usertype']) && ($_SESSION['usertype'] == "admin" || $_SESSION['usertype'] == "editor")){
										?>
											<a class="button" href="admin.php"><span>ADD NEW PROJECT</span></a>
										<?php
										} else{
											echo "<br>";
										}
										?>
										</br>
										<div>
											<input type="submit" name="submit" value="UPDATE"/>
											<input type="submit" name="reset" value="RESET"/>
										</div>
									</div>
								</div>

								<!--START FILTERS-->
								<div id="filters_wrapper">
									<div id="filters">
										<!--START GLOBALS-->
										<div id="input_globals">

											<input class="collapsable_title" id="input_globals_checkbox" type="checkbox" name="input_globals_checkbox" value="checked" 
											onchange="expand('input_globals_checkbox','input_globals_hidewrapper', 'block', 'img_globals')" style="opacity:0; position:absolute; height:0px;" <?php echo $expand_globals?> />
											<label for="input_globals_checkbox"><span class="expandImg" id="img_globals" /><span>img</span></span><h2>Global Parameters</h2></label>

											<div id="input_globals_hidewrapper" style="display:none;">
												<!-- Listing Title-->
												<div>
													<label><span class="input_title">Listing Title</span></br> <span class="description">eg: Liste de services – 3 dernières années </span></br>
														<input id="name" class="wide_input_text" type="text" name="listtitle" maxlength="150" value="<?php echo $listtitle ?>" />
													</label>
												</div>
												
												<!-- Debug Field  made to resolve a bug with the validateForm function-->
												<input id="debug_field" type="hidden" name="debug_field" maxlength="150" value="debug"/>

												<!-- Language-->
												<div>
													<label><span class="input_title">Language:</span></br> <?php
													createSelectList($languages, "language", FALSE, $language);
														?> </label>
												</div>

												<!-- exclude-->
												<input type="hidden" name="exclude" value="" />

												<!-- Display Mode-->
												<div>
													<label><span class="input_title">Display Mode:</span></label>
													<?php
													createSelectList($displaymodes, "display_mode", FALSE, $display_mode);
													?>
												</div>
											</div>
										</div>
										<!--END GLOBALS-->

										<!--START VISIBLE FIELDS-->
										<div id="input_visibility" >

											<input class="collapsable_title" id="input_visibility_checkbox" type="checkbox" name="input_visibility_checkbox" value="checked" onchange="expand('input_visibility_checkbox','input_visibility_hidewrapper', 'block', 'img_visi')" style="opacity:0; position:absolute; height:0px;" <?php echo $expand_visibility?>/>
											<label for="input_visibility_checkbox"><span class="expandImg" id="img_visi" /><span>img</span></span><h2>Visibility</h2></label>

											<div id="input_visibility_hidewrapper" style="display:none;">
												<!-- Visible Fields-->
												<div>
													<label>
														<div class="italic gray">
															select what fields will be visible/hidden in the listing:
														</div></br>
														<div style="display:none;">
															<?php createReverseCheckbox("hidden", "visibility","hidden") ?></br>
														</div>
														<div>
															<?php createReverseCheckbox(joinData($pnumber_labels), "visibility",$pnumber_labels[2], "", $hiddenFields) ?></br>
														</div>
														<div>
															<?php createReverseCheckbox(joinData($address_labels), "visibility",$address_labels[2], "", $hiddenFields) ?></br>
														</div>
														<div>
															<?php createReverseCheckbox(joinData($cl_labels), "visibility",$cl_labels[2], "", $hiddenFields) ?></br>
														</div>
														<div>
															<?php createReverseCheckbox(joinData($pt_labels), "visibility",$pt_labels[2], "", $hiddenFields) ?></br>
														</div> 
														<div>
															<?php createReverseCheckbox(joinData($it_labels), "visibility",$it_labels[2], "", $hiddenFields) ?></br>
														</div>
														<div>
															<?php createReverseCheckbox(joinData($st_labels), "visibility",$st_labels[2], "", $hiddenFields) ?></br>
														</div>
														<div>
															<?php createReverseCheckbox(joinData($sc_labels), "visibility",$sc_labels[2], "", $hiddenFields) ?></br>
														</div>														
														<div>
															<?php createReverseCheckbox(joinData($sa_labels), "visibility",$sa_labels[2], "swap('".joinData($sa_labels)."','SA_value','block')", $hiddenFields) ?> </br>
															<div id="SA_value" style="display:block;" >
																&nbsp;&nbsp;<span class="arrow">&#8627;</span>
																<span class="italic">
																if available, show
																<?php  
																	createSelectList(array(joinData($gsa_labels), joinData($wsa_labels)), "visibilityHidden", TRUE, $hiddenFields); 
																?>
																value
																</span>
															</div>
														</div>
														<div>
															<?php createReverseCheckbox(joinData($eel_labels), "visibility",$eel_labels[2],"swap('".joinData($eel_labels)."','EE_value','block')", $hiddenFields) ?>
															<div id="EE_value" style="display:block;">
																<span class="arrow">&#8627;</span>
																<span class="italic">
																<?php createReverseCheckbox(joinData($eev_labels), "visibility", "show energy value", "", $hiddenFields) ?>
																</span>
															</div>
														</div> 
														<div>
															<?php createReverseCheckbox(joinData($bt_labels), "visibility",$bt_labels[2], "", $hiddenFields) ?></br>
														</div> <!-- 10 -->
														<div>
															<?php createReverseCheckbox(joinData($cons_labels), "visibility",$cons_labels[2], "", $hiddenFields) ?></br>
														</div>
														<div>
															<?php createReverseCheckbox(joinData($tup_labels), "visibility",$tup_labels[2], "", $hiddenFields) ?></br>
														</div>
														<div>
															<?php createReverseCheckbox(joinData($awards_labels), "visibility",$awards_labels[2], "", $hiddenFields) ?></br>
														</div>
														<div>
															<?php createReverseCheckbox(joinData($publications_labels), "visibility",$publications_labels[2], "", $hiddenFields) ?></br>
														</div></label>
												</div>
											</div>
										</div>
										<!--END VISIBLE FIELDS-->

										<!--START INPUT FILTERS-->
										<div id="input_filters">

											<input class="collapsable_title" id="input_filters_checkbox" type="checkbox" name="input_filters_checkbox" value="checked" onchange="expand('input_filters_checkbox','input_filters_hidewrapper', 'block', 'img_filters')" style="opacity:0; position:absolute; height:0px;" <?php echo $expand_filters?>/>
											<label for="input_filters_checkbox"><span class="expandImg" id="img_filters" /><span>img</span></span><h2>Filters</h2></label>

											<div id="input_filters_hidewrapper" style="display:none;">

												<!-- Ignore Empty-->
												<div>
													<label><span class="input_title"></span>
														<input id="sev1" type="checkbox" name="show_empty" value="checked" <?php echo $show_empty ?>/>
														<label for="sev1" title="include projects where the filtered field was has no value">inlude empty fields</label> </br> </label>
												</div>
												</br>

												<span class="" for="ct1">filter by:</span>

												<!-- Project Type-->
												<div>
													<label><span class="input_title" for="pt1">Project Type</span>
														<input id="pt1" type="checkbox" name="filter_by_pt" value="checked" onchange="swap('pt1','ptfilter','block')" <?php echo $filter_by_pt ?> />
														<span class="filters_input" id="ptfilter" style="display:none;"> <?php
														$c = array("commission", "competition", "feasibility study", "real estate", "other");
														$name = "project_type";
														createCheckboxes($projecttypes, $name, joinData($filter_ptypes));
															?> </span> </label>
												</div>

												<!-- Category-->
												<div id="category_wrapper">
													<label><span class="input_title" for="ct1">Category</span>
														<input id="ct1" type="checkbox" name="filter_by_category" value="checked" onchange="swap('ct1','categoryfilter','block')" <?php echo $filter_by_category ?> />
														<span id="categoryfilter" class="input filters_input" style="display:none;"> <?php
														$name = "category";
														createCheckboxes($categories, $name, joinData($filter_categories));
															?> </span> </label>
												</div>

												<!-- Client-->
												<div>
													<label><span class="input_title" for="cl1">Client</span>
														<input id="cl1" type="checkbox" name="filter_by_client" value="checked" onchange="swap('cl1','clientfilter','block')" <?php echo $filter_by_client ?> />
														<span class="filters_input" id="clientfilter" style="display:none;"> <?php
														$name = "client";
														createCheckboxes($clienttypes, $name, joinData($filter_clients));
															?> </span> </label>
												</div>

												<!-- Date-->
												<div>
													<label><span class="input_title" for="fbd1">Date</span>
														<input id="fbd1" type="checkbox" name="filter_by_date" value="checked" onchange="swap('fbd1','datefilter','block')" <?php echo $filter_by_date ?> />
														<div class="filters_input" id="datefilter" style="display:none;">
															</br>
															<label>active between
																<select id="start_date" name="start_date">
																	<?php
																	//generates select options from the year 2000 to the current year +1
																	for ($i = 2000; $i <= date("Y") + 1; $i++) {
																		//check if year was selected
																		$selected = ($filter_startdate == $i) ? "selected=\"selected\"" : "";
																		//make option
																		echo "<option value=\"" . $i . "\" " . $selected . ">" . $i . "</option>";
																	}																	
																	?>
																</select> and
																<select id="end_date" name="end_date">
																	<?php			
																	//generates select options from the year 2000 to 15 years after the current year
																	for ($i = 2000; $i <= date("Y") + 10; $i++) {
																		//check if year was selected				
																		$selected = ($filter_enddate == $i) ? "selected=\"selected\"" : "";
																		//make option
																		echo "<option value=\"" . $i . "\" " . $selected . ">" . $i . "</option>";
																	}
																	?>
																</select> </label>
														</div> </label>
												</div>

												<!-- Project Status-->
												<div>
													<label><span class="input_title" for="ps1">Project Status</span>
														<input id="ps1" type="checkbox" name="filter_by_ps" value="checked" onchange="swap('ps1','psfilter','block')" <?php echo $filter_by_ps ?> />
														<span class="filters_input" id="psfilter" style="display:none;"> <?php
														$name = "PSL";														
														createCheckboxes($statusses, $name, joinData($filter_statusses));
													?>
														</span> </label>
												</div>

												<!-- Energy Efficiency level-->
												<div>
													<label><span class="input_title" for="eel1">Energy Efficiency Level</span>
														<input id="eel1" type="checkbox" name="filter_by_eel" value="checked" onchange="swap('eel1','eelfilter','block')" <?php echo $filter_by_eel ?> />
														<span class="filters_input" id="eelfilter" style="display:none;"> <?php
														$c = array( array("passive", "passive", 1), array("very low-energy", "very low-energy", 1), array("low-energy", "low-energy", 1), array("standard energy efficiency", "standard energy efficiency", 1));
														$name = "EFL";
														createCheckboxes($eelevels, $name, joinData($filter_eels));
													?>
														</span> </label>
												</div>

												<!-- Energy Efficiency value-->
												<div>
													<label><span class="input_title" for="eev1">Energy Efficiency Value</span>
														<input id="eev1" type="checkbox" name="filter_by_eev" value="checked" onchange="swap('eev1','eevfilter','block')" <?php echo $filter_by_eev ?> />
														<div class="filters_input" id="eevfilter" style="display:none;">
															</br>
															<label>Energy Efficiency between<br>
																<select id="min_eev" name="min_eev">
																	<?php
																	//generates select options from the year 2000 to the current year +1
																	for ($i = 0; $i <= 100; $i++) {
																		//check if year was selected				
																		$selected = ($filter_eev_min == $i) ? "selected=\"selected\"" : "";
																		//make option
																		echo "<option value=\"" . $i . "\" " . $selected . ">" . $i . "</option>";																	
																	}
																	?>
																</select> kWh/m².yr and
																<select id="max_eev" name="max_eev">
																	<?php
																	//generates select options from the year 2000 to the current year +1
																	for ($i = 0; $i <= 100; $i++) {
																		//check if year was selected				
																		$selected = ($filter_eev_max == $i) ? "selected=\"selected\"" : "";
																		//make option
																		echo "<option value=\"" . $i . "\" " . $selected . ">" . $i . "</option>";	
																	}
																	?>
																</select> kWh/m².yr </label>
														</div> </label>
												</div>

												<!-- Budget estimate-->
												<div>
													<label><span class="input_title" for="be1">Budget (estimate)</span>
														<input id="be1" type="checkbox" name="filter_by_budget_e" value="checked" onchange="swap('be1','budget_estimate','block')" <?php echo $filter_by_budget_e ?> />
														<div class="filters_input" id="budget_estimate" style="display:none;">
															</br>
															<label>Estimated budget between
																<input type="text" id="budget_e_min" name="budget_e_min" maxlength="150" value="<?php echo $filter_budget_estimate_min ?>"/>
																€ and
																<input type="text" id="budget_e_max" name="budget_e_max" maxlength="150" value="<?php echo $filter_budget_estimate_max ?>"/>
																€ </label>
														</div> </label>
												</div>

												<!-- Busdget final-->
												<div>
													<label><span class="input_title" for="bf1">Budget (final)</span>
														<input id="bf1" type="checkbox" name="filter_by_budget_f" value="checked" onchange="swap('bf1','budget_final','block')" <?php echo $filter_by_budget_f ?> />
														<div class="filters_input" id="budget_final" style="display:none;">
															</br>
															<label>Final budget between
																<input type="text" name="budget_f_min" id="budget_f_min" maxlength="150" value="<?php echo $filter_budget_final_min ?>"/>
																€ and
																<input type="text" name="budget_f_max" id="budget_f_max" maxlength="150" value="<?php echo $filter_budget_final_max ?>"/>
																€ </label>
														</div> </label>
												</div>
												</br>
												</br>

											</div>
										</div>
										<!--END INPUT FILTERS-->
										
										<!-- hidden field passes the page we came from-->
										<input type="hidden" name="previouspage" value="<?php echo $display_mode ?>" />
										
									</div>
								</div>
					</div>
					<!--END UI-->
				</div>
			</div>
			<!--SIDEBAR END-->
			<!--CONTENT START-->
			<div id="content_wrapper_<?php echo split(" ", trim($display_mode))[0] ?>">
				<div id="content">
					<?php
					//check if logged in before displaying data
					if(!$sitewide || ($sitewide && isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == 1)){
						//determine what mode to display in
						switch ($display_mode) {
							case $displaymodes[1] :
								retrieveProjectsPreviewFormat($listtitle, $language, $hiddenFields, $filter_sql);
								break;
	
							default :
								//echo $language." ". $projects_pp." ". $filter_sql;
								retrieveProjectsDataFormat($language, $projects_pp, 0, $filter_sql);
								break;
						}
					?>
				</div>
				<div id="load_more_wrapper">
					<a class="button load_more big_button" id="load_more" onclick="loadXMLDoc()">
						LOAD NEXT <?php echo $projects_pp ?> PROJECTS
					</a>
					<?php } ?>
				</div>
			</div>
			
			</fieldset>
			</form>
			<!--CONTENT END-->
		</div>
	</body>
</html>

