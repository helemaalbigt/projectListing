<?php

//get required files
require_once './inc/tfpdf.inc.php';
require_once './inc/db.inc.php';
require_once './inc/project.inc.php';
require_once './inc/functions.inc.php';

define('EURO',chr(128));

//extend TFPDF class
class PDF extends TFPDF {
		
	//PROJECT LAYOUT PARAMETERS
	//indent of text area of the project
	var $indent = 58;
	var $margin_bottom = 10;
	var $lineHeight = 3.75;
	var $min_project_height = 38;
	//title
	var $margin_bottom_subtitle = 3;
	var $margin_bottom_titleblock = 6;	
	var $programW = 120;
	//data
	var $space_between_data = 3.75;
	var $datakey_indent = 30;
	var $datakeyW = 93;
	//y height within current project. Used to estimate height of next project
	var $pZ = 0;
	
	//PAGE LAYOUT PARAMETERS
	//starting height from top for project content
	var $content_top_margin = 25;
	//total content height at whitch a new page is made
	var $page_cutoffpoint= 290;
	//current height of total page content
	var $Z = 0;
	
	// Page header
	function Header() {
		$listtitle = (isset($_GET['title'])) ? $_GET['title'] : "";
		// Logo
		$this -> Image('images/uplong.jpg', 12.5, 9, 57);
		//color
		$this -> SetTextColor(0,0,0);
		// Open Sans 15
		$this ->SetFont('OpenSans-R','',15);
		// Title
		$this -> Cell(0, 10, $listtitle, 0, 0, 'R');
		// Line break
		$this -> Ln(15);
	}

	// Page footer
	function Footer() {
		// Position at 1.5 cm from bottom
		$this -> SetY(-14);
		// OS 10
		$this ->SetFont('OpenSans-R','',10);
		// year
		$this -> SetTextColor(127,127,127);
		$this -> Cell(0, 0, date("Y"), 0, 0, 'L');
		// Page number
		$this -> SetY(-14);
		$this -> Cell(0, 0, $this -> PageNo() . '/{nb}', 0, 0, 'C');
		// Website
		$this -> SetY(-14);
		$this -> SetTextColor(127,127,127);
		$this -> Cell(0, 0, "www.                                    ", 0, 0, 'R');
		$this -> SetTextColor(227,0,0);
		$this -> Cell(0, 0, "urbanplatform         ", 0, 0, 'R');
		$this -> SetTextColor(127,127,127);
		$this -> Cell(0, 0, ".com", 0, 0, 'R');
	}
	
	//calc number of lines in string
	function NumberOfLines($font, $string, $width) {
		$this -> SetFont($font,'',9);
		$lines = 0;
		//split text into paragraphs
		$breaks = array("<br />", "<br>", "<br/>", "</br>");
		$string = str_ireplace($breaks, "%%%", $string);
		$paragraphs = explode("%%%", $string);
		//add n° of paragraphs
		$lines += count($paragraphs)-1;
		//go through paragraphs and count lines
		for( $i=0; $i<count($paragraphs); $i++){
			//if more than one paragraph, add a line per break
			$lines += floor( ceil($this -> GetStringWidth($paragraphs[$i])) / ($width+0));
			//echo ($this -> GetStringWidth($paragraphs[$i]) - 2)." ---- ".$width;exit;
		}
		
		return $lines;
	}
	
	//print projects
	function PrintProjectList() {
		//inititalize
		$this->AddPage();
		
		//get superglobals
		$sql = (isset($_SESSION['sql_pp'])) ? $_SESSION['sql_pp'] : "";
		
		//open database connection and store it
		$db = new PDO(DB_INFO, DB_USER, DB_PASS);
		
		//execute query
		$stmt = $db -> prepare($sql);
		$stmt -> execute();
		
		//add the top margin to total Z height
		$this->Z += $this->content_top_margin;
		//loop through results and print each project
		while ($row = $stmt -> fetch()) {
			//if the height at whitch the new project starts + the estimated height of the 
			//next project (pZ) exceeds the page height, cutoff
			if($this->Z + $this->GetProjectHeight($row['id']) > $this->page_cutoffpoint){
				$this->Z = $this->content_top_margin;
				$this->AddPage();
			}
			
			$this-> pZ = 0;
			$this -> PrintProject($row['id'], 14, $this->Z);	
		}
		$stmt -> closeCursor();
		
	}
	
	//calculate height of project
	function GetProjectHeight($id){
		//total height
		$H = 0;
		//create new project instance without default values, update it and generate a datalist
		$project = new Project(FALSE);
		//get superglobals
		$language = (isset($_GET['language'])) ? $_GET['language'] : "FR";
		$project -> setLanguage($language);
		$project -> updateParameters($id);
		//set subtitle font
		$this -> SetFont('OpenSans-R','',9);
		//program prep
		$program = $project -> program[$project->L];
		//add up height for titleblock
		$H += 7 + $this->margin_bottom_subtitle + ($this->NumberOfLines('OpenSans-R', $program, $this->programW)*($this->lineHeight)) + $this->margin_bottom_titleblock;;
		
		//add up height for datalist
		//get datalist
		$dl = $project -> generateDatalist("projectlist",$language);
		//loop through results
		for($i = 0; $i < count($dl); $i++) {
			$c = array_slice($dl, $i);
			$Hvalue = array_values($c)[0];	
			
			if(!in_array($Hvalue, array("/","",NULL))){
				$this -> SetFont('OpenSans-R','',9);
				$H += ((($this->NumberOfLines('OpenSans-R', $Hvalue, $this->datakeyW)) - 0)*($this->lineHeight)) + $this->space_between_data;
			}			
		}
		
		return $H;		
	}
	
	//print projects
	function PrintProject($id, $X, $Y) {
		//get required files
		require './inc/label_values.inc.php';
		require './inc/parameter_values.inc.php';
		//create new project instance without default values, update it and generate a datalist
		$project = new Project(FALSE);
		//get superglobals
		$language = (isset($_GET['language'])) ? $_GET['language'] : "FR";
		$hidden = (isset($_GET['hidden'])) ? $_GET['hidden'] : "";
		$project -> setLanguage($language);
		$project -> updateParameters($id);		
		
		// draw image	
		$this -> SetXY($X, $Y);
		$this -> Image("../".APP_FOLDER.str_replace("/projectListing", "",$project->coverimage),null,null,55);
		
		//draw titleblock
		//title
		$this -> SetXY($X + $this->indent, $Y);
		$this -> SetFont('OpenSans-R','',14);
		$this -> SetTextColor(0,0,0);
		//check if titlenumber is hidden
		if(strpos($hidden, trim(split(" ",$pnumber_labels[0])[0])) == false){
			$this -> Cell(11, 4, $project->number, 0, 0);
			$this -> Cell(0, 4, $project->name, 0, 0);
		} else{
			$this -> Cell(0, 4, $project->name, 0, 0);
		}
		//subtitle prep
		$won = ($project->competitionwon == "yes") ? " - ".$competitionwon_labels[$project->L] : "";
		$competition = ($project->projecttype == $projecttypes[1]) ? splitData($project->projecttype)[$project->L].$won.", " : "";
		$endD = (count(splitData($project->enddate))>1) ? splitData($project->enddate)[$project->L] : $project->enddate;
		$location = $project -> city[$project->L] . " - " . $project -> countrycode;
		$date = ($project->startdate != $project->enddate) ? $project -> startdate. " - " . $endD : $project -> startdate;
		$subtitle = $competition.$location.", ".$date;
		//subtitle print
		$this -> pZ += 7;
		$this -> SetXY($X + $this->indent, $Y+$this->pZ);
		$this -> SetFont('OpenSans-LI','',9);
		$this -> SetTextColor(100,100,100);
		$this -> Cell(0, 0, $subtitle, 0, 0);
		//program prep
		$program = $project -> program[$project->L];
		//program print
		$this -> pZ += $this->margin_bottom_subtitle;
		$this -> SetXY($X + $this->indent, $Y+$this->pZ);
		$this -> SetFont('OpenSans-R','',9);
		$this -> SetTextColor(0,0,0);
		$this -> MultiCell($this->programW, $this->lineHeight, $program, 0, 'L');
		$this -> pZ += ( $this->NumberOfLines('OpenSans-R', $program, $this->programW)*($this->lineHeight) );
		
		//add total height titleblock (+margin under it) to Z-height and set Y to this height
		$this->pZ += $this->margin_bottom_titleblock;
		
		// draw datalist
		//get datalist
		$dl = $project -> generateDatalist("projectlist",$language);
		//loop through results
		for($i = 0; $i < count($dl); $i++) {
			$c = array_slice($dl, $i);
			$Hkey = key($c);
			$Hvalue = array_values($c)[0];	
			
			//check if value is not one of hidden values passed in the url
			//if key contains brackets (eg. (honorary), get only the key in front of it
			$stringToCheck = (strpos($Hkey,"\(") !== false) ? trim(split("\(",$Hkey)[0]) : $Hkey;
			if(strpos($hidden, $stringToCheck) == false){      
				//key print
				$this -> SetXY($X + $this->indent, $Y+$this->pZ);
				$this -> SetFont('OpenSans-LI','',9);
				$this -> SetTextColor(0,0,0);
				$this -> Cell(0, $this->lineHeight, $Hkey , 0, 0); 
				
				//value print
				//if budget, use different font for euro sign (split removes possible "honorary" appendix of budget)   
				if(strpos(joinData($bt_labels),split(" ",$Hkey)[0]) !== false){
					//separate special characters
					$HvalueA = split("&#128;", $Hvalue);
					$totalW = 0;
					for($j =0; $j<count($HvalueA); $j++) {
						//print text	
						$this -> SetXY($X + $this->indent + $this->datakey_indent + $totalW, $Y+$this->pZ);
						$this -> SetFont('OpenSans-R','',9);
						$this -> SetTextColor(100,100,100);
						$this -> Cell(0, $this->lineHeight, $HvalueA[$j] , 0, 0);
						
						$totalW += $this -> GetStringWidth($HvalueA[$j]);
						
						if($j!=count($HvalueA)-1){
							//print euro sign
							$this -> SetXY($X + $this->indent + $this->datakey_indent + $totalW, $Y+$this->pZ);
							$this -> SetFont('courier','I',9);
							//$this -> SetTextColor(140,140,140);
							$this -> Cell(0, $this->lineHeight, EURO , 0, 0);
							
							$totalW += $this -> GetStringWidth(EURO);
						}
					}						
				} 
				//print if energy efficiency value
				else if(strpos(joinData($eel_labels),split(" ",$Hkey)[0]) !== false){
					$this -> SetXY($X + $this->indent + $this->datakey_indent, $Y+$this->pZ);
					$this -> SetFont('OpenSans-R','',9);
					$this -> SetTextColor(100,100,100);
					$eev = explode("- ", $Hvalue);
					// if eelevel has a value and we're supposed to hide it
					if(count($eev)==2 && strpos($hidden, trim(split(" ",$eev_labels[0])[0])) == true){
						$this -> MultiCell($this->datakeyW, $this->lineHeight, br2newl($eev[0]), 0, 'L');	
						$this -> pZ += ( $this->NumberOfLines('OpenSans-R', $Hvalue, $this->datakeyW)*($this->lineHeight) );	
					} else{
						$this -> MultiCell($this->datakeyW, $this->lineHeight, br2newl($Hvalue), 0, 'L');	
						$this -> pZ += ( $this->NumberOfLines('OpenSans-R', $Hvalue, $this->datakeyW)*($this->lineHeight) );	
					}
				}
				//print if energy efficiency value
				else if(strpos(joinData($sa_labels),split(" ",$Hkey)[0]) !== false){
					$this -> SetXY($X + $this->indent + $this->datakey_indent, $Y+$this->pZ);
					$this -> SetFont('OpenSans-R','',9);
					$this -> SetTextColor(100,100,100);
					$sa = explode("- ", $Hvalue);
					//if two values are given, and 'wieghted' was selected, show weighted value, else show gross value
					if(count($sa)==2){
						if(strpos($hidden, trim(split(" ",$wsa_labels[0])[0])) == true){
							$this -> MultiCell($this->datakeyW, $this->lineHeight, br2newl($sa[1]), 0, 'L');	
							$this -> pZ += ( $this->NumberOfLines('OpenSans-R', $Hvalue, $this->datakeyW)*($this->lineHeight) );	
						} else {
							$this -> MultiCell($this->datakeyW, $this->lineHeight, br2newl($sa[0]), 0, 'L');	
							$this -> pZ += ( $this->NumberOfLines('OpenSans-R',$Hvalue, $this->datakeyW)*($this->lineHeight) );	
						}
					} 
					//print whatever value was given
					else{
						$this -> MultiCell($this->datakeyW, $this->lineHeight, br2newl($Hvalue), 0, 'L');	
						$this -> pZ += ( $this->NumberOfLines('OpenSans-R', $Hvalue, $this->datakeyW)*($this->lineHeight) );	
					}
					
				}
				//print regular values
				else {
					$this -> SetXY($X + $this->indent + $this->datakey_indent, $Y+$this->pZ);
					$this -> SetFont('OpenSans-R','',9);
					$this -> SetTextColor(100,100,100);
					$this -> MultiCell($this->datakeyW, $this->lineHeight, br2newl($Hvalue), 0, 'L');	
					$this -> pZ += ( $this->NumberOfLines('OpenSans-R', $Hvalue, $this->datakeyW)*($this->lineHeight) );	
				}
			
				//add space between this and next dataitem
				$this->pZ+=$this->space_between_data;
			}
		} 
		//remove last space
		//$this->pZ -= $this->space_between_data;
		
		//if pZ is smaller than minimal height project set to min height
		$this->pZ = ($this->pZ <= $this->min_project_height) ? $this->min_project_height : $this->pZ;
		
		//add pZ and margin under project to Z
		$this->Z += $this->pZ + $this->margin_bottom;
	}

}

$pdf = new PDF('P','mm',array('210', '297'));

//set the margins
$pdf -> SetMargins(14,7,9);
//get total number of pages
$pdf -> AliasNbPages();
//declare Open Sans fonts
$pdf->AddFont('OpenSans-LI','','OpenSans-LightItalic.ttf', TRUE);
$pdf->AddFont('OpenSans-R','','OpenSans-Regular.ttf', TRUE);
//$pdf->AddFont('OpenSans-SB','','./OpenSans-SemiboldItalic.ttf', TRUE);


//print projects
$pdf -> PrintProjectList();

//output the PDF
$pdf -> Output();
?>