<?php
//include necessary files
include_once './inc/functions.inc.php';
include_once './inc/db.inc.php';
include_once './inc/project.inc.php';
include_once './inc/parameter_values.inc.php';

$db = new PDO(DB_INFO, DB_USER, DB_PASS);

//default page title
$pagetitle = "Add a project";

//define all parameters and their default values;
$project = new Project();

//edited project?
$edit = "false";

//code for handeling editing
if (isset($_GET['id'])) {
	//get data for the post we're editing'
	$id = htmlentities(strip_tags($_GET['id']));
	$project -> updateParameters($id);

	//edit the page title
	$pagetitle = "Edit \"" . $project -> name . "\"";

	$edit = "true";
}

//check if logged in and logged in as admin or editor. If not, don't render page
if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == 1 && isset($_SESSION['usertype']) && ($_SESSION['usertype'] == "admin" || $_SESSION['usertype'] == "editor")){
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
	<!-- HEAD START -->
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="icon" type="image/ico" href="images/up.ico">
		<title>input form</title>
		<meta name="author" content="Thomas" />
		<!-- CSS LINKS -->
		<link rel="stylesheet" type="text/css" media="all" href="css/style.css" />
		<!-- JAVASCRIPT LINKS -->
		<script src="js/jquery-1.11.1.min.js" type="text/javascript"></script>
		<script type="text/javascript" src="js/script.js"></script>
		<script type="text/javascript" src="js/charcount.js"></script>
		<script type="text/javascript">
									//calls a function to check for errors, passes arrays with elements to check
			function checkInputForm(edit) {
				//array of all inputs to check for 'empty' errors [id,name]
				var checkEmpty = new Array;
				//if editing, omit the check for empty images
				if (edit) {
					checkEmpty = [["projectNmb", "Project number"], ["name", "Name"], ["country_code", "Country Code"], ["program_FR", "Program (French)"], ["program_NL", "Program (Dutch)"], ["program_EN", "Program (English)"], ["location_city_FR", "Location City (FR)"], ["location_city_NL", "Location City (NL)"], ["location_city_EN", "Location City (EN)"], ["gross_surface_area", "Gross Surface Area"]];
				} else{
					checkEmpty = [["projectNmb", "Project number"], ["name", "Name"], ["cover_image", "Cover Image"], ["country_code", "Country Code"], ["program_FR", "Program (French)"], ["program_NL", "Program (Dutch)"], ["program_EN", "Program (English)"], ["location_city_FR", "Location City (FR)"], ["location_city_NL", "Location City (NL)"], ["location_city_EN", "Location City (EN)"], ["gross_surface_area", "Gross Surface Area"]];
				}

				// array of all inputs to check whether they are numerical errors [id,name]
				var checkNumerical = [["projectNmb", "Project number"], ["gross_surface_area", "Gross Surface Area"], ["weighted_surface_area", "Weighted Surface Area"], ["ef_value", "Energy Efficiency Value"], ["timebudget_estimate", "Timebudget (Estimate)"], ["timebudget_final", "Timebudget (Final)"], ["b_e", "Budget (Estimate)"], ["b_f", "Budget (Final)"], ["ib_e", "Internal budget (estimate)"], ["ib_f", "Internal Budget (final)"]];

				// array of all inputs to check whether they are bigger than the other [id1,id2, error message]
				var checkBiggerThan = [["date_start", "date_end", "The enddate should be later the the starting date"]];
				
				// array of all inputs to check filesize [id, name]
				var checkFilesize = [['cover_image', "Cover Image"]];
				//get all input other images (variable amount)
				var otherimagesIDs = document.getElementById("images").getElementsByClassName("inputimage");
				var ids ="";
				for(var i=1; i < otherimagesIDs.length; i++){
					var appendArray = [otherimagesIDs[i].getAttribute("id"), "This Image"];
					checkFilesize.push(appendArray);
				}

				// array of all inputs to check valid date [id month, id day,id year, name]
				var checkValidDate = [['sortingdate_M', 'sortingdate_D', 'sortingdate_Y', "Sorting Date"]];

				//array for all "if X ihas value Y than Z can't be empty" errors [id Z, value Y, id X, errormessage]
				var checkIfThan = [["client_name", "<?php echo $clienttypes[1]; ?>", "client_type", "If the client is public, the client name is obligatory!"],
				["new_projectNmb", "yes", "competition_won_select", "If the competition was won, you must define a new number for the project"]];
				
				//array for all dual values where one of both needs to be filled in [id1, id2, errormeesage]
				var checkEither = [["b_e","b_f","You need to fill in at least one of the budget fields"]];
				
				//array for all dual values where one of both needs to be filled in [id labeldiv, id parentdiv, name]
				var checkEmptyCheckboxes = [["intervention_type","intervention_type_wrapper","Select at least one Intervention Type"],
				["category_type", "category_type_wrapper", "Select at least one Category"]];

				//execute form check  //checkEmpty
				return validateForm(true, checkEmpty, checkNumerical, checkBiggerThan, checkFilesize, checkValidDate, checkIfThan, checkEither, checkEmptyCheckboxes);
			}


			
			window.onload = function() {
				/*
				 * call charcount library
				 */
				charcount();
				
				/*
				 * augment native DOM function to allow deletion
				 * http://stackoverflow.com/questions/3387427/javascript-remove-element-by-id
				 */
				Element.prototype.remove = function() {
				    this.parentElement.removeChild(this);
				}
				
				NodeList.prototype.remove = HTMLCollection.prototype.remove = function() {
				    for(var i = 0, len = this.length; i < len; i++) {
				        if(this[i] && this[i].parentElement) {
				            this[i].parentElement.removeChild(this[i]);
				        }
				    }
				}
						
				/*
				 * calls swapSelect to hide/show elements based on selection
				 */
				
				//define the variables for each swapSelect function you want to call
					var s1 = ['project_type', '<?php echo $projecttypes[1]; ?>
					','competition_won', 'inline'];
					var s2 = ['competition_won_select', 'yes', 'new_projectnumber', 'inline'];
					//join all these variables in an array
					var list = [s1, s2];
					//loop through the array and call swapSelect for each value
					for (var i = 0; i < list.length; i++) {

					var s = document.getElementById(list[i][0]);
					var e1 = list[i][0];
					var e2 = list[i][1];
					var e3 = list[i][2];
					var e4 = list[i][3];

					s.setAttribute('onchange',"swapSelect('"+e1+"','"+e2+"','"+e3+"','"+e4+"')");
					}

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
				
					/*
					*Function to execute any JS code in checked checkboxes on windowload
					*/
					var inputs = document.getElementsByTagName("input");
					for (var i = 0; i < inputs.length; i++) {
						if (inputs[i].getAttribute('type') == 'checkbox') {
							if (inputs[i].hasAttribute('onchange')){
								inputs[i].onchange();
							}
						}
					}
				}

		</script>
		<!-- Date: 2014-08-04 -->
	</head>
	<!-- HEAD END -->
	<!-- BODY START -->
	<body>
		<div id="admin">
		<div id="content_wrapper">
			<div id="content">
		<div id="inputform_wrapper">
			<h1 class="red"><?php echo $pagetitle ?></h1>
			</br>
		
			<form class="inputform" id="inputform" name="addProject" method="post" action="./inc/update.inc.php" enctype="multipart/form-data" onsubmit="return checkInputForm(<?php echo $edit ?>)">
				<fieldset>

					<!--<legend>
					<span class="input_title">Add a project to the database</span>
					</legend>-->
					

					</br>
					<h1>Project Data</h1>
					<h4 class="subtitle">*required fields</h4>
					<hr>

					<!--Project Number-->
					<div class="input_wrapper">
						<label><span class="input_title"><?php echo ($project -> competitionwon == 'yes') ? "*Competition N°:" : "*Project N°:" ?></span></label>
						<div class="input"> 
							<input class="very_small big_text" id="projectNmb" type="text" name="projectNmb" maxlength="3" value="<?php echo $project->number ?>" />
							<span id="new_number_show" style="display:<?php echo ($project -> competitionwon == 'yes') ? "inline" : "none" ?>;">
								<span>&nbsp; &#x21e8; &nbsp;</span>
								<label><span style="color:gray" class="input_title">*Project N°: &nbsp;</span></label>
								<span class="big_text" id="new_number" style="color:gray"><?php echo $project -> newnumber ?></span>
							</span>
						</div>
					</div>

					<!--Project Name-->
					<div class="input_wrapper">
						<label><span class="input_title">*Name:</span></label>
						<div class="input">
							<input class="very_large big_text" id="name" type="text" name="name" maxlength="50" value="<?php echo htmlentities($project->name) ?>"/>	
							</br><span class="description"> "name of street / neighbourhood / institution / development" + "function (where applicable)"</span>	
							</br><span class="description">eg: "LOCHTEN Housing", "SEMENCE School"</span>	
						</div>
					</div>
					
					<hr>
					
					<!--Date period-->
					<div class="input_wrapper">
						<label><span class="input_title">*Display Date:</span></label>
						<div class="input">  
							from
							<select id="date_start" name="date_start">
								<?php
								//generates select options from the year 2000 to the current year +1
								for ($i = 2000; $i <= date("Y") + 1; $i++) {
									//check whether this year is the same as the default, if so add "selected"
									$startselected = ($i == $project -> startdate) ? "selected=\"selected\"" : "";
									echo "<option value=\"" . $i . "\" " . $startselected . ">" . $i . "</option>";
								}
								?>
							</select> to
							<select id="date_end" name="date_end">
								<?php
								//generates select options from the year 2000 to 15 years after the current year
								for ($i = 2000; $i <= date("Y") + 10; $i++) {
									$endselected = ($i == $project -> enddate) ? "selected=\"selected\"" : "";
									echo "<option value=\"" . $i . "\" " . $endselected . ">" . $i . "</option>";
								}
								?>
								<?php $endselected = (joinData(array("en cours","lopend","ongoing"))==$project->enddate) ? "selected=\"selected\"" : " " ?>
								<option value="<?php echo joinData(array("en cours", "lopend", "ongoing")); ?>" <?php echo $endselected; ?>>ongoing</option>
							</select> 
							</br>
							<span class="description">The period during which the project was active. This date will be displayed on the project listings</span>
						</div>
					</div>
					
					<!--Sorting Date-->
					<div class="input_wrapper">
						<label><span class="input_title">*Sorting Date:</span></label>
						<div class="input"> 
							<select id="sortingdate_Y" name="sortingdate_Y">
								<?php
								//generates select options from the year 2000 to 15 years after the current year
								for ($i = 2000; $i <= date("Y") + 1; $i++) {
									$endselected = ($i == $project -> sortingdate_Y) ? "selected=\"selected\"" : "";
									echo "<option value=\"" . $i . "\" " . $endselected . ">" . $i . "</option>";
								}
								?>
							</select> -
							<?php
							//generate array with all months
							createMonthSelectList("sortingdate_M", FALSE, $project -> sortingdate_M);
							?>
							- <select id="sortingdate_D" name="sortingdate_D">
								<?php
								//generates select options from the year 2000 to 15 years after the current year
								for ($i = 1; $i <= 31; $i++) {
									while (strlen($i) < 2) {
										$i = "0" . $i;
									}
									$endselected = ($i == $project -> sortingdate_D) ? "selected=\"selected\"" : "";
									echo "<option value=\"" . $i . "\" " . $endselected . ">" . $i . "</option>";
								}
								?>
							</select>						
							</br>
							<div class="description">(YYYY-MM-DD)  Projects will be ordered chronologicaly by this date. This date will not be displayed.</div>
							<div class="description">By default this date should be given the same value as the project's startdate, 
							but it can be changed to put it before or after other projects in the listing</div>
						</div>
					</div>
					
					<!--Location-->
					<div class="input_wrapper">
						<label><span class="input_title">*Location:</span></label>
						<div class="input"> 
							<!--</br> <span class="description">eg:"BE - Belgique", "FR - France"</span>-->
							Country Code:
							<input class="very_small" id="country_code" type="text" name="country_code" maxlength="2" value="<?php echo $project->countrycode?>"/>&nbsp;&nbsp;&nbsp;&nbsp;<span class="description">ref: <a href="http://countrycode.org/" target="_blank">"http://countrycode.org/"</a></span>
							</br></br>City (FR) 
							<input id="location_city_FR" type="text" name="location_city[]" maxlength="75" value=<?php echo $project->city[0] ?> />
							</br>City (NL)
							<input id="location_city_NL" type="text" name="location_city[]" maxlength="75" value=<?php echo $project->city[1] ?> />
							</br>City (EN)
							<input id="location_city_EN" type="text" name="location_city[]" maxlength="75" value=<?php echo $project->city[2] ?> />
							</br></br>Postal Code:
							<input class="very_small" id="location_pcode" type="text" name="location_pcode" maxlength="75" value="<?php echo $project->city_pcode?>" />&nbsp; Street:<input id="location_street" type="text" name="location_street" maxlength="75" value="<?php echo $project->street?>" /> &nbsp; N°:
							<input class="very_small"  id="location_number" type="text" name="location_number" maxlength="10" value="<?php echo $project->street_number?>" />		
						</div>
					</div>
					
					<hr>

					<!--Cover Image-->
					<div class="input_wrapper">
						<label><span class="input_title">*Cover Image:</span></label>
						<div class="input"> 
							<?php
							$img = APP_FOLDER."/images/noimage.jpg";
							//if you're editing, display an image
							if (isset($_GET['id'])) {
								$img = APP_FOLDER.str_replace("/projectListing", "", $project -> coverimage);
							}
								echo <<<COVERIMG
								<img id="coverimage_preview" src="$img"/>
COVERIMG;
							
								?>
							
							<div class="imageinput">
								<span class="description">Max filesize 2Mb</span>
								<input id="cover_image" type="file" name="cover_image" onchange="updatePreviewImage(this)"/>
							</div>
						</div>
					</div>
					
					<!--Other Images-->
					<div class="input_wrapper">
						<label><span class="input_title">Other Images:</span></label>
						<div class="input"> 
							<div id="images" class="copyablefield">
								<!--hidden empty inputfield that can be coppied-->
								<div id="image">
									<img class="otherimage" src="./images/noimage.jpg"/>
									<div class="imageinput">
										<span class="duplicate_counterimage"></span>
								 		<span class="description">Max filesize 2Mb</span>
										<input type="file" class="inputimage" name="other_images[]" onchange="updatePreviewImage(this)"/></br>
										<span class="description">Description (max.100 char)</span></br>
										<span class="description">FR: </span><input class="large" maxlength="100" type="text" name="other_images_description_FR[]" value=" " /></br>
										<span class="description">NL: </span><input class="large" maxlength="100" type="text" name="other_images_description_NL[]" value=" " /></br>
										<span class="description">EN: </span><input class="large" maxlength="100" type="text" name="other_images_description_EN[]" value=" " />
										<input type="hidden" name="other_img_src[]" value="" />
										
										<button type="button" class="thin_button" onclick="document.getElementById('image').remove();">
											Delete Image
										</button>
									</div>
									</br></br>
								</div>
								<!--load existing fields-->
								<?php
								//list existing images
								if(isset($_GET['id']) && $project->otherimages!= NULL && $project->otherimages[0]!=""){
									for ($i = 0; $i < count($project->otherimages); $i++) {
										
										$Pimg = splitData($project->otherimages[$i])[0];
										$Psrc = APP_FOLDER.str_replace("/projectListing", "", splitData($Pimg)[1]);
										$Pdescrition = splitData(splitData($project->otherimages[$i])[1]);
										$PdFR = ($Pdescrition[0]=="/") ? "": htmlentities($Pdescrition[0]);
										$PdNL = ($Pdescrition[1]=="/") ? "": htmlentities($Pdescrition[1]);	
										$PdEN = ($Pdescrition[2]=="/") ? "": htmlentities($Pdescrition[2]);
										
										//takes care of weir bug that refuses to diplay $i=2
										$ID = "image_".$i;									
												
										echo <<<EXISTINGIMG
									
									<div id="image_div_$i">
										<img class="otherimage" src="$Psrc"/>
									
										<span class="duplicate_counterimage"></span>
										<div class="imageinput">
											<span class="description">Max filesize 2Mb</span>
											<input id="$ID" class="inputimage" type="file" name="other_images[]" onchange="updatePreviewImage(this)"/></br>
											<span class="description">Description (max.100 char)</span></br>
											<span class="description">FR: </span><input maxlength="120" class="large" type="text" name="other_images_description_FR[]" value="$PdFR" /></br>
											<span class="description">NL: </span><input maxlength="120" class="large" type="text" name="other_images_description_NL[]" value="$PdNL" /></br>
											<span class="description">EN: </span><input maxlength="120" class="large" type="text" name="other_images_description_EN[]" value="$PdEN" />
											<input type="hidden" name="other_img_src[]" value="$Pimg" />
											
											<button type="button" class="thin_button" onclick="document.getElementById('image_div_$i').remove();">
												Delete Image
											</button>
										</div>
										</br></br>
									</div>
EXISTINGIMG;
									}
								}
								//or create empty field
								else{
									echo <<<INPUTIMG
									<div id="image_div_0">
										<img class="otherimage" src="./images/noimage.jpg"/>
										<div class="imageinput">	
											<span class="duplicate_counterimage"></span>
											<span class="description">Max filesize 2Mb</span>
											<input id="image_0" class="inputimage" type="file" name="other_images[]" onchange="updatePreviewImage(this)"/></br>
											<span class="description">Description (max.100 char)</span></br>
											<span class="description">FR: </span><input maxlength="120" class="large" type="text" name="other_images_description_FR[]" value=" " /></br>
											<span class="description">NL: </span><input maxlength="120" class="large" type="text" name="other_images_description_NL[]" value=" " /></br>
											<span class="description">EN: </span><input maxlength="120" class="large" type="text" name="other_images_description_EN[]" value=" " />
											<input type="hidden" name="other_img_src[]" value="" />
											
											<button type="button" class="thin_button" onclick="document.getElementById('image_div_0').remove();">
												Delete Image
											</button>
										</div>
										</br></br>
									</div>
INPUTIMG;
								}
								?>
								<div id="ins_img"></div>
							</div>
							</br>
							<button type="button" id="add_contractor" onclick="duplicateElement('images','image','ins_img',8)">
								Add Image
							</button>
						</div>
					</div>
					
					<hr>
					
					<!--Program description-->
					<div class="input_wrapper">
						<label><span class="input_title">*Program:</span></label>
						<div class="input">  
							<div class="description">Short description of the program. (max characters: 130)</div>
							<div class="description">eg: "Quartier durable comprenant 55 logements neufs, 3 logements 
								pour étudiants, 10 logements habitats groupés et 8 logements d’insertion sociale. "</div></br> FR
							<br>
							<textarea id="program_FR" name="program_FR" maxlength="130" cols="60" rows="2"><?php echo $project->program[0] ?></textarea>
							<br>
							NL
							<br>
							<textarea id="program_NL" name="program_NL" maxlength="130" cols="60" rows="2"><?php echo $project->program[1] ?></textarea>
							<br>
							EN
							<br>
							<textarea id="program_EN" name="program_EN" maxlength="130" cols="60" rows="2"><?php echo $project->program[2] ?></textarea>
							<br>
						</div>
					</div>
					
					<!--Detailed Description-->
					<div class="input_wrapper">
						<label><span class="input_title">Detailed description:</span></label>
						<div class="input"> 
							<span class="description">Detailed description of the project. (max characters: 1800)</span>
							<br>
							</br>
							FR
							<br>
							<textarea name="description_FR" maxlength="1800" cols="60" rows="15"><?php echo br2newl($project->description[0]) ?></textarea>
							<br>
							NL
							<br>
							<textarea name="description_NL" maxlength="1800" cols="60" rows="15"><?php echo br2newl((count($project -> description) > 1) ? $project -> description[1] : ""); ?></textarea>
							<br>
							EN
							<br>
							<textarea name="description_EN" maxlength="1800" cols="60" rows="15"><?php echo br2newl((count($project -> description) > 1) ? $project -> description[2] : ""); ?></textarea>
							<br>
						
						</div>
					</div>
					
					<hr>
					
					<!--Client -->
					<div class="input_wrapper">
						<label><span class="input_title">*Client:</span></label>
						<div class="input"> 
							&nbsp;*Type:<?php
							createSelectList($clienttypes, "client_type", FALSE, $project -> clienttype);
							?>&nbsp;&nbsp;Name:
							<input id="client_name" type="text" name="client_name" maxlength="75" value="<?php echo $project -> clientname?>"/>
						</div>
					</div>

					<!--Project Type-->
					<div class="input_wrapper">
						<label><span class="input_title">*Project Type:</span></label> 
						<div class="input"> <?php
						//select projects
						createSelectList($projecttypes, "project_type", FALSE, $project -> projecttype);
							?>
							<span id="competition_won" style="display:<?php echo ($project -> projecttype == $projecttypes[1]) ? "inline" : "none" ?>;">&nbsp;competition won? 
								<?php
								//select competition won if won
								createSelectList(array("no", "yes"), "competition_won_select", FALSE, $project -> competitionwon);
								?>
								<span id="new_projectnumber" style="display:<?php echo ($project -> competitionwon == 'yes') ? "inline" : "none" ?>;">&nbsp;new project n°
									<input class="very_small" id="new_projectNmb" type="text" name="new_projectNmb" maxlength="3" value="<?php echo $project->newnumber ?>" />
								</span>						
							</span>		
						</div>
					</div>
					
					<!--Intervention Type:-->
					<div class="input_wrapper">
						<label><span class="input_title" id="intervention_type">*Intervention Type:</span></label> 
						<div class="input" id="intervention_type_wrapper"><?php
						createCheckboxes($interventiontypes, "intervention_type", $project -> interventiontype);
							?>
						</div>
					</div>
					
					<!--Project Status:-->
					<div class="input_wrapper">
						<label><span class="input_title">Project Status:</span></label>
						<div class="input"> <?php
						createSelectList($statusses, "project_status", FALSE, $project -> status);
							?>
						</div>
					</div>

					<!--Project Category-->
					<!--http://stackoverflow.com/questions/4631224/getting-multiple-checkboxes-names-ids-with-php-->
					<div class="input_wrapper" id="category_wrapper">
						<label><span class="input_title" id="category_type">*Category:</span></label>
						<div class="input" id="category_type_wrapper"> <?php
						//code generates a chechbox list based on the array (value,readable output)
						createCheckboxes($categories, "category", $project -> category);
						?>
						</div>
					</div>
					
					<hr>

					<!--Project Scale-->
					<div class="input_wrapper">
						<label> <span class="input_title">Scale:</span></label>
						<div class="input">
							<?php
							createSelectList($scales, "scale", FALSE, $project -> scale);
							?>
						</div>
					</div>

					<!--Surface area-->
					<div class="input_wrapper">
						<label><span class="input_title">*Surface Area:</span></label> 
						<div class="input">
							*gross value:
							<input id="gross_surface_area" type="text" name="gross_surface_area" maxlength="75" value="<?php echo $project->area_gross ?>"/>
							m² &nbsp; weighted value:
							<input id="weighted_surface_area" type="text" name="weighted_surface_area" maxlength="75" value="<?php echo $project->area_weighted ?>"/>
							m²
						</div>
					</div>

					<!--Energy efficiency-->
					<div class="input_wrapper">
						<label> <span class="input_title">*Energy Efficiency:</span></label>
						<div class="input">
							*level:
							<?php
							createSelectList($eelevels, "ef_level", FALSE, $project -> eelevel);
							?>							
							&nbsp; value:
							<input id="ef_value" type="text" name="ef_value" maxlength="10" value="<?php echo $project -> eevalue?>"/>
							kWh/m².year
							</br></br>
							old value:
							<input id="eeloldvalue" type="text" name="eeloldvalue" maxlength="10" value="<?php echo $project -> eeloldvalue?>"/>
							&nbsp; 
							<?php
							createSelectList($eeloldunits, "eeloldunit", FALSE, $project -> eeloldunit);
							?>	
						</div>
					</div>

					<!--Budget-->
					<div class="input_wrapper">
						<label><span class="input_title">*Budget:</span></label>
						<div class="input">
							estimate:
							<input id="b_e" type="text" name="b_e" maxlength="75" value="<?php echo $project -> budget_estimate ?>"/>
							€ &nbsp; final:
							<input id="b_f" type="text" name="b_f" maxlength="75" value="<?php echo $project -> budget_final ?>"/>
							€
							<div class="description">At least one (or both) budgets needs to be filled out.</div></br>
							budget type:
							<?php
							createSelectList($budgettypes, "budget_type", FALSE, $project -> budget_type);
							?>
						</div>
					</div>
					
					<hr>
					
					<!--Consultants-->
					<div class="input_wrapper">
						<label><span class="input_title">Consultants:</span></label>
						<div class="input">
							<div id="consultants" class="copyablefield">
								<!--hidden empty inputfield that can be coppied-->
								<div id="consultant">
									<span class="duplicate_counterconsultant"></span>
									name:
									<input type="text" name="consultant_name[]" maxlength="75" />
									&nbsp;
									role:									
									<?php
									createSelectList($consultanttypes, "consultant_type", TRUE);
									?>
									
									<button type="button" class="thin_button" onclick="document.getElementById('consultant').remove();">
										Delete Consultant
									</button>
									
									</br>
								</div>
								<!--load existing fields-->
								<?php
								//list existing consultants
								if($project->consultants!= NULL && $project->consultants[0]!=""){
									for ($i = 0; $i < count($project->consultants); $i++) {
										echo "<div id=\"consultant_div_".$i."\">";
										echo "<span class=\"duplicate_counterconsultant\"></span>";
										$Pname = splitData($project->consultants[$i])[0];
										echo "name: <input type=\"text\" name=\"consultant_name[]\" maxlength=\"75\" value=\"".$Pname."\" />";
										$Pjob = splitData($project->consultants[$i])[1];
										echo "&nbsp; role:";
										createSelectList($consultanttypes, "consultant_type", TRUE, $Pjob);
										echo <<<DB
										<button type="button" class="thin_button" onclick="document.getElementById('consultant_div_$i').remove();">
										Delete Consultant
										</button>
DB;
										echo "</br>";
										echo "</div>";
									}
								}
								//or create empty field 
								else{
									echo "<div id=\"consultant_first\">";
									echo "<span class=\"duplicate_counterconsultant\"></span>";
									echo "name: <input type=\"text\" name=\"consultant_name[]\" maxlength=\"75\" />";
									echo "&nbsp; role:";
									createSelectList($consultanttypes, "consultant_type", TRUE);
									echo <<<DB
										<button type="button" class="thin_button" onclick="document.getElementById('consultant_first').remove();">
										Delete Consultant
										</button>
DB;
									echo "</br></div>";
								}
								?>	
								<div id="ins_consultant"></div>
							</div> 
							</br>
							<button type="button" id="add_contractor" onclick="duplicateElement('consultants','consultant','ins_consultant',10)">
								Add Consultant
							</button>
							</br>
							</br>
						</div>
					</div>

					<!--Team UP-->
					<div class="input_wrapper">
						<label> <span class="input_title">Team UP:</span></label>
						<div class="input">				
							<textarea name="team_up" cols="60" maxlength="500" rows="2"><?php echo $project -> teamUP ?></textarea>
							<div class="description"> List teammember names, separated by commas </div> 			
						</div>
					</div>
					
					<hr>
					
					<!--Awards-->
					<div class="input_wrapper">
						<label><span class="input_title">Awards:</span></label>
						<div class="input">
							<div class="description">Grand Prix d'Architecture de Wallonie 2012</div></br>
							<div id="awards" class="copyablefield">
								<!--hidden empty inputfield that can be coppied-->
								<div id="award">
									<span class="duplicate_counteraward"></span>
									FR:
									<input class="large" type="text" name="awardsFR[]" maxlength="100" /></br>
									NL:
									<input class="large" type="text" name="awardsNL[]" maxlength="100" /></br>
									EN:
									<input class="large" type="text" name="awardsEN[]" maxlength="100" />
									
									<button type="button" class="thin_button" onclick="document.getElementById('award').remove();">
										Delete Award
									</button>
									
									</br></br>
								</div>
								<!--load existing fields-->
								<?php
								//list existing consultants
								if($project->awards!= NULL && $project->awards[0]!=""){
									for ($i = 0; $i < count($project->awards); $i++) {
										echo "<div id=\"award_div_".$i."\">";
										echo "<span class=\"duplicate_counteraward\"></span>";
										$Pname = splitData($project->awards[$i]);
										$PnameFR = ($Pname[0]=="/") ? "": $Pname[0];
										$PnameNL = ($Pname[1]=="/") ? "": $Pname[1];	
										$PnameEN = ($Pname[2]=="/") ? "": $Pname[2];
										echo "FR: <input class=\"large\" type=\"text\" name=\"awardsFR[]\" maxlength=\"100\" value=\"".$PnameFR."\" /></br>";
										echo "NL: <input class=\"large\" type=\"text\" name=\"awardsNL[]\" maxlength=\"100\" value=\"".$PnameNL."\" /></br>";
										echo "EN: <input class=\"large\" type=\"text\" name=\"awardsEN[]\" maxlength=\"100\" value=\"".$PnameEN."\" />";
										echo <<<DB
										<button type="button" class="thin_button" onclick="document.getElementById('award_div_$i').remove();">
										Delete Award
										</button>
DB;
										echo "</br></br>";
										echo "</div>";
									}
								}
								//or create empty field 
								else{
									echo "<div id=\"award_first\">";
									echo "<span class=\"duplicate_counteraward\"></span>";
									echo "FR: <input class=\"large\" type=\"text\" name=\"awardsFR[]\" maxlength=\"100\"  /></br>";
									echo "NL: <input class=\"large\" type=\"text\" name=\"awardsNL[]\" maxlength=\"100\"  /></br>";
									echo "EN: <input class=\"large\" type=\"text\" name=\"awardsEN[]\" maxlength=\"100\"  />";
									echo <<<DB
										<button type="button" class="thin_button" onclick="document.getElementById('award_first').remove();">
										Delete award
										</button>
DB;
									echo "</br></br></div>";
								}
								?>	
								<div id="ins_award"></div>
							</div> 
							</br>
							<button type="button" id="add_award" onclick="duplicateElement('awards','award','ins_award',5)">
								Add Award
							</button>
							</br>
							</br>
						</div>
					</div>
					
					<!--Publications-->
					<div class="input_wrapper">
						<label><span class="input_title">publications:</span></label>
						<div class="input">
							<div class="description">eg: 06/2014, "Corporate Architecture" - Chris van Uffelen, Braun publishing, p.54-57</div></br>
							<div id="publications" class="copyablefield">
								<!--hidden empty inputfield that can be coppied-->
								<div id="publication">
									<span class="duplicate_counterpublication"></span>
									<input class="large" type="text" name="publications[]" maxlength="150" />
									
									<button type="button" class="thin_button" onclick="document.getElementById('publication').remove();">
										Delete Publication
									</button>
									
									</br>
								</div>
								<!--load existing fields-->
								<?php
								//list existing consultants
								if($project->publications!= NULL && $project->publications[0]!=""){
									for ($i = 0; $i < count($project->publications); $i++) {
										echo "<div id=\"publication_div_".$i."\">";
										echo "<span class=\"duplicate_counterpublication\"></span>";
										$Pname = htmlspecialchars ($project->publications[$i]) ;
										echo "<input class=\"large\" type=\"text\" name=\"publications[]\" maxlength=\"150\" value=\"".$Pname."\" />";
										echo <<<DB
										<button type="button" class="thin_button" onclick="document.getElementById('publication_div_$i').remove();">
										Delete publication
										</button>
DB;
										echo "</br>";
										echo "</div>";
									}
								}
								//or create empty field 
								else{
									echo "<div id=\"publication_first\">";
									echo "<span class=\"duplicate_counterpublication\"></span>";
									echo "<input class=\"large\" type=\"text\" name=\"publications[]\" maxlength=\"150\"/>";
									echo <<<DB
										<button type="button" class="thin_button" onclick="document.getElementById('publication_first').remove();">
										Delete Publication
										</button>
DB;
									echo "</br></div>";
								}
								?>	
								<div id="ins_publication"></div>
							</div> 
							</br>
							<button type="button" id="add_publication" onclick="duplicateElement('publications','publication','ins_publication',14)">
								Add Publication
							</button>
							</br>
							</br>
						</div>
					</div>
					
					<hr>

					<!--Internal TimeBudget-->
					<div class="input_wrapper">
						<label> <span class="input_title">Internal Timebudget:</span></label>
						<div class="input">
							estimate:
							<input id="timebudget_estimate" type="text" name="timebudget_estimate" maxlength="75" value="<?php echo $project -> timebudget_estimate ?>" />
							h 
							&nbsp;
							final:
							<input id="timebudget_final" type="text" name="timebudget_final" maxlength="75" value="<?php echo $project -> timebudget_final ?>"/>
							h 
							</br>
							<span class="description"> Time spent by UP employees on the project.</span>
						</div>
					</div>

					<!--Internal Budget-->
					<div class="input_wrapper">
						<label><span class="input_title">Internal Budget:</span></label>
						<div class="input">
							estimate:
							<input id="ib_e" type="text" name="ib_e" maxlength="75" value="<?php echo $project -> internalbudget_estimate ?>"/>
							€
							&nbsp;
						    final:
							<input id="ib_f" type="text" name="ib_f" maxlength="75" value="<?php echo $project -> internalbudget_final ?>"/>
							€
							</br>
							<span class="description"> UP's budget for developing this project.</span>
						</div>
					</div>
					
					<hr>


					<!--id-->
					<input type="hidden" name="id" value="<?php echo $project->id ?>" />
					
					<!--submit-->
					<div id="submitproject">
						<!--<input type="submit" name="cancel" value="cancel"/>-->	
						<a class="button submitproject" href="index.php">CANCEL</a>			
						<input type="submit" name="submit" value="save project"/>					
					</div>
				</fieldset>
			</form>

		</div>
		</div>
		</div>
		</div>
	</body>
	<!-- BODY END -->
</html>
<?php } else{
	echo "ERROR: You are not authorized to see this page. <br> <br>Login on the home page with a valid admin or editor acount on the homepage";
} ?>