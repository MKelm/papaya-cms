<?php
/**
* Papaya Message Dispatcher Template, handle messages to be shown to the user in browser
*
* @copyright 2010 by papaya Software GmbH - All rights reserved.
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
* @subpackage Messages
* @version $Id: Template.php 34066 2010-04-22 12:11:03Z weinert $
*/

/**
* Papaya Message Dispatcher Template, handle messages to be shown to the user in browser
*
* Make sure that the dispatcher does not initialize it's resources only if needed,
* It will be created at the start of the script, unused initialzation will slow the script down.
*
* @package Papaya-Library
* @subpackage Messages
*/
class PapayaMessageDispatcherTemplate
  extends PapayaObject
  implements PapayaMessageDispatcher {

  /**
  * Add message to the output, for now uses the old error system.
  *
  * Only messages that implements PapayaMessageDisplay are used, all other message are ignored.
  *
  * @todo temporary implementation, replace after PapayaTemlateXslt is finished.
  *
  * @param PapayaMessage $message
  * @return boolean
  */
  public function dispatch(PapayaMessage $message) {
    if ($message instanceof PapayaMessageDisplayable) {
      if (isset($GLOBALS['PAPAYA_MSG']) &&
          $GLOBALS['PAPAYA_MSG'] instanceof base_errors) {
        return $GLOBALS['PAPAYA_MSG']->add($message->getType(), $message->getMessage());
      }
    }
    return FALSE;
  }
}