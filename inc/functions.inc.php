<?php
//initialize session if none exists
if (session_id() == '' || !isset($_SESSION)) {
	// session isn't started
	session_start();
}

//helps interptre french accented characters. They have special needs
header('Content-type: text/html; charset=utf-8');

//check if function is called through URL. Get function and the array of arguments, then execute function
if (isset($_GET['arguments'])) {
	$arguments = splitData($_GET['arguments'], 0, "joiner");
	//current offset not being used
	if (isset($_SESSION['$projects_current_offset']))
		unset($_SESSION['$projects_current_offset']);
	$_SESSION['$projects_current_offset'] = $_GET['offset'];
	//execute function
	//echo "<script> alert(\"".$arguments[3]."\");</script>";
	retrieveProjectsDataFormat($arguments[0], $arguments[1], $arguments[2], $arguments[3]);
}

/**
 * Creates a list of checkboxes based on array data
 *
 * @param array $c array with values for the checkboxes
 * @param string $name name used as checkbox name and as part of the unique id
 * @param string $checked String containing all values that need to be checked by default
 * @return NULL
 */
function createCheckboxes($c, $name, $checked = "%%%%%%%%%%%%%") {

	for ($i = 0; $i < count($c); $i++) {
		//split option value into three language values
		$c_split = splitData($c[$i]);
		//determine visible value of option (if 3 languages, take english [2])
		$value = (count($c_split) >= 3) ? $c_split[2] : $c_split[0];

		//open div
		echo "<div id=\"" . $c[$i] . "_wrapper\" class=\"checkbox\" >";
		//check checkbox if the passed string contains the value of this specific checkbox
		if ((strpos($checked, $c[$i]) !== false)) {
			echo "<input id=\"" . $name . "-" . $c[$i] . "\" type=\"checkbox\" name=\"" . $name . "[]\" value=\"" . $c[$i] . "\" style=\"\" onchange=\"\" checked/>";
		} else {
			echo "<input id=\"" . $name . "-" . $c[$i] . "\" type=\"checkbox\" name=\"" . $name . "[]\" value=\"" . $c[$i] . "\" onchange=\"\"/>";
		}
		echo "<label for=\"" . $name . "-" . $c[$i] . "\">" . $value . "</label></div>";
	}
}

/**
 * Creates a dropdown select list from a 2D array containing the available values (datavalue, visible value)
 *
 * @param array $c array with values for the checkboxes. Values are strings combining three languages
 * @param string $name name used as checkbox name and as part of the unique id
 * @param boolean $array Whether or not this values is grouped together in an Array on submit
 * @param string $default Checks for a value that matches this string, if found, set it to default
 * @return NULL
 */
function createSelectList($c, $name, $array = FALSE, $default = NULL) {

	//make the opening select tag
	if ($array) {
		echo "<select id=\"" . $name . "\" name=\"" . $name . "[]" . "\" onchange=\"\">";
	} else {
		echo "<select id=\"" . $name . "\" name=\"" . $name . "\" onchange=\"\" >";
	}

	//write the options
	for ($i = 0; $i < count($c); $i++) {
		//split option value into three language values
		$c_split = splitData($c[$i]);
		//determine visible value of option (if 3 languages, take english [2])
		$value = (count($c_split) >= 3) ? $c_split[2] : $c_split[0];
		//$selected = ($c[$i] == trim($default)) ? "selected=\"selected\"" : "";
		$selected = (strpos($default, $c[$i]) !== false) ? "selected=\"selected\"" : "";

		echo "<option value=\"" . $c[$i] . "\" " . $selected . ">" . $value . "</option>";
	}

	//make the closing select tag
	echo "</select>";
}

/**
 * Create Checkbox with hidden field to report non selected values
 *
 * @param
 */
function createReverseCheckbox($value, $name, $labelname, $onchange = "", $unchecked = null) {

	$name1 = $name . "[]";
	$name2 = $name . "Hidden[]";
	if (strpos($unchecked, $value) !== false) {//$unchecked != null &&
		$unchecked = "";
	} else {
		$unchecked = "checked";
	}

	echo <<<CHECKBX
 	<input id="$value" class="$name1 reversecheckbox" type="checkbox" name="$name1" value="$value" onchange="$onchange" $unchecked/>
	<input id='$name2' class="$name2 reversecheckboxHidden" type='hidden' value="$value" name="$name2" />
	<label for="$value">$labelname</label>  	
CHECKBX;
}

/**
 * Convert <br> to new lines for use in textareas
 *
 * @param string text
 * @return string
 */
function br2newl($text) {
	$breaks = array("<br />", "<br>", "<br/>", "</br>");
	$text = str_ireplace(array("\n"), "", $text);
	$text = str_ireplace($breaks, "\n", $text);
	return $text;
}

/**
 * Convert new lines to <br> for saving in database
 *
 * @param string text
 * @return string
 */
function newl2br($text) {
	$breaks = array("\n");
	$text = str_ireplace($breaks, "</br>", $text);
	return $text;
}

/**
 * Creates a dropdown select list for all the months in the year
 *
 * @param string $name name used as checkbox name and as part of the unique id
 * @param boolean $array Whether or not this values is grouped together in an Array on submit
 * @param string $default Default selected month
 * @return NULL
 */
function createMonthSelectList($name, $array = NULL, $default = NULL) {

	//get months
	$M = cal_info(0);
	$months = array_slice($M['months'], 0, 12);

	//make the opening select tag
	if ($array) {
		echo "<select id=\"" . $name . "\" name=\"" . $name . "[]" . "\" onchange=\"\">";
	} else {
		echo "<select id=\"" . $name . "\" name=\"" . $name . "\" onchange=\"\" >";
	}

	//write the options
	for ($i = 0; $i < count($months); $i++) {
		$value = ($i + 1 < 10) ? "0" . ($i + 1) : $i + 1;
		$selected = ($value == $default) ? "selected=\"selected\"" : "";
		echo "<option value=\"" . $value . "\" " . $selected . ">" . $value . "</option>";
	}

	//make the closing select tag
	echo "</select>";
}

/**
 * Joins all strings in an array into a single string, separated by 's-_--e'. Returns the string
 *
 * @param array $c array to handle
 * @return string combined string
 */
function joinData($c, $key = "s-_--e") {

	//if strings already joined, join on second level
	for ($i = 0; $i < count($c); $i++) {
		$key = (strpos($c[$i], $key) !== false) ? "s-_-_-e" : $key;
	}
	//if strings already joined on second level, join on third level
	for ($i = 0; $i < count($c); $i++) {
		$key = (strpos($c[$i], "s-_-_-e") !== false) ? "s---_-e" : $key;
	}
	//debug
	if (isset($c[0])) {
		$returnString = (trim($c[0]) == "") ? "/" : $c[0];
	} else {
		$returnString = "/";
	}
	for ($i = 1; $i < count($c); $i++) {
		//if value empty then replace by /
		$value = (trim($c[$i]) == "") ? "/" : $c[$i];
		$returnString .= $key . $value;
	}

	return $returnString;
}

/**
 * Pushes Key and Value to an array
 *
 * @param array $a
 * @param string $key
 * @param string $value
 * @return array
 */
function pushKeyValue($a, $key, $value) {
	//push value to array
	array_push($a, $value);
	//add field with proper key
	$a[$key] = end($a);
	//remove field with number key
	unset($a[0]);
	//return the array
	return $a;
}

/**
 * Takes a string of data and seperates it at 's-_--e'. Returns array of values
 *
 * @param string $input input string of joined values
 * @return array
 */
function splitData($input, $level = 0, $key = "s-_--e") {

	//if string is joined on two levels split the first level
	$key = (strpos($input, "s-_-_-e") !== false) ? "s-_-_-e" : $key;
	//if string already joined on second level, split on third level
	$key = (strpos($input, "s---_-e") !== false) ? "s---_-e" : $key;
	//if level is passed, set specific key
	switch ($level) {
		case 1 :
			$key = "s-_--e";
			break;
		case 2 :
			$key = "s-_-_-e";
			break;
		case 3 :
			$key = "s---_-e";
			break;

		default :
			break;
	}
	//debug
	//echo "</br>".$key."</br>";

	$c = explode($key, $input);
	return $c;
}

/**
 * Check if input is empty
 *
 * @param string $value
 * @return boolean
 */
function emptyValue($value) {
	return ($value != "");
}

/**
 *Retrieves total number of projects in database based on a passed SQL query
 *
 * @param string $where where clause of query
 * @return
 */
function retrieveNumberOfProjects($where = "") {
	include_once 'db.inc.php';
	//Open a database connection and store it
	$db = new PDO(DB_INFO, DB_USER, DB_PASS);

	$sql = "select count(*) as total from projects " . $where;
	$stmt = $db -> prepare($sql);
	$stmt -> execute();
	$number_of_projects = $stmt -> fetchColumn();
	$stmt -> closeCursor();

	return $number_of_projects;
}

/**
 *Retrieves all ids of projects in database based on a passed SQL query
 *
 * @param string $where where clause of query
 * @return
 */
function retrieveIds($where = "") {
	include_once 'db.inc.php';
	//Open a database connection and store it
	$db = new PDO(DB_INFO, DB_USER, DB_PASS);

	$sql = "select id from projects" . $where;
	$stmt = $db -> prepare($sql);
	$stmt -> execute();
	$ids = $stmt -> fetchAll(PDO::FETCH_COLUMN, 0);
	$stmt -> closeCursor();

	return $ids;
}

/**
 *Retrieves list of projects from the database based on a passed SQL query and formats them in dataformat mode
 *
 * @param string $language language to use
 * @param int $projects_pp amount of projects to load
 * @param string $included_projects ids of projects to include
 * @param array $project_inclusion
 * @param string $where where clause of query
 * @return
 */
function retrieveProjectsDataFormat($language, $projects_pp, $projects_offset, $where = "") {

	include_once 'project.inc.php';
	include_once 'db.inc.php';

	//Open a database connection and store it
	$db = new PDO(DB_INFO, DB_USER, DB_PASS);

	//compose sql query
	$sql = "SELECT id
			FROM projects " . $where . " ORDER BY date DESC, created DESC 
			LIMIT " . $projects_pp . " OFFSET " . $projects_offset;
	$stmt = $db -> prepare(html_entity_decode($sql));
	$stmt -> execute();
	while ($row = $stmt -> fetch()) {
		$project = new Project(FALSE);
		$project -> setLanguage($language);
		$project -> updateParameters($row['id']);
		//check if id is set in the $project_inclusion array, and take the incluision state from there. If not set to true (visible)
		$included = (array_key_exists($project -> id, $_SESSION['project_inclusion'])) ? $_SESSION['project_inclusion'][$project -> id] : TRUE;
		//check if contributor or admin is logged in, set boolean to make menus visible accordingly
		$menus = (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == 1) ? TRUE : FALSE;
		echo $project -> formatProjectData($included, TRUE, $menus, TRUE);
		flush();
	}
	$stmt -> closeCursor();
}

/**
 *Retrieves list of projects from the database based on a passed SQL query and formats them in dataformat mode
 *
 * @param PDO $db databse to query from
 * @param string $language language to use
 * @param string $hiddenFields fields to hide in each project
 * @param array $included_projects projects that should be visible
 * @param string $sql query to execute
 * @return
 */
function retrieveProjectsPreviewFormat($listtitle, $language, $hiddenFields, $filter_sql = "") {

	$ids = joinData(array_keys($_SESSION['project_inclusion'], 0), ',');
	$where = "";
	if (isset($_SESSION['project_inclusion']) && $ids != "/") {
		$where = "WHERE id NOT IN (" . $ids . ")";
		//if there is also a filter query add it
		if ($filter_sql != ""){
			//echo "ok";
			$where = ($where!="") ? $where." AND ".ltrim($filter_sql,"WHERE") : "";
		}
	} 
	if($where == ""){
		//if there is only a filterquery
		if ($filter_sql != ""){
			$where = $filter_sql;
		}
	}


	$sql = "SELECT id FROM projects " . $where . " ORDER BY date DESC, created DESC";

	//echo $sql;exit;
	$spaces = array("\n", " ", "  ", "   ");
	$sql = str_ireplace($spaces, "%20", $sql);

	echo <<<PREVIEW
		<iframe id="previewIframe" src="/projectListing/project_listing_print.php?title=$listtitle&language=$language&sql=$sql&hidden=$hiddenFields" frameBorder="0"><p>Your browser does not support iframes.</p></iframe>
PREVIEW;
}


/**
 * Print login or user popup
 * 
 * @param boolean $loggedIN
 * @param boolean $ isAdmin
 * @return
 */
 function printLogin($loggedIn = FALSE, $isAdmin = FALSE){
 	$returnHTML = <<<CLOSE
 					<div id="login_form">
						<a id="hide_login" onclick="swapDisplay('login_form_wrapper', 'none')">
						X
						</a>
CLOSE;

	if(!$loggedIn){
		$returnHTML .= <<<LOGIN
						<form id="login_inputform"  action="/projectListing/inc/update.inc.php" method="post">
							<fieldset>
								<div>
									<div class="login_input">
										<span>NAME</span><input class="large" name="login_name" type="text"/>
									</div>
									<div class="login_input">
										<span>PASSWORD</span><input class="large" name="login_password" type="password"/>
									</div>
									<input type="hidden" name="action" value="login"/>
									<input id="login_submit" type="submit" name="login" value="LOG IN"/>
								</div>
							</fieldset>
						</form>
LOGIN;
	}
	
	if($loggedIn){
		$n = ucfirst ($_SESSION['username']);
		$t = ucfirst ($_SESSION['usertype']);
		switch ($_SESSION['usertype']) {
			case 'admin':
				$authorization = <<<AU
				<li>Authorization: 
				<ul>
					<li>create project listings</li>
					<li>add/edit/delete projects</li>
					<li>add new users</li>
				</ul>
			</li>
AU;
		
				break;
			
			default:
				$authorization = <<<AU2
				<li>Authorization: 
				<ul>
					<li>create project listings</li>
					<li>add/edit/delete projects</li>
				</ul>
				</li>
AU2;
				
				break;
		}
		$returnHTML .= <<<INFO
		<ul>
			<li>Username: $n </li>
			<li>Account Type: $t</li>
			$authorization
		</ul>
INFO;
 	
	}
	
	if($loggedIn && $isAdmin){
		$returnHTML .= <<<ADD
						<hr>
						<div class="red">CREATE NEW USER</div>
						<form id="createuser_inputform" action="/projectListing/inc/update.inc.php" method="post">
							<fieldset>
								<div></br>
									<div class="login_input">
										<span>NAME</span><input class="large" name="login_name" type="text"/>
									</div>
									<div class="login_input">
										<span>PASSWORD</span><input class="large" name="login_password" type="password"/>
									</div>
									<div class="login_input">
										<span>TYPE</span>
										<select name="usertype">
											<option value="contributor">Contributor &nbsp;<span class="gray">(edit projects)</span></option>
											<option value="admin">Admin &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="gray">(edit users/projects)</span></option>
										</select>
									</div>
									<input type="hidden" name="action" value="create_user"/>
									<input id="login_submit" type="submit" name="login" value="CREATE"/>
								</div>
							</fieldset>
						</form>
						
ADD;
	}

	if($loggedIn){
		$returnHTML .= <<<LOGOUT
		<hr>
		<form id="logout_inputform" action="/projectListing/inc/update.inc.php" method="post">
			<fieldset>
				<input type="hidden" name="action" value="logout"/>
				<input id="logout_submit" type="submit" name="logout" value="LOG OUT"/>
			</fieldset>
		</form>
LOGOUT;
	}
	
	$returnHTML .= <<<CLOSE
						</div>
CLOSE;

	echo $returnHTML;
 }
?>