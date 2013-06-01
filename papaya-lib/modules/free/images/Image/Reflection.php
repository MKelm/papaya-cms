<?php
/**
* Generate an image with reflection effect
*
* Known issue: The transparency mode does not support soft edges in the reflection image,
* because the set colored pixel function does not support alpha channel information greater than 0
* correctly.
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
* Generate an image with reflection effect
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
    'background_color' => array('Background Color', 'isHTMLColor', FALSE, 'color', 7, '', NULL),
    'transparent_background' => array('Transparent Background', 'isNum', FALSE, 'yesno', NULL, NULL, 0),
    'include_image' => array('Include Image', 'isNum', FALSE, 'yesno', NULL, NULL, 0),
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

      if (empty($this->attributes['background_color']) && PAPAYA_THUMBS_BACKGROUND != '') {
        $backgroundColor = $controller->colorToRGB(PAPAYA_THUMBS_BACKGROUND);
      } elseif (!empty($this->attributes['background_color'])) {
        $backgroundColor = $controller->colorToRGB($this->attributes['background_color']);
      }
      if (PAPAYA_THUMBS_TRANSPARENT > 0 && $this->attributes['transparent_background'] > 0) {
        $transparentBackground = TRUE;
      } else {
        $transparentBackground = FALSE;
      }

      // generate thumbnail if needed
      $thumbnailSize = $this->attributes['thumbnail_size'];
      if ($thumbnailSize > 0) {
        include_once(PAPAYA_INCLUDE_PATH.'system/base_thumbnail.php');
        $baseThumbnail = new base_thumbnail();
        $baseThumbnail->getThumbnail(
          $this->attributes['image_guid'], NULL, $thumbnailSize, $thumbnailSize,
          $this->attributes['thumbnail_resize_mode'],
          array('bgcolor' => $this->attributes['background_color'])
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

        if (!$transparentBackground && !(PAPAYA_THUMBS_TRANSPARENT == 0)) {
          // set a background if the system option differs from attributes
          $tempImage = imagecreatetruecolor($width, $height);
          imagesavealpha($tempImage, FALSE);
          imagealphablending($tempImage, FALSE);
          $tempImageColorIdx = imagecolorallocate(
            $tempImage,
            $backgroundColor['red'],
            $backgroundColor['green'],
            $backgroundColor['blue']
          );
          imagefilledrectangle($tempImage, 0, 0, $width, $height, $tempImageColorIdx);
          imagealphablending($tempImage, TRUE);
          imagecopyresampled(
            $tempImage, $image, 0, 0, 0, 0, $width, $height, $width, $height
          );
          $image = $tempImage;
          unset($tempImage);
          unset($tempImageColorIdx);
        }
        $sourceImage = $image;

        $reflectionHeight = $this->attributes['reflection_height'];
        $dividerLineSize = $this->attributes['divider_line_height'];
        $startingTransparency = $this->attributes['starting_transparency'];

        // prepare reflection line
        $backgroundLine = imagecreatetruecolor($width, 1);
        if ($transparentBackground) {
          imagealphablending($backgroundLine, FALSE);
          imagesavealpha($backgroundLine, TRUE);
          $backgroundLineColorIdx = imagecolorallocatealpha(
            $backgroundLine,
            $backgroundColor['red'],
            $backgroundColor['green'],
            $backgroundColor['blue'],
            127
          );
          imagefill($backgroundLine, 0, 0, $backgroundLineColorIdx);
        } else {
          $backgroundLineColorIdx = imagecolorallocate(
            $backgroundLine,
            $backgroundColor['red'],
            $backgroundColor['green'],
            $backgroundColor['blue']
          );
          imagefill($backgroundLine, 0, 0, $backgroundLineColorIdx);
        }
        unset($backgroundLineColorIdx);

        // flip image
        $tempImage = imagecreatetruecolor($width, $height);
        if ($transparentBackground) {
          imagealphablending($tempImage, FALSE);
          imagesavealpha($tempImage, TRUE);
        }
        for ($y = 0; $y < $height; $y++) {
          imagecopy($tempImage, $image, 0, $y, 0, $height - $y - 1, $width, 1);
        }
        $image = $tempImage;
        unset($tempImage);

        // clip image to reflection height
        $tempImage = imagecreatetruecolor($width, $reflectionHeight);
        if ($transparentBackground) {
          imagealphablending($tempImage, FALSE);
          imagesavealpha($tempImage, TRUE);
          $tempImageColorIdx = imagecolorallocatealpha(
            $tempImage,
            $backgroundColor['red'],
            $backgroundColor['green'],
            $backgroundColor['blue'],
            127
          );
          imagefill($tempImage, 0, 0, $tempImageColorIdx);
          unset($tempImageColorIdx);
        }
        for ($y = 0; $y < $reflectionHeight; $y++) {
          imagecopy($tempImage, $image, 0, $y, 0, $y, $width, 1);
        }
        $image = $tempImage;
        unset($tempImage);
        unset($tempImageColorIdx);

        // add transparency effect
        if ($transparentBackground) {
          $tempImage = imagecreatetruecolor($width, $reflectionHeight);
          imagealphablending($tempImage, FALSE);
          imagesavealpha($tempImage, TRUE);
          $increaseTransparency = 127 / $reflectionHeight;
          $transparency = ($startingTransparency / 100) * 127;
          for ($y = 0; $y < $height; $y++) {
            if ($transparency > 127) {
              $transparency = 127;
            }
            for ($x = 0; $x < $width; $x++) {
              $colors = imagecolorsforindex($image, imagecolorat($image, $x, $y));
              if ($colors['alpha'] == 0) {
                $tempImageColorIdx = imagecolorallocatealpha(
                  $tempImage , $colors['red'], $colors['green'], $colors['blue'], floor($transparency)
                );
                imagesetpixel($tempImage, $x, $y, $tempImageColorIdx);
              } else {
                $tempImageColorIdx = imagecolorallocatealpha(
                  $tempImage, $colors['red'], $colors['green'], $colors['blue'], 127
                );
                imagesetpixel($tempImage, $x, $y, $tempImageColorIdx);
              }
            }
            $transparency += $increaseTransparency;
          }
          $image = $tempImage;
          unset($tempImage);
          unset($tempImageColorIdx);
        } else {
          $increaseTransparency = 100 / $reflectionHeight;
          $transparency = $startingTransparency;
          for ($y = 0; $y <= $reflectionHeight; $y++){
            if ($transparency > 100) $transparency = 100;
            imagecopymerge($image, $backgroundLine, 0, $y, 0, 0, $width, 1, $transparency);
            $transparency += $increaseTransparency;
          }
        }

         // get new height for image(s) with divider line
        if ($this->attributes['include_image'] > 0) {
          $newHeight = $height + $reflectionHeight + $dividerLineSize;
        } else {
          $newHeight = $reflectionHeight + $dividerLineSize;
        }
        $tempImage = imagecreatetruecolor($width, $newHeight);
        if ($transparentBackground) {
          imagealphablending($tempImage, FALSE);
          imagesavealpha($tempImage, TRUE);
          $tempImageColorIdx = imagecolorallocatealpha(
            $tempImage, 0, 0, 0, 127
          );
          imagefill($tempImage, 0, 0, $tempImageColorIdx);
        } else {
          $tempImageColorIdx = imagecolorallocate(
            $tempImage,
            $backgroundColor['red'],
            $backgroundColor['green'],
            $backgroundColor['blue']
          );
          imagefill($tempImage, 0, 0, $tempImageColorIdx);
        }
        unset($tempImageColorIdx);

        // finally copy part image(s) to new image with divider line
        if ($this->attributes['include_image'] > 0) {
          imagecopy($tempImage, $sourceImage, 0, 0, 0, 0, $width, $height);
          imagecopy($tempImage, $image, 0, $height + $dividerLineSize, 0, 0, $width, $reflectionHeight);
          $image = $tempImage;
        } else {
          imagecopy($tempImage, $image, 0, $dividerLineSize, 0, 0, $width, $reflectionHeight);
          $image = $tempImage;
        }
        return $image;
      }
    }
    return FALSE;
  }
}