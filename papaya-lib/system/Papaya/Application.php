<?php
/**
* Papaya Application - object registry with profiles
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
* @subpackage Application
* @version $Id: Application.php 38361 2013-04-04 12:09:41Z hapke $
*/

/**
* Papaya Application - object registry with profiles
* @package Papaya-Library
* @subpackage Application
*/
class PapayaApplication {

  /**
  * Duplicate profiles trigger an error
  * @var integer
  */
  const DUPLICATE_ERROR = 0;

  /**
  * Ignore duplicate profiles
  * @var integer
  */
  const DUPLICATE_IGNORE = 1;

  /**
  * Overwrite duplicate profiles
  * @var intgeger
  */
  const DUPLICATE_OVERWRITE = 2;

  /**
  * Class variable for singleton instance
  * @var PapayaApplication
  */
  private static $instance = NULL;


  /**
  * Profile objects
  * @var array(PapayaObjectProfile)
  */
  private $_profiles = array();

  /**
  * Objects
  * @var array(Object)
  */
  private  $_objects = array();

  /**
  * Create a new instance of this class or return existing one (singleton)
  *
  * @param boolean $reset
  * @return PapayaApplication Instance of Application Object
  */
  public static function getInstance($reset = FALSE) {
    if ($reset || is_null(self::$instance)) {
      self::$instance = new PapayaApplication();
    }
    return self::$instance;
  }

  /**
  * Register a collection of profiles
  * @param PapayaApplicationProfiles $profiles
  * @param integer $duplicationMode
  * @return void
  */
  public function registerProfiles(PapayaApplicationProfiles $profiles,
                                   $duplicationMode = self::DUPLICATE_ERROR) {
    foreach ($profiles->getProfiles($this) as $profile) {
      $this->registerProfile($profile, $duplicationMode);
    }
  }

  /**
  * Register an object profile
  * @param PapayaApplicationProfile $profile
  * @return void
  */
  public function registerProfile(PapayaApplicationProfile $profile,
                                  $duplicationMode = self::DUPLICATE_ERROR) {
    $index = strtolower($profile->getIdentifier());
    if (isset($this->_profiles[$index])) {
      switch ($duplicationMode) {
      case self::DUPLICATE_OVERWRITE :
        break;
      case self::DUPLICATE_ERROR :
        throw new InvalidArgumentException(
          sprintf(
            'Duplicate application object profile: "%s"',
            $profile->getIdentifier()
          )
        );
      case self::DUPLICATE_IGNORE :
        return;
      }
    }
    if (!empty($index)) {
      $this->_profiles[$index] = $profile;
    }
  }

  /**
  * Get object instance, if the object does not exist and no profile is found, $className is
  * used to create a new object, if provided.
  *
  * @param string $identifier
  * @param string $className
  * @return object|NULL
  */
  public function getObject($identifier, $className = NULL) {
    $index = strtolower($identifier);
    if (isset($this->_objects[$index]) &&
        is_object($this->_objects[$index])) {
      return $this->_objects[$index];
    }
    if (isset($this->_profiles[$index])) {
      $this->_objects[$index] = $this->_profiles[$index]->createObject($this);
    } elseif (isset($className)) {
      $this->_objects[$index] = new $className();
    } else {
      throw new InvalidArgumentException(
        'Unknown profile identifier: '.$identifier
      );
    }
    return $this->_objects[$index];
  }

  /**
  * Store an object in the application registry.
  *
  * @param string $identifier
  * @param object $object
  * @return void
  */
  public function setObject($identifier,
                            $object,
                            $duplicationMode = self::DUPLICATE_ERROR) {
    $index = strtolower($identifier);
    if (isset($this->_objects[$index])) {
      switch ($duplicationMode) {
      case self::DUPLICATE_OVERWRITE :
        break;
      case self::DUPLICATE_ERROR :
        throw new LogicException(
          sprintf(
            'Application object does already exists: "%s"',
            $identifier
          )
        );
      case self::DUPLICATE_IGNORE :
        return;
      }
    }
    $this->_objects[$index] = $object;
  }

  /**
  * Check if an object or an profile for an object exists
  *
  * @param string $identifier
  * @param boolean $checkProfiles
  * @return boolean
  */
  public function hasObject($identifier, $checkProfiles = TRUE) {
    $index = strtolower($identifier);
    if (isset($this->_objects[$index]) &&
        is_object($this->_objects[$index])) {
      return TRUE;
    } elseif (!$checkProfiles) {
      return FALSE;
    }
    return isset($this->_profiles[$index]);
  }

  /**
  * Allow property syntax to get objects from the registry.
  *
  * @see getObject
  * @param string $name
  * @return object
  */
  public function __get($name) {
    return $this->getObject($name);
  }

  /**
  * Allow property syntax to put objects into the registry.
  *
  * @see setObject
  * @param string $name
  * @param object $value
  */
  public function __set($name, $value) {
    if (is_object($value)) {
      return $this->setObject($name, $value);
    } else {
      throw new InvalidArgumentException('The property value must be an object.');
    }
  }



  /**
  * Allow method syntax to get/set objects from/into the registry.
  *
  * @see __get
  * @see __set
  * @param string $name
  * @param object $value
  * @return object
  */
  public function __call($name, $arguments) {
    if (isset($arguments[0])) {
      return $this->__set($name, $arguments[0]);
    }
    return $this->__get($name);
  }

  /**
  * Allow property syntax to check object are availiable, this will return true even if only
  * a profile for the object exists.
  *
  * @see setObject
  * @param string $name
  * @param object $value
  */
  public function __isset($name) {
    return $this->hasObject($name);
  }
}
?>