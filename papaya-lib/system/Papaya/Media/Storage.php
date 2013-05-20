<?php
/**
* Papaya Media Storage Service Factory
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
* @package Papaya-Library
* @subpackage Media-Storage
* @version $Id: Storage.php 36488 2011-12-13 16:10:31Z weinert $
*/

/**
* Papaya Media Storage Service Factory
* @package Papaya-Library
* @subpackage Media-Storage
*/
class PapayaMediaStorage {

  private static $_serviceObjects = array();

  private static $_services = array('File', 'S3');

  /**
  * get the service
  *
  * @param string $service
  * @param PapayaConfiguration $configuration
  * @param boolean $static optional, default value TRUE
  * @access public
  * @return PapayaMediaStorageService
  */
  public function getService($service = '', $configuration = NULL, $static = TRUE) {
    if (empty($service)) {
      $service = defined('PAPAYA_MEDIA_STORAGE_SERVICE')
        ? PAPAYA_MEDIA_STORAGE_SERVICE : 'File';
    }
    $service = ucfirst(strtolower($service));
    if (in_array($service, self::$_services)) {
      if ($static && isset(self::$_serviceObjects[$service])) {
        return self::$_serviceObjects[$service];
      }
      $class = 'PapayaMediaStorageService'.$service;
      $object = new $class();
      if (isset($configuration)) {
        $object->setConfiguration($configuration);
      }
      if ($static) {
        return self::$_serviceObjects[$service] = $object;
      } else {
        return $object;
      }
    } else {
      throw new InvalidArgumentException(
        'Unknown media storage service: '.$service
      );
    }
  }
}