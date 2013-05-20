<?php
/**
* Application object profile for languages object
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
* @subpackage Application
* @version $Id: References.php 36942 2012-04-04 14:50:16Z weinert $
*/

/**
* Application object profile for languages object
*
* @package Papaya-Library
* @subpackage Application
*/
class PapayaApplicationProfilePageReferences implements PapayaApplicationProfile {

  /**
  * Return the identifier of the profile object
  * @return string
  */
  public function getIdentifier() {
    return 'PageReferences';
  }

  /**
  * Create the profile object and return it
  * @param PapayaApplication $application
  * @return PapayaDatabaseManager
  */
  public function createObject($application) {
    $references = new PapayaUiReferencePageFactory();
    $references->papaya($application);
    return $references;
  }
}
