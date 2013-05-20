<?php
/**
* Papaya request parser for system urls
*
* @copyright 2009 by papaya Software GmbH - All rights reserved.
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
* @subpackage Request
* @version $Id: System.php 35317 2011-01-14 10:02:45Z weinert $
*/

/**
* Papaya request parser for system urls
*
* @package Papaya-Library
* @subpackage Request
*/
class PapayaRequestParserSystem extends PapayaRequestParser {

  /**
  * PCRE pattern for thumbnail links
  * @var string
  */
  private $_pattern = '(/index\.(?P<mode>urls|status)$)Dix';

  /**
  * Parse url and return data
  * @return FALSE|array
  */
  public function parse($url) {
    if (preg_match($this->_pattern, $url->getPath(), $matches)) {
      $result = array(
        'mode' => $matches['mode']
      );
      return $result;
    }
    return FALSE;
  }
}

?>