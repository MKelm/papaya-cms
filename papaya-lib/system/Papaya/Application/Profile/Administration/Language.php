<?php
/**
* Application object profile for the content language switcher
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
* @version $Id: Language.php 35847 2011-07-01 12:33:56Z weinert $
*/

/**
* Application object profile for the content language switcher
*
* @package Papaya-Library
* @subpackage Application
*/
class PapayaApplicationProfileAdministrationLanguage implements PapayaApplicationProfile {

  /**
  * Return the classname/identifier of the profile object
  * @return string
  */
  public function getIdentifier() {
    return 'AdministrationLanguage';
  }

  /**
  * Create the profile object and return it
  * @param PapayaApplication $application
  * @return stdClass
  */
  public function createObject($application) {
    $switch = new PapayaAdministrationLanguagesSwitch();
    return $switch;
  }
}