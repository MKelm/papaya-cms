<?php
/**
* class for a pdf table column
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
* @version $Id: papaya_pdf_table_column.php 37705 2012-11-22 17:39:48Z weinert $
*/

/**
* class for a pdf table column
*
* @package Papaya-Modules
* @subpackage Free-PDF
*/
class papaya_pdf_table_column {
  /**
  * table (parent object)
  * @var papaya_pdf_table $table
  */
  var $table = NULL;
  /**
  * column index
  * @var integer $columnIndex
  */
  var $columnIndex = NULL;

  /**
  * needed minimum width
  * @var float $_minWidth
  */
  var $_minWidth = 5;
  /**
  * needed maximum width
  * @var float $_maxWidth
  */
  var $_maxWidth = 0;
  /**
  * needed maximum width
  * @var float $_maxWidth
  */
  var $_fixedWidth = 0;

  /**
  * calculated width
  * @var float $width
  */
  var $width = 0;

  /**
  * PHP5 constructor table row
  *
  * @param papaya_pdf_table &$table
  * @access public
  */
  function __construct(&$table) {
    $this->table = &$table;
  }

  /**
  * PHP 4 constructor redirect
  *
  * @param papaya_pdf_table &$table
  * @access public
  */
  function papaya_pdf_table_column(&$table) {
    $this->__construct($table);
  }

  /**
  * set a fixed width
  *
  * @param $width
  * @access public
  * @return void
  */
  function setWidth($width) {
    if ($this->_fixedWidth < $width) {
      $this->_fixedWidth = $width;
      $this->updateWidth($width, $width);
    }
  }

  /**
  * update needed min and max for width (increase only)
  *
  * @param float $min minimum width
  * @param float $max maximum width
  * @access public
  */
  function updateWidth($min, $max = 0) {
    if ($this->table->border <= 0 &&
        (
         $this->columnIndex == 0 ||
         $this->columnIndex + 1 == count($this->table->_cols)
        )
       ) {
      $paddingInc = $this->table->padding + 0.001;
    } else {
      $paddingInc = $this->table->border + ($this->table->padding * 2) + 0.001;
    }
    if ($min + $paddingInc > $this->_minWidth) {
      $this->_minWidth = $min + $paddingInc;
    }
    if ($max + $paddingInc > $this->_maxWidth) {
      $this->_maxWidth = $max + $paddingInc;
    }
    if ($this->_minWidth > $this->_maxWidth) {
      $this->_maxWidth = $this->_minWidth;
    }
    $this->width = $this->_minWidth;
  }

  /**
  * get minimum width
  *
  * @access public
  * @return float
  */
  function getMinWidth() {
    return $this->_minWidth;
  }

  /**
  * get differece between max and min width
  *
  * @access public
  * @return float
  */
  function getWidthDiff() {
    if ($this->_fixedWidth > 0) {
      return 0;
    }
    return ($this->_maxWidth - $this->_minWidth);
  }

  /**
  * Get left
  *
  * @access public
  * @return float
  */
  function getLeft() {
    $result = 0;
    for ($i = 0; $i < $this->columnIndex; $i++) {
      $result += $this->table->_cols[$i]->width;
    }
    return $result;
  }

  /**
  * calculate width
  *
  * @param float $widthOverhead
  * @param float $allColumnDiff
  * @access public
  */
  function calcWidth($widthOverhead, $allColumnDiff) {
    if ($this->_fixedWidth == 0) {
      $diff = $this->_maxWidth - $this->_minWidth;
      $add = ($widthOverhead * $diff / $allColumnDiff);
      if ($add > 0) {
        $this->width = $this->_minWidth + $add;
      }
    } elseif ($this->_fixedWidth > $this->_minWidth) {
      $this->width = $this->_fixedWidth;
    } else {
      $this->width = $this->_minWidth;
    }
  }
}
?>