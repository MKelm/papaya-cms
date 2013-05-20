<?php
/**
* Papaya Application Profile Collection for papaya CMS
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
* @version $Id: Cms.php 36942 2012-04-04 14:50:16Z weinert $
*/

/**
* Papaya Application Profile Collection for papaya CMS
*
* @package Papaya-Library
* @subpackage Application
*/
class PapayaApplicationProfilesCms implements PapayaApplicationProfiles {

  /**
  * Get a collection of application object profiles
  * @param $application
  * @return array
  */
  public function getProfiles($application) {
    $profiles = array();
    $profiles[] = new PapayaApplicationProfileLanguages();
    $profiles[] = new PapayaApplicationProfileMessages();
    $profiles[] = new PapayaApplicationProfileOptions();
    $profiles[] = new PapayaApplicationProfilePageReferences();
    $profiles[] = new PapayaApplicationProfilePlugins();
    $profiles[] = new PapayaApplicationProfileRequest();
    $profiles[] = new PapayaApplicationProfileDatabase();
    $profiles[] = new PapayaApplicationProfileSession();
    $profiles[] = new PapayaApplicationProfileSurfer();
    $profiles[] = new PapayaApplicationProfileProfiler();
    $profiles[] = new PapayaApplicationProfileAdministrationUser();
    $profiles[] = new PapayaApplicationProfileAdministrationLanguage();
    return $profiles;
  }
}

?>