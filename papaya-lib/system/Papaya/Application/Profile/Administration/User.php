<?php
/**
* Application object profile for default options object
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
* @subpackage Application
* @version $Id: User.php 34243 2010-05-17 08:15:35Z weinert $
*/

/**
* Application object profile for default options object
*
* @package Papaya-Library
* @subpackage Application
*/
class PapayaApplicationProfileAdministrationUser implements PapayaApplicationProfile {

  /**
  * Return the classname/identifier of the profile object
  * @return string
  */
  public function getIdentifier() {
    return 'AdministrationUser';
  }

  /**
  * Create the profile object and return it
  * @param PapayaApplication $application
  * @return stdClass
  */
  public function createObject($application) {
    $options = $application
      ->options
      ->defineDatabaseTables();
    $user = new base_auth();
    return $user;
  }
}
?>