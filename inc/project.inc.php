<?php
include_once 'db.inc.php';
include_once 'functions.inc.php';
include_once 'images.inc.php';

class Project {

	//the database connection
	public $db;

	//the language index
	public $L;

	//define all default values for parameters
	public $id;
	public $number;
	public $name;

	public $startdate;
	public $enddate;
	public $sortingdate_D;
	public $sortingdate_M;
	public $sortingdate_Y;
	public $sortdate;
	public $countrycode;
	public $city;
	public $city_pcode;
	public $street;
	public $street_number;

	public $coverimage;
	public $otherimages;
	
	public $program;
	public $description;

	public $clienttype;
	public $clientname;
	public $projecttype;
	public $competitionwon;
	public $newnumber;
	public $interventiontype;
	public $status;
	public $category;

	public $scale;
	public $area_gross;
	public $area_weighted;
	//energy efficiency level
	public $eelevel;
	public $eevalue;
	public $eeloldvalue;
	public $eeloldunit;
	public $budget_estimate;
	public $budget_final;
	public $budget_type;

	public $consultants;
	public $teamUP;
	
	public $awards;
	public $publications;

	public $timebudget_estimate;
	public $timebudget_final;
	public $internalbudget_estimate;
	public $internalbudget_final;

	//Upon class instantiation, open a database connection, and generate all default values. Some fixed values are taken from the paramter_values.php list
	public function __construct($set_defaults = TRUE) {
		//Open a database connection and store it
		$this -> db = new PDO(DB_INFO, DB_USER, DB_PASS);
		//the language index
		$this -> L = 0;

		//loads default values into project instance. Used when creatuing a new project or when editing a project
		if ($set_defaults) {
			//include predefined parameter values
			include 'parameter_values.inc.php';
			//set all dfault values for parameters
			$this -> id = NULL;
			$this -> number = NULL;
			$this -> name = NULL;

			$this -> startdate = "2000";
			$this -> enddate = joinData(array("en cours", "lopend", "ongoing"));
			$this -> sortingdate_D = NULL;
			$this -> sortingdate_M = NULL;
			$this -> sortingdate_Y = NULL;
			$this -> sortdate = NULL;
			$this -> countrycode = "BE";
			$this -> city = array("Bruxelles", "Brussel", "Brussels");
			$this -> city_pcode = "1000";
			$this -> street = NULL;
			$this -> street_number = NULL;

			$this -> coverimage = "images/default.jpg";
			$this -> otherimages = NULL;
			
			$this -> program = array("-- Programme encore pas écrit. Update asap! --", "-- Programma nog niet geschreven. Update asap! --", "-- Program not written yet. Update asap! --");
			$this -> description = array("-- Description encore pas écrit. Update asap! --", "-- Oschrijving nog niet geschreven. Update asap! --", "-- Description not written yet. Update asap! --");

			$this -> clienttype = $clienttypes[0];
			$this -> clientname = NULL;
			$this -> projecttype = $projecttypes[0];
			$this -> competitionwon = NULL;
			$this -> newnumber = NULL;
			$this -> interventiontype = NULL;
			$this -> status = $statusses[5];
			$this -> category = NULL;
			//"urbanisme%_%%stedenbouw%_%%urbanism%_%_%programmation%_%%programmatie%_%%programmation%_%_%bureau%_%%kantoor%_%%office";

			$this -> scale = $scales[1];
			$this -> area_gross = NULL;
			$this -> area_weighted = NULL;
			$this -> eelevel = $eelevels[3];
			$this -> eevalue = NULL;
			$this -> eeloldvalue = NULL;
			$this -> eeloldunit = NULL;
			$this -> budget_estimate = NULL;
			$this -> budget_final = NULL;
			$this -> budget_type = $budgettypes[0];

			$this -> consultants = NULL;
			//splitData("Roger%_%_%agent%_%%agent%_%%agent%%%_%Marie%_%_%autre%_%%andere%_%%other%%%_%Pieter%_%_%paysagiste%_%%landschapsarchitect%_%%landscape architect");
			$this -> teamUP = NULL;
			
			$this -> awards = NULL;//splitData("award%_%%award%_%%award%_%_%awardo%_%%awardo%_%%awardo");//
			$this -> publications = NULL;//splitData("award%_%%award%_%%award%_%_%awardo%_%%awardo%_%%awardo");//

			$this -> timebudget_estimate = NULL;
			$this -> timebudget_final = NULL;
			$this -> internalbudget_estimate = NULL;
			$this -> internalbudget_final = NULL;
		}
	}

	/**
	 * Replaces default parameters with project-specific values
	 *
	 * @param string $id
	 */
	public function updateParameters($id) {

		$e = $this -> retrieveProjectById($id);

		//replace default values for parameters with those of the edited project
		//textfields are already split into arrays containging the three languages
		$this -> id = $id;
		//prepare number
		$nmb = $e['number'];
		while (strlen($nmb) < 3) {
			$nmb = "0" . $nmb;
		}
		$this -> number = $nmb;
		$this -> name = $e['name'];

		$this -> startdate = $e['startdate'];
		$this -> enddate = $e['enddate'];
		$this -> sortdate = $e['date'];
		$dateArray = split("-", $this -> sortdate);
		$this -> sortingdate_D = $dateArray[2];
		$this -> sortingdate_M = $dateArray[1];
		$this -> sortingdate_Y = $dateArray[0];
		//...
		$this -> countrycode = $e['countrycode'];
		$this -> city = splitData($e['city']);
		$this -> city_pcode = $e['city_pcode'];
		$this -> street = $e['street'];
		$this -> street_number = $e['street_number'];
		//...

		$this -> coverimage = $e['coverimage'];
		$this -> otherimages = splitData($e['otherimages'],3);
		
		$this -> program = splitData($e['program']);
		$this -> description = splitData($e['description']);

		$this -> clienttype = $e['clienttype'];
		$this -> clientname = $e['clientname'];
		$this -> projecttype = $e['projecttype'];
		$this -> competitionwon = $e['competitionwon'];
		$new_nmb = $e['newnumber'];
		while (strlen($nmb) < 3) {
			$new_nmb = "0" . $new_nmb;
		}
		$this -> newnumber = $new_nmb;
		$this -> interventiontype = $e['interventiontype'];
		$this -> status = $e['status'];
		$this -> category = $e['category'];
		
		$this -> scale = $e['scale'];
		$this -> area_gross = $e['area_gross'];
		$this -> area_weighted = $e['area_weighted'];
		$this -> eelevel = $e['eelevel'];
		$this -> eevalue = $e['eevalue'];
		$this -> eeloldvalue = $e['eeloldvalue'];
		$this -> eeloldunit = $e['eeloldunit'];
		$this -> budget_estimate = $e['budget_estimate'];
		$this -> budget_final = $e['budget_final'];
		$this -> budget_type = $e['budget_type'];
		
		$this -> consultants = splitData($e['consultants'],3);
		$this -> teamUP = $e['teamUP'];
		
		$this -> awards = splitData($e['awards'],2);
		$this -> publications = splitData($e['publications']);
		
		$this -> timebudget_estimate = $e['timebudget_estimate'];
		$this -> timebudget_final = $e['timebudget_final'];
		$this -> internalbudget_estimate = $e['internalbudget_estimate'];
		$this -> internalbudget_final = $e['internalbudget_final'];
	}

	/**
	 *Retrieves one project from the database based on a passed id
	 *
	 * @param string $id project id to fetch
	 * @return array array with results
	 */
	function retrieveProjectById($id) {
		$sql = "SELECT number, name, coverimage, otherimages, program, startdate, enddate, countrycode, city, clienttype, date, city_pcode, street, street_number, clientname, description, projecttype, competitionwon, newnumber, status, interventiontype, category, scale, area_gross, area_weighted, eelevel, eevalue, eeloldvalue, eeloldunit, budget_estimate, budget_final, budget_type, consultants, teamUP, awards, publications, timebudget_estimate, timebudget_final, internalbudget_estimate, internalbudget_final FROM projects WHERE id=? LIMIT 1";
		$stmt = $this -> db -> prepare($sql);
		$stmt -> execute(array($id));

		//save the returned array
		$e = $stmt -> fetch();
		$stmt -> closeCursor();

		return $e;
	}

	/**
	 * Method for saving or updating a new project
	 *
	 * @param array $p The $_POST superglobal
	 */
	public function updateProject($p) {
			
		/*
		 * PROCESS DATA
		 */
		//handle date
		$date = $p['sortingdate_Y']."-".$p['sortingdate_M']."-".$p['sortingdate_D'];
		
		//handle city
		$city = joinData($p['location_city']);

		//handle cover image
		$img_path = array(NULL, NULL);
		//echo realpath("../images/original/"); exit;
		//if clause prevent execution if project was edited(id exists) and no new image was added (image is not empty)
		if (empty($p['id']) || $_FILES['cover_image']['name'] != '') {
			try {
				//Isstantiate the class and set a save path
				$img = new ImageHandler("/images/original/", "/images/resized/");
				//Process the file and store the returned path
				$img_path = $img -> processUploadedImage($_FILES['cover_image']);
			} catch (Exception $e) {
				//if an error occurred, output your custom error message
				die($e -> getMessage());
			}
		}
		
		//handle other images
		$otherimagesA = array();
		for($i=1; $i < count($_FILES['other_images']['name']); $i++){
			try {
				$paths = NULL;
				//if filefield not empty process the new image
				if($_FILES['other_images']['name'][$i] != ''){
					//Isstantiate the class and set a save path
					$img = new ImageHandler("/images/original/","/images/resized/");
					//rarrange array
					$file_array = array("name"=>$_FILES['other_images']['name'][$i], "type"=>$_FILES['other_images']['type'][$i], "tmp_name"=>$_FILES['other_images']['tmp_name'][$i], "error"=>$_FILES['other_images']['error'][$i], "size"=>$_FILES['other_images']['size'][$i]);
					//Process the file and store the returned path
					$other_img_path = $img -> processUploadedImage($file_array);
					$paths = joinData($other_img_path);
				} 
				//if filefield is empty but there was already an image, update anyway
				else if($p['other_img_src'][$i] != "" && $p['other_img_src'][$i] != NULL){
					$paths = $p['other_img_src'][$i];
				}
				//generate rest of description if we got a path
				if($paths!=NULL){
					$img_descriptions = array($p['other_images_description_FR'][$i], $p['other_images_description_NL'][$i], $p['other_images_description_EN'][$i]);
					$img_description = joinData($img_descriptions); 
					array_push($otherimagesA, joinData(array($paths, $img_description)));
				}	
			 } catch (Exception $e){
			 	//if an error occurred, output your custom error message
				die($e -> getMessage());
			}			
		}
		//otherimage String structure: ( ((original image location, image resize 1 location, image resize 2 location, ...), (descriptionFR, descriptionNL, descriptionEN)) , ((original image location, image resize 1 location, image resize 2 location, ...), (descriptionFR, descriptionNL, descriptionEN)) , (name, (jobFR, jobNL, jobEN))) , ...)
		$otherimages = ($otherimagesA!=NULL) ? joinData($otherimagesA) : NULL;
		//echo $otherimages; exit;
		
		//handle intervention type
		$interventiontype = joinData($p['intervention_type']);
		
		//handle category
		$category = joinData($p['category']);
		
		//handle program
		$program = joinData(array($p['program_FR'], $p['program_NL'], $p['program_EN']));
		
		//handle description 
		$description = joinData(array(newl2br($p['description_FR']), newl2br($p['description_NL']), newl2br($p['description_EN'])));
		
		//handle consultants
		$consultantsA = array();
		for($i=1; $i < count($p['consultant_type']); $i++){
			if($p['consultant_name'][$i]!=""){
				array_push($consultantsA, joinData(array($p['consultant_name'][$i], $p['consultant_type'][$i])));
			}
		}
		//consultant String structure: ( (name, (jobFR, jobNL, jobEN)) , (name, (jobFR, jobNL, jobEN)) , ...)
		$consultant = ($consultantsA!=NULL) ? joinData($consultantsA) : NULL;
		
		//handle awards
		$awardsA = array();
		for($i=1; $i < count($p['awardsFR']); $i++){
			if($p['awardsFR'][$i]!="" || $p['awardsNL'][$i]!="" || $p['awardsEN'][$i]!=""){
				array_push($awardsA, joinData(array($p['awardsFR'][$i], $p['awardsNL'][$i], $p['awardsEN'][$i])));
			}
		}
		$awards = ($awardsA!=NULL) ? joinData($awardsA) : NULL;
		
		//handle publications
		$publicationsA = array();
		for($i=1; $i < count($p['publications']); $i++){
			if($p['publications'][$i]!=""){
				array_push($publicationsA, $p['publications'][$i]);
			}
		}
		$publications = ($publicationsA!=NULL) ? joinData($publicationsA) : NULL;

		/*
		 * UPDATE ENTRY
		 */
		//if an id was passed, edit the existing entry
		if (!empty($p['id'])) {
			$appendSQL ="";
			$appendSTMT = array();
			//check if new image was added, add some stuff to the query if it is
			if ($_FILES['cover_image']['name'] != ''){
				$appendSQL .= ", coverimage_original=?, coverimage=?";
				$appendSTMT = array($img_path[0], $img_path[1]);
			}
			//add some stuff to the otherimage query if it is
			$appendSQL .= ", otherimages=?";
			array_push($appendSTMT, $otherimages);

			//prepare the sql query and append a part if we're adding images
			$sql = "UPDATE projects SET number=?, name=?, program=?, startdate=?, enddate=?, countrycode=?, city=?, clienttype=?, date=?, city_pcode=?, street=?, street_number=?, clientname=?, description=?, projecttype=?, competitionwon=?, newnumber=?, status=?, interventiontype=?, category=?, scale=?, area_gross=?, area_weighted=?, eelevel=?, eevalue=?, eeloldvalue=?, eeloldunit=?, budget_estimate=?, budget_final=?, budget_type=?, consultants=?, teamUP=?, awards=?, publications=?, timebudget_estimate=?, timebudget_final=?, internalbudget_estimate=?, internalbudget_final=?".$appendSQL." WHERE id=? LIMIT 1";

			if ($stmt = $this -> db -> prepare($sql)) {
				$A = array_merge(array_merge(array($p['projectNmb'], $p['name'], $program, $p['date_start'], $p['date_end'], $p['country_code'], $city, $p['client_type'], $date,$p['location_pcode'], $p['location_street'], $p['location_number'], $p['client_name'], $description, $p['project_type'], $p['competition_won_select'], $p['new_projectNmb'], $p['project_status'], $interventiontype, $category, $p['scale'], $p['gross_surface_area'], $p['weighted_surface_area'], $p['ef_level'], $p['ef_value'], $p['eeloldvalue'], $p['eeloldunit'], $p['b_e'], $p['b_f'], $p['budget_type'], $consultant, $p['team_up'], $awards, $publications, $p['timebudget_estimate'], $p['timebudget_final'], $p['ib_e'], $p['ib_f']), $appendSTMT),array($p['id']));
				$stmt -> execute($A);
				$stmt -> closeCursor();
				
				//get the ID of the entry that was just edited
				$this -> id = $p['id'];
			}
		}
		//save the entry into the database
		else {
			$sql = "INSERT INTO projects (number, name, coverimage_original, coverimage, otherimages, program, startdate, enddate, countrycode, city, clienttype, date, city_pcode, street, street_number, clientname, description, projecttype, competitionwon, newnumber, status, interventiontype, category, scale, area_gross, area_weighted, eelevel, eevalue, eeloldvalue, eeloldunit, budget_estimate, budget_final, budget_type, consultants, teamUP, awards, publications, timebudget_estimate, timebudget_final, internalbudget_estimate, internalbudget_final) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
			if ($stmt = $this -> db -> prepare($sql)) {
				$stmt -> execute(array($p['projectNmb'], $p['name'], $img_path[0], $img_path[1], $otherimages, $program, $p['date_start'], $p['date_end'], $p['country_code'], $city, $p['client_type'], $date, $p['location_pcode'], $p['location_street'], $p['location_number'], $p['client_name'], $description, $p['project_type'], $p['competition_won_select'], $p['new_projectNmb'], $p['project_status'], $interventiontype, $category, $p['scale'], $p['gross_surface_area'], $p['weighted_surface_area'], $p['ef_level'], $p['ef_value'], $p['eeloldvalue'], $p['eeloldunit'], $p['b_e'], $p['b_f'], $p['budget_type'], $consultant, $p['team_up'], $awards, $publications, $p['timebudget_estimate'], $p['timebudget_final'], $p['ib_e'], $p['ib_f']));
				$stmt -> closeCursor();

				//get the ID of the entry that was just saved
				$id_obj = $this -> db -> query("SELECT LAST_INSERT_ID()");
				//gets unique ID generated for last entry into database
				$new_id = $id_obj -> fetch();
				//pass data to the $id variable (array with the id in index [0])
				$id_obj -> closeCursor();
				$this -> id = $new_id[0];
			}
		}
		return $this -> id;
	}

	/**
	 * Formats the project data into an html object that can be printed on a page
	 *
	 * @param boolean $checked is the includebox checked?
	 * @param boolean $showImages Option to hide the images of a project
	 * @param boolean $showMenu 
	 * @param boolean $showIncludeCheckbox
	 * @param boolean $showIncludeCheckbox
	 */
	public function formatProjectData($checked, $showImages = TRUE, $showMenu = FALSE, $showIncludeCheckbox = TRUE) {
		//DATA PREPARATION
		//include predefined label values
		include 'label_values.inc.php';
		include 'parameter_values.inc.php';

		//GENERATE OUTPUT HTML
		$formattedProject = "";
		$Pid = $this->id;
		//generate include checkbox
		if ($showIncludeCheckbox) {
		$ch = ($checked==true) ? "checked":"";
		$formattedProject .= <<<INCLUDECB
		<input id="include_$Pid" class="include_checkbox reversecheckbox" type="checkbox" name="include_checkbox[]" value="$Pid" onchange="toggleClass('include_$Pid','wrapper_$Pid','opacity')" $ch/>
		<label for="include_$Pid"></label>
		<input id='includeHidden_$Pid' class="reversecheckboxHidden" type='hidden' value="$Pid" name="include_checkboxHidden[]" /> 

INCLUDECB;
		}
		
		//generate project wrapper
		$formattedProject .= <<<WRAPPERSTART
		<div id="wrapper_$Pid" class="project_wrapper">
		<div class="list_item">
WRAPPERSTART;

		//generate image
		if ($showImages) {
			//build the image src. built ass the app folder + location (trim /projectListing from old data)
			$img = APP_FOLDER.str_replace("/projectListing", "", $this -> coverimage);
			//add coverimage
			$formattedProject .= <<<IMG
		<div class="img_column">
			<img src='$img'/>	
			<span class="description">&#8593; cover image</span>
IMG;

			if($this->otherimages != NULL && $this->otherimages[0] != ""){
				//add space
				$formattedProject .= <<<SP
				</br></br></br>
				<span class="description">&#8595; other images</span>
				</br>
SP;
				for($i = 0; $i<count($this->otherimages); $i++){
					$other_src = splitData(splitData($this->otherimages[$i])[0])[1];
					//add other images
					$formattedProject .= <<<OTHERIMG
			<img class="otherimage" src='$other_src'/>
OTHERIMG;
				}
			}
			
			//close div
			$formattedProject .= <<<IMGCLOSE
		</div>
IMGCLOSE;
		}

		//generate project wrapper
		$formattedProject .= <<<TEXTWRAPPERSTART
		<div class="text_area">
TEXTWRAPPERSTART;

		//generate title
		$number = ($this->competitionwon == "yes") ? $this -> newnumber : $this -> number;
		$name = $this -> name;

		$formattedProject .= <<<TITLE
		<div class="title">
			<h1><span>$number</span>&nbsp;&nbsp;<span>$name</span></h1>						
		</div>
TITLE;

		//generate subtitle
		$won = ($this->competitionwon == "yes") ? " - ".$competitionwon_labels[$this->L] : "";
		$competition = ($this->projecttype == $projecttypes[1]) ? splitData($this->projecttype)[$this->L].$won.", " : "";
		$endD = (count(splitData($this->enddate))>1) ? splitData($this->enddate)[$this->L] : $this->enddate;
		$location = $this -> city[$this->L] . " - " . $this -> countrycode;
		$date = $this -> startdate. " - " . $endD;
		$program = $this -> program[$this->L];

		$formattedProject .= <<<SUBTITLE
		<div class="subtitle">
			<span class="gray italic">$competition $location,&nbsp;&nbsp;$date</span>						
		</div>
		<div class="program">
			<p>
			$program
			</p>
		</div>
SUBTITLE;
		
		//get datalist
		$dl = $this->generateDatalist();
		//loop through results
		for($i = 0; $i < count($dl); $i++) {
			$c = array_slice($dl, $i);
			$Hkey = key($c);
			$Hvalue = strip_tags(array_values($c)[0], "<a>");
			
			$formattedProject .= <<<LIST
		 <div class="text_wrapper">
		 	<div class="key italic">
				$Hkey
		 	</div>
		 	<div class="value italic gray">
		 		$Hvalue
			</div>
		 </div>
LIST;
		}

		//end wrappers
		$formattedProject .= <<<TEXTWRAPPERSTART
		</div>
		</div>
		</div>
TEXTWRAPPERSTART;

		if ($showMenu) {
			//administrative links
			$Lid = $this->id;

			$formattedProject .= <<<LINKS
		<div class="menu_wrapper">
			<div class="menu">
				<ul>
					<li><a class="button" href="./admin.php?id=$Lid">Edit</a></li>
					<li><a class="button delete" href="./inc/update.inc.php?action=project_delete&id=$Lid">Delete</a></li>
				</ul>
			</div>
		</div>
LINKS;
		}

		return $formattedProject;
	}

	/**
	 * Method for deleting a project
	 *
	 * @param string $id The id of the priject to delete
	 */
	public function deleteProject($id) {
		$sql = "DELETE FROM projects WHERE id=? LIMIT 1";
		if ($stmt = $this -> db -> prepare($sql)) {
			//Execute the command, free used memory, and return true
			$stmt -> execute(array($id));
			$stmt -> closeCursor();
			return TRUE;
		} else {
			//if something went wrong return false
			return FALSE;
		}
	}
	/**
	 * Method for setting the language
	 *
	 * @param string $language desired language 
	 * @return array Array with all formatted datapoints to list for a project
	 */
	public function setLanguage($language = "FR"){
		//determine language
		switch ($language) {
			case 'NL' :
				//selector for arrays containing 3 languages (0= french, 1= Dutch, 2= English)
				$this -> L = 1;
				break;

			case 'EN' :
				$this -> L = 2;
				break;

			default :
				$this -> L = 0;
				break;
		}
	}	
	
	/**
	 * Method for generating an array with each formatted dataelement for a project
	 * 
	 * By default this method returns an unfiltered list, but for specific listings it will filter
	 * rresults that are empty or have specific values
	 *
	 * @param string $listtype $name for the type of datalist wanted (values = data, projectlist)
	 * @return array Array with all formatted datapoints to list for a project
	 */
	public function generateDatalist($listtype = "data"){
		//include predefined label values
		include 'label_values.inc.php';
		include 'parameter_values.inc.php';
		
		//PROJECT TYPE
		$Ptype = (count(splitData($this->projecttype))>1) ? splitData($this->projecttype)[$this->L]: "/";
		
		//STATUS
		$Pstatus = (count(splitData($this->status))>1) ? splitData($this -> status)[$this->L] : "/";
		
		//INTERVENTIONTYPE
		//check whether one or more types were checked, make an array with types
		$itypes = (count(splitData(splitData($this -> interventiontype)[0])) > 1) ? splitData($this -> interventiontype) : array($this -> interventiontype);
		//returnstring
		$Pinterventiontypes ="";
		if($itypes[0]!=NULL){
			foreach ($itypes as &$type) {
				//split by language and concat to returnstring
				$Pinterventiontypes .= splitData($type)[$this->L].", ";
			}
		}
		//if no categories were selected, return slash. else trim last comma from slash
		$Pinterventiontypes = ($this->interventiontype != "") ? $Pinterventiontypes = rtrim($Pinterventiontypes, ", ") : "/";		
		
		//CATEGORIES
		$ctypes = (count(splitData(splitData($this -> category)[0])) > 1) ? splitData($this -> category) : array($this -> category);
		$Pcategories ="";
		//define first level categories
		$Cheaders = array($categories[0], $categories[7], $categories[10], $categories[14], $categories[18]);
		if($ctypes[0]!=NULL){
			foreach ($ctypes as &$type) {
				$Pcategories .= (in_array($type, $Cheaders)) ? ucfirst(splitData($type)[$this->L])."</br>" : "-&nbsp;&nbsp;".splitData($type)[$this->L]."</br>";
			}
		}
		$Pcategories = ($this->category != "") ? $Pcategories = rtrim($Pcategories, "</br>") : "/";
		
		//ADRESS
		$address = ($this->street !="" || $this->street_number != "") ? rtrim($this -> city[$this->L]." - ".$this->city_pcode."</br>".$this->street." ".$this->street_number, "</br> ") : "/";
		
		//CLIENT
		$client =  ($this -> clientname != "") ? $this -> clientname." (".splitData($this->clienttype)[$this->L].")" : splitData($this->clienttype)[$this->L]; 
		
		//DESCRIPTION
		$description = (count($this->description)>1 && $this -> description[$this->L] != "") ? $this -> description[$this->L] : "/";
		
		//SCALE
		$Pscale = ($this->scale != "") ? splitData($this -> scale)[$this->L] : "/";
		
		//SURFACE AREA
		$Parea = ($this->area_weighted == NULL || $this->area_weighted == 0) ? number_format($this->area_gross, 0, ',', '.')." m² (".$gsa_labels[$this->L].")" : number_format($this->area_gross, 0, ',', '.')." m² (".$gsa_labels[$this->L].") - ".number_format($this->area_weighted, 0, ',', '.')." m² (".$wsa_labels[$this->L].")" ;
		$Parea = (($this->area_gross != "")&&($this->area_weighted != "")) ? $Parea : "/";
		
		//EELEVEL
		if(count(splitData($this -> eelevel)) > 1){
			$Peelevel = ($this->eevalue == NULL || $this->eevalue == 0) ? splitData($this->eelevel)[$this->L] : splitData($this->eelevel)[$this->L]." - ".$this->eevalue." ".$eev_labels[$this->L] ; 
			$Peelevel = (($this->eevalue == NULL || $this->eevalue == 0) && ($this->eeloldvalue !=0)) ? splitData($this->eelevel)[$this->L]." - ".$this->eeloldunit." ".$this->eeloldvalue : $Peelevel;
		} else{
			$Peelevel = "/";
		}
		
		//BUDGET
		if ($this->budget_final != NULL && $this->budget_estimate != NULL){
			$Pbudget = ($this->budget_estimate != 0 && $this->budget_final == 0) ? number_format($this->budget_estimate, 0, ',', '.')." &#128; (".$be_labels[$this->L].")" : number_format($this->budget_final, 0, ',', '.')." &#128; (".$bf_labels[$this->L].")" ;
			$Pbudget = ($this->budget_estimate != 0 && $this->budget_final != 0) ? number_format($this->budget_estimate, 0, ',', '.')." &#128; (".$be_labels[$this->L].") - ".number_format($this->budget_final, 0, ',', '.')." &#128; (".$bf_labels[$this->L].")" : $Pbudget; 
			//if budget is confidential, hide the value
			$Pbudget = ($this->budget_type == $budgettypes[2]) ? splitData($budgettypes[2])[$this->L] : $Pbudget;
		}
		//if budget is honorary, change the label
		$Pbudget_label = ($this->budget_type == $budgettypes[1]) ? $bt_labels[$this->L]." (".splitData($budgettypes[1])[$this->L].")" : $bt_labels[$this->L];
		$Pbudget = (($this->budget_final != "")&&($this->area_weighted != "")) ? $Pbudget : "/";
		
		//TEAM UP
		$PteamUP = ($this->teamUP != "" && $this->teamUP != NULL) ? $this -> teamUP : "/";
		
		//CONSULTANTS
		if($this->consultants[0] != NULL){
			$Pconsultants = "";
			for($i=0;$i<count($this->consultants);$i++){
				$Pname = splitData($this->consultants[$i])[0];
				$Pjob = splitData(splitData($this->consultants[$i])[1])[$this->L];
				$Pconsultants.=$Pname." (".$Pjob.") </br>";
			}
			$Pconsultants = rtrim($Pconsultants, "</br>");
		} else{
			$Pconsultants = "/";
		}
		
		//AWARDS
		if($this->awards[0] != NULL){
			$Pawards = "";
			for($i=0;$i<count($this->awards);$i++){
				$Paw = splitData($this->awards[$i])[$this->L];
				$Pawards.=$Paw."</br>";
			}
			$Pawards = rtrim($Pawards, "</br>");
		} else{
			$Pawards = "/";
		}
		
		//PUBLICATIONS
		if($this->publications[0] != NULL){
			$Ppublications = "";
			for($i=0;$i<count($this->publications);$i++){
				$Ppublications.=$this->publications[$i]."</br>";
			}
			$Ppublications = rtrim($Ppublications, "</br>");
		} else{
			$Ppublications = "/";
		}
		
		//TIMEBUDGET
		if ($this->timebudget_estimate != NULL && $this->timebudget_final != NULL){
			$Ptimebudget = ($this->timebudget_estimate != 0 && $this->timebudget_final == 0) ? number_format($this->timebudget_estimate, 0, ',', ' ')." h (".$be_labels[$this->L].")" : number_format($this->timebudget_final, 0, ',', ' ')." h (".$bf_labels[$this->L].")" ;
			$Ptimebudget = ($this->timebudget_estimate != 0 && $this->timebudget_final != 0) ? number_format($this->timebudget_estimate, 0, ',', ' ')." h (".$be_labels[$this->L].") - ".number_format($this->timebudget_final, 0, ',', ' ')." h (".$bf_labels[$this->L].")" : $Ptimebudget; 
		}
		$Ptimebudget = (($this->timebudget_estimate == "" || $this->timebudget_estimate == 0)&&($this->timebudget_final == "" || $this->timebudget_final == 0)) ? "/" : $Ptimebudget;
		
		//INTERNAL BUDGET
		if ($this->internalbudget_estimate != NULL && $this->internalbudget_final != NULL){
			$Pinternalbudget = ($this->internalbudget_estimate != 0 && $this->internalbudget_final == 0) ? number_format($this->internalbudget_estimate, 0, ',', '.')." &#128; (".$be_labels[$this->L].")" : number_format($this->internalbudget_final, 0, ',', '.')." &#128; (".$bf_labels[$this->L].")" ;
			$Pinternalbudget = ($this->internalbudget_estimate != 0 && $this->internalbudget_final != 0) ? number_format($this->internalbudget_estimate, 0, ',', '.')." &#128; (".$be_labels[$this->L].") - ".number_format($this->internalbudget_final, 0, ',', '.')." &#128; (".$bf_labels[$this->L].")" : $Pinternalbudget; 
		}
		$Pinternalbudget = (($this->internalbudget_estimate == "" || $this->internalbudget_estimate == 0)&&($this->internalbudget_final == "" || $this->internalbudget_final == 0)) ? "/" : $Pinternalbudget;
		
		switch ($listtype) {
			case 'projectlist':
				//MAKE DATALIST ARRAY WITH ONLY THE VALUES NEEDED FOR THE PROJECT LIST 
				//omit empty values ("/"), and specific values we dont want to show
				$c = array();
				//adress
				$c = ($address != "/") ? pushKeyValue($c, $address_labels[$this->L], $address) : $c;
				//client
				$c = ($client != "/") ? pushKeyValue($c, $cl_labels[$this->L], $client) : $c;
				//ptype
				$c = ($Ptype != "/") ? pushKeyValue($c, $pt_labels[$this->L] , $Ptype) : $c;
				//interventiontype
				$c = ($Pinterventiontypes != "/") ? pushKeyValue($c, $it_labels[$this->L] , $Pinterventiontypes) : $c;
				//status
				$c = ($Pstatus != "/") ? pushKeyValue($c, $st_labels[$this->L] , $Pstatus) : $c;
				//scale
				$c = ($Pscale != "/") ? pushKeyValue($c, $sc_labels[$this->L] , $Pscale) : $c;
				//area
				$c = ($Parea != "/") ? pushKeyValue($c, $sa_labels[$this->L] , $Parea) : $c;
				//eelevels: omit if empty or if just conform to EPB standards
				//echo $this->name."---------------".trim($this->eelevel)."------------------".trim($eelevels[3]);exit;
				$c = (trim($this->eelevel) !== trim($eelevels[3]) && $Peelevel != "/") ? pushKeyValue($c, $eel_labels[$this->L], $Peelevel) : $c;
				//budget
				$c = ($Pbudget != "/") ? pushKeyValue($c, $Pbudget_label , $Pbudget) : $c;
				//team UP
				$c = ($PteamUP != "/") ? pushKeyValue($c, $tup_labels[$this->L] , $PteamUP) : $c;
				//consultants
				$c = ($Pconsultants != "/") ? pushKeyValue($c, $cons_labels[$this->L] , $Pconsultants) : $c;
				//awards
				$c = ($Pawards != "/") ? pushKeyValue($c, $awards_labels[$this->L] , $Pawards) : $c;
				//publications
				$c = ($Ppublications != "/") ? pushKeyValue($c, $publications_labels[$this->L] , $Ppublications) : $c;
				//print_r($c);exit;
				break;
			
			default:
				//MAKE DATALIST ARRAY WITH ALL VALUES
				$c = array($sortdate_labels[$this->L] => $this->sortdate, 
				$st_labels[$this->L] => $Pstatus,
				$pt_labels[$this->L] => $Ptype,
				$it_labels[$this->L] => $Pinterventiontypes,
				$ca_labels[$this->L] => $Pcategories,
				$address_labels[$this->L] => $address, 
				$cl_labels[$this->L] => $client,
				$description_labels[$this->L] => $description,
				$sc_labels[$this->L] => $Pscale,
				$sa_labels[$this->L] => $Parea,
				$eel_labels[$this->L] => $Peelevel,
				$Pbudget_label => $Pbudget,
				$tup_labels[$this->L] => $PteamUP,
				$cons_labels[$this->L] => $Pconsultants,
				$awards_labels[$this->L] => $Pawards,
				$publications_labels[$this->L] => $Ppublications,
				$tbu_labels[$this->L] => $Ptimebudget,
				$ibu_labels[$this->L] => $Pinternalbudget);
				
				break;
		}
		
		return $c;
	}

}
?>
