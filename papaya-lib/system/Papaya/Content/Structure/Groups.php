<?php
/**
* Content structure values group list
*
* @copyright 2013 by papaya Software GmbH - All rights reserved.
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
* @subpackage Content
* @version $Id: Groups.php 38263 2013-03-11 19:05:10Z weinert $
*/

/**
* Content structure values group list
*
* Content structure values are organized in groups and pages. A page can contain multiple groups
* and a group multiple values.
*
* @package Papaya-Library
* @subpackage Content
*/
class PapayaContentStructureGroups extends PapayaObjectList {

  private $_page = NULL;

  public function __construct(PapayaContentStructurePage $page) {
    parent::__construct('PapayaContentStructureGroup');
    $this->_page = $page;
  }

  /**
   * Load group data from xml
   *
   * @param PapayaXmlElement $pageNode
   */
  public function load(PapayaXmlElement $pageNode) {
    foreach ($pageNode->ownerDocument->xpath()->evaluate('group', $pageNode) as $node) {
      $this[] = $group = new PapayaContentStructureGroup($this->_page);
      $group->name = $node->getAttribute('name');
      $group->title = $node->getAttribute('title');
      $group->values()->load($node);
    }
  }
}
