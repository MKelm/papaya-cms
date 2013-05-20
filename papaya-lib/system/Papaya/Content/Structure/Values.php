<?php
/**
* Content structure values list
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
* @version $Id: Values.php 38263 2013-03-11 19:05:10Z weinert $
*/

/**
* Content structure values list
*
* Content structure values are organized in groups and pages. A page can contain multiple groups
* and a group multiple values.
*
* @package Papaya-Library
* @subpackage Content
*/
class PapayaContentStructureValues extends PapayaObjectList {

  private $_group = NULL;

  public function __construct(PapayaContentStructureGroup $group) {
    parent::__construct('PapayaContentStructureValue');
    $this->_group = $group;
  }

  /**
   * Load value data from xml
   *
   * @param PapayaXmlElement $pageNode
   */
  public function load(PapayaXmlElement $groupNode) {
    foreach ($groupNode->ownerDocument->xpath()->evaluate('value', $groupNode) as $node) {
      $this[] = $value = new PapayaContentStructureValue($this->_group);
      $value->name = $node->getAttribute('name');
      $value->title = $node->getAttribute('title');
      $value->default = $node->getAttribute('default');
      if ($node->hasAttribute('type')) {
        $value->type = $node->getAttribute('type');
      }
      if ($node->hasAttribute('hint')) {
        $value->hint = $node->getAttribute('hint');
      } else {
        $value->hint = $groupNode->ownerDocument->xpath()->evaluate('string(hint)', $node);
      }
      $value->fieldType = $node->getAttribute('field');
      if ($node->hasAttribute('field-parameter')) {
        $value->fieldParameters = $node->getAttribute('field-parameter');
      } else {
        $parameterNodes = $groupNode->ownerDocument->xpath()->evaluate('field-parameter', $node);
        if (count($parameterNodes) > 0) {
          $fieldParameters = array();
          foreach ($parameterNodes as $parameterNode) {
            $key = $parameterNode->getAttribute('key');
            $text = $parameterNode->textContent;
            if (empty($key)) {
              $fieldParameters[$text] = $text;
            } else {
              $fieldParameters[$key] = $text;
            }
          }
          $value->fieldParameters = $fieldParameters;
        }
      }
    }
  }
}
