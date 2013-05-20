<?php
/**
* class for a pdf table row
*
* @copyright 2002-2009 by papaya Software GmbH - All rights reserved.
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
* @version $Id: papaya_pdf_table_row.php 34957 2010-10-05 15:57:41Z weinert $
*/

/**
* class for a pdf table row
*
* @package Papaya-Modules
* @subpackage Free-PDF
*/
class papaya_pdf_table_row {
  /**
  * table (parent object)
  * @var papaya_pdf_table $table
  */
  var $table = NULL;

  /**
  * array of papaya_pdf_table_cell
  * @var array $_cells
  */
  var $_cells = array();

  /**
  * row index
  * @var integer $index
  */
  var $index = 0;

  /**
  * needed height (first possible line break)
  * @var float $minHeight
  */
  var $minHeight = 0;

  /**
  * top left of this column
  * @var array $leftTop
  */
  var $leftTop = NULL;
  /**
  * bottom left of this column
  * @var array $leftBottom
  */
  var $leftBottom = NULL;

  /**
  * attrbutes
  * @var array $attr
  */
  var $attr = array();

  /**
  * table row
  *
  * @param papaya_pdf_table &$table
  * @access public
  */
  function __construct(&$table, $attr = NULL) {
    $this->table = &$table;
    if (isset($attr)) {
      $this->attributes = $attr;
    }
  }

  /**
  * PHP 4 constructor redirect
  *
  * @param papaya_pdf_table &$table
  * @access public
  */
  function papaya_pdf_table_row(&$table, $attr = NULL) {
    $this->__construct($table, $attr);
  }

  /**
  * add a table cell object to row
  *
  * @param papaya_pdf_table_cell &$cell
  * @access public
  */
  function addCell(&$cell) {
    $idx = count($this->_cells);
    $this->_cells[$idx] = &$cell;
    return $idx;
  }

  /**
  * minimum height needed for (breakable) content
  *
  * @param float $height
  * @access public
  */
  function updateMinHeight($height) {
    if ($this->table->border <= 0 &&
        ($this->index == 0 || $this->index + 1 == count($this->table->_rows))) {
      $padInc = $this->table->padding;
    } else {
      $padInc = $this->table->border + ($this->table->padding * 2);
    }
    if (($height + $padInc) > $this->minHeight) {
      $this->minHeight = $height + $padInc;
    }
  }

  /**
  * Set left top
  *
  * @param integer $page
  * @param integer $column
  * @param integer $x
  * @param integer $y
  * @access public
  */
  function setLeftTop($page, $column, $x, $y) {
    $this->leftTop = array(
      'page' => $page,
      'col' => $column,
      'x' => $x,
      'y' => $y,
    );
  }

  /**
  * Get left top
  *
  * @access public
  * @return array|NULL left top positions
  */
  function getLeftTop() {
    if (isset($this->leftTop)) {
      return $this->leftTop;
    } elseif ($this->index > 0) {
      $row = &$this->table->getRowByIndex($this->index - 1);
      $this->leftTop = $row->getLeftBottom();
      return $this->leftTop;
    } else {
      return NULL;
    }
  }

  /**
  * Get attributes
  *
  * @access public
  * @return string attributes in string
  */
  function getAttributes() {
    $parentAttr = $this->table->getAttributes();
    if (isset($this->attributes)) {
      return array_merge($parentAttr, $this->attributes);
    } else {
      return $parentAttr;
    }
  }

  /**
  * Set left bottom
  *
  * @param integer $page
  * @param integer $column
  * @param integer $x
  * @param integer $y
  * @access public
  */
  function setLeftBottom($page, $column, $x, $y) {
    if (!isset($this->leftBottom) ||
        ($this->leftBottom['page'] < $page) ||
        ($this->leftBottom['page'] == $page && $this->leftBottom['col'] < $column) ||
        (
         $this->leftBottom['page'] == $page &&
         $this->leftBottom['col'] == $column &&
         $this->leftBottom['y'] < $y
        )
       ) {
      $this->leftBottom = array(
        'page' => $page,
        'col' => $column,
        'x' => $x
      );
      if ($this->table->border <= 0 &&
          ($this->index == 0 || $this->index + 1 == count($this->table->_rows))) {
        $this->leftBottom['y'] = $y;
      } else {
        $this->leftBottom['y'] =
          $y + $this->table->padding;
      }
    }
  }

  /**
  * Get left bottom
  *
  * @access public
  */
  function getLeftBottom() {
    return $this->leftBottom;
  }

  /**
  * Count cells
  *
  * @access public
  * @return integer count
  */
  function cellCount() {
    return count($this->_cells);
  }

  /**
  * get cell by index
  *
  * @param integer $cellIdx
  * @access public
  * @return papaya_pdf_table_cell|NULL
  */
  function &getCellByIndex($cellIdx) {
    if (isset($this->_cells[$cellIdx])) {
      return $this->_cells[$cellIdx];
    }
    $null = NULL;
    return $null;
  }
}
?>