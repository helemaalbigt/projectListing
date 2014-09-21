<?php

class ImageHandler {

	//folder in which to save images
	public $save_dir_original;
	public $save_dir_resized;
	public $max_dims;

	//sets the save_dir_resized on instatiation
	public function __construct($save_dir_original, $save_dir_resized, $max_dims = array(430,310)) {
		$this -> save_dir_original = $save_dir_original;
		$this -> save_dir_resized = $save_dir_resized;
		$this -> max_dims = $max_dims;
	}

	/**
	 * Resizes/resamples an image uploaded via a web form
	 *
	 * @param array $upload the array contained in $_FILES
	 * @param bool $rename whether or not the image should be renamed
	 * @return string the path to the resized uploaded file
	 */
	public function processUploadedImage($file, $rename = TRUE) {
		// Separate the uploaded file array
		list($name, $type, $tmp, $err, $size) = array_values($file);
		//placeholder variables for the new images
		$original;
		$resized;

		//if an error occured, throw an exception
		if ($err != UPLOAD_ERR_OK) {
			throw new Exception('An error occurred uploading the Image!');
			exit ;
		}

		//check that the directory exists
		$this -> checkSaveDir();

		//rename the file if the flag is set to TRUE
		if ($rename == TRUE) {
			//Retrieve information about the image
			$img_ext = $this -> getImageExtensions($type);

			$name = $this -> renameFile($img_ext);
		}

		
		/*
		 * Handle the resized image
		 */

		//Create the full path to the  resized image for saving
		$filepath_resized = $this -> save_dir_resized . $name;
		//store the absolute filepath to move the image
		$absolute_resized = $_SERVER['DOCUMENT_ROOT'] .APP_FOLDER. $filepath_resized;
		
		//generate a resized image
		$this -> doImageResize($tmp, $absolute_resized);
		
		/*
		 * Handle the original image
		 */

		//Create the full path to the  resized image for saving
		$filepath_original = $this -> save_dir_original . $name;
		//store the absolute filepath to move the image
		$absolute_original = $_SERVER['DOCUMENT_ROOT'] .APP_FOLDER. $filepath_original;
		
		//save original
		if (!move_uploaded_file($tmp, $absolute_original)) {
		 throw new Exception("Couldn't save the uploaded image!");
	    }

		return array($filepath_original, $filepath_resized);
	}

	/**
	 * Ensures that the save directory exists
	 *
	 * Checks for the existence of the supplied save directory, and creates it if it doesn't exist. creation is recursive.
	 *
	 * @param void
	 * @return void
	 */
	private function checkSaveDir() {
		//determines the path to check
		$path_resized = $_SERVER['DOCUMENT_ROOT'] .APP_FOLDER. $this -> save_dir_resized;
		$path_original = $_SERVER['DOCUMENT_ROOT'] .APP_FOLDER. $this -> save_dir_original;

		//checks if directory exists
		if (!is_dir($path_resized)) {
			//creates the directory
			if (!mkdir($path_resized, 0777, TRUE)) {
				//on failure, throw an error
				throw new Exception("Can't create the image directory!");
			}
		}
		if (!is_dir($path_original)) {
			//creates the directory
			if (!mkdir($path_original, 0777, TRUE)) {
				//on failure, throw an error
				throw new Exception("Can't create the image directory!");
			}
		}
	}

	/**
	 * Generates a unique name for a file
	 *
	 * Uses the current timestamp and a randomly generated number
	 * to create a unique name to be used for an uploaded file.
	 * This helps preventing a new file upload from overwriting an
	 * existing file with the same name.
	 *
	 * @param string $ext the file extension for the upload
	 * @return string the new filename
	 */
	private function renameFile($ext) {
		/*
		 * returns the current timestamp and a random number
		 * to avoid duplicate filenames
		 */
		return time() . '_' . mt_rand(1000, 9999) . $ext;
	}

	/**
	 * Determines the filetype and extension of an image
	 *
	 * @param string $type the MIME type of the image
	 * @return string the extension to be used with the file
	 */
	private function getImageExtensions($type) {
		switch ($type) {
			case 'image/gif' :
				return '.gif';

			case 'image/jpeg' :
			case 'image/pjpeg' :
				return '.jpg';

			case 'image/png' :
				return '.png';

			default :
				throw new Exception('Image File type is not recognized!');

				break;
		}

	}

	/**
	 * Determines new dimensions for an image
	 *
	 * @param string $img the path to the upload
	 * @return array the new and original image dimensions
	 */
	private function getNewDims($img) {
		//assemble the necessary variables for processing
		list($src_w, $src_h) = getimagesize($img);
		list($max_w, $max_h) = $this -> max_dims;

		//check that the image is bigger than the maximumdimensions
		if ($src_w > $max_w || $src_h > $src_h) {
			//determine the scale to which the image will be resized, smallest factor will be the one used
			$s = max($max_w / $src_w, $max_h / $src_h);
		} else {
			/*
			 * If the image is smaller than the max dimensions, keep its dimensions by multiplying by 1
			 */
			$s = 1;
		}

		//get new dimensions
		$new_w = round($src_w * $s);
		$new_h = round($src_h * $s);
		
		/*echo $new_w."-".$new_h; 
		exit; */

		//Return the new dimensions
		return array($new_w, $new_h, round($new_w/$s), round($new_h/$s));
	}

	/**
	 * Determines how to process images
	 *
	 * Uses the MIME type of the provided image to determine
	 * what image handling functions should be used. Thia
	 * increases the performance of the script versus using
	 * imagecreatefromstring().
	 *
	 * @param string $img the path to the upload
	 * @return array the image type-specific functions
	 */
	private function getImageFunctions($img) {
		$info = getimagesize($img);

		switch(strtolower($info['mime'])) {
			case 'image/jpeg' :
			case 'image/pjpeg' :
				return array('imagecreatefromjpeg', 'imagejpeg');
				break;
			case 'image/gif' :
				return array('imagecreatefromgif', 'imagegif');
				break;
			case'image/png' :
				return array('imagecreatefrompng', 'imagepng');
				break;
			default :
				return FALSE;
				break;
		}
	}

	/**
	 * Generates a resampled and resized image
	 *
	 * Creates and saves a new image based on the new dimensions
	 * and image type-specific functions determined by other class methods.
	 *
	 * @param array $img the path to the upload
	 * @return void
	 */
	private function doImageResize($img, $destination) {
		//Determine the new dimensions
		$d = $this -> getNewDims($img);
		$md = $this -> max_dims;
		//Determine what functions to use
		$funcs = $this -> getImageFunctions($img);

		//Create the image resources for resampling
		$src_img = $funcs[0]($img);
		$new_img = imagecreatetruecolor($md[0], $md[1]);

		//copy new image from source to new
		if (imagecopyresampled($new_img, $src_img, -round(($d[0] - $md[0])/2), -round(($d[1] - $md[1])/2), 0, 0, $d[0], $d[1], $d[2], $d[3])) { 
			//free memory from source image
			imagedestroy($src_img);
			//save the image
			if ($new_img && $funcs[1]($new_img, $destination)) {
				imagedestroy($new_img);
			} else {
				throw new Exception('Failed to save the new image!');
			}
		} else {
			throw new Exception('Could not resample the image!');
		}
	}

}
?>