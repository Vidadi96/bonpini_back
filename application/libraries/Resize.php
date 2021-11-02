<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Resize {

 var $image;
 var $width;
 var $height;
 var $imageResized;
 var $extension;

 function getFileInfo($filename){
  // *** Open up the file
  $this->image = $this->openImage($filename);

  // *** Get width and height
  $this->width  = imagesx($this->image);
  $this->height = imagesy($this->image);
 }

 ## --------------------------------------------------------

 function openImage($file)
 {
  // *** Get extension
  $this->extension = strtolower(strrchr($file, '.'));

  switch($this->extension)
  {
   case '.jpg':
   case '.jpeg':
   case '.JPG':
   case '.JPEG':
    $img = @imagecreatefromjpeg($file);
    break;
   case '.gif':
    $img = @imagecreatefromgif($file);
    break;
   case '.png':
    $img = @imagecreatefrompng($file);
    break;
   default:
    $img = false;
    break;
  }
  return $img;
 }

 ## --------------------------------------------------------

 public function resizeImage($newWidth, $newHeight, $option="auto")
 {
  // *** Get optimal width and height - based on $option
  $optionArray = $this->getDimensions($newWidth, $newHeight, $option);
  $optimalWidth  = $optionArray['optimalWidth'];
  $optimalHeight = $optionArray['optimalHeight'];


	if($option=="auto" || $option=="landscape")
	{
		//list($img_width, $img_height) = getimagesize($this->image);
		if($this->width < $optimalWidth)
		{
			$optimalWidth = $this->width;
			$optimalHeight = $this->height;
		}
	}else if( $option=="portrait")
	{
		//list($img_width, $img_height) = getimagesize($this->image);
		if($this->height < $optimalHeight)
		{
			$optimalWidth = $this->width;
			$optimalHeight = $this->height;
		}
	}










  // *** Resample - create image canvas of x, y size
  $this->imageResized = imagecreatetruecolor($optimalWidth, $optimalHeight);
  // preserve transparency
    if($this->extension == ".gif" or $this->extension == ".png"){
    imagecolortransparent($this->imageResized, imagecolorallocatealpha($this->imageResized, 0, 0, 0, 127));
    imagealphablending($this->imageResized, false);
    imagesavealpha($this->imageResized, true);
    }
  imagecopyresampled($this->imageResized, $this->image, 0, 0, 0, 0, $optimalWidth, $optimalHeight, $this->width, $this->height);


  // *** if option is 'crop', then crop too
  if ($option == 'crop') {
   $this->crop($optimalWidth, $optimalHeight, $newWidth, $newHeight);
  }
 }

 ## --------------------------------------------------------

 private function getDimensions($newWidth, $newHeight, $option)
 {

    switch ($option)
  {
   case 'exact':
    $optimalWidth = $newWidth;
    $optimalHeight= $newHeight;
    break;
   case 'portrait':
    $optimalWidth = $this->getSizeByFixedHeight($newHeight);
    $optimalHeight= $newHeight;
    break;
   case 'landscape':
    $optimalWidth = $newWidth;
    $optimalHeight= $this->getSizeByFixedWidth($newWidth);
    break;
   case 'auto':
    $optionArray = $this->getSizeByAuto($newWidth, $newHeight);
    $optimalWidth = $optionArray['optimalWidth'];
    $optimalHeight = $optionArray['optimalHeight'];
    break;
   case 'crop':
    $optionArray = $this->getOptimalCrop($newWidth, $newHeight);
    $optimalWidth = $optionArray['optimalWidth'];
    $optimalHeight = $optionArray['optimalHeight'];
    break;
  }
  return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
 }

 ## --------------------------------------------------------

 private function getSizeByFixedHeight($newHeight)
 {
  $ratio = $this->width / $this->height;
  $newWidth = $newHeight * $ratio;
  return $newWidth;
 }

 private function getSizeByFixedWidth($newWidth)
 {
  $ratio = $this->height / $this->width;
  $newHeight = $newWidth * $ratio;
  return $newHeight;
 }

 private function getSizeByAuto($newWidth, $newHeight)
 {
  if ($this->height < $this->width)
  // *** Image to be resized is wider (landscape)
  {
   $optimalWidth = $newWidth;
   $optimalHeight= $this->getSizeByFixedWidth($newWidth);
  }
  elseif ($this->height > $this->width)
  // *** Image to be resized is taller (portrait)
  {
   $optimalWidth = $this->getSizeByFixedHeight($newHeight);
   $optimalHeight= $newHeight;
  }
  else
  // *** Image to be resizerd is a square
  {
   if ($newHeight < $newWidth) {
    $optimalWidth = $newWidth;
    $optimalHeight= $this->getSizeByFixedWidth($newWidth);
   } else if ($newHeight > $newWidth) {
    $optimalWidth = $this->getSizeByFixedHeight($newHeight);
    $optimalHeight= $newHeight;
   } else {
    // *** Sqaure being resized to a square
    $optimalWidth = $newWidth;
    $optimalHeight= $newHeight;
   }
  }

  return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
 }

 ## --------------------------------------------------------

 private function getOptimalCrop($newWidth, $newHeight)
 {

  $heightRatio = $this->height / $newHeight;
  $widthRatio  = $this->width /  $newWidth;

  if ($heightRatio < $widthRatio) {
   $optimalRatio = $heightRatio;
  } else {
   $optimalRatio = $widthRatio;
  }

  $optimalHeight = $this->height / $optimalRatio;
  $optimalWidth  = $this->width  / $optimalRatio;

  return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
 }

 ## --------------------------------------------------------

 private function crop($optimalWidth, $optimalHeight, $newWidth, $newHeight)
 {
  // *** Find center - this will be used for the crop
  $cropStartX = ( $optimalWidth / 2) - ( $newWidth /2 );
  $cropStartY = ( $optimalHeight/ 2) - ( $newHeight/2 );

  $crop = $this->imageResized;
  //imagedestroy($this->imageResized);

  // *** Now crop from center to exact requested size
  $this->imageResized = imagecreatetruecolor($newWidth , $newHeight);
  // preserve transparency
    if($this->extension == ".gif" or $this->extension == ".png"){
    imagecolortransparent($this->imageResized, imagecolorallocatealpha($this->imageResized, 0, 0, 0, 127));
    imagealphablending($this->imageResized, false);
    imagesavealpha($this->imageResized, true);
    }
    imagecopyresampled($this->imageResized, $crop , 0, 0, $cropStartX, $cropStartY, $newWidth, $newHeight , $newWidth, $newHeight);

 }

 ## --------------------------------------------------------

 public function saveImage($savePath, $imageQuality="100", $deg = 0)
 {
  // *** Get extension
  $extension = strrchr($savePath, '.');
  $extension = strtolower($extension);

  switch($extension)
  {
   case '.jpg':
   case '.jpeg':
    if (imagetypes() & IMG_JPG) {
     $rotate = imagerotate($this->imageResized, $deg, 0);
     imagejpeg($rotate, $savePath, $imageQuality);
    }
    break;

   case '.gif':
    if (imagetypes() & IMG_GIF) {
     imagegif($this->imageResized, $savePath);
    }
    break;

   case '.png':
    // *** Scale quality from 0-100 to 0-9
    $scaleQuality = round(($imageQuality/100) * 9);

    // *** Invert quality setting as 0 is best, not 9
    $invertScaleQuality = 9 - $scaleQuality;

    if (imagetypes() & IMG_PNG) {
      $rotate = imagerotate($this->imageResized, $deg, 0);
      imagepng($rotate, $savePath, $invertScaleQuality);
    }
    break;

   // ... etc

   default:
    // *** No extension - No save.
    break;
  }

  imagedestroy($this->imageResized);
 }


 ## --------------------------------------------------------


}
?>
