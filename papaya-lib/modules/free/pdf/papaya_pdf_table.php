<?php
/**
* class for a pdf table
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
* @version $Id: papaya_pdf_table.php 37714 2012-11-23 14:26:01Z weinert $
*/

/**
* Requires
*/
require_once(dirname(__FILE__).'/papaya_pdf_table_row.php');
require_once(dirname(__FILE__).'/papaya_pdf_table_cell.php');
require_once(dirname(__FILE__).'/papaya_pdf_table_column.php');

/**
* class for a pdf table
*
* @package Papaya-Modules
* @subpackage Free-PDF
*/
class papaya_pdf_table {
  /**
  * array of papaya_pdf_table_row
  * @var array $_rows
  */
  var $_rows = array();
  /**
  * array of columns
  * @var array $_cols
  */
  var $_cols = array();
  /**
  * current table row
  * @var papaya_pdf_table_row $_currentRow
  */
  var $_currentRow = NULL;
  /**
  * current table row
  * @var papaya_pdf_table_cell $_currentCell
  */
  var $_currentCell = NULL;

  /**
  * pdf document
  * @var papaya_pdf $_pdfDocument
  */
  var $_pdfDocument = NULL;

  /**
  * table width
  * @var float $width
  */
  var $width;

  /**
  * cell padding
  * @var float $padding
  */
  var $padding = 0.5;

  /**
  * border size - only 0 and 1 possible
  * @var integer $border
  */
  var $border = 1;

  /**
  * border color, rgb array - default black
  * @var array $borderColor
  */
  var $borderColor = array(0,0,0);

  /**
  * attrbutes
  * @var array $attributes
  */
  var $attributes = array();

  /**
  * constructor - remember pdf document object
  *
  * @param papaya_pdf &$pdf
  * @param array $attr attributes, default value NULL
  * @access public
  */
  function __construct(&$pdf, $attr = NULL) {
    $this->_pdfDocument = &$pdf;
    if (isset($attr)) {
      $this->attributes = $attr;
      if (isset($attr['border']) && $attr['border'] >= 0) {
        $this->border = (int)$attr['border'];
      }
      if (isset($attr['padding']) && $attr['padding'] > 0) {
        $this->padding = (float)$attr['padding'];
      }
      if (!empty($attr['border-color'])) {
        $this->borderColor = $pdf->getRGB($attr['border-color']);
      }
    }
  }

  /**
  * PHP 4 constructor redirect
  *
  * @param papaya_pdf &$pdf
  * @param array $attr attributes
  * @access public
  */
  function papaya_pdf_table(&$pdf, $attr) {
    $this->__construct($pdf, $attr);
  }

  /**
  * add a new table row
  *
  * @access public
  */
  function addRow() {
    $rowIdx = count($this->_rows);
    $this->_rows[$rowIdx] = new papaya_pdf_table_row($this);
    $this->_currentRow = &$this->_rows[$rowIdx];
    $this->_currentRow->index = $rowIdx;
  }

  /**
  * end current row
  *
  * @access public
  */
  function endRow() {
    $null = NULL;
    $this->_currentRow = &$null;
  }

  /**
  * add a table cell
  *
  * @param array $attr attributes
  * @param boolean $headerCell new cell is header cell (optional, default value FALSE)
  * @access public
  */
  function addCell($attr, $headerCell = FALSE) {
    if (count($this->_rows) <= 0) {
      $this->addRow();
    }
    $rowIdx = count($this->_rows) - 1;
    if ($headerCell) {
      $this->_currentCell =
        new papaya_pdf_table_headercell($this->_rows[$rowIdx], $attr);
    } else {
      $this->_currentCell = new papaya_pdf_table_cell($this->_rows[$rowIdx], $attr);
    }
    if (!isset($this->_cols[$this->_currentCell->columnIndex])) {
      $this->_cols[$this->_currentCell->columnIndex] =
        new papaya_pdf_table_column($this);
      $this->_cols[$this->_currentCell->columnIndex]->columnIndex =
        $this->_currentCell->columnIndex;
    }
  }

  /**
  * set text data for current cell
  *
  * @param string $str
  * @access public
  * @return boolean
  */
  function addCellContent($str) {
    if (isset($this->_currentCell)) {
      $this->_currentCell->addContent($str);
      return TRUE;
    }
    return FALSE;
  }

  /**
  * add an image to current cell content
  *
  * @param string $fileName
  * @param float $width
  * @param float $height
  * @access public
  * @return boolean
  */
  function addCellContentImage($fileName, $width, $height) {
    if (isset($this->_currentCell)) {
      $this->_currentCell->addContentImage($fileName, $width, $height);
      return TRUE;
    }
    return FALSE;
  }


  /**
  * set tag data for current cell
  *
  * @param string $tag tag name
  * @param array $attr tag attributes
  * @access public
  * @return boolean
  */
  function addCellContentTag($tag, $attr) {
    if (isset($this->_currentCell)) {
      $this->_currentCell->addContentTag($tag, $attr);
      return TRUE;
    }
    return FALSE;
  }

  /**
  * set tag data for current cell
  *
  * @param string $tag tag name
  * @param array $attr tag attributes
  * @access public
  * @return boolean
  */
  function endCellContentTag($tag, $attr) {
    if (isset($this->_currentCell)) {
      $this->_currentCell->endContentTag($tag, $attr);
      return TRUE;
    }
    return FALSE;
  }

  /**
  * end current cell
  *
  * @access public
  */
  function endCell() {
    if (isset($this->_currentCell)) {
      $this->_currentCell->applyAttributes();
    }
    $null = NULL;
    $this->_currentCell = &$null;
  }

  /**
  * set a fixed width for a column (a minimum width will overwrite this value)
  *
  * @param integer $colIdx
  * @param float $width
  * @access public
  * @return void
  */
  function setColumnWidth($colIdx, $width) {
    if (isset($this->_cols[$colIdx])) {
      $this->_cols[$colIdx]->setWidth($width);
    }
  }

  /**
  *
  *
  * @param integer $colIdx
  * @param float $min needed min width
  * @param float $max needed max width, default value 0
  * @access public
  */
  function updateColumnWidth($colIdx, $min, $max = 0) {
    if (isset($this->_cols[$colIdx])) {
      $this->_cols[$colIdx]->updateWidth($min, $max);
    }
  }

  /**
  * caclulate width for all columns
  *
  * @param float $maxWidth maximum width
  * @access public
  * @return float table minimum width
  */
  function calcColumnWidths($maxWidth) {
    $tableMinWidth = 0;
    $tableWidthDiff = 1;
    foreach (array_keys($this->_cols) as $colIdx) {
      $tableMinWidth += $this->_cols[$colIdx]->getMinWidth();
      $tableWidthDiff += $this->_cols[$colIdx]->getWidthDiff();
    }
    if ($maxWidth > $tableMinWidth) {
      $widthOverhead = $maxWidth - $tableMinWidth;
      $this->width = 0;
      foreach (array_keys($this->_cols) as $colIdx) {
        $this->_cols[$colIdx]->calcWidth($widthOverhead, $tableWidthDiff);
        $this->width += $this->_cols[$colIdx]->width;
      }
    } else {
      $this->width = $tableMinWidth;
    }
  }

  /**
  * get minimum height for row
  *
  * @param integer $rowIdx
  * @access public
  * @return float line height
  */
  function getMinRowHeight($rowIdx) {
    if (isset($this->_rows[(int)$rowIdx])) {
      return $this->_rows[(int)$rowIdx]->minHeight;
    } else {
      return $this->getLineHeight();
    }
  }

  /**
  * get min and max width for a string
  *
  * @param string $str
  * @access public
  * @return array
  */
  function getTextWidth($str) {
    $style = $this->_pdfDocument->getCurrentElementStyle();
    $this->_pdfDocument->activateCurrentStyle($style);
    $max = $this->_pdfDocument->GetStringWidth($str);
    $min = 0;
    $first = 0;
    $last = 0;
    $words = explode(' ', $str);
    $hasSpaces = count($words) > 1;
    if (is_array($words) && count($words) > 0) {
      $first = $this->_pdfDocument->GetStringWidth($words[0]);
      $last = $this->_pdfDocument->GetStringWidth($words[count($words) - 1]);
      foreach ($words as $word) {
        $i = $this->_pdfDocument->GetStringWidth($word);
        if ($i > $min) {
          $min = $i;
        }
      }
    }
    return array($min, $max, $first, $last, $hasSpaces);
  }

  /**
  * Enable layout for content output
  * @param string $tag
  * @return void
  */
  function enableTagLayout($tag) {
    $this->_pdfDocument->enableTagLayout($tag);
  }

  /**
  * Disable layout for content output
  * @param string $tag
  * @return void
  */
  function disableTagLayout($tag) {
    $this->_pdfDocument->disableTagLayout($tag);
  }

  /**
  * Get attributes
  *
  * @access public
  * @return array attributes
  */
  function getAttributes() {
    if (isset($this->attributes)) {
      return $this->attributes;
    } else {
      return array();
    }
  }

  /**
  * Get line height
  *
  * @access public
  * @return float line height
  */
  function getLineHeight() {
    return $this->_pdfDocument->getLineHeight();
  }

  /**
  * Count rows
  *
  * @access public
  * @return integer count
  */
  function rowCount() {
    return count($this->_rows);
  }

  /**
  * Get row by index
  *
  * @param integer $rowIdx
  * @access public
  * @return mixed NULL or integer id
  */
  function &getRowByIndex($rowIdx) {
    if (isset($this->_rows[$rowIdx])) {
      return $this->_rows[$rowIdx];
    }
    $null = NULL;
    return $null;
  }

  /**
  * Get collumn by index
  *
  * @param integer $colIdx
  * @access public
  * @return mixed NULL or integer id
  */
  function &getColByIndex($colIdx) {
    if (isset($this->_cols[$colIdx])) {
      return $this->_cols[$colIdx];
    }
    $null = NULL;
    return $null;
  }
}
?>