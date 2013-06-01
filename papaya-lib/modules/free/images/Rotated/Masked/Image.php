<?php
/**
* Generate a rotated and masked image
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
* Generate a rotated and masked image
*
* @package Papaya-Modules
* @subpackage Free-Images
*/
class ImagesRotatedMaskedImage extends base_dynamicimage {

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'Source Image',
    'thumbnail_size' => array('Thumbnail Size', 'isNum', FALSE, 'input', 50,
      'Set a thumbnail size to increase performance on large images.', ''),
    'thumbnail_resize_mode' => array('Thumbnail Resize Mode', 'isAlpha', FALSE, 'combo',
      array(
         'abs' => 'Absolute', 'max' => 'Maximum', 'maxfill' => 'Maximum fill', 'min' => 'Minimum',
         'mincrop' => 'Minimum cropped'
       ), '', 'mincrop'
    ),
    'Mask Image',
    'mask_image_guid' => array('Image', 'isGuid', FALSE, 'imagefixed', 32,
      'Use a PNG image with a transparent background.', ''),
    'mask_size' => array('Size', 'isFloat', FALSE, 'input', 50,
      'Use a float value greater than 0.0 and less or equal than 1.0 to modify the mask size in
      relation to the rotated source image size. Needs the options PAPAYA_THUMBS_TRANSPARENT = on and
       	PAPAYA_THUMBS_FILETYPE = PNG. Leave this option empty if you have a mask image with a size
        less or equal than the source image or the source image thumbnail.',
      '')
  );

  /**
  * Attribute fields
  * @var array $attributeFields
  */
  var $attributeFields = array(
    'Source Image',
    'image_guid' => array('Image', 'isGuid', TRUE, 'imagefixed', 32, '', ''),
    'rotation_angle' => array('Rotation Angle', 'isFloat', FALSE, 'input', 50,
      'Use a float value to define the angle in degrees. A positive value rotates the image anticlockwise.',
      ''),
    'rotation_bg_color' => array('Rotation Background Color', 'isHTMLColor', FALSE, 'color', 7,
      'Use to fill empty areas on rotation. The default value is PAPAYA_THUMBS_BACKGROUND.', '')
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

      // generate thumbnail if needed
      $thumbnailSize = $this->data['thumbnail_size'];
      if ($thumbnailSize > 0) {
        include_once(PAPAYA_INCLUDE_PATH.'system/base_thumbnail.php');
        $baseThumbnail = new base_thumbnail();
        $baseThumbnail->getThumbnail(
          $this->attributes['image_guid'], NULL, $thumbnailSize, $thumbnailSize,
          $this->data['thumbnail_resize_mode']
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
        if (!empty($this->attributes['rotation_angle'])) {
          if (!empty($this->attributes['rotation_bg_color'])) {
            $rotationBgColor = $controller->colorToRGB($this->attributes['rotation_bg_color']);
          } else {
            $rotationBgColor = $controller->colorToRGB(PAPAYA_THUMBS_BACKGROUND);
          }
          $rotationBgColor = imagecolorallocate(
            $image,
            $rotationBgColor['red'],
            $rotationBgColor['green'],
            $rotationBgColor['blue']
          );
          $image = imagerotate($image, $this->attributes['rotation_angle'], $rotationBgColor);
        }

        if (!empty($this->data['mask_image_guid'])) {
          $width = imagesx($image);
          $height = imagesy($image);

          include_once(PAPAYA_INCLUDE_PATH.'system/base_thumbnail.php');

          if (empty($this->data['mask_size'])) {
            $maskImage = &$controller->getMediaFileImage($this->data['mask_image_guid']);
            $maskWidth = imagesx($maskImage);
            $maskHeight = imagesy($maskImage);
          } else {
            $maskSize = (float)$this->data['mask_size'];
            $maskWidth = floor($maskSize * $width);
            $maskHeight = floor($maskSize * $height);
            $baseThumbnail = new base_thumbnail();
            $baseThumbnail->getThumbnail(
              $this->data['mask_image_guid'], NULL, $maskWidth, $maskHeight, 'maxfill'
            );
            $maskImage = &$controller->loadImage(
              PAPAYA_PATH_DATA.'media/thumbs/'.$baseThumbnail->lastThumbFileName
            );
            unset($baseThumbnail);
          }
          $maskXOffset = floor(($width - $maskWidth) / 2);
          $maskYOffset = floor(($height - $maskHeight) / 2);

          if ($maskXOffset >= 0 && $maskYOffset >= 0) {
            $tempImage = imagecreatetruecolor($maskWidth, $maskHeight);
            if ($controller->imageConf['image_format'] == 3 ||
                ($controller->imageConf['image_format'] == 0 &&
                 PAPAYA_THUMBS_FILETYPE == 3)) {
              imagesavealpha($tempImage, TRUE);
              imagealphablending($tempImage, FALSE);
              $tempImageColorIdx = imagecolorallocatealpha(
                $tempImage, 0, 0, 0, 127
              );
              imagefill($tempImage, 0, 0, $tempImageColorIdx);
            } else {
              imagesavealpha($tempImage, FALSE);
              imagealphablending($tempImage, TRUE);
              $tempImageColor = $controller->colorToRGB(PAPAYA_THUMBS_BACKGROUND);
              $tempImageColorIdx = imagecolorallocate(
                $tempImage, $tempImageColor['red'], $tempImageColor['green'], $tempImageColor['blue']
              );
              imagefill($tempImage, 0, 0, $tempImageColorIdx);
            }

            for ($y = 0; $y < $height; $y++) {
              for ($x = 0; $x < $width; $x++) {
                if ($x >= $maskXOffset && $y >= $maskYOffset && $x < $maskXOffset + $maskWidth &&
                    $y < $maskYOffset + $maskHeight) {
                  $maskColors = imagecolorsforindex(
                    $maskImage, imagecolorat($maskImage, $x - $maskXOffset, $y - $maskYOffset)
                  );
                  if ($maskColors['alpha'] < 127) {
                    $imageColors = imagecolorsforindex($image, imagecolorat($image, $x, $y));
                    $tempImageColorIdx = imagecolorallocatealpha(
                      $tempImage,
                      $imageColors['red'],
                      $imageColors['green'],
                      $imageColors['blue'],
                      $maskColors['alpha']
                    );
                    imagesetpixel($tempImage, $x - $maskXOffset, $y - $maskYOffset, $tempImageColorIdx);
                  }
                }
              }
            }
            $image = $tempImage;
          }
        }
        return $image;
      }
    }
    return FALSE;
  }
}