<?php
/**
* Papaya Object Interface - implementing objects provide access to the papaya application registry
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
* @subpackage Objects
* @version $Id: Interface.php 36292 2011-10-10 13:03:07Z weinert $
*/

/**
* Papaya Object Interface - implementing objects provide access to the papaya application registry
*
* @package Papaya-Library
* @subpackage Objects
*/
interface PapayaObjectInterface {

  /**
  * Getter/Setter for the application registry
  *
  * @param PapayaApplication $application
  * @return PapayaApplication
  */
  function papaya(PapayaApplication $application = NULL);
}