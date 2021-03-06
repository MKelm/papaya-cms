<?php
/**
* Papaya Database Result, this will be a new result interface for database queries
*
* @copyright 2002-2009 by papaya Software GmbH - All rights reserved.
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
* @subpackage Database
* @version $Id: Result.php 36743 2012-02-14 16:21:29Z weinert $
*/

/**
* Papaya Database Result, this will be a new result interface for database queries
*
* For now it provides constants to specifiy the fetch mode.
*
* @package Papaya-Library
* @subpackage Database
*/
interface PapayaDatabaseResult extends IteratorAggregate {

  /**
  * Fetch numeric and named keys
  *
  * @var integer
  */
  const FETCH_BOTH = 0;
  /**
  * Fetch numeric keys
  *
  * @var integer
  */
  const FETCH_ORDERED = 1;
  /**
  * Fetch named keys
  *
  * @var integer
  */
  const FETCH_ASSOC = 2;

  /**
  * Fetch row from result
  *
  * @param integer $mode
  * @return array
  */
  function fetchRow($mode = self::FETCH_ORDERED);

  /**
  * Fetch field from result
  *
  * @param integer|string $column
  * @return array
  */
  function fetchField($column = 0);

  /**
  * Seek internal pointer to the given row
  *
  * @param integer $index
  * @return array
  */
  function seek($index);

  /**
  * return count of records in compiled result with limit
  *
  * @return integer
  */
  function count();

  /**
  * return count of records in compiled result without limit
  *
  * @return integer
  */
  function absCount();
}