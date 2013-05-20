<?php
/**
* A factory object that creates file and directory wrapper objects
*
* @copyright 2012 by papaya Software GmbH - All rights reserved.
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
* @subpackage FileSystem
* @version $Id: Factory.php 37291 2012-07-25 15:01:51Z weinert $
*/

/**
* A factory object that creates file and directory wrapper objects
*
* @package Papaya-Library
* @subpackage FileSystem
*/
class PapayaFileSystemFactory {

  /**
   * Return an object wrapping a file in the file system
   *
   * @return PapayaFileSystemFile
   */
  public function getFile($filename) {
    return new PapayaFileSystemFile($filename);
  }

  /**
   * Return an object wrapping a directory in the file system
   *
   * @return PapayaFileSystemDirectory
   */
  public function getDirectory($directory) {
    return new PapayaFileSystemDirectory($directory);
  }
}
