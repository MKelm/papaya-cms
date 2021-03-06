<?php

class PapayaDatabaseConditionGenerator {

  private $_mapping = NULL;
  private $_databaseAccess = NULL;

  private $_functions = array(
    'equal' => 'isEqual',
    'greater' => 'isGreaterThan',
    'greaterorequal' => 'isGreaterThanOrEqual',
    'less' => 'isLessThan',
    'lessorequal' => 'isLessThanOrEqual'
  );

  /**
   *
   * @param PapayaDatabaseInterfaceAccess|PapayaDatabaseAccess $parent
   * @param PapayaDatabaseInterfaceMapping $mapping
   * @throws InvalidArgumentException
   */
  public function __construct($parent, PapayaDatabaseInterfaceMapping $mapping = NULL) {
    if ($parent instanceOf PapayaDatabaseInterfaceAccess) {
      $this->_databaseAccess = $parent->getDatabaseAccess();
    } elseif ($parent instanceOf PapayaDatabaseAccess) {
      $this->_databaseAccess = $parent;
    } else {
      throw new InvalidArgumentException(
        sprintf('Invalid parent class %s in %s', get_class($parent), __METHOD__)
      );
    }
    $this->_mapping = $mapping;
  }

  public function fromArray($filter) {
    $group = new PapayaDatabaseConditionGroup($this->_databaseAccess, $this->_mapping, 'AND');
    $this->appendConditions($group, $filter);
    return $group;
  }

  private function appendConditions(PapayaDatabaseConditionGroup $group, $filter, $limit = 42) {
    foreach ($filter as $key => $value) {
      $definition = explode(',', $key);
      $field = PapayaUtilArray::get($definition, 0, '');
      $condition = strtoLower(PapayaUtilArray::get($definition, 1, 'equal'));
      if ($condition == 'and' && is_array($value)) {
        $this->appendConditions($group->logicalAnd(), $value, $limit - 1);
      } elseif ($condition == 'or' && is_array($value)) {
        $this->appendConditions($group->logicalOr(), $value, $limit - 1);
      } elseif (isset($this->_functions[$condition])) {
        call_user_func(array($group, $this->_functions[$condition]), $field, $value);
      }
    }
  }
}