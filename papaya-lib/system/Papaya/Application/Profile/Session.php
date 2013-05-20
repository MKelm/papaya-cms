<?php
/**
* Application object profile for default session object
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
* @version $Id: Session.php 35042 2010-10-22 10:25:00Z weinert $
*/

/**
* Application object profile for default session object
*
* @package Papaya-Library
* @subpackage Application
*/
class PapayaApplicationProfileSession implements PapayaApplicationProfile {

  /**
  * Return the identifier of the profile object
  * @return string
  */
  public function getIdentifier() {
    return 'Session';
  }

  /**
  * Create the profile object and return it
  * @param PapayaApplication $application
  * @return stdClass
  */
  public function createObject($application) {
    $session = new PapayaSession();
    return $session;
  }
}
?>