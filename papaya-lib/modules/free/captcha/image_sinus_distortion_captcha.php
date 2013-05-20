<?php
/**
* Generate a captcha image and save the text value into the session
*
* image plugins must be inherited from this superclass
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
* @subpackage Free-Captcha
* @version $Id: image_sinus_distortion_captcha.php 38496 2013-05-17 10:32:04Z weinert $
*/

/**
* Base class for image plugins
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_dynamicimage.php');

require_once(PAPAYA_INCLUDE_PATH.'system/base_mediadb.php');

/**
* Generate a button using a background image or color and a text
*
* image plugins must be inherited from this superclass
*
* @package Papaya-Modules
* @subpackage Free-Captcha
*/
class image_sinus_distortion_captcha extends base_dynamicimage {

  /**
  * @var boolean $cacheable it makes no sense to cache captchas, since they are random
  */
  var $cacheable = FALSE;

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'padding' => array('Padding', 'isNum', TRUE, 'input', 2, '', 20),
    'image_color' => array('Background color', 'isHTMLColor', TRUE, 'color', 7, '',
      '#FFFFFF'),
    'number_of_chars' => array('Number of chars', 'isNum', TRUE, 'input', 2, '', '6'),
    'line_width' => array('Line width', 'isNum', TRUE, 'input', 2, '', 2),
    'setting_chars' => array('Valid characters', 'isNoHTML', TRUE, 'input', 200, '',
      'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'),

    'Wave settings',
    'wave_t' => array('Times', 'isFloat', TRUE, 'input', 4, '', 2),
    'amplitude' => array('Amplitude', 'isFloat', TRUE, 'input', 4, '', .25),

    'Font',
    'font_type' => array('Font', 'isSomeText', TRUE, 'mediafile', 400, '', ''),
    'font_size' => array('Size', 'isNum', TRUE, 'input', 4, '', 18),
    'font_color' => array('Base Color', 'isHTMLColor', TRUE, 'color', 7, '', '#FFAAAA'),
  );

  /**
  * Attribute fields
  * @var array $attributeFields
  */
  var $attributeFields = array(
    'identifier' => array('Identifier', 'isGUID', TRUE, 'input', 32, '',
      '123456789012345678901234567890AF')
  );

  /**
  * Session index name
  * @var string
  */
  var $sessionIndex = '';

  /**
  * Chars
  * @var array $chars
  */
  var $chars = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'Q', 'J', 'K',
    'L', 'M', 'N', 'P', 'R', 'S', 'T', 'U', 'V', 'Y', 'W', '2', '3', '4', '5',
    '6', '7', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'j', 'k', 'm', 'n', 'p',
    'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'
  );

  /**
  * Captcha text
  * @var array $captchaText
  */
  var $captchaText = array();

  /**
  * Captcha String
  * @var string
  */
  var $captchaString = '';

  /**
  * cachable
  * @var boolean
  */
  var $cachable = FALSE;

  /**
  * image width
  * @var integer
  */
  var $imageWidth = 1;

  /**
  * image height
  * @var integer
  */
  var $imageHeight = 1;

  /**
  * text width
  * @var integer
  */
  var $textWidth = 30;

  /**
  * text height
  * @var integer
  */
  var $textHeight = 120;

  /**
  * max x move wave
  * @var integer
  */
  var $maxXMoveWave = 0;

  /**
  * max y move wave
  * @var integer
  */
  var $maxYMoveWave = 0;

  /**
  * font color
  * @var array
  */
  var $fontColor = array();


  /**
  * generate the captcha image
  *
  * @param object base_imagegenerator &$controller controller object
  * @access private
  * @return image $result resource id
  */
  function &generateImage(&$controller) {
    $this->setDefaultData();
    $this->initializeParams();

    $sessionIndex = 'PAPAYA_SESS_CAPTCHA';
    $sessionData = $this->getSessionValue($sessionIndex);

    $this->chars = preg_split('/(?<=.)(?=.)/us', trim($this->data['setting_chars']));

    $this->fontColor = $controller->colorToRGB($this->data['font_color']);
    $this->bgColor = $controller->colorToRGB($this->data['image_color']);

    $this->captchaString = $this->_generateRandomCaptchaString();
    $sessionData[trim($this->attributes['identifier'])] = $this->captchaString;

    $this->setSessionValue($sessionIndex, $sessionData);

    //create image with the captcha chars and apply wave filter on it
    $imageString = $this->_generateImageString($this->captchaString);

    return $imageString;
  }

  /**
  * generate random captcha string
  *
  * @access private
  */
  function _generateRandomCaptchaString() {
    $result = '';
    $charCount = count($this->chars) - 1;
    for ($i = 0; $i < $this->data['number_of_chars']; $i++) {
      srand(((double)microtime() * 1000000));
      $chr = $this->chars[rand(0, $charCount)];
      $result .= $chr;
    }
    return $result;
  }

  /**
  * generate image string
  *
  * @param STRING $captchaString - string to generate
  * @access private
  * @return image resource id
  */
  function _generateImageString($captchaString) {
    $mediaDB = base_mediadb::getInstance();
    $fontSize = (int)$this->data['font_size'];
    $padding = (int)$this->data['padding'];

    //$result = &$this->imageCreateAlpha();
    if ($fontType = $mediaDB->getFileName($this->data['font_type'])) {

      // calculate string sizes
      list($blX, $blY, $brX, $brY, $urX, $urY, $ulX, $ulY) = imagettfbbox(
        $fontSize, 0, $fontType, $captchaString
      );
      $width = $padding * 2 + $brX - $ulX;
      $height = $padding * 2 + $brY - $ulY;

      $result = imageCreateTrueColor($width, $height);
      imagealphablending($result, TRUE);
      //get text color index
      $fontColorIdx = imagecolorallocatealpha(
        $result,
        $this->fontColor['red'],
        $this->fontColor['green'],
        $this->fontColor['blue'],
        0
      );

      $bgColorIdx = imagecolorallocatealpha(
        $result,
        $this->bgColor['red'],
        $this->bgColor['green'],
        $this->bgColor['blue'],
        0
      );

      imagefill($result, 0, 0, $bgColorIdx);

      // draw characters
      $pos = 0;
      $factor = mt_rand(7, 10) / 10 / $this->data['wave_t'];
      $phase = mt_rand(-$height, $height);
      $captchaLength = PapayaUtilStringUtf8::length($captchaString);

      for ($x = 0; $x < $captchaLength; $x++) {
        list($img, $charWidth, $charHeight) = $this->generateCharImage(
          PapayaUtilStringUtf8::copy($captchaString, $x, 1),
          $fontSize,
          $fontType,
          $fontColorIdx,
          $bgColorIdx,
          $factor,
          $phase++
        );

        for ($xs = 0; $xs < $charWidth; $xs++) {
          $buff = imagecreate(1, $charHeight);
          imagecopy($buff, $img, 0, 0, $xs, 0, 1, $charHeight);
          $colorCount = imagecolorstotal($buff);
          if ($colorCount > 2) {
            imagecopy($result, $img, $pos, $padding, $xs, 0, 1, $charHeight);
            $pos++;
          }
          imagedestroy($buff);
        }
        imagedestroy($img);
      }

    } else {
      trigger_error("cannot load a ttf font", E_USER_WARNING);
      return FALSE;
    }
    return $result;
  }

  /**
  * Convert character into an image slice
  * @param string $char
  * @param float $size
  * @param string $font
  * @param integer $color
  * @param integer $bgColor
  * @param float $waveFactor
  * @param float $phase
  * @return array
  */
  function generateCharImage($char, $size, $font, $color, $bgColor, $waveFactor, $phase) {
    list($blX, $blY, $brX, $brY, $urX, $urY, $ulX, $ulY) = imagettfbbox(
      $size, 0, $font, $char
    );
    $width = $brX - $ulX;
    $height = $brY - $ulY;
    $angle = 2 * M_PI * $waveFactor / $height;
    $img0 = imageCreateTrueColor($width, $height * 2);
    $img1 = imageCreateTrueColor($width * 2, $height * 2);
    imagefill($img0, 0, 0, $bgColor);
    imagefill($img1, 0, 0, $bgColor);

    $amplitude = $width / strlen($char) * $this->data['amplitude'];
    imagettftext($img0, $size, 0, 0, $height, $color, $font, $char);

    for ($y = 0; $y < $height * 2; $y++) {
      $x = sin($angle * ($y + $phase)) * $amplitude + $amplitude;
      imagecopy($img1, $img0, $x, $y, 0, $y, $width, 1);
    }
    imagedestroy($img0);
    return array($img1, $width * 2, $height * 2);
  }

  /**
  * image create alpha
  *
  * @access public
  * @return image resource id
  */
  function &imageCreateAlpha() {
    $result = imageCreateTrueColor($this->imageWidth, $this->imageHeight);
    imageSaveAlpha($result, TRUE);
    imageAlphaBlending($result, FALSE);
    $bgColor = imagecolorallocatealpha($result, 220, 220, 220, 127);
    imagefill($result, 0, 0, $bgColor);
    imageAlphaBlending($result, TRUE);
    return $result;
  }

}
?>
