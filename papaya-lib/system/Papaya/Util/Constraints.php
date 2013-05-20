<?php
/**
* Papaya Utilities implementing contraints
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
* @subpackage Util
* @version $Id: Constraints.php 38059 2013-01-31 12:07:01Z weinert $
*/

/**
* Papaya Utilities implementing contraints
*
* The functions of this class check for a simple type. If not given they throw an exception.
*
* @package Papaya-Library
* @subpackage Util
*/
class PapayaUtilConstraints {

  /**
  * Handle an assertion failure (throw the exception)
  *
  * @throws UnexpectedValueException
  * @param string $expected expected types string
  * @param mixed $value actual value
  * @param string $message Individual error message (can be empty)
  */
  protected static function handleFailure($expected, $value, $message) {
    if (empty($message)) {
      throw new UnexpectedValueException(
        sprintf(
          'Unexpected value type: Expected "%s" but "%s" given.',
          $expected,
          is_object($value) ? get_class($value) : gettype($value)
        )
      );
    } else {
      throw new UnexpectedValueException($message);
    }
  }

  /**
  * Assert value is an array
  *
  * @throws UnexpectedValueException
  * @param mixed $value
  * @param string $message Individual error message (can be empty)
  * @return TRUE
  */
  public static function assertArray($value, $message = '') {
    if (is_array($value)) {
      return TRUE;
    }
    return self::handleFailure('array', $value, $message);
  }

  /**
  * Assert value is an array or an Traverable instance. If either one is true, foreach can be
  * used on the variable.
  *
  * @throws UnexpectedValueException
  * @param mixed $value
  * @param string $message Individual error message (can be empty)
  * @return TRUE
  */
  public static function assertArrayOrTraversable($value, $message = '') {
    if (is_array($value)) {
      return TRUE;
    } elseif ($value instanceOf Traversable) {
      return TRUE;
    }
    return self::handleFailure('array, Traversable', $value, $message);
  }

  /**
  * Assert value is a boolean
  *
  * @throws UnexpectedValueException
  * @param mixed $value
  * @param string $message Individual error message (can be empty)
  * @return TRUE
  */
  public static function assertBoolean($value, $message = '') {
    if (is_bool($value)) {
      return TRUE;
    }
    return self::handleFailure('boolean', $value, $message);
  }

  /**
  * Assert value is a boolean
  *
  * @throws UnexpectedValueException
  * @param mixed $value
  * @param string $message Individual error message (can be empty)
  * @return TRUE
  */
  public static function assertCallable($value, $message = '') {
    if (is_callable($value)) {
      return TRUE;
    }
    return self::handleFailure('Callable', $value, $message);
  }

  /**
  * Assert value is a float
  *
  * @throws UnexpectedValueException
  * @param mixed $value
  * @param string $message Individual error message (can be empty)
  * @return TRUE
  */
  public static function assertFloat($value, $message = '') {
    if (is_float($value)) {
      return TRUE;
    }
    return self::handleFailure('float', $value, $message);
  }

  /**
  * Assert value is an integer
  *
  * @throws UnexpectedValueException
  * @param mixed $value
  * @param string $message Individual error message (can be empty)
  * @return TRUE
  */
  public static function assertInteger($value, $message = '') {
    if (is_integer($value)) {
      return TRUE;
    }
    return self::handleFailure('integer', $value, $message);
  }

  /**
  * Assert value is not empty
  *
  * @throws UnexpectedValueException
  * @param mixed $value
  * @param string $message Individual error message (can be empty)
  * @return TRUE
  */
  public static function assertNotEmpty($value, $message = '') {
    if (empty($value)) {
      return self::handleFailure(
        '',
        NULL,
        empty($message) ? 'Empty value given but not allowed.' : $message
      );
    }
    return TRUE;
  }

  /**
  * Assert value is a number (integer or float)
  *
  * @throws UnexpectedValueException
  * @param mixed $value
  * @param string $message Individual error message (can be empty)
  * @return TRUE
  */
  public static function assertNumber($value, $message = '') {
    if (is_integer($value) || is_float($value)) {
      return TRUE;
    }
    return self::handleFailure('integer, float', $value, $message);
  }

  /**
  * Assert value is an object
  *
  * @throws UnexpectedValueException
  * @param mixed $value
  * @param string $message Individual error message (can be empty)
  * @return TRUE
  */
  public static function assertObject($value, $message = '') {
    if (is_object($value)) {
      return TRUE;
    }
    return self::handleFailure('object', $value, $message);
  }

  /**
  * Assert value is an object or NULL
  *
  * This is not a class check! Use type hints and the instanceof operator.
  *
  * @throws UnexpectedValueException
  * @param mixed $value
  * @param string $message Individual error message (can be empty)
  * @return TRUE
  */
  public static function assertObjectOrNull($value, $message = '') {
    if (is_object($value) || is_null($value)) {
      return TRUE;
    }
    return self::handleFailure('object, NULL', $value, $message);
  }

  /**
  * Assert value is an instance of $className
  *
  * @throws UnexpectedValueException
  * @param string $expectedClass
  * @param mixed $value
  * @param string $message Individual error message (can be empty)
  * @return TRUE
  */
  public static function assertInstanceOf($expectedClass, $value, $message = '') {
    if ($value instanceof $expectedClass) {
      return TRUE;
    }
    return self::handleFailure($expectedClass, $value, $message);
  }

  /**
  * Assert value is a resource
  *
  * @throws UnexpectedValueException
  * @param mixed $value
  * @param string $message Individual error message (can be empty)
  * @return TRUE
  */
  public static function assertResource($value, $message = '') {
    if (is_resource($value)) {
      return TRUE;
    }
    return self::handleFailure('resource', $value, $message);
  }

  /**
  * Assert value is a string
  *
  * @throws UnexpectedValueException
  * @param mixed $value
  * @param string $message Individual error message (can be empty)
  * @return TRUE
  */
  public static function assertString($value, $message = '') {
    if (is_string($value)) {
      return TRUE;
    }
    return self::handleFailure('string', $value, $message);
  }
}
