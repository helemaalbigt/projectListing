<?php
//include necessary files
include_once 'functions.inc.php';
/*
 *CENTRAL LISTING OF ALL MULTILINGUAL DEFAULT VALUE LISTS.
 * 
 * Each value is a string containing a joined version of the value in three languages
 */
	//CLIENTTYPE
	$clienttypes = array(
		$ct1 = joinData(array("privé", "privaat", "private")),
		$ct2 = joinData(array("public", "publiek", "public"))
    );
	
	
	//PROJECT TYPE	
	$projecttypes = array(
		$pt1 = joinData(array("projet", "project", "project")),
		$pt2 = joinData(array("concours", "wedstrijd", "competition")),
		$pt3 = joinData(array("étude de faisibilité", "haalbaarheidsstudie", "feasibility study")),
		$pt4 = joinData(array("immobilier", "vastgoed", "real estate")),
		$pt5 = joinData(array("autre", "andere", "other"))
	);

	
	//INTERVENTION TYPES	
	$interventiontypes = array(
		$it1 = joinData(array("construction neuve","nieuwbouw","new construction")),
		$it2 = joinData(array("rénovation","renovatie","renovation")),
		$it3 = joinData(array("restauration","restauratie","restauration")),
		$it4 = joinData(array("extension","uitbreiding","extention")),
		$it5 = joinData(array("transformation","verbouwing","remodeling")),
		$it6 = joinData(array("pas applicable","niet van toepassing","not applicable"))
	);

	
	//STATUS	
	$statusses = array(
		$st1 = joinData(array("esquisse","voorstudie","research")),
		$st2 = joinData(array("avant-projet","voorontwerp","preliminary design")),
		$st3 = joinData(array("permis d'urbanisme","stedenbouwkundige vergunning","building permit")),
		$st4 = joinData(array("soumission","aanbesteding","tender")),
		$st5 = joinData(array("exécution","uitvoering","construction")),
		$st6 = joinData(array("délivré","afgeleverd","delivered"))
	);

	
	//CATEGORY
	$categories = array(
		//urbanism
		$ca1 = joinData(array("urbanisme", "stedenbouw", "urbanism")),
		$ca2 = joinData(array("programmation", "programmatie", "programmation")),
		$ca3 = joinData(array("masterplan", "masterplan", "masterplan")),
		$ca4 = joinData(array("rénovation urbaine", "stadsvernieuwing", "urban renewal")),
		$ca5 = joinData(array("étude strategie", "strategisch onderzoek", "strategic research")),
		$ca6 = joinData(array("infrastructure", "infrastructuur", "infrastructure")),
		$ca7 = joinData(array("espace public", "publieke ruimte", "public space")),
		//residential
		$ca8 = joinData(array("résidentiel", "residentieel", "residential")),
		$ca9 = joinData(array("maison", "woning", "house")),
		$ca10 = joinData(array("appartements", "appartementen", "apartments")),
		//public
		$ca11 = joinData(array("public", "publiek", "public")),
		$ca12 = joinData(array("education", "onderwijs", "education")),
		$ca13 = joinData(array("santé", "gezondheidszorg", "health")),
		$ca14 = joinData(array("culture", "cultuur", "culture")),
		//office
		$ca15 = joinData(array("bureau", "kantoor", "office")),
		$ca16 = joinData(array("bureaux", "kantoren", "offices")),
		$ca17 = joinData(array("commerce", "handelsruimte", "commerce")),
		$ca18 = joinData(array("logistique", "logistiek", "logistics")),
		//other
		$ca19 = joinData(array("autre", "andere", "other")),
		$ca20 = joinData(array("recherche", "onderzoek", "research")),
		$ca21 = joinData(array("usage mixte", "gemengd gebruik", "mixed use")),
		$ca22 = joinData(array("interieure", "interieur", "interior")),
		$ca23 = joinData(array("exposition", "tentoonstellingsruimte", "exhibition")),
		$ca24 = joinData(array("autre categorie", "andere categorie", "other catagory"))
	);

	
	//SCALE
	$scales = array(
		$sc1 = joinData(array("petit", "klein", "small")),
		$sc2 = joinData(array("moyen", "medium", "medium")),
		$sc3 = joinData(array("grand", "groot", "large"))
	);
	
	
	//ENERGY EFFICIENCE LEVEL
	$eelevels = array(
		$eel1 = joinData(array("passif", "passief", "passive")),
		$eel2 = joinData(array("très basse énergie", "zeer lage-energie", "very low-energy")),
		$eel3 = joinData(array("basse énergie", "lage-energie", "low energy")),
		$eel4 = joinData(array("PEB-conforme", "EPB-conform", "standard energy efficiency"))
	);
	
	$eeloldunits = array(
		$eelold1 = "U",
		$eelold2 = "K"
	);	
	
	//BUDGETTYPE
	$budgettypes = array(
		$bt1 = joinData(array("traveaux","werken","construction")),
		$bt2 = joinData(array("Honoraire","Ereloon","Honorary")),
		$bt3 = joinData(array("confidentiel","vertrouwelijk","confidential"))
	);
	
	
	//consultant TYPE
	$consultanttypes = array(
		$pt1 = joinData(array("architecte associé","geassocieerde architecten","partner architect")),
		$pt2 = joinData(array("maitre d'ouvrage délégué", "Vertegenwoordiger bouwheer", "client representative")),
		$pt3 = joinData(array("ingénieur Stabilité","ingenieur stabiliteit", "structural engineer")),
		$pt4 = joinData(array("project manager","project manager","project manager")),
		$pt5 = joinData(array("ingénieur techniques spéciales","ingenieur speciale technieken","technical engineer")),
		$pt6 = joinData(array("conseiller en développement durable","consulent duurzaam bouwen","advisor sustainable development")),
		$pt7 = joinData(array("conseiller PEB","consulent energie","energy advisor")),
		$pt8 = joinData(array("coordinateur sécurité santé","veiligheidscoördinator","safety inspector")),
		$pt9 = joinData(array("acousticien","ingenieur akoestiek","acoustical engineer")),
		$pt10 = joinData(array("paysagiste","landschapsarchitect","landscape architect")),
		$pt11 = joinData(array("artiste","kunstenaar","artist")),
		$pt12 = joinData(array("opérateur","uitbater","operator")),
		$pt13 = joinData(array("agent","agent","agent")),
		$pt14 = joinData(array("photographe","fotograaf","photographer")),
		$pt15 = joinData(array("conseiller en mobilité","consulent mobiliteit","mobility advisor")),
		$pt16 = joinData(array("conseiller en écologique","consulent milieuaspecten","environmental advisor")),
		$pt17 = joinData(array("assanissement","sanering","remediation")),
		$pt18 = joinData(array("urbaniste","urbanist","urbanist")),
		$pt19 = joinData(array("scénographe","scenograaf","scenographer")),
		$pt20 = joinData(array("éclairagiste","verlichting","lighting")),
		$pt21 = joinData(array("autre","andere","other"))
	);
	$ct_labels = array("Equipe SS","Consultants","Consultants");

?>