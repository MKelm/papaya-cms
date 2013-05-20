<?php

class PapayaDatabaseConditionRoot extends PapayaDatabaseConditionGroup {

  public function __call($method, $arguments) {
    if (count($this) > 0) {
      throw new LogicException(
        sprintf(
          '"%s" can only contain a single condition use logicalAnd() or logicalOr().',
          get_class($this)
        )
      );
    }
    return parent::__call($method, $arguments);
  }

  public function getSql($silent = FALSE) {
    foreach ($this as $condition) {
      return $condition->getSql($silent);
    }
    return '';
  }
}
