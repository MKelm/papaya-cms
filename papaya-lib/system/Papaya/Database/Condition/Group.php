<?php

/**
 *
 * @method PapayaDatabaseConditionGroup logicalAnd()
 * @method PapayaDatabaseConditionGroup logicalOr()
 * @method PapayaDatabaseConditionElement isEqual()
 *   isEqual(string $field, mixed $value)
 * @method PapayaDatabaseConditionElement isGreaterThan()
 *   isGreaterThan(string $field, mixed $value)
 * @method PapayaDatabaseConditionElement isGreaterThanOrEqual()
 *   isGreaterThanOrEqual(string $field, mixed $value)
 * @method PapayaDatabaseConditionElement isLessThan()
 *   isLessThan(string $field, mixed $value)
 * @method PapayaDatabaseConditionElement isLessThanOrEqual()
 *   isLessThanOrEqual(string $field, mixed $value)
 */
class PapayaDatabaseConditionGroup
  extends PapayaDatabaseConditionElement
  implements IteratorAggregate, Countable {

  private $_conditions = array();
  private $_databaseAccess = NULL;
  private $_mapping = NULL;

  private $_classes = array(
    'isequal' => array('PapayaDatabaseConditionElement', '='),
    'isnull' => array('PapayaDatabaseConditionElement', 'ISNULL'),
    'isgreaterthan' => array('PapayaDatabaseConditionElement', '>'),
    'isgreaterthanorequal' => array('PapayaDatabaseConditionElement', '>='),
    'islessthan' => array('PapayaDatabaseConditionElement', '<'),
    'islessthanorequal' => array('PapayaDatabaseConditionElement', '<='),
  );

  public function __construct(
      $parent, PapayaDatabaseInterfaceMapping $mapping = NULL, $operator = 'AND'
    ) {
    if ($parent instanceOf PapayaDatabaseConditionGroup) {
      parent::__construct($parent, NULL, NULL, $operator);
    } elseif ($parent instanceOf PapayaDatabaseInterfaceAccess) {
      $this->_databaseAccess = $parent->getDatabaseAccess();
    } elseif ($parent instanceOf PapayaDatabaseAccess) {
      $this->_databaseAccess = $parent;
    } else {
      throw new InvalidArgumentException(
        sprintf('Invalid parent class %s in %s', get_class($parent), __METHOD__)
      );
    }
    $this->_mapping = $mapping;
    $this->_operator = $operator;
  }

  public function end() {
    return $this->getParent();
  }

  public function getDatabaseAccess() {
    if (isset($this->_databaseAccess)) {
      return $this->_databaseAccess;
    }
    return parent::getDatabaseAccess();
  }

  public function getMapping() {
    if (isset($this->_mapping)) {
      return $this->_mapping;
    }
    return parent::getMapping();
  }

  public function __call($methodName, $arguments) {
    $name = strtolower($methodName);
    switch ($name) {
    case 'logicaland' :
      $this->_conditions[] = $condition = new PapayaDatabaseConditionGroup($this, NULL, 'AND');
      return $condition;
    case 'logicalor' :
      $this->_conditions[] = $condition = new PapayaDatabaseConditionGroup($this, NULL, 'OR');
      return $condition;
    default :
      if (isset($this->_classes[$name])) {
        list($field, $value) = $arguments;
        list($className, $operator) = $this->_classes[$name];
        $this->_conditions[] = new $className($this, $field, $value, $operator);
        return $this;
      }
    }
    throw new BadMethodCallException(
      sprintf('Invalid condition create method %s::%s().', get_class($this), $methodName)
    );
  }

  public function getIterator() {
    return new ArrayIterator($this->_conditions);
  }

  public function count() {
    return count($this->_conditions);
  }

  public function getSql($silent = FALSE) {
    $operator = ' '.$this->_operator.' ';
    $result = '';
    foreach ($this as $condition) {
      if ($sql = $condition->getSql($silent)) {
        $result .= $operator.$sql;
      }
    }
    $result = substr($result, strlen($operator));
    return empty($result) ? '' : '('.$result.')';
  }
}
