<?php
/**
* Papaya request parser for dynamic image links
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
* @version $Id: Image.php 35317 2011-01-14 10:02:45Z weinert $
*/

/**
* Papaya request parser for dynamic image links
*
* @package Papaya-Library
* @subpackage Request
*/
class PapayaRequestParserImage extends PapayaRequestParser {

  /**
  * PCRE pattern for thumbnail links
  * @var string
  */
  private $_pattern = '(/
    (?:(?P<image_id>[a-zA-Z\d_-]+)\.) # identifier
    (?:(?P<mode>image)) # output mode
    (?:\.(?P<format>[a-zA-Z]+)) # format (extension)
    (?:\.
      (?P<preview>preview) # preview
      (?:\.(?P<preview_time>\d+))? # preview time
    )?
  $)Dix';

  /**
  * Parse url and return data
  * @return FALSE|array
  */
  public function parse($url) {
    if (preg_match($this->_pattern, $url->getPath(), $matches)) {
      $result = array();
      $result['mode'] = 'image';
      if (!empty($matches['preview'])) {
        $result['preview'] = TRUE;
      }
      $result['image_identifier'] = $matches['image_id'];
      $result['image_format'] = $matches['format'];
      return $result;
    }
    return FALSE;
  }
}