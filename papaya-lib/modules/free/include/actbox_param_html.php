<?php
/**
* Action box for HTML, depending on a parameter
*
* @copyright 2002-2007 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Modules
* @subpackage Free-Include
* @version $Id: actbox_param_html.php 33004 2009-11-12 09:38:59Z weinert $
*/

/**
* Basic class Action box
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');

/**
* Action box for HTML, depending on a parameter
*
* @package Papaya-Modules
* @subpackage Free-Include
*/
class actionbox_param_html extends base_actionbox {

  /**
  * Preview allowed?
  * @var boolean $preview
  */
  var $preview = TRUE;

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'param_name' => array('Parameter namespace', 'isNoHTML', FALSE, 'input', 100,
      'Leave empty to fetch get parameters.',''),
    'param' => array('Parameter', 'isNoHTML', TRUE, 'input', 100,'',''),
    'operator' => array(
      'Operator',
      '(^(EQ|NE|LT|GT|LE|GE|RE)$)',
      TRUE,
      'combo',
      array(
        'EQ' => '==',
        'NE' => '!=',
        'LT' => '<',
        'GT' => '>',
        'LE' => '<=',
        'GE' => '>=',
        'RE' => 'Regular expression match'
      ),
      '',
      'EQ'
    ),
    'compare' => array(
      'Match value', 'isSomeText', TRUE, 'input', 255, 'Value to match against.', ''
    ),
    'text' => array('Text if match', 'isSomeText', FALSE, 'textarea', 20,'',''),
    'else_text' => array('Text if no match', 'isSomeText', FALSE, 'textarea', 20, '','')
  );

  /**
  * Get parsed data
  *
  * @access public
  * @return string
  */
  function getParsedData() {
    if (isset($this->data['text']) && trim($this->data['text']) != '') {
      if (!empty($this->data['param_name'])) {
        $this->paramName = papaya_strings::escapeHTMLChars($this->data['param_name']);
        $this->initializeParams();
        $params = &$this->params;
      } else {
        $params = &$_GET;
      }
      if (isset($this->data['param']) && isset($params[$this->data['param']]) &&
          isset($this->data['compare'])) {
        $operators = array('EQ', 'NE', 'LT', 'GT', 'LE', 'GE', 'RE');
        if (isset($this->data['operator']) && in_array($this->data['operator'], $operators)) {
          $match = FALSE;
          $value = $params[$this->data['param']];
          $compare = $this->data['compare'];
          switch ($this->data['operator']) {
          case 'EQ':
            if ($value == $compare) {
              $match = TRUE;
            }
            break;
          case 'NE':
            if ($value != $compare) {
              $match = TRUE;
            }
            break;
          case 'LT':
            if ($value < $compare) {
              $match = TRUE;
            }
            break;
          case 'GT':
            if ($value > $compare) {
              $match = TRUE;
            }
            break;
          case 'LE':
            if ($value <= $compare) {
              $match = TRUE;
            }
            break;
          case 'GE':
            if ($value >= $compare) {
              $match = TRUE;
            }
            break;
          case 'RE':
            if (preg_match($compare, $value)) {
              $match = TRUE;
            }
            break;
          }
          if ($match) {
            return $this->data['text'];
          }
        }
      }
    }
    if (isset($this->data['else_text']) && trim($this->data['else_text']) != '') {
      return $this->data['else_text'];
    }
    return FALSE;
  }
}
?>
