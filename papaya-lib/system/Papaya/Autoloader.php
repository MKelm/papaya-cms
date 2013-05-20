<?php
/**
* Papaya autoloader
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
* @package Papaya
* @version $Id: Autoloader.php 36083 2011-08-15 08:43:36Z weinert $
*/

/**
* Papaya autoloader
*
* @package Papaya-Library
*/
class PapayaAutoloader {

  /**
  * prefix => path mapping array for modules/plugins.
  *
  * @var array
  */
  private static $_paths = array();

  /**
  *
  * @param string $name
  * @return void
  */
  public static function load($name, $file = NULL) {
    if (!class_exists($name, FALSE)) {
      $file = (is_null($file)) ? self::getClassFile($name) : $file;
      if (file_exists($file) &&
          is_file($file) &&
          is_readable($file)) {
        include($file);
      }
    }
  }

  /**
  * Get file for a class
  *
  * @param string $className
  * @return string
  */
  public static function getClassFile($className) {
    $fileName = self::prepareFileName($className);
    if (0 !== strpos($fileName, '/Papaya/') ||
        0 === strpos($fileName, '/Papaya/Module/')) {
      foreach (self::$_paths as $prefix => $path) {
        if (0 === strpos($fileName, $prefix)) {
          return $path.substr($fileName, strlen($prefix)).'.php';
        }
      }
    }
    return PAPAYA_INCLUDE_PATH.'system'.$fileName.'.php';
  }

  /**
  * Get file from matches class parts
  *
  * The file will include only the part of the path defined by the class.
  *
  * @param array $className
  * @return string
  */
  private static function prepareFileName($className) {
    $classPattern = '((?:[A-Z][a-z\d]+)|(?:[A-Z]+(?![a-z\d])))S';
    if (preg_match_all($classPattern, $className, $matches)) {
      $parts = $matches[0];
    } else {
      return '/'.$className;
    }
    $result = '';
    foreach ($parts as $part) {
      $result .= '/'.ucfirst(strtolower($part));
    }
    return $result;
  }

  /**
  * Register an path for classes starting with a defined prefix. The prefix "Papaya" is reserved,
  * except "PapayaModule".
  *
  * @param string $modulePrefix
  * @param string $modulePath
  */
  public static function registerPath($modulePrefix, $modulePath) {
    self::$_paths[self::prepareFileName($modulePrefix).'/'] =
      PapayaUtilFilePath::cleanup($modulePath);
    uksort(self::$_paths, array('self', 'compareByCharacterLength'));
  }

  /**
  * Registered prefix are sortet by length (descending). A longer prefix has a higher priority.
  *
  * @param string $prefixOne
  * @param string $prefixTwo
  */
  public static function compareByCharacterLength($prefixOne, $prefixTwo) {
    if (strlen($prefixOne) > strlen($prefixTwo)) {
      return -1;
    } else {
      return strcmp($prefixOne, $prefixTwo);
    }
  }

  /**
  * Clear all additional registeret paths.
  */
  public static function clearPaths() {
    self::$_paths = array();
  }
}