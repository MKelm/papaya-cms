<?php
/**
* Application object profile for database (manager) object
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
* @version $Id: Database.php 36922 2012-04-02 15:31:23Z weinert $
*/

/**
* Application object profile for database (manager) object
*
* @package Papaya-Library
* @subpackage Application
*/
class PapayaApplicationProfileDatabase implements PapayaApplicationProfile {

  /**
  * Return the identifier of the profile object
  * @return string
  */
  public function getIdentifier() {
    return 'Database';
  }

  /**
  * Create the profile object and return it
  * @param PapayaApplication $application
  * @return PapayaDatabaseManager
  */
  public function createObject($application) {
    $database = new PapayaDatabaseManager();
    $database->papaya($application);
    $database->setConfiguration($application->getObject('Options'));
    return $database;
  }
}
