<?php
/**
* class for a pdf table cell
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
* @version $Id: papaya_pdf_table_cell.php 37699 2012-11-21 17:58:26Z weinert $
*/

/**
* Class for a pdf table cell
*
* @package Papaya-Modules
* @subpackage Free-PDF
*/
class papaya_pdf_table_cell {
  /**
  * parent row
  * @var papaya_pdf_table_row $_row
  */
  var $_row = NULL;
  /**
  * cell column index
  * @var integer $columnIndex
  */
  var $columnIndex = 0;

  /**
  * xml tree for content elements
  * @var DOMDocument|xmltree_common $_xmlTree
  */
  var $_xmlTree = NULL;
  /**
  * current xml node for a content element
  * @var DOMNode|expat_node $_currentNode
  */
  var $_currentNode = NULL;

  /**
  * minum height for this cell
  * @var float $_minHeight
  */
  var $_minHeight = NULL;

  var $_minWidth = 0;
  var $_maxWidth = 0;
  var $_wordWidth = 0;

  /**
  * block tags - trigger a linebreak
  * @var array $blockElementTags
  */
  var $blockElementTags = array('p', 'br', 'h1', 'h2', 'h3', 'h4', 'h5', 'li', 'ul');

  /**
  * attrbutes
  * @var array $attr
  */
  var $attr = array();

  /**
  * table data cell contrcutor
  *
  * @param papaya_pdf_table_row &$row
  * @access public
  */
  function __construct(&$row, $attr = NULL) {
    $this->_row = &$row;

    if (class_exists('DOMDocument', FALSE) && version_compare(PHP_VERSION, '5.2', '>=')) {
      $this->_xmlTree = new DOMDocument('1.0', 'UTF-8');
    } else {
      $this->_xmlTree = &simple_xmltree::create();
    }
    $cell = &$this->_xmlTree->createElement('cell');
    $this->_xmlTree->appendChild($cell);
    $this->_currentNode = &$cell;
    $this->columnIndex = $this->_row->addCell($this);
    if (isset($attr)) {
      $this->attributes = $attr;
    }
  }

  /**
  * PHP 4 constructor redirect
  *
  * @param papaya_pdf_table_row &$row
  * @access public
  */
  function papaya_pdf_table_cell(&$row, $attr = NULL) {
    $this->__construct($row, $attr);
  }

  /**
  * apply attributes to cell
  *
  * @access public
  * @return void
  */
  function applyAttributes() {
    if (isset($this->attributes['width'])) {
      $this->_row->table->setColumnWidth($this->columnIndex, (float)$this->attributes['width']);
    }
    if (isset($this->attributes['min-width'])) {
      $this->_row->table->updateColumnWidth(
        $this->columnIndex, (float)$this->attributes['min-width']
      );
    }
  }

  /**
  * set content
  *
  * @param string $str
  * @access public
  */
  function addContent($str) {
    if (isset($this->_currentNode)) {
      $newNode = &$this->_xmlTree->createTextNode($str);
      $this->_currentNode->appendChild($newNode);
      list($minWidth, $maxWidth, $firstWidth, $lastWidth, $hasSpaces) =
        $this->_row->table->getTextWidth($str);
      $this->_wordWidth += $firstWidth;
      $this->_maxWidth += $maxWidth;
      $this->updateColumnWidth(
        $width = ($this->_wordWidth > $minWidth) ? $this->_wordWidth : $minWidth,
        $this->_maxWidth
      );
      if ($hasSpaces) {
        $this->_wordWidth = $lastWidth;
      }
      $this->updateCellHeight($this->_row->table->getLineHeight());
    }
  }

  /**
  * add a content image - this is a special tag
  *
  * @param string $fileName
  * @param float $width
  * @param float $height
  * @access public
  */
  function addContentImage($fileName, $width, $height) {
    if (isset($this->_currentNode)) {
      $newNode = &$this->_xmlTree->createElement('image');
      $newNode->setAttribute('src', $fileName);
      $this->_currentNode->appendChild($newNode);
      $this->updateColumnWidth($width);
      $this->updateCellHeight($this->_row->table->getLineHeight());
    }
  }

  /**
  * add a new content tag
  *
  * @param string $tag
  * @param array $attr
  * @access public
  */
  function addContentTag($tag, $attr) {
    $newNode = &$this->_xmlTree->createElement($tag);
    if (isset($attr) && is_array($attr)) {
      foreach ($attr as $key => $val) {
        $newNode->setAttribute($key, $val);
      }
    }
    if (isset($this->_currentNode)) {
      $this->_currentNode->appendChild($newNode);
    } else {
      $this->_xmlTree->documentElement->appendChild($newNode);
    }
    $this->_currentNode = &$newNode;
    $this->_row->table->enableTagLayout($tag);
    if (in_array($tag, $this->blockElementTags)) {
      $this->_wordWidth = 0;
    }
  }

  /**
  * end a content tag
  *
  * @param string $tag
  * @param array $attr
  * @access public
  */
  function endContentTag($tag, $attr) {
    if (isset($this->_currentNode) && isset($this->_currentNode->parentNode)) {
      $this->_currentNode = &$this->_currentNode->parentNode;
    } else {
      $null = NULL;
      $this->_currentNode = &$null;
    }
    if (in_array($tag, $this->blockElementTags)) {
      $this->_wordWidth = 0;
      $this->_maxWidth = 0;
    }
    $this->_row->table->disableTagLayout($tag);
  }

  /**
  * update column width  depending on text width
  *
  * @param float $minWidth
  * @param float $maxWidth
  * @access public
  */
  function updateColumnWidth($minWidth, $maxWidth = 0) {
    $this->_row->table->updateColumnWidth($this->columnIndex, $minWidth, $maxWidth);
  }

  /**
  * set the minimum height need for this cell content
  *
  * @param float $minHeight
  * @access public
  */
  function updateCellHeight($minHeight) {
    if (!isset($this->_minHeight)) {
      $this->_minHeight = $minHeight;
      $this->_row->updateMinHeight($minHeight);
    }
  }

  /**
  * Get width
  *
  * @access public
  * @return float
  */
  function getWidth() {
    $col = &$this->_row->table->getColByIndex($this->columnIndex);
    return $col->width;
  }

  /**
  * Get left
  *
  * @access public
  * @return float
  */
  function getLeft() {
    $col = &$this->_row->table->getColByIndex($this->columnIndex);
    return $col->getLeft();
  }

  /**
  * Get attributes
  *
  * @access public
  * @return string attributes in string
  */
  function getAttributes() {
    $parentAttr = $this->_row->getAttributes();
    if (isset($this->attributes)) {
      return array_merge($parentAttr, $this->attributes);
    } else {
      return $parentAttr;
    }
  }

  /**
  * Output data
  *
  * @param papaya_pdf &$pdf
  * @access public
  */
  function outputData(&$pdf) {
    if (isset($this->_xmlTree) && isset($this->_xmlTree->documentElement)) {
      $this->outputDataNode($pdf, $this->_xmlTree->documentElement);
    }
  }

  /**
  * Output data node
  *
  * @param papaya_pdf &$pdf
  * @param expat_element_node &$node
  * @access public
  */
  function outputDataNode(&$pdf, &$node) {
    if ($node->hasChildNodes()) {
      for ($idx = 0; $idx < $node->childNodes->length; $idx++) {
        $subNode = &$node->childNodes->item($idx);
        if (isset($subNode) && $subNode->nodeType == XML_ELEMENT_NODE) {
          $pdf->openTag($subNode->nodeName, $subNode->attributes);
          $this->outputDataNode($pdf, $subNode);
          $pdf->closeTag($subNode->nodeName, $subNode->attributes);
        } elseif (isset($subNode) && $subNode->nodeType == XML_TEXT_NODE) {
          if (method_exists($subNode, 'valueOf')) {
            $pdf->writeText($subNode->valueOf());
          } else {
            $pdf->writeText($subNode->nodeValue);
          }
        }
      }
    }
  }
}

/**
* Table header cell (different default formatting)
*
* @package Papaya-Modules
* @subpackage Free-PDF
*/
class papaya_pdf_table_headercell extends papaya_pdf_table_cell {

}
?>