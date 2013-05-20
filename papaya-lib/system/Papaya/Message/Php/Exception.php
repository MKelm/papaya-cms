<?php
/**
* Papaya Message Php Exception, message object representing an php error exception
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
* @version $Id: Exception.php 34179 2010-05-05 11:05:54Z weinert $
*/

/**
* Papaya Message Hook Exception, capture exceptions and handle them
*
* @package Papaya-Library
* @subpackage Messages
*/
class PapayaMessagePhpException
  extends PapayaMessagePhp {

  /**
  * Create object and set values from erorr exception object
  *
  * @param ErrorException $exception
  */
  public function __construct(ErrorException $exception,
                              PapayaMessageContextBacktrace $trace = NULL) {
    parent::__construct();
    $this->setSeverity($exception->getSeverity());
    $this->_message = $exception->getMessage();
    $this
      ->_context
      ->append(
        is_null($trace)
          ? new PapayaMessageContextBacktrace(0, $exception->getTrace())
          : $trace
      );
  }
}