<?php
/**
* Papaya Message, abstract superclass for all messages
*
* @copyright 2009 by papaya Software GmbH - All rights reserved.
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
* @version $Id: Message.php 34068 2010-04-22 13:15:06Z weinert $
*/

/**
* Papaya Message, abstract superclass for all messages
*
* @package Papaya-Library
* @subpackage Messages
*/
interface PapayaMessage {

  /**
  * Information message type
  * @var integer
  */
  const TYPE_INFO = 0;
  /**
  * Warning message type
  * @var integer
  */
  const TYPE_WARNING = 1;
  /**
  * Error message type
  * @var integer
  */
  const TYPE_ERROR = 2;
  /**
  * Error message type
  * @var integer
  */
  const TYPE_DEBUG = 3;

  /**
  * Get type of message (info, warning, error)
  * @return integer
  */
  function getType();

  /**
  * Get message string
  * @return string
  */
  function getMessage();

}