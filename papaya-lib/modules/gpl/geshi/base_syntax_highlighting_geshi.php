<?php
/**
* This class acts as an interface to syntax highlighters, currently GeSHi is used.
*
* <code>
* <?php
*   $syntaxHighlighter = papaya_pluginloader::getPluginInstance(
*     '7d3a2fa61b1746b10662b849e82011c5', $this
*   );
*   $syntaxHighlighter->highlight($string, $language);
* ?>
* </code>
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
* @subpackage GPL-Geshi
* @version $Id: base_syntax_highlighting_geshi.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* This class acts as an interfaces to syntax highlighters, currently GeSHi is used.
*
* @package Papaya-Modules
* @subpackage GPL-Geshi
*/
class base_syntax_highlighting_geshi {
  /**
  * Set application registry
  *
  * The syntax highlighter does not actually need the registry for now,
  * but the plugin loader will call setApplication() on the plugin anyway.
  *
  * @param PapayaApplication $application
  */
  function setApplication($application) {
    return TRUE;
  }

  /**
  * This method initializes a single instance of the syntax highlighting parser
  * and parses the given string in the given language.
  */
  function highlight(&$string, $language, $engine = 'geshi') {
    $result = '';
    static $highlighter;

    switch ($engine) {
    default:
    case 'geshi':
      if (is_file(dirname(__FILE__).'/external/geshi.php') &&
          is_readable(dirname(__FILE__).'/external/geshi.php') &&
          include_once(dirname(__FILE__).'/external/geshi.php')) {
        if (!isset($highlighter) ||
            !is_object($highlighter) ||
            !is_a($highlighter, 'GeSHi')) {
          $highlighter = new GeSHi($string, $language);
        }
        $result = $highlighter->parse_code();
      }
      break;
    }
    return $result;
  }
}
?>
