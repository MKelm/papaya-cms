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
* @version $Id: image_speaking_captcha.php 38496 2013-05-17 10:32:04Z weinert $
*/

/**
* Base class for image plugins
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_dynamicimage.php');

require_once(PAPAYA_INCLUDE_PATH.'system/base_mediadb.php');

/**
* Generate a button using a background image or color and a text
*
* image  plugins must be inherited from this superclass
*
* @package Papaya-Modules
* @subpackage Free-Captcha
*/
class image_speaking_captcha extends base_dynamicimage {

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
      'abcdefghijklmnopqrstuvwxyz0123456789'),

    'Wave settings',
    'wave_t' => array('Times', 'isFloat', TRUE, 'input', 4, '', 2),
    'amplitude' => array('Amplitude', 'isFloat', TRUE, 'input', 4, '', .25),

    'Font',
    'font_type' => array('Font', 'isSomeText', TRUE, 'mediafile', 400, '', ''),
    'font_size' => array('Size', 'isNum', TRUE, 'input', 4, '', 18),
    'font_color' => array('Base Color', 'isHTMLColor', TRUE, 'color', 7, '', '#FFAAAA'),

    'Audio',
    'pause_length' => array('Mute period (seconds)', 'isFloat', TRUE, 'input', 4,
      'The length of the silent period between the sounds.', .5),
    'voice_file' => array('Voice file', 'isSomeText', TRUE, 'mediafile', 400,
      '.wav PCM file', ''),
    'voice_timings' => array('Sound timings file', 'isSomeText', TRUE, 'mediafile', 400,
      'An XML file that contains start and stop times for each sound unit.', ''),
    'bg_sound_file' => array('Background sound', 'isSomeText', TRUE, 'mediafile', 400,
      '.wav PCM file', ''),
    'mp3_encoder' => array('MP3 encoder', 'isSomeText', FALSE, 'combo',
      array('' => '-none-', 'lame' => 'LAME audio compressor', 'sox' => 'sox - Sound eXchange'),
      'Please select an installed encoder.',
      ''),
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
    'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
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
  * generate voice captcha
  */
  function generateVoice($timings) {

    include_once(dirname(__FILE__).'/wav_sampler.php');

    $voiceFile = $this->mediaDB->getFileName($this->data['voice_file']);
    if (! $voiceFile || ! file_exists($voiceFile)) {
      trigger_error("voice file not found", E_USER_WARNING);
      return FALSE;
    }

    $sampler = new wavSampler();
    if (! $sampler->loadVoice($voiceFile)) {
      return FALSE;
    }

    $bgFile = $this->mediaDB->getFileName($this->data['bg_sound_file']);
    if ($this->data['bg_sound_file']) {
      $bgFile = $this->mediaDB->getFileName($this->data['bg_sound_file']);
      if (file_exists($bgFile)) {
        $sampler->loadBackground($bgFile);
      }
    }

    $sampler->addPause($this->data['pause_length']);
    for ($num = 0; $num < strlen($this->captchaString); $num++) {
      $char = substr($this->captchaString, $num, 1);
      if (isset($timings[$char])) {
        list($start, $stop) = $timings[$char];

        // add tone
        $sampler->copyFromSource($start, $stop);

        // add pause
        $sampler->addPause($this->data['pause_length']);
      } else {
        trigger_error("no timing defined for character $char", E_USER_WARNING);
        return FALSE;
      }
    }

    header("Content-type: audio/x-wav;");
    header(sprintf("Content-Disposition: attachment; filename=speaking_captcha_%s.wav", time()));
    $sampler->out();

    return TRUE;

  }

  /**
  * get tone timings
  */
  function getTimings() {
    include_once(PAPAYA_INCLUDE_PATH.'system/sys_simple_xmltree.php');
    $voiceTimings = $this->mediaDB->getFileName($this->data['voice_timings']);
    if (! $voiceTimings || ! file_exists($voiceTimings)) {
      trigger_error("tone timing file $voiceTimings not found", E_USER_WARNING);
      return FALSE;
    }

    $timingFileFontents = file_get_contents($voiceTimings);
    $xmlTree = &simple_xmltree::create();
    if (empty($timingFileFontents) || ! $xmlTree->loadXML($timingFileFontents)) {
      simple_xmltree::destroy($xmlTree);
      trigger_error("xml error", E_USER_WARNING);
      return FALSE;
    }

    $root = $xmlTree->documentElement;
    if ($root->nodeName != 'timing' || ! $root->hasChildNodes()) {
      simple_xmltree::destroy($xmlTree);
      trigger_error("wrong xml format", E_USER_WARNING);
      return FALSE;
    }

    $result = array();
    foreach ($root->childNodes as $item) {
      if ($item->nodeType == XML_ELEMENT_NODE) {
        if ($item->hasAttribute('name') &&
            $item->hasAttribute('start') &&
            $item->hasAttribute('stop')) {
          $result[$item->getAttribute('name')] = array(
            $item->getAttribute('start'),
            $item->getAttribute('stop')
          );
        } else {
          trigger_error("wrong xml format", E_USER_WARNING);
          break;
        }
      }
    }
    simple_xmltree::destroy($xmlTree);
    return $result;
  }

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

    if (! empty($this->data['voice_file']) &&
        ! empty($this->data['voice_timings']) &&
        isset($this->params['voice']) &&
        $this->params['voice']) {

      // voice captcha

      include_once(PAPAYA_INCLUDE_PATH.'system/base_mediadb.php');
      $this->mediaDB = base_mediadb::getInstance();

      if (! $timings = $this->getTimings($controller)) {
        return FALSE;
      }

      $this->chars = array();
      foreach (preg_split('/(?<=.)(?=.)/s', $this->data['setting_chars']) as $char) {
        if (isset($timings[$char])) {
          $this->chars[] = $char;
        }
      }
      if (sizeof($this->chars) < $this->data['number_of_chars'] * 2) {
        trigger_error(
          "none or very low applicable characters found, please check tone setings",
          E_USER_WARNING
        );
        return FALSE;
      }

      if (! isset($sessionData[$this->attributes['identifier']])) {
        $this->_generateRandomCaptchaString();
        $sessionData[$this->attributes['identifier']] = $this->captchaString;
        $this->setSessionValue($sessionIndex, $sessionData);
      } else {
        $this->captchaString = $sessionData[$this->attributes['identifier']];
      }

      if ($this->generateVoice($timings)) {
        exit;
      }
      return FALSE;
    }

    $this->chars = preg_split('/(?<=.)(?=.)/s', $this->data['setting_chars']);

    $this->fontColor = $controller->colorToRGB($this->data['font_color']);
    $this->bgColor = $controller->colorToRGB($this->data['image_color']);
    $this->_generateRandomCaptchaString();

    $sessionData[$this->attributes['identifier']] = $this->captchaString;

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
    $this->captchaString = '';
    $this->captchaChars = array();
    $charCount = count($this->chars) - 1;
    for ($i = 0; $i < $this->data['number_of_chars']; $i++) {
      srand(((double)microtime() * 1000000));
      $chr = $this->chars[rand(0, $charCount)];
      $this->captchaChars[] = array('char' => $chr);
      $this->captchaString .= $chr;
    }
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
      for ($x = 0,$len = strlen($captchaString); $x < $len; $x++) {
        list($img, $charWidth, $charHeight) = $this->generateCharImage(
          substr($captchaString, $x, 1),
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