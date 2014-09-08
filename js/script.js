/**
 * Duplicates element on Buttonclick. Adds an invisible span with a class to act as a means of counting the amount of duplicates
 *
 * @param string ID of parent element above element to duoplicate
 * @param string ID element to duplicate
 * @param string insert duplicate before this element
 * @param int max Max n° of duplicates to allow
 */
function duplicateElement(parent, element, insertPoint, max) {
	//get the number of duplicants that exist
	var duplicate_counter = document.getElementsByClassName("duplicate_counter" + element);
	var parent = document.getElementById(parent);
	if (duplicate_counter.length <= max) {
		//get and setup item to clone
		var itm = document.getElementById(element);
		//set specific id for parent div
		itm.setAttribute("id", element + "_div_" + (duplicate_counter.length - 1));
		//if item contains input field, set a unique id for it on the hidden node thats copied
		var input = itm.getElementsByTagName("input")[0];
		input.setAttribute("id", element + "_" + (duplicate_counter.length - 1));
		//if item contains button, update input
		var btn = itm.getElementsByTagName("button")[0];
		//remove()");
		btn.setAttribute("onclick", "document.getElementById('" + element + '_div_' + (duplicate_counter.length - 1) + "').remove()");

		//clone item
		var cln = itm.cloneNode(true);
		//reset id on hidden node
		input.setAttribute("id", "");
		itm.setAttribute("id", element);

		var counterSpan = document.createElement("span");
		counterSpan.className = "duplicate_counter" + element;
		counterSpan.setAttribute("id", "javascript generated" + duplicate_counter);
		btn.setAttribute("onclick", "");

		//parent.insertBefore(counterSpan, document.getElementById(insertPoint));
		parent.insertBefore(cln, document.getElementById(insertPoint));
	} else {
		var maxreached = document.createElement("div");
		maxreached.innerHTML = "Maximum number reached! (" + max + ")";

		parent.insertBefore(maxreached, document.getElementById(insertPoint));
	}
}

/**
 *Change display state of ellement with ID 'id' between none to state 'state' when a checkbox is checked
 *
 * @param string checkbox the id of the chechbox to check for change confirmation
 * @param array id Array of id's of elements you want to influence
 * @param string state the desired state
 */
function swap(checkbox, id, state) {

	var C = document.getElementById(checkbox);
	//convert passed IDstring into an array of id's to check
	var idA = splitData(id);
	//itterate over each element to turn on/off
	for (var i = 0; i < idA.length; i++) {
		var E = document.getElementById(idA[i]);
		if (C.checked) {
			E.style.display = state;
		} else {
			E.style.display = 'none';
		}
	}
}

/**
 *Change display state of ellement with ID 'id' between none to state 'state' when a value is selected in a select list
 *
 * @param string select the id of the select list to check for change confirmation
 * @param string value the value to check for in the select list
 * @param string id the id of the element you want to influence
 * @param string state the desired state
 */
function swapSelect(select, value, id, state) {

	var C = document.getElementById(select);
	var Cvalue = C.options[C.selectedIndex].value;
	var E = document.getElementById(id);
	//alert(Cvalue+" "+value);

	if (Cvalue.trim() == value.trim()) {
		//alert("ok");
		E.style.display = state;
	} else {
		//alert("not ok");
		E.style.display = 'none';
	}
}

/**
 * Joins all strings in an array into a single string, separated by 's-_--e'. Returns the string
 *
 * @param array $c array to handle
 * @return string combined string
 */
function joinData(c, key) {
	//key is an optional parameter
	key = ( typeof key === "undefined") ? "s-_--e" : key;
	//check if string is already composite
	key = (c[0].indexOf("s-_--e") > -1) ? "s-_-_-e" : key;

	var returnString = c[0];
	for (var i = 1; i < c.length; i++) {
		returnString = returnString.concat(key);
		returnString = returnString.concat(c[i]);
	}
	return returnString;
}

/**
 * Takes a string of data and seperates it at 's-_--e'. Returns array of values
 *
 * @param string $input input string of joined values
 * @return array
 */
function splitData(c, key) {
	//key is an optional parameter
	key = ( typeof key === "undefined") ? "s-_--e" : key;
	//check if string is already composite
	key = (c.indexOf("s-_--e") > -1) ? "s-_-_-e" : key;

	var returnArray = c.split(key);
	return returnArray;
}

/**
 *if a checkbox is checked, add a class to an element
 *
 * @param string cb checkbox id
 * @param string target target element id
 * @param string cl class to add/remove to target
 */
function toggleClass(cb, target, cl) {
	var C = document.getElementById(cb);
	var T = document.getElementById(target);

	if (C.checked) {
		T.className = T.className.replace(cl,"");
	} else {
		T.className = T.className+' '+cl;
	}
}

/**
 * Check for errors in forms
 *
 * Checks empty fields, non numerical input in a numerical field, and bigger than errors.
 * Changes CSS
 *
 * @param boolean gotoError Whether or not to go to the first error after reporting it
 * @param array(array) checkEmpty 2D array containing elements to check for 'empty' errors (("ID","human readable name"), (...))
 * @param array(array) checkNumerical 2D array containing elements to check for 'Not Numerical' errors (("ID","human readable name"), (...))
 * @param array(array) checkBiggerThan 2D array containing elements to check for 'X bigger than Y' errors (("ID1","ID2","Error Message"), (...))
 * @param array(array) checkFileSize 2D array containing elements to check for filesize bigger than 'maxsize' errors (("ID","human readable name"), (...))
 * @param array(array) checkValidDate 2D array containing elements to check for valid date format (("ID","human readable name"), (...))
 * @param array(array) checkIfThan 2D array  "if X ihas value Y than Z can't be empty" errors (("id Z","value Y", id X, errormessage), (...))
 * @param array(array) checkEither 2D array elements to check where at least one needs to be filled
 * @param array(array) checkEmptycb array of checkboxes to see if they are empty (parent id, printout name)
 * @return boolean False if error found
 */
function validateForm(gotoError, checkEmpty, checkNumerical, checkBiggerThan, checkFilesize, checkValidDate, checkIfThan, checkEither, checkEmptycb) {
	var error = false;
	var message = "ERROR";
	var errorColor = "rgb(245,170,165)";
	//max filesize
	var maxsize = 2000000;
	//where the window goes after error report
	var goTo = "#";

	var checkAll = checkIfThan.concat(checkValidDate, checkFilesize, checkBiggerThan, checkEmpty, checkNumerical, checkEither, checkEmptycb);
	//var checkAll = checkBiggerThan.concat(checkEmpty.concat(checkNumerical));

	//reset all backgroundcolors
	for (var i = 0; i < checkAll.length; i++) {
		//var e = document.forms["addProject"][checkAll[i][0]];
		var e = document.getElementById(checkAll[i][0]);
		e.style.backgroundColor = "transparent";
	}

	/*
	 * EMPTY ERROR
	 */
	for (var i = 0; i < checkEmpty.length; i++) {
		//check if element exists
		if (document.getElementById(checkEmpty[i][0]) != null) {
			//get element
			var e = document.getElementById(checkEmpty[i][0]);
			var v = e.value;
			//if element is empty, add a line to the error string, change css, change goTo location
			if (v == null || v == "") {
				var returnString = "\n-" + checkEmpty[i][1] + " is a required field! ";
				message += returnString;
				error = true;
				if (goTo == "#") {
					goTo = checkEmpty[i][0];
				}
				e.style.backgroundColor = errorColor;
			}
		}
	}

	/*
	 * NOT NUMERICAL ERROR
	 */
	for (var i = 0; i < checkNumerical.length; i++) {
		//check if element exists
		if (document.getElementById(checkNumerical[i][0]) != null) {
			//get element
			var e = document.getElementById(checkNumerical[i][0]);
			var v = e.value;
			//if element is not empty and not numerical, add a line to the error string, change css
			if (!(v == null || v == "") && v != parseInt(v)) {
				var returnString = "\n-" + checkNumerical[i][1] + " has to be a number! No decimal numbers, commas or points are allowed!";
				message += returnString;
				error = true;
				if (goTo == "#") {
					goTo = checkNumerical[i][0];
				}
				e.style.backgroundColor = errorColor;
			}
		}
	}

	/*
	 * BIGGER THAN ERROR
	 */
	for (var i = 0; i < checkBiggerThan.length; i++) {
		//check if element exists
		if (document.getElementById(checkBiggerThan[i][0]) != null && document.getElementById(checkBiggerThan[i][1]) != null) {
			//get elements
			var e1 = document.getElementById(checkBiggerThan[i][0]);
			var e2 = document.getElementById(checkBiggerThan[i][1]);
			var v1, v2;
			//check whether its a 'select' input or an 'input' input
			if (e1.tagName == "INPUT") {
				v1 = e1.value;
				v2 = e2.value;
			} else {
				v1 = e1.options[e1.selectedIndex].value;
				v2 = e2.options[e2.selectedIndex].value;
			}
			//if element is not empty, and v1 is bigger than v2, add a line to the error string, change css
			if (!(v1 == null || v1 == "") && !(v2 == null || v2 == "") && (parseInt(v1) > parseInt(v2))) {
				var returnString = "\n-" + checkBiggerThan[i][2];
				message += returnString;
				error = true;
				if (goTo == "#") {
					goTo = checkBiggerThan[i][0];
				}
				e1.style.backgroundColor = errorColor;
			}
		}
	}

	/*
	 * FILESIZE ERROR
	 */
	for (var i = 0; i < checkFilesize.length; i++) {
		// Check for the various File API support.
		if (window.File && window.FileReader && window.FileList && window.Blob) {
			//check if element exists
			if (document.getElementById(checkFilesize[i][0]) != null) {
				//get element file size
				var e = document.getElementById(checkFilesize[i][0]);
				//check if file is selected
				if (!(e.value == null || e.value == "")) {
					//get filesize
					var v = document.getElementById(checkFilesize[i][0]).files[0].size;
					//if element is not empty and not numerical, add a line to the error string, change css
					if (v > maxsize) {
						var returnString = "\n-" + checkFilesize[i][1] + " filesize is bigger than the maximum of 2Mb!";
						message += returnString;
						error = true;
						if (goTo == "#") {
							goTo = checkFilesize[i][0];
						}
						e.style.backgroundColor = errorColor;
					}
				}
			}
		}
	}

	/*
	 * INVALID DATE ERROR
	 */
	for (var i = 0; i < checkValidDate.length; i++) {
		//check if element exists
		if (document.getElementById(checkValidDate[i][0]) != null) {
			// get date variables
			var M = document.getElementById(checkValidDate[i][0]);
			var m = M.options[M.selectedIndex].value;

			var D = document.getElementById(checkValidDate[i][1]);
			var d = D.options[D.selectedIndex].value;

			var Y = document.getElementById(checkValidDate[i][2]);
			var y = Y.options[Y.selectedIndex].value;

			var date = new Date(m + " " + d + ", " + y);

			//the day is the only field you can really fuck up, so its the only one we check
			if (d != date.getDate()) {
				var returnString = "\n-" + checkValidDate[i][3] + " is not a valid date!";
				message += returnString;
				error = true;
				if (goTo == "#") {
					goTo = checkValidDate[i][0];
				}
				M.style.backgroundColor = errorColor;
			}
		}
	}

	/*
	 * IF X=Y than Z ERROR
	 */
	for (var i = 0; i < checkIfThan.length; i++) {
		//check if element X exists
		if (document.getElementById(checkIfThan[i][2]) != null) {

			var X = document.getElementById(checkIfThan[i][2]);
			var Y = X.options[X.selectedIndex].value;
			var Z = document.getElementById(checkIfThan[i][0]);
			var v = Z.value;

			if ((checkIfThan[i][1] == Y) && (v == null || v == "")) {
				var returnString = "\n-" + checkIfThan[i][3];
				message += returnString;
				error = true;
				if (goTo == "#") {
					goTo = checkIfThan[i][0];
				}
				Z.style.backgroundColor = errorColor;
			}
		}
	}

	/*
	 * EITHER ERROR
	 */
	for (var i = 0; i < checkEither.length; i++) {
		//check if element X exists
		if (document.getElementById(checkEither[i][0]) != null) {

			var X = document.getElementById(checkEither[i][0]);
			var x = X.value;
			var Y = document.getElementById(checkEither[i][1]);
			var y = Y.value;

			if ((x == null || x == "") && (y == null || y == "")) {
				var returnString = "\n-" + checkEither[i][2];
				message += returnString;
				error = true;
				if (goTo == "#") {
					goTo = checkEither[i][0];
				}
				X.style.backgroundColor = errorColor;
			}
		}
	}

	/*
	 * EMPTY CHECKBOX ERROR
	 */
	for (var i = 0; i < checkEmptycb.length; i++) {
		//check if element X exists
		if (document.getElementById(checkEmptycb[i][1]) != null) {
			//get checkboxes
			var checkboxes = document.getElementById(checkEmptycb[i][1]).getElementsByTagName('INPUT');
			var allUnChecked = true;
			for (var x = 0; x < checkboxes.length; x++) {
				if (checkboxes[x].type.toUpperCase() == 'CHECKBOX') {
					if (checkboxes[x].checked == true) {
						allUnChecked = false;
					}
				}
			}
			if (allUnChecked) {
				var returnString = "\n-" + checkEmptycb[i][2];
				message += returnString;
				error = true;
				if (goTo == "#") {
					goTo = checkEmptycb[i][0];
				}
				document.getElementById(checkEmptycb[i][0]).style.backgroundColor = errorColor;
			}
		}
	}

	//if any errors occurred return error message
	if (error) {
		alert(message);
		if(gotoError){
			//reset url
			window.location.hash = "#";
			//go to goTo location in window
			window.location.hash = goTo;
		}
		return false;
	}
}