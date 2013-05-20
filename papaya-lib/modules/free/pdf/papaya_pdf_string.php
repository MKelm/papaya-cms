<?php
/**
* Class for a pdf string token
*
* @copyright 2002-2006 by papaya Software GmbH - All rights reserved.
* @link      http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Modules
* @subpackage Free-PDF
* @version $Id: papaya_pdf_string.php 32244 2009-09-29 10:19:00Z weinert $
*/

/**
* Class for a pdf string token
*
* @package Papaya-Modules
* @subpackage Free-PDF
*/
class papaya_pdf_string {

  /**
  * string content
  * @var string
  */
  var $content = '';

  /**
  * style attributes
  * @var array
  */
  var $style = NULL;
  /**
  * width on pdf canvas
  */
  var $width = 0;

  /**
  * create an initlize a pdf string token
  * @param papaya_pdf $pdf
  * @param string $content
  * @param array $style
  */
  function __construct(&$pdf, $content, $style) {
    $this->pdf = &$pdf;
    $this->content = $content;
    $this->style = $style;
    $this->calcWidth();
  }

  /**
  * php 4 constructor redirect
  * @param papaya_pdf $pdf
  * @param string $content
  * @param array $style
  */
  function papaya_pdf_wordbreak(&$pdf, $content, $style) {
    $this->__construct($pdf, $content, $style);
  }

  /**
  * calculate needed width on pdf canvas
  * @return void
  */
  function calcWidth() {
    $this->width = $this->pdf->GetStringWidth($this->content);
  }
}
?>