<?php
/**
* Interface definition for Papaya Application Profiles
*
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
* @version $Id: Profile.php 35319 2011-01-14 10:03:55Z weinert $
*/

/**
* Interface definition for Papaya Application Profiles
* @package Papaya-Library
* @subpackage Application
*/
interface PapayaApplicationProfile {

  /**
  * Return the identifier of the profile object
  * @return string
  */
  function getIdentifier();

  /**
  * Create the profile object and return it
  * @param PapayaApplication $application
  * @return stdClass
  */
  function createObject($application);
}
?>