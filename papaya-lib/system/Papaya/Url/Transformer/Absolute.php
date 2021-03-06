<?php
/**
* Papaya URL Transformer, calculates new absolute url from an absolute url and a relative url
*
* @copyright 2002-2010 by papaya Software GmbH - All rights reserved.
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
* @subpackage URL
* @version $Id: Absolute.php 37117 2012-06-11 11:18:10Z weinert $
*/

/**
* Papaya URL Transformer, calculates new absolute url from an absolute url and a relative url
*
* @package Papaya-Library
* @subpackage URL
*/
class PapayaUrlTransformerAbsolute {

  /**
  * Calculates an absolute url from a url and a (possibly relative) path
  *
  * @param PapayaUrl $currentUrl current url
  * @param string $target url to transform
  * @return string
  */
  public function transform(PapayaUrl $currentUrl, $target) {
    $result = NULL;
    if (($url = parse_url($target)) && isset($url['host'])) {
      return $target;
    } elseif (substr($target, 0, 1) == '/') {
      $newPath = $target;
    } else {
      $currentPath = $currentUrl->getPath();
      // remove any potential trailing file name from the path
      $basePath = substr($currentPath, 0, strrpos($currentPath, '/'));
      $newPath = $basePath.'/'.$target;
    }
    if (!empty($newPath)) {
      $result = $currentUrl->getHostUrl().$this->_calculateRealPath($newPath);
    }
    return $result;
  }

  /**
  * This method calculates /../ occurrences and removes // and /./ occurrences from a path
  *
  * @param string $path
  * @return string
  */
  protected function _calculateRealPath($path) {
    // in order to keep leading/trailing slashes, remember them
    $leadingSlash = ($path{0} == '/');
    $trailingSlash = (substr($path, -1) == '/');

    $pathElements = explode('/', $path);
    $outputElements = array();
    foreach ($pathElements as $element) {
      if ($element == '..') {
        if (count($outputElements) > 0) {
          // going one level up, we drop the last valid folder element
          array_pop($outputElements);
        }
      } elseif ($element != '.' && $element != '') {
        // ignoring same folder and empty elements, adding valid folders to output
        $outputElements[] = $element;
      }
    }

    $result = ($leadingSlash) ? '/' : '';
    $result .= implode('/', $outputElements);
    if ($result != '/' && $trailingSlash) {
      $result .= '/';
    }

    return $result;
  }

}
?>