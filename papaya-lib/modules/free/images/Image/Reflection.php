<?php
/**
* Generate an image with reflaction effect
*
* @copyright 2013 by Martin Kelm
* @link http://mkelm.github.io/papaya-cms
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Modules
* @subpackage Free-Images
* @version $Id: $
*/

/**
* Base class for image plugins
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_dynamicimage.php');

/**
* Generate an image with reflaction effect
*
* @package Papaya-Modules
* @subpackage Free-Images
*/
class ImagesImageReflection extends base_dynamicimage {

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
  );

  /**
  * Attribute fields
  * @var array $attributeFields
  */
  var $attributeFields = array(
    'image_guid' => array('Image', 'isGuid', TRUE, 'imagefixed', 32, '', ''),
    'thumbnail_size' => array('Thumbnail Size', 'isNum', FALSE, 'input', 50, '', 0),
    'thumbnail_resize_mode' => array('Thumbnail Resize Mode', 'isAlpha', FALSE, 'input', 50, '', 'max'),
    'reflection_height' => array('Reflection Height', 'isNum', FALSE, 'input', 50, '', 100),
    'divider_line_height' => array('Devider Line Height', 'isNum', FALSE, 'input', 50, '', 1),
    'starting_transparency' => array('Starting Transparency', 'isNum', FALSE, 'input', 50, '', 30),
    'background_color' => array('Background Color', 'isHTMLColor', FALSE, 'color', 7, '', '#FFFFFF')
  );
  /**
  * generate the image
  *
  * @param object base_imagegenerator &$controller controller object
  * @access public
  * @return image $result resource image
  */
  function &generateImage(&$controller) {
    $this->setDefaultData();
    $result = NULL;
    foreach ($this->attributeFields as $field => $data) {
      if (empty($this->attributes[$field]) && is_array($data) && !empty($data[6])) {
        $this->attributes[$field] = $data[6];
      }
    }
    if (!empty($this->attributes['image_guid'])) {
      $backgroundColor = $controller->colorToRGB($this->attributes['background_color']);

      // generate thumbnail if needed
      $thumbnailSize = $this->attributes['thumbnail_size'];
      if ($thumbnailSize > 0) {
        include_once(PAPAYA_INCLUDE_PATH.'system/base_thumbnail.php');
        $baseThumbnail = new base_thumbnail();
        $baseThumbnail->getThumbnail(
          $this->attributes['image_guid'], NULL, $thumbnailSize, $thumbnailSize,
          $this->attributes['thumbnail_resize_mode']
        );
        $image = &$controller->loadImage(
          PAPAYA_PATH_DATA.'media/thumbs/'.$baseThumbnail->lastThumbFileName
        );
        unset($baseThumbnail);
      } else {
        $image = &$controller->getMediaFileImage($this->attributes['image_guid']);
      }

      $width = imagesx($image);
      $height = imagesy($image);
      if ($width > 0 && $height > 0) {

        $reflectionHeight = $this->attributes['reflection_height'];
        $dividerLineSize = $this->attributes['divider_line_height'];
        $startingTransparency = $this->attributes['starting_transparency'];

        // prepare reflection line
        $backgroundLine = imagecreatetruecolor($width, 1);
        $backgroundColor = imagecolorallocate(
          $backgroundLine,
          $backgroundColor['red'],
          $backgroundColor['green'],
          $backgroundColor['blue']
        );
        imagefilledrectangle($backgroundLine, 0, 0, $width, 1, $backgroundColor);

        // flip image
        $tempImage = imagecreatetruecolor($width, $reflectionHeight);
        $rotationColor = imagecolorallocate($image, 255, 255, 255);
        $image = imagerotate($image, 180, $rotationColor);
        imagecopyresampled($tempImage, $image, 0, 0, 0, 0, $width, $height, $width, $height);
        $image = $tempImage;
        $tempImage = imagecreatetruecolor($width, $reflectionHeight);
        for ($x = 0; $x < $width; $x++) {
          imagecopy($tempImage, $image, $x, 0, $width - $x - 1, 0, 1, $reflectionHeight);
        }
        $image = $tempImage;

        imagealphablending( $image, false );
        imagesavealpha( $image, true );

        // add transparency effect
        $increaseTransparency = 100 / $reflectionHeight;
        $transparency = $startingTransparency;
        for ($y = 0; $y <= $reflectionHeight; $y++){
          if ($transparency > 100) $transparency = 100;
          imagecopymerge($image, $backgroundLine, 0, $y, 0, 0, $width, 1, $transparency);
          $transparency += $increaseTransparency;
        }

        // set divider line
        $dividerLine = imagecreatetruecolor($width, $dividerLineSize);
        imagecopyresized($dividerLine, $backgroundLine, 0, 0, 0, 0, $width, $dividerLineSize, $width, 1);
        imagecopymerge($image, $dividerLine, 0, 0, 0, 0, $width, $dividerLineSize, 100);
        return $image;
      }
    }
    return FALSE;
  }
}