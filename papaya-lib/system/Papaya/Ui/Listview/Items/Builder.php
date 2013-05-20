<?php
/**
* Create listview items from an Traversable or Iterator
*
* @copyright 2011 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Ui
* @version $Id: Builder.php 36896 2012-03-28 09:44:17Z weinert $
*/

/**
* Create listview items from an Traversable or Iterator
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiListviewItemsBuilder {

  private $_dataSource = NULL;
  private $_callbacks = NULL;

  /**
  * Create object and store the data source
  *
  * @param Traversable|Array $dataSource
  */
  public function __construct($dataSource) {
    PapayaUtilConstraints::assertArrayOrTraversable($dataSource);
    $this->_dataSource = $dataSource;
  }

  /**
  * Getter for the datasource member variable
  *
  * @return Traversalbe|array
  */
  public function getDataSource() {
    return $this->_dataSource;
  }

  /**
  * Build the items
  *
  * @param PapayaUiListviewItems $items
  */
  public function fill(PapayaUiListviewItems $items) {
    if (!$this->callbacks()->onBeforeFill($items)) {
      $items->clear();
    }
    if (!isset($this->callbacks()->onCreateItem)) {
      $this->callbacks()->onCreateItem = array($this, 'createItem');
    }
    foreach ($this->getDataSource() as $index => $element) {
      $this->callbacks()->onCreateItem($items, $element, $index);
    }
    $this->callbacks()->onAfterFill($items);
  }

  /**
  * Getter/Setter for the callbacks list.
  *
  *
  * @param PapayaUiListviewItemsBuilderCallbacks $callbacks
  */
  public function callbacks(PapayaUiListviewItemsBuilderCallbacks $callbacks = NULL) {
    if (isset($callbacks)) {
      $this->_callbacks = $callbacks;
    } elseif (is_null($this->_callbacks)) {
      $this->_callbacks = new PapayaUiListviewItemsBuilderCallbacks();
    }
    return $this->_callbacks;
  }

  /**
  * Create a single item from a data source element and add it to the items. This method
  * will be used if no callback for onCreateItem is defined.
  *
  * @param object $context
  * @param PapayaUiListviewItems $items
  * @param mixed $element
  * @param scalar $index
  */
  public function createItem($context, PapayaUiListviewItems $items, $element, $index) {
    $items[] = new PapayaUiListviewItem('', (string)$element);
  }
}