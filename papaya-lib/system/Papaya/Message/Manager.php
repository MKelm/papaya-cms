<?php
/**
* Papaya Message Manager, central message manager, handles the dispatchers
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
* @version $Id: Manager.php 38026 2013-01-28 14:34:23Z weinert $
*/

/**
* Papaya Message Manager, central message manager, handles the dispatchers
*
* @package Papaya-Library
* @subpackage Messages
*/
class PapayaMessageManager extends PapayaObject {

  /**
  * Internal list of message dispatchers
  * @var array
  */
  private $_dispatchers = array();

  /**
  * List of php event hooks
  * @var array
  */
  private $_hooks = NULL;

  /**
  * Add a dispatcher to the list
  * @param PapayaMessageDispatcher $dispatcher
  */
  public function addDispatcher(PapayaMessageDispatcher $dispatcher) {
    $this->_dispatchers[] = $dispatcher;
  }

  /**
  * Dispatch a message to all available dispatchers
  * @param PapayaMessage $message
  */
  public function dispatch(PapayaMessage $message) {
    foreach ($this->_dispatchers as $dispatcher) {
      $dispatcher->dispatch($message);
    }
  }

  /**
  * Debug message shortcut, creates a default log message with debug contexts
  *
  * If arguments are provided, they are added to a variable context as an array.
  */
  public function debug() {
    $message = new PapayaMessageLog(
      PapayaMessageLogable::GROUP_DEBUG, PapayaMessage::TYPE_DEBUG, ''
    );
    if (func_num_args() > 0) {
      $message->context()->append(new PapayaMessageContextVariable(func_get_args(), 5, 9999));
    }
    $message
      ->context()
      ->append(new PapayaMessageContextMemory())
      ->append(new PapayaMessageContextRuntime())
      ->append(new PapayaMessageContextBacktrace(1));
    $this->dispatch($message);
  }

  /**
   * Encapsulate an callback into an sandbox, capturing all exceptions and dispatching them
   * as logable error messages.
   *
   * @param Callable $callback
   * @return PapayaMessageSandbox
   */
  public function encapsulate($callback) {
    PapayaUtilConstraints::assertCallable($callback);
    $sandbox = new PapayaMessageSandbox($callback);
    $sandbox->papaya($this->papaya());
    return array($sandbox, '__invoke');
  }

  /**
  * Register error and exceptions hooks
  */
  public function hooks(array $hooks = NULL) {
    if (isset($hooks)) {
      $this->_hooks = $hooks;
    } elseif (is_null($this->_hooks)) {
      $this->_hooks = array(
        $exceptionsHook = new PapayaMessageHookExceptions($this),
        new PapayaMessageHookErrors($this, $exceptionsHook),
      );
    }
    return $this->_hooks;
  }

  /**
  * Setup message system
  *
  * This functions initializes the start time for runtime debug and activates the hooks for
  * php messages and exceptions.
  *
  * @param PapayaOptions
  */
  public function setUp($options) {
    PapayaMessageContextRuntime::setStartTime(microtime(TRUE));
    error_reporting($options->getOption('PAPAYA_LOG_PHP_ERRORLEVEL', E_ALL & ~E_STRICT));
    foreach ($this->hooks() as $hook) {
      $hook->activate();
    }
  }
}