<?php
/**
* Content structure pages, defines the main items list of a constent structure.
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
* @version $Id: Pages.php 38263 2013-03-11 19:05:10Z weinert $
*/

/**
* Content structure pages, defines the main items list of a constent structure.
*
* Content structures are organized in groups and pages. A page can contain multiple groups
* and a group multiple values.
*
* @package Papaya-Library
* @subpackage Content
*/
class PapayaContentStructurePages extends PapayaObjectList {

  public function __construct() {
    parent::__construct('PapayaContentStructurePage');
  }

  /**
   * Load page data from xml
   *
   * @param PapayaXmlElement $root
   */
  public function load(PapayaXmlElement $structure) {
    foreach ($structure->ownerDocument->xpath()->evaluate('page', $structure) as $node) {
      $this[] = $page = new PapayaContentStructurePage();
      $page->name = $node->getAttribute('name');
      $page->title = $node->getAttribute('title');
      $page->groups()->load($node);
    }
  }
}
