<?php
/**
* Encapsulation object for the libxml errors.
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
* @subpackage Xml
* @version $Id: Errors.php 37425 2012-08-16 16:02:21Z weinert $
*/

/**
* Encapsulation object for the libxml errors.
*
* This is a wrapper for the libxml error handling function, it converts the warnings and errors
* into PapayaMessage objects and dispatches them into the MessageManager.
*
* @package Papaya-Library
* @subpackage Xml
*/
class PapayaXmlErrors extends PapayaObject {

  private $_savedStatus = FALSE;

  /**
  * Map libxml error types to message types
  * @var array
  */
  private $_errorMapping = array(
    LIBXML_ERR_NONE => PapayaMessage::TYPE_INFO,
    LIBXML_ERR_WARNING => PapayaMessage::TYPE_WARNING,
    LIBXML_ERR_ERROR => PapayaMessage::TYPE_ERROR,
    LIBXML_ERR_FATAL => PapayaMessage::TYPE_ERROR
  );

  /**
  * Activate the libxml internal error capturing (and clear the current buffer)
  */
  public function activate() {
    $this->_savedStatus = libxml_use_internal_errors(TRUE);
    libxml_clear_errors();
  }

  /**
  * Deactivate the libxml internal error capturing (and clear the current buffer)
  */
  public function deactivate() {
    libxml_clear_errors();
    libxml_use_internal_errors($this->_savedStatus);
  }

  /**
  * Dispatches messages for the libxml errors in the internal buffer.
  */
  public function omit($fatalOnly = FALSE) {
    $errors = libxml_get_errors();
    foreach ($errors as $error) {
      if ($error->level == LIBXML_ERR_FATAL) {
        throw new PapayaXmlException($error);
      } elseif (!$fatalOnly && 0 !== strpos($error->message, 'Namespace prefix papaya')) {
        $this
          ->papaya()
          ->messages
          ->dispatch(
            $this->getMessageFromError($error)
          );
      }
    }
    libxml_clear_errors();
  }

  /**
  * Converts a libxml error object into a PapayaMessage
  *
  * @param libXMLError $error
  */
  public function getMessageFromError(libXMLError $error) {
    $messageType = $this->_errorMapping[$error->level];
    $message = new PapayaMessageLog(
      PapayaMessageLogable::GROUP_SYSTEM,
      $messageType,
      sprintf(
        '%d: %s in line %d at char %d',
        $error->code,
        $error->message,
        $error->line,
        $error->column
      )
    );
    if (!empty($error->file)) {
      $message
        ->context()
        ->append(
          new PapayaMessageContextFile(
            $error->file, $error->line, $error->column
          )
        );
    }
    $message
      ->context()
      ->append(
        new PapayaMessageContextBacktrace(3)
      );
    return $message;
  }
}