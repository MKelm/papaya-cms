<?php
/**
* A subitem is additional data, attached to an listview item. They are displayed as additional
* columns in the most cases.
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
* @version $Id: Subitem.php 35859 2011-07-05 11:51:11Z weinert $
*/

/**
* A subitem is additional data, attached to an listview item. They are displayed as additional
* columns in the most cases.
*
* @package Papaya-Library
* @subpackage Ui
*/
abstract class PapayaUiListviewSubitem extends PapayaUiControlCollectionItem {

  /**
  * Alignment, if it is NULL, the column alignment is used, "left" is the default value.
  *
  * @var NULL|integer
  */
  protected $_align = NULL;

  /**
  * Specific parameters for a link
  *
  * @var array
  */
  protected $_actionParameters = NULL;

  /**
  * Set the alignment.
  *
  * @param NULL|integer $align
  */
  public function setAlign($align) {
    $this->_align = $align;
  }

  /**
  * Get the alignment, if the internal value is NULL. It will try to get the alignment from the
  * column. If the column is not available it will return "left".
  *
  * @return integer
  */
  public function getAlign() {
    if (is_null($this->_align)) {
      $columnIndex = $this->index();
      if ($this->hasCollection() &&
          $this->collection()->getListview()->columns()->has($columnIndex + 1)) {
        return $this->collection()->getListview()->columns()->get($columnIndex + 1)->getAlign();
      } else {
        return PapayaUiOptionAlign::LEFT;
      }
    } else {
      return $this->_align;
    }
  }

  /**
  * Store action parameters for links or form elements in the subitem
  *
  * @param array $actionParameters
  */
  public function setActionParameters(array $actionParameters = NULL) {
    $this->_actionParameters = $actionParameters;
  }
}