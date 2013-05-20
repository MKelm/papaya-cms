<?php
/**
* Application object profile for profiler
*
* @copyright 2002-2007 by papaya Software GmbH - All rights reserved.
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
* @subpackage Application
* @version $Id: Profiler.php 36246 2011-09-27 19:26:09Z weinert $
*/

/**
* Application object profile for profiler
*
* @package Papaya-Library
* @subpackage Application
*/
class PapayaApplicationProfileProfiler implements PapayaApplicationProfile {

  private $_builder = NULL;

  /**
  * Return the identifier of the profile object
  * @return string
  */
  public function getIdentifier() {
    return 'Profiler';
  }

  /**
  * Create the profile object and return it
  * @param PapayaApplication $application
  * @return stdClass
  */
  public function createObject($application) {
    $builder = $this->builder();
    $builder->papaya($application);
    $profiler = new PapayaProfiler($builder->createCollector(), $builder->createStorage());
    if ($application->options->getOption('PAPAYA_PROFILER_ACTIVE', FALSE)) {
      $profiler->setDivisor($application->options->getOption('PAPAYA_PROFILER_DIVISOR', 50));
    } else {
      $profiler->setDivisor(0);
    }
    return $profiler;
  }

  /**
  * Getter/Setter for profiler builder
  *
  * @return PapayaProfilerBuilder
  */
  public function builder(PapayaProfilerBuilder $builder = NULL) {
    if (isset($builder)) {
      $this->_builder = $builder;
    } elseif (is_null($this->_builder)) {
      $this->_builder = new PapayaProfilerBuilder();
    }
    return $this->_builder;
  }
}
?>