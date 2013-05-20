<?php
/**
* Generate a audio stream from wav pcm source
*
* @copyright 2002-2009 by papaya Software GmbH - All rights reserved.
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
* @version $Id: wav_sampler.php 38361 2013-04-04 12:09:41Z hapke $
*/

/**
* Generate a audio stream from wav pcm source
*
* @package Papaya-Modules
* @subpackage Free-Captcha
*/
class wavSampler {

  /**
  * public properites, will be changed automatically if a source is loaded
  */
  public $audioFormat = 1; // PCM
  public $numChannels = 1; // MONO
  public $sampleRate = 22050;
  public $bitsPerSample = 8;

  public $samples = '';

  private $riffHeaderLenght = 44;
  private $source;

  /**
  * output formatted stream
  * does direct output, please use ob_* functions to capture
  */
  public function out() {

    if ($this->sourceFH) {
      fclose($this->sourceFH);
      unset($this->sourceFH);
    }

    $blockAlign = $this->numChannels * $this->bitsPerSample / 8;
    $byteRate = $this->sampleRate * $blockAlign;
    $chunk1Size = 16;
    $chunk2Size = strlen($this->samples);
    $commonLength = 12 + $chunk1Size + $chunk2Size;

    if ($this->backgroundFH) {
      $this->addBackground();
    }

    print  // header
      "RIFF" .
      pack("V", $commonLength) .
      "WAVE" .
      // fmt subchunk
      "fmt " .
      pack(
        "VvvVVvv",
        $chunk1Size,
        $this->audioFormat,
        $this->numChannels,
        $this->sampleRate,
        $byteRate,
        $blockAlign,
        $this->bitsPerSample
      ) .
      // data subchunk
      "data" .
      pack("V", $chunk2Size).
      $this->samples;
  }

  /**
  * read RIFF Headers
  */
  private function readHeaders($fl) {
    rewind($fl);
    $header = fread($fl, $this->riffHeaderLenght);

    if (! preg_match('/^RIFF....WAVEfmt\s.{20}data....$/', $header, $v)) {
      return FALSE;
    }

    $v = unpack(
      'x4/VcomLength/x4/x4/Vch1size/vformat/vnumChan/VsampleRate/VbyteRate/vba/vbps/x4/Vdlength',
      $header
    );
    $sampleRate = $v['sampleRate'];
    $numChannels = $v['numChan'];
    $bitsPerSample = $v['bps'];

    $byteRate = $sampleRate * $numChannels * $bitsPerSample / 8;
    $blockAlign = $numChannels * $bitsPerSample / 8;
    $chunk1Size = 16;
    $chunk2Size = $v['dlength'];
    $commonLength = 12 + $chunk1Size + $chunk2Size;

    $status = $byteRate == $v['byteRate'];
    if (! $status) {
      trigger_error(sprintf(__LINE__.": %d != %d\n", $byteRate, $v['byteRate']), E_USER_WARNING);
      return FALSE;
    }
    $status = $blockAlign == $v['ba'];
    if (! $status) {
      trigger_error(sprintf(__LINE__.": %d != %d\n", $blockAlign, $v['ba']), E_USER_WARNING);
      return FALSE;
    }
    $status = $chunk1Size == $v['ch1size'];
    if (! $status) {
      trigger_error(sprintf(__LINE__.": %d != %d\n", $chunk1Size, $v['ch1size']), E_USER_WARNING);
      return FALSE;
    }

    return array(
      'sampleRate' => $sampleRate,
      'numChannels' => $numChannels,
      'bitsPerSample' => $bitsPerSample,
      'chunk2Size' => $chunk2Size,
      'commonLength' => $commonLength
    );

  }

  /**
  * read sample data
  */
  private function readData($pos, $length) {
    if (! isset($this->source[$pos])) {

      fseek($this->sourceFH, $this->riffHeaderLenght + $pos, SEEK_SET);
      if ($length <= 0) {
        throw new Exception("$length <= 0");
      }
      $this->source[$pos] = fread($this->sourceFH, $length);
    }
    return $this->source[$pos];
  }

  /**
  * read in voice file
  * @param STRING $file - file name
  */
  public function loadVoice($file) {
    if (! file_exists($file)) {
      trigger_error("file $file does not exist", E_USER_WARNING);
      return FALSE;
    }
    if (! $fl = fopen($file, "r")) {
      trigger_error("cannot open $file", E_USER_WARNING);
      return FALSE;
    }

    $headers = $this->readHeaders($fl);
    if (! $headers) {
      fclose($fl);
      return FALSE;
    }

    $this->sampleRate = $headers['sampleRate'];
    $this->numChannels = $headers['numChannels'];
    $this->bitsPerSample = $headers['bitsPerSample'];
    $this->chunk2Size = $headers['chunk2Size'];
    $this->commonLength = $headers['commonLength'];

    $this->source = array();
    $this->sourceFH = $fl;
    return TRUE;
  }

  /**
  * read in background file
  * @param STRING $file - file name
  */
  public function loadBackground($file) {
    if (! file_exists($file)) {
      trigger_error("file $file does not exist", E_USER_WARNING);
      return FALSE;
    }
    if (! $fl = fopen($file, "r")) {
      trigger_error("cannot open $file", E_USER_WARNING);
      return FALSE;
    }

    $headers = $this->readHeaders($fl);
    if (! $headers) {
      trigger_error("wrong file headers", E_USER_WARNING);
      fclose($fl);
      return FALSE;
    }

    if ($headers['bitsPerSample'] != $this->bitsPerSample) {
      trigger_error(
        "background sound should have a " . $this->bitsPerSample ." bit per sample ratio",
        E_USER_WARNING
      );
      fclose($fl);
      return FALSE;
    }

    $this->backgroundFH = $fl;
    $this->backgroundHeaders = $headers;

    return TRUE;

  }

  /**
  * add a background musik to $this->samples property
  */
  public function addBackground() {
    $voiceFrameWidth = $this->numChannels * $this->bitsPerSample / 8;
    $voiceSampleWidth = $this->bitsPerSample / 8;
    $srcLength = strlen($this->samples);
    $srcNumChannels = $this->numChannels;

    $bgChannels = $this->backgroundHeaders['numChannels'];
    $bgBitsPerSample = $this->backgroundHeaders['bitsPerSample'];
    $bgSampleRate = $this->backgroundHeaders['sampleRate'];
    $bgFrameWidth = $bgChannels * $bgBitsPerSample / 8;
    $bgSampleWidth = $bgBitsPerSample / 8;
    $rateFactor = $bgSampleRate * $bgFrameWidth / $this->sampleRate;
    $bgLength = $bgSampleRate * $srcLength * $bgFrameWidth / ($voiceFrameWidth * $this->sampleRate);
    $startPos = mt_rand(0, ($this->backgroundHeaders['chunk2Size'] - $bgLength) / $bgFrameWidth) *
      $bgFrameWidth;
    fseek($this->backgroundFH, $this->riffHeaderLenght + $startPos, SEEK_SET);
    $bgSound = fread($this->backgroundFH, $bgLength);

    $amplitude = pow(2, $this->bitsPerSample - 1);
    if ($this->bitsPerSample == 8) {
      $bias = $amplitude;
      $format = 'C';
    } else {
      $bias = 0;
      $format = 's';
    }

    $bgAmplitude = pow(2, $bgBitsPerSample - 1);
    if ($bgBitsPerSample == 8) {
      $bgBias = $bgAmplitude;
      $bgFormat = 'C';
    } else {
      $bgBias = 0;
      $bgFormat = 's';
    }

    $bgPos = 0;
    $bgPosFloat = 0;
    ob_start();
    if ($srcNumChannels == 1) {
      // mono voice
      for ($pos = 0; $pos < $srcLength; $pos += $voiceFrameWidth) {
        list(, $bgSample) = unpack($bgFormat, substr($bgSound, $bgPos, $bgSampleWidth));
        $bgPosFloat += $rateFactor;
        $bgPos = (int)($bgPosFloat / $bgFrameWidth) * $bgFrameWidth;
        list(, $srcSample) = unpack($format, substr($this->samples, $pos, $voiceSampleWidth));
        $srcSample += $bgSample - $bgBias;
        echo pack($format, (int)($srcSample / 2));
      }
    } else {
      // handle stereo or more channels with worse performance
      for ($pos = 0; $pos < $srcLength; $pos += $voiceFrameWidth) {
        list(, $bgSample) = unpack($bgFormat, substr($bgSound, $bgPos, $bgSampleWidth));
        $bgPosFloat += $rateFactor;
        $bgPos = (int)($bgPosFloat / $bgFrameWidth) * $bgFrameWidth;
        list(, $srcSample) = unpack($format, substr($this->samples, $pos, $voiceSampleWidth));
        $srcSample += $bgSample - $bgBias;
        for ($ch = 0; $ch < $srcNumChannels; $ch++) {
          echo pack($format, (int)($srcSample / 2));
        }
      }
    }
    $this->samples = ob_get_clean();

  }

  /**
  * add a pause
  * @param FLOAT $length - pause length (s)
  */
  public function addPause($length) {
    return $this->addSinusSample(0, $length);
  }

  /**
  * add a simple sinus tone by frequency and length
  * @param FLOAT $freq - frequency (Hz), may be 0 for a pause
  * @param FLOAT $length - sample length (s)
  */
  public function addSinusSample($freq, $length) {

    $amplitude = pow(2, $this->bitsPerSample - 1);

    if ($this->bitsPerSample == 8) {
      $bias = $amplitude;
      $format = 'C';
    } else {
      $bias = 0;
      $format = 's';
    }

    if ($freq == 0) {
      $this->samples .= str_repeat(
        pack($format, $bias),
        $this->sampleRate * $length
      );
      return;
    }

    $step = 2 * M_PI / ($this->sampleRate / $freq);
    $sinus = '';
    for ($w = 0; $w < 2 * M_PI - $step; $w += $step) {
      $val = sin($w) * $amplitude + $bias;
      $sinus .= pack($format, (int)$val);
    }

    $this->samples .= str_repeat($sinus, $freq * $length);
  }

  /**
  * copy a sample from source
  * @param FLOAT $begin - sample begin (s)
  * @param FLOAT $end - sample end (s)
  */
  public function copyFromSource($begin, $end) {
    if (is_null($this->source)) {
      trigger_error("no source defined", E_USER_WARNING);
      return FALSE;
    }

    $frameWidth = $this->numChannels * $this->bitsPerSample / 8;
    $start = (int)($begin * $this->sampleRate) * $frameWidth;
    $stop = (int)($end * $this->sampleRate) * $frameWidth;

    $this->samples .= $this->readData($start, $stop - $start);
    return TRUE;
  }
}
