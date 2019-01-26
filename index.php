<?php
/*
*
*** This is intended as demo code ***
* This is for testing PHP resize functions on uploaded images.
* This file includes the upload form, and the code for processing the image.
* It has code for resizing an image, and for automatically making a centered/cropped thumbnail.
*/

/* display errors */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$upload = false; // true if file upload succeeded
$cropVar = array(); // collection of parameters for cropping image
$thumbpos =	(!empty(isset($_POST['thumbpos']))) ? trim($_POST['thumbpos']) : 'center' ;

/* create uploads folder */

if (!file_exists('uploads')) {
	mkdir('uploads');
}


/* handle file upload */
if (isset($_FILES['userfile']['name'])) {

	/* variables from uploaded file */
	$name = $_FILES['userfile']['name'];
	$tmp = $_FILES['userfile']['tmp_name'];
	$target = 'uploads/'.$name;
	$basename = pathinfo($name, PATHINFO_BASENAME);
	$filename = pathinfo($name, PATHINFO_FILENAME);

	echo $basename.'<br>';
	echo $filename.'<br>';

	/* save uploaded file to destination folder */
	if (move_uploaded_file($tmp, $target)) {
		echo $target." uploaded<br>";
		$upload = true;
	} else {
		echo "file didn't upload<br>";
	}

} else {
	echo "select a file<br>";
}

/* if upload was successful, process it */

if ($upload) {

	/* check file type to create image */
	switch($_FILES['userfile']['type']) {
		case "image/png":
			$imageType = "PNG";
			$sourceImage = imagecreatefrompng($target);
			break;
		case "image/jpeg":
			$imageType = "JPEG";
			$sourceImage = imagecreatefromjpeg($target);
			break;	
		case "image/gif":
			$imageType = "GIF";
			$sourceImage = imagecreatefromgif($target);
			break;			
		case "image/bmp":
			$imageType = "BMP";
			$sourceImage = imagecreatefrombmp($target);
			break;			
		case "image/jpg":
			$imageType = "JPG";
			$sourceImage = imagecreatefromjpeg($target);
			break;
		default:
			$imageType = "None";
			$sourceImage = NULL;		
	}

	echo "File type = ".$imageType;
	echo "<br>";


	if ($sourceImage!=NULL) {

		/* get image dimensions */
		$imgWd = imagesx($sourceImage); //width
		$imgHt = imagesy($sourceImage); //height
		$imgAsp = ($imgWd/$imgHt); // aspect ration

		echo "width: ".$imgWd."<br>";
		echo "height: ".$imgHt."<br>";
		echo "aspect: ".$imgAsp."<br>";
		echo $thumbpos;

		/* information for centering and cropping */
		if ($imgAsp<1) { 
			$cropVar['x'] = 0;
			$cropVar['y'] = ($imgHt - $imgWd)/2;
			$cropVar['w'] = $imgWd;
			$cropVar['h'] = $imgWd;
		} else {
			$cropVar['x'] = ($imgWd - $imgHt)/2;
			$cropVar['y'] = 0;
			$cropVar['w'] = $imgHt;
			$cropVar['h'] = $imgHt;
		}

		/* crop image then resize */
		$imgCropCntr = imagecrop($sourceImage, ['x'=>$cropVar['x'], 'y'=>$cropVar['y'], 'width'=>$cropVar['w'], 'height'=>$cropVar['h']]);
		$imgCropScl = imagescale($imgCropCntr, 300);

		/* resize and crop image */
		$imgScaled800 = imagescale($sourceImage, 800);
		$imgScaled300 = imagescale($sourceImage, 300);
		$imgCrop300 = imagecrop($imgScaled300, ['x'=>0, 'y'=>0, 'width'=>300, 'height'=>300]);

		/* save scaled images as jpg */
		imagejpeg($imgScaled800, 'uploads/'.$filename.'-800.jpg', 90);
		imagejpeg($imgScaled300, 'uploads/'.$filename.'-300.jpg', 90);
		imagejpeg($imgCrop300, 'uploads/'.$filename.'-thumb-300.jpg', 90);
		imagejpeg($imgCropScl, 'uploads/'.$filename.'-thumb-cntr.jpg', 90);

		/* display the centered/cropped/rescaled thumbnail */
		echo '<h4>Thumbnail test:</h4><img src="'.'uploads/'.$filename.'-thumb-cntr.jpg'.'">';
	}

}

?>

<h2>PHP image resize tests</h2>

<form method="post" action="" enctype="multipart/form-data">

	<input type="file" name="userfile" id="userfile">
	<select name="thumbpos" id="thumbpos">
		<option value="center">center</option>
		<option value="left">left</option>
		<option value="right">right</option>
		<option value="top">top</option>
		<option value="bottom">bottom</option>
	</select>

	<input type="submit">

</form>
<a href=".">reset</a>