<?php
/**
* Generate an rating bar using an background and colors
*
*
* @copyright 2002-2007 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Modules
* @subpackage Free-PageRating
* @version $Id: image_pagerating.php 34083 2010-04-23 18:20:08Z elbrecht $
*/

/**
* Base class for image plugins
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_dynamicimage.php');

/**
* Generate an rating bar using an background and colors
*
* @package Papaya-Modules
* @subpackage Free-PageRating
*/
class image_pagerating extends base_dynamicimage {

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'Colors',
    'color_left' => array('Left', 'isHTMLColor', TRUE, 'color', 7, '', '#FF0000'),
    'color_right' => array('Right', 'isHTMLColor', TRUE, 'color', 7, '', '#008000'),
    'color_grid' => array('Grid', 'isHTMLColor', TRUE, 'color', 7, '', '#C0C0C0'),
    'Background',
    'image_bg' => array('Image', 'isSomeText', TRUE, 'imagefixed', 400, '', ''),
    'Padding',
    'padding_h' => array('Horizontal', 'isNum', TRUE, 'input', 4, '', 10),
    'padding_v' => array('Vertical', 'isNum', TRUE, 'input', 4, '', 10),
  );

  /**
  * Attribute fields
  * @var array
  */
  var $attributeFields = array(
    'position' => array ('Current position', 'isNum', TRUE, 'input', 3, '', 0),
  );

  /**
  * generate the image
  *
  * @param object base_imagegenerator &$controller controller object
  * @access public
  * @return mixed $result resource image or FALSE
  */
  function &generateImage(&$controller) {
    $this->setDefaultData();
    if ($imageBG = &$controller->getMediaFileImage(@$this->data['image_bg'])) {
      $width = imagesx($imageBG);
      $height = imagesy($imageBG);

      $left = @(int)$this->data['padding_h'];
      $top = @(int)$this->data['padding_v'];

      $center = floor($width / 2);
      $barWidth = $width - $left - $left;
      $barHeight = $height - $top - $top;

      $left--;
      $top--;

      $result = imagecreate($width, $height);
      imagecopy($result, $imageBG, 0, 0, 0, 0, $width, $height);

      //bar
      $barPosition = (int)$this->attributes['position'];
      if ($barPosition > 100) {
        $barPosition = 100;
      } elseif ($barPosition < -100) {
        $barPosition = -100;
      }

      $barSize = floor(abs($barPosition / 2) * $barWidth / 100);

      if ($barPosition > 0) {
        $barColor = $controller->colorToRGB($this->data['color_right']);
        $barColorIdx = imagecolorallocate(
          $result, $barColor['red'], $barColor['green'], $barColor['blue']
        );
        imagefilledrectangle(
          $result,
          $center,
          $top,
          $center + $barSize,
          $top + $barHeight,
          $barColorIdx
        );
      } elseif ($barPosition < 0) {
        $barColor = $controller->colorToRGB($this->data['color_left']);
        $barColorIdx = imagecolorallocate(
          $result, $barColor['red'], $barColor['green'], $barColor['blue']
        );
        imagefilledrectangle(
          $result,
          $center - $barSize,
          $top,
          $center,
          $top + $barHeight,
          $barColorIdx
        );
      }

      $gridColor = $controller->colorToRGB($this->data['color_grid']);
      $gridColorIdx = imagecolorallocate(
        $result, $gridColor['red'], $gridColor['green'], $gridColor['blue']
      );
      imagerectangle(
        $result,
        $left,
        $top,
        $left + $barWidth,
        $top + $barHeight,
        $gridColorIdx
      );

      //grid
      $gridColor = $controller->colorToRGB($this->data['color_grid']);
      $gridColorIdx = imagecolorallocate(
        $result, $gridColor['red'], $gridColor['green'], $gridColor['blue']
      );
      imagerectangle(
        $result,
        $left,
        $top,
        $left + $barWidth,
        $top + $barHeight,
        $gridColorIdx
      );

      $gridTop = $top - 3;
      $gridBottom = $top + $barHeight + 3;
      imagefilledrectangle(
        $result,
        $left,
        $gridTop,
        $left + 3,
        $gridBottom,
        $gridColorIdx
      );
      imagefilledrectangle(
        $result,
        $center - 1,
        $gridTop,
        $center + 1,
        $gridBottom,
        $gridColorIdx
      );
      imagefilledrectangle(
        $result,
        $left + $barWidth - 3,
        $gridTop,
        $left + $barWidth,
        $gridBottom,
        $gridColorIdx
      );

      $gridTop = $top - 2;
      $gridBottom = $top + $barHeight + 2;
      $step = round($barWidth / 10);
      if ($step > 1) {
        for ($i = $step; $i < ($barWidth / 2); $i += $step) {
          imageline(
            $result, $center - $i, $gridTop, $center - $i, $gridBottom, $gridColorIdx
          );
          imageline(
            $result, $center + $i, $gridTop, $center + $i, $gridBottom, $gridColorIdx
          );
        }
      }

      return $result;
    } else {
      $this->lastError = 'Cannot load background image';
    }
    $result = FALSE;
    return $result;
  }
}
?>
