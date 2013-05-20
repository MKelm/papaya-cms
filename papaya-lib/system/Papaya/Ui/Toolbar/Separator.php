<?php
/**
* A menu/toolbar element separator.
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
* @version $Id: Separator.php 35663 2011-04-08 09:58:21Z weinert $
*/

/**
* A menu/toolbar element separator.
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiToolbarSeparator extends PapayaUiToolbarElement {

  /**
  * Append the separator to the parent xml element
  *
  * @param PapayaXmlElement $parent
  */
  public function appendTo(PapayaXmlElement $parent) {
    if ($this->isDisplayed()) {
      $parent->appendElement('separator');
    }
  }

  /**
  * A separator is not displayed to the xml if it is the first element, the last element or if the
  * previous element is an separator, too.
  */
  public function isDisplayed() {
    $index = $this->index();
    $previous = $index - 1;
    $next = $index + 1;
    if ($previous >= 0 &&
        $next < count($this->collection())) {
      if (!$this->collection()->get($previous) instanceOf self) {
        return TRUE;
      }
    }
    return FALSE;
  }
}