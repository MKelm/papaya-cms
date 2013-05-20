<?php
/**
* Subitems are additional data, attached to an listview item. They are displayed as additional
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
* @version $Id: Subitems.php 35586 2011-03-30 14:24:57Z weinert $
*/

/**
* Subitems are additional data, attached to an listview item. They are displayed as additional
* columns in the most cases.
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiListviewSubitems
  extends PapayaUiControlCollection {

  /**
  * Only {@see PapayaUiListviewSubItem} objects are allowed in this list
  *
  * @var string
  */
  protected $_itemClass = 'PapayaUiListviewSubItem';

  /**
  * Provide no tag name, so no additional element will be added in
  * {@see PapayaUiControlCollection::appendTo()) that whould wrap the items.
  *
  * @var string
  */
  protected $_tagName = '';

  /**
  * Create object an set owner listview object.
  *
  * @param PapayaUiListview $listview
  */
  public function __construct(PapayaUiListviewItem $item) {
    $this->owner($item);
  }

  /**
  * Return the listview item for this list of subitems
  */
  public function owner(PapayaUiListviewItem $item = NULL) {
    return parent::owner($item);
  }

  /**
  * Return the listview the owner item is part of.
  *
  * @return PapayaUiListview
  */
  public function getListview() {
    return $this->owner()->collection()->owner();
  }
}