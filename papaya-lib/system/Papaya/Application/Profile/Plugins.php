<?php
/**
* Application object profile for the plugin loader
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
* @version $Id: Plugins.php 36477 2011-12-03 13:25:26Z weinert $
*/

/**
* Application object profile for the plugin loader
*
* @package Papaya-Library
* @subpackage Application
*/
class PapayaApplicationProfilePlugins implements PapayaApplicationProfile {

  /**
  * Return the classname/identifier of the profile object
  * @return string
  */
  public function getIdentifier() {
    return 'Plugins';
  }

  /**
  * Create the profile object and return it
  * @param PapayaApplication $application
  * @return stdClass
  */
  public function createObject($application) {
    $plugins = new PapayaPluginLoader();
    $plugins->papaya($application);
    return $plugins;
  }
}
?>