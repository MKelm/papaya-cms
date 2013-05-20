<?php
/**
* Class for a pdf wordbreak token
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
* @version $Id: papaya_pdf_wordbreak.php 32244 2009-09-29 10:19:00Z weinert $
*/

/**
* Class for a pdf wordbreak token
*
* @package Papaya-Modules
* @subpackage Free-PDF
*/
class papaya_pdf_wordbreak {

  /**
  * pdf generator
  * @var papaya_pdf
  */
  var $pdf = NULL;

  /**
  * create and initialize object
  * @param papaya_pdf $pdf
  * @return unknown_type
  */
  function __construct(&$pdf) {
    $this->pdf = &$pdf;
    $this->calcWidth();
  }

  /**
  * php 4 constructor redirect
  * @param papaya_pdf $pdf
  */
  function papaya_pdf_wordbreak(&$pdf) {
    $this->__construct($pdf);
  }

  /**
  * calculate needed width on pdf canvas
  * @return void
  */
  function calcWidth() {
    $this->width = $this->pdf->GetStringWidth(' ');
  }
}

?>