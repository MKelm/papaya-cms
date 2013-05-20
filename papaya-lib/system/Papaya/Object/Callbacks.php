<?php
/**
* A list of callbacks, this can be used in another object to allow the user to set
* callbacks for different events inside the object.
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
* @subpackage Ui
* @version $Id: Callbacks.php 35899 2011-07-11 08:43:06Z weinert $
*/

/**
* A list of callbacks, this can be used in another object to allow the user to set
* callbacks for different events inside the object.
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaObjectCallbacks {

  /**
  * List of callbacks
  *
  * @var array(string=>PapayaObjectCallback)
  */
  private $_callbacks = array();

  /**
  * Create list and initialize callbacks and default return values.
  *
  * @param array $definitions
  */
  public function __construct(array $definitions) {
    $this->defineCallbacks($definitions);
  }

  /**
  * This check the list of given callback names. For each name a PapayaObjectCallback instance is
  * created.
  *
  * If of of the given names is an existing method in the current object ($this) an exception
  * is thrown.
  *
  * @throws LogicException
  * @param array $definitions
  */
  protected function defineCallbacks(array $definitions) {
    if (count($definitions) < 1) {
      throw new LogicException('No callback definitions provided.');
    }
    $this->_callbacks = array();
    foreach ($definitions as $name => $defaultReturn) {
      if (method_exists($this, $name)) {
        throw new LogicException(
          sprintf(
            'Method "%s" does already exists and can not be defined as a callback.',
            $name
          )
        );
      }
      $this->_callbacks[$name] = new PapayaObjectCallback($defaultReturn);
    }
  }

  /**
  * Allows to check if a php callback is available for the given name.
  *
  * @param string $name
  * @return boolean
  */
  public function __isset($name) {
    return isset($this->_callbacks[$name]->callback);
  }

  /**
  * Returns the PapayaObjectCallback instance for the given name.
  *
  * @param string $name
  * @return PapayaObjectCallback
  */
  public function __get($name) {
    $this->validateName($name);
    return $this->_callbacks[$name];
  }

  /**
  * Change a callback. If the value is an instance of PapayaObjectCallback is will be assigned.
  * If it is a PHP callback it will be assigned to the PapayaObjectCallback instance.
  *
  * @param string $name
  * @param NULL|PapayaObjectCallback|Callback $callback
  */
  public function __set($name, $callback) {
    $this->validateName($name);
    if (is_null($callback)) {
      $this->_callbacks[$name]->callback = NULL;
    } elseif ($callback instanceOf PapayaObjectCallback) {
      $this->_callbacks[$name] = $callback;
    } elseif (is_callable($callback)) {
      $this->_callbacks[$name]->callback = $callback;
    } else {
      throw new InvalidArgumentException(
        'Argument $callback must be an valid Callback or an instance of PapayaObjectCallback.'
      );
    }
  }

  /**
  * Unset the PHP callback in the match PapayaObjectCallback
  *
  * @param string $name
  */
  public function __unset($name) {
    $this->validateName($name);
    unset($this->_callbacks[$name]->callback);
  }

  /**
  * Execute the callback using {@see PapayaObjectCallback::execute()}.
  *
  * @param string $name
  */
  public function __call($name, $arguments) {
    $this->validateName($name);
    return call_user_func_array(array($this->_callbacks[$name], 'execute' ), $arguments);
  }

  /**
  * Validate the callback name agains the defined callback names
  *
  * @throws InvalidArgumentException
  * @param string $name
  */
  private function validateName($name) {
    if (!isset($this->_callbacks[$name])) {
      throw new LogicException(
        sprintf(
          'Invalid callback name: %s.', $name
        )
      );
    }
  }
}