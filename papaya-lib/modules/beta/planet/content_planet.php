<?php
/**
* Page module link with teaser.
*
* @copyright 2002-2009 by papaya Software GmbH - All rights reserved.
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
* @subpackage Beta-Planet
* @version $Id: content_planet.php 32554 2009-10-14 11:22:09Z weinert $
*/

/**
* Basic class page module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');
/**
* Page module link with teaser.
*
* This link comes with additional data which will be shown by another Category
*
* @package Papaya-Modules
* @subpackage Beta-Planet
*/
class content_planet extends base_content {

  /**
  * Is cacheable?
  * @var boolean $cacheable
  */
  var $cacheable = FALSE;

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'title' => array('Title', 'isSomeText', TRUE, 'input', 400, '', ''),
    'subtitle' => array('Subtitle', 'isSomeText', FALSE, 'input', 400, '', ''),
    'Entries',
    'maximum' => array('Maximum', 'isSomeText', FALSE, 'input', 3, '', 50)
  );

  /**
  * Redirect to URL
  *
  * @access public
  * @param array | NULL $parseParams Parameters from output filter
  * @return string
  */
  function getParsedData($parseParams = NULL) {
    $this->setDefaultData();
    $result = sprintf(
      '<title encoded="%s">%s</title>'.LF,
      rawurlencode($this->data['title']),
      papaya_strings::escapeHTMLChars($this->data['title'])
    );
    $result .= sprintf(
      '<subtitle encoded="%s">%s</subtitle>'.LF,
      rawurlencode($this->data['subtitle']),
      papaya_strings::escapeHTMLChars($this->data['subtitle'])
    );
    include_once(dirname(__FILE__).'/base_planet.php');
    $planet = new base_planet();
    $planet->loadEntries($this->data['maximum']);
    $result .= '<entries>';
    foreach ($planet->entries as $entry) {
      $result .= sprintf(
        '<entry ident="%s" url="%s" updated="%s">',
        papaya_strings::escapeHTMLChars($entry['feedentry_ident']),
        papaya_strings::escapeHTMLChars($entry['feedentry_url']),
        gmdate('Y-m-d H:i:s', $entry['feedentry_updated'])
      );
      $result .= sprintf(
        '<title>%s</title>',
        papaya_strings::escapeHTMLChars($entry['feedentry_title'])
      );
      $result .= sprintf(
        '<summary type="%s">%s</summary>',
        papaya_strings::escapeHTMLChars($entry['feedentry_summary_type']),
        papaya_strings::escapeHTMLChars($entry['feedentry_summary'])
      );
      $result .= '</entry>';
    }
    $result .= '</entries>';
    return $result;
  }
}

?>