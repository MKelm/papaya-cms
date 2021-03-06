<?php
/**
* Papaya filter class for times.
*
* @copyright 2011 by papaya Software GmbH - All rights reserved.
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
* @subpackage Filter
* @version $Id: Time.php 38143 2013-02-19 14:58:24Z weinert $
*/

/**
* This filter class checks a time in human-readable format.
*
* @package Papaya-Library
* @subpackage Filter
*/
class PapayaFilterTime implements PapayaFilter {
  /**
  * Step in seconds, default 60
  * @var float
  */
  private $_step = 60.0;

  /**
  * Constructor
  *
  * @param float $step in seconds (optional, default 60.0)
  */
  public function __construct($step = 60.0) {
    PapayaUtilConstraints::assertNumber($step, 'Step must be a number.');
    if ($step <= 0) {
      throw new UnexpectedValueException('Step must be greater than 0.');
    }
    $this->_step = $step;
  }

  /**
  * Validate a time
  *
  * @param string $value
  * @throws PapayaFilterExceptionType
  * @throws PapayaFilterExceptionRangeMaximum
  * @return boolean
  */
  public function validate($value) {
    $patternTimeISO = '(^
      (?P<hour>[0-9]{1,2})
      (?::(?P<minute>[0-9]{1,2})
        (?::(?P<second>[0-9]{1,2})
          (?:\.(?P<millisecond>[0-9]{1,3}))?
          (?:(?P<offsetOperator>[+-])
            (?:(?P<offsetHour>[0-9]{1,2})
              (?::(?P<offsetMinute>[0-9]{1,2})
                (?::(?P<offsetSecond>[0-9]{1,2}))?
              )?
            )?
          )?
        )?
      )?
      $)Dx';
    if (!preg_match($patternTimeISO, $value, $match)) {
      throw new PapayaFilterExceptionType('ISO time.');
    }
    if (!empty($match['offsetOperator'])) {
      throw new PapayaFilterExceptionType('Time must not include a time zone offset.');
    }
    $limits = array(
      'hour' => 23,
      'minute' => 59,
      'second' => 59,
    );
    foreach ($limits as $element => $limit) {
      if (isset($match[$element]) && $match[$element] > $limit) {
        throw new PapayaFilterExceptionRangeMaximum($limit, $match[$element]);
      }
    }
    $timeStamp = $this->_toTimestamp(
      $match['hour'],
      isset($match['minute']) ? $match['minute'] : 0,
      isset($match['second']) ? $match['second'] : 0
    );
    if ($timeStamp % $this->_step != 0) {
      throw new PapayaFilterExceptionType('Time matching the expected step.');
    }
    return TRUE;
  }

  /**
  * Filter a time
  *
  * @param string $value
  * @return mixed the filtered time value or NULL
  */
  public function filter($value) {
    try {
      $this->validate(trim($value));
    } catch(PapayaFilterException $e) {
      return NULL;
    }
    return trim($value);
  }

  protected function _toTimestamp($hour, $minute, $second) {
    return 3600 * $hour + 60 * $minute + $second;
  }
}