<?php
/**
* Page module - Sitemap
*
* @copyright 2002-200p by papaya Software GmbH - All rights reserved.
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
* @subpackage _Base
* @version $Id: content_sitemap.php 36476 2011-12-02 16:50:52Z weinert $
*/

/**
* Basic class page module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');
/**
* Navigation class
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_sitemap.php');

/**
* Page module - Sitemap
*
* @package Papaya-Modules
* @subpackage _Base
*/
class content_sitemap extends base_content {

  var $editFields = array(
    'title' => array('Title', 'isNoHTML', FALSE, 'input', 50, '', ''),
    'text' => array('Text', 'isSomeText', FALSE, 'textarea', 10, '', ''),
    'cols' => array('Columns', 'isNum', FALSE, 'input', 2, '', 2),
    'root' => array('Base', 'isNum', FALSE, 'pageid', 5, '', 0),
    'format' => array('View', 'isAlpha', TRUE, 'combo',
      array('breadcrumb' => 'breadcrumb', 'path' => 'path',
        'static' => 'static'), '', 'path'),
    'forstart' => array('Offset', 'isNum', FALSE, 'input', 2, '', 1),
    'forend' => array('Depth', 'isNum', FALSE, 'input', 2, '', 2),
    'focus' => array('Focus', 'isSomeText', TRUE, 'combo',
      array('dyna' => 'dynamic', 'root' => 'root based'), '', 'root'),
    'foclevels' => array('Focus levels', 'isNum', FALSE, 'input', 2, '', 0),
    'link_showhidden' => array(
      'Show hidden', 'isNum', TRUE, 'yesno', NULL, '', 0
    ),
    'link_outputmode' => array(
      'Link output mode', 'isAlphaNumChar', FALSE, 'function', 'callbackOutputModes', '', ''
    )
  );

  /**
  * Get parsed data
  *
  * @access public
  * @param array|NULL $parseParams Parameters from output filter
  * @return string
  */
  function getParsedData($parseParams = NULL) {
    $this->setDefaultData();
    $result = '';
    $result .= sprintf(
      '<title>%s</title>',
      papaya_strings::escapeHTMLChars($this->data['title'], TRUE)
    );
    $result .= sprintf(
      '<text>%s</text>',
      $this->getXHTMLString($this->data['text'], TRUE)
    );
    if (defined('PAPAYA_DEFAULT_HOST') && PAPAYA_DEFAULT_HOST) {
      $result .= sprintf(
        '<host>%s</host>',
        $this->getXHTMLString(PAPAYA_DEFAULT_HOST, TRUE)
      );
    }
    if ($this->data['link_outputmode'] == '[view]') {
      $viewMode = empty($parseParams['link_outputmode'])
        ? $parseParams['viewmode'] : $parseParams['link_outputmode'];
    } elseif (!empty($this->data['link_outputmode'])) {
      $viewMode = $this->data['link_outputmode'];
    } else {
      $viewMode = NULL;
    }
    $this->map = new base_sitemap(
      $this->parentObj,
      $this->data,
      NULL,
      $viewMode
    );
    $this->map->getMode = 'Content';
    $result .= $this->map->getXML(!$this->data['link_showhidden']);
    return $result;
  }

  /**
  * Get XHTML for special edit field showing all currunt output modes in a selectbox,
  * adding special items "None" and "[View]".
  *
  * @param string $name
  * @param array $field
  * @param string $data
  */
  function callbackOutputModes($name, $field, $data) {
    $result = sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale">',
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($name)
    );
    if (empty($data)) {
      $data = isset($this->parentObj->viewLink['viewmode_ext'])
        ? $this->parentObj->viewLink['viewmode_ext']
        : $this->papaya()->options->get('PAPAYA_URL_EXTENSION');
    }
    include_once(PAPAYA_INCLUDE_PATH.'system/base_viewlist.php');
    $viewList = new base_viewlist();
    $viewList->loadViewModesList();
    $result .= sprintf(
      '<option value="">%s</option>',
      papaya_strings::escapeHTMLChars($this->_gt('[None]'))
    );
    $selected = ('[system]' == $data) ? ' selected="selected"' : '';
    $result .= sprintf(
      '<option value="[view]"%s>%s</option>',
      $selected,
      papaya_strings::escapeHTMLChars($this->_gt('[View]'))
    );
    if (isset($viewList->viewModes) && is_array($viewList->viewModes)) {
      foreach ($viewList->viewModes as $viewMode) {
        $selected = ($viewMode['viewmode_ext'] == $data) ? ' selected="selected"' : '';
        $result .= sprintf(
          '<option value="%s"%s>%s</option>',
          papaya_strings::escapeHTMLChars($viewMode['viewmode_ext']),
          $selected,
          papaya_strings::escapeHTMLChars($viewMode['viewmode_ext'])
        );
      }
    }
    $result .= '</select>';
    return $result;
  }
}
?>