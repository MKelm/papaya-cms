<?php
/**
* This object defines the possible view mode types. A view mode type socified a logical group
* for a view mode.
*
* @copyright 2012 by papaya Software GmbH - All rights reserved.
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
* @subpackage Content
* @version $Id: Types.php 37787 2012-12-07 15:42:47Z weinert $
*/

/**
* This object defines the possible view mode types. A view mode type socified a logical group
* for a view mode. The current types are:
*
* page : A user readable content output of a page like html, pdf, ...
* feed : A machine readable content output using standard formats like atom, rss, ...
* hidden : A machine readable content output using a specific xml or json output for a project
*   specific javascript or content sharing
*
* @package Papaya-Library
* @subpackage Content
*/
class PapayaContentViewModeTypes implements ArrayAccess, IteratorAggregate {

  const PAGE = 'page';
  const FEED = 'feed';
  const HIDDEN = 'hidden';

  private static $_typeCaptions = array(
    self::PAGE => 'Page',
    self::FEED => 'Feed',
    self::HIDDEN => 'Hidden',
  );

  /**
   * Static function to validate if a type is valid withotu the need to create an object
   *
   * @param string $mode
   * @return boolean
   */
  public static function exists($mode) {
    return isset(self::$_typeCaptions[$mode]);
  }

  /**
   * An iterator for all types and their captions
   * @see IteratorAggregate::getIterator()
   *
   * @return Iterator
   */
  public function getIterator() {
    return new ArrayIterator(self::$_typeCaptions);
  }

  /**
   * Validate if an type exists
   *
   * @see ArrayAccess::offsetExists()
   * @return boolean
   */
  public function offsetExists($mode) {
    return self::exists($mode);
  }

  /**
   * Get the caption for a type
   *
   * @see ArrayAccess::offsetGet()
   * @return string
   */
  public function offsetGet($mode) {
    return $this->offsetExists($mode)
      ? self::$_typeCaptions[$mode]
      : self::$_typeCaptions[self::PAGE];
  }

  /**
   * Throw an exeption if someone tries to modify the list
   *
   * @throws LogicException
   * @see ArrayAccess::offsetSet()
   */
  public function offsetSet($mode, $caption) {
    throw new LogicException('View types list can not be modified.');
  }

  /**
   * Throw an exeption if someone tries to modify the list
   *
   * @throws LogicException
   * @see ArrayAccess::offsetSet()
   */
  public function offsetUnset($mode) {
    throw new LogicException('View types list can not be modified.');
  }
}