<?php

abstract class PapayaTemplateSimpleAstNode implements PapayaTemplateSimpleAst {

  /**
   * Read private properties stored in constructor
   *
   * @param string $name
   */
  public function __get($name) {
    $property = '_'.$name;
    if (property_exists($this, $property)) {
      return $this->$property;
    }
    throw new LogicException(
      sprintf('Unknown property: %s::$%s', get_class($this), $name)
    );
  }

  /**
   * Block all undefined properties
   *
   * @throws LogicException
   * @param string $name
   * @param mixed $name
   */
  public function __set($name, $value) {
    throw new LogicException('All properties are defined in the constrcutor, they are read only.');
  }

  /**
   * Tell the visitor to visit this node.
   *
   * @param PapayaTemplateSimpleVisitor $visitor
   */
  public function accept(PapayaTemplateSimpleVisitor $visitor) {
    $visitor->visit($this);
  }
}