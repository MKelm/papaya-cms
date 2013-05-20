<?php
/**
* Application object profile for the messages (manager) object
*
* @copyright 2002-2010 by papaya Software GmbH - All rights reserved.
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
* @version $Id: Messages.php 34160 2010-05-04 08:07:36Z weinert $
*/

/**
* Application object profile for the messages (manager) object
*
* @package Papaya-Library
* @subpackage Application
*/
class PapayaApplicationProfileMessages implements PapayaApplicationProfile {

  /**
  * Return the identifier of the profile object
  * @return string
  */
  public function getIdentifier() {
    return 'Messages';
  }

  /**
  * Create the profile object and return it
  * @param PapayaApplication $application
  * @return PapayaMessagesManager
  */
  public function createObject($application) {
    $messages = new PapayaMessageManager();
    $messages->addDispatcher(new PapayaMessageDispatcherTemplate());
    $messages->addDispatcher(new PapayaMessageDispatcherDatabase());
    $messages->addDispatcher(new PapayaMessageDispatcherWildfire());
    $messages->addDispatcher(new PapayaMessageDispatcherXhtml());
    return $messages;
  }
}
