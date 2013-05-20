<?php

$modulePath = dirname(__FILE__);
require_once('PHPUnit/Runner/Version.php');
if (version_compare(PHPUnit_Runner_Version::id(), '3.5', '<')) {
  include_once('PHPUnit/Util/Class.php');
  include_once('PHPUnit/Util/Filter.php');
  include_once('PHPUnit/Framework/Exception.php');
}

class PapayaProxyObjectGenerator {

  /*
  * @var array
  */
  protected static $cache = array();

  /**
  * @var array
  */
  protected static $blacklistedMethodNames = array(
    '__clone' => TRUE,
    '__destruct' => TRUE,
    'abstract' => TRUE,
    'and' => TRUE,
    'array' => TRUE,
    'as' => TRUE,
    'break' => TRUE,
    'case' => TRUE,
    'catch' => TRUE,
    'class' => TRUE,
    'clone' => TRUE,
    'const' => TRUE,
    'continue' => TRUE,
    'declare' => TRUE,
    'default' => TRUE,
    'do' => TRUE,
    'else' => TRUE,
    'elseif' => TRUE,
    'enddeclare' => TRUE,
    'endfor' => TRUE,
    'endforeach' => TRUE,
    'endif' => TRUE,
    'endswitch' => TRUE,
    'endwhile' => TRUE,
    'extends' => TRUE,
    'final' => TRUE,
    'for' => TRUE,
    'foreach' => TRUE,
    'function' => TRUE,
    'global' => TRUE,
    'goto' => TRUE,
    'if' => TRUE,
    'implements' => TRUE,
    'interface' => TRUE,
    'instanceof' => TRUE,
    'namespace' => TRUE,
    'new' => TRUE,
    'or' => TRUE,
    'private' => TRUE,
    'protected' => TRUE,
    'public' => TRUE,
    'static' => TRUE,
    'switch' => TRUE,
    'throw' => TRUE,
    'try' => TRUE,
    'use' => TRUE,
    'var' => TRUE,
    'while' => TRUE,
    'xor' => TRUE
  );

  /**
  * @param  string  $originalClassName
  * @param  array   $methods
  * @param  string  $proxyClassName
  * @return array
  */
  public function generate($originalClassName, array $methods = NULL, $proxyClassName = '',
                           $callAutoload = FALSE) {

    if ($proxyClassName == '') {
      $key = md5(
        $originalClassName.
        serialize($methods)
      );

      if (isset(self::$cache[$key])) {
        return self::$cache[$key];
      }
    }

    $proxy = self::generateProxy(
      $originalClassName,
      $methods,
      $proxyClassName,
      $callAutoload
    );

    if (isset($key)) {
      self::$cache[$key] = $proxy;
    }

    return $proxy;
  }

  /**
  * @param  string  $originalClassName
  * @param  array   $methods
  * @param  string  $proxyClassName
  * @return array
  */
  protected static function generateProxy($originalClassName, array $methods = NULL,
                                          $proxyClassName = '', $callAutoload = FALSE) {
    $templateDir = dirname(__FILE__).DIRECTORY_SEPARATOR.'Generator'.DIRECTORY_SEPARATOR;
    $classTemplate = self::createTemplateObject(
      $templateDir.'proxied_class.tpl'
    );

    $proxyClassName = self::generateProxyClassName(
      $originalClassName, $proxyClassName
    );

    if (interface_exists($proxyClassName['fullClassName'], $callAutoload)) {
      throw new PHPUnit_Framework_Exception(
        sprintf(
          '"%s" is an interface.',
          $proxyClassName['fullClassName']
        )
      );
    }

    if (!class_exists($proxyClassName['fullClassName'], $callAutoload)) {
      throw new PHPUnit_Framework_Exception(
        sprintf(
          'Class "%s" does not exist.',
          $proxyClassName['fullClassName']
        )
      );
    }

    $class = new ReflectionClass($proxyClassName['fullClassName']);

    if ($class->isFinal()) {
      throw new PHPUnit_Framework_Exception(
        sprintf(
          'Class "%s" is declared "final". Can not create proxy.',
          $proxyClassName['fullClassName']
        )
      );
    }

    $proxyMethods = array();
    if (is_array($methods) && count($methods) > 0) {
      foreach ($methods as $methodName) {
        if ($class->hasMethod($methodName)) {
          $method = $class->getMethod($methodName);
          if (self::canProxyMethod($method)) {
            $proxyMethods[] = $method;
          } else {
            throw new PHPUnit_Framework_Exception(
              sprintf(
                'Can not proxy method "%s" of class "%s".',
                $methodName,
                $proxyClassName['fullClassName']
              )
            );
          }
        } else {
          throw new PHPUnit_Framework_Exception(
            sprintf(
              'Class "%s" has no protected method "%s".',
              $proxyClassName['fullClassName'],
              $methodName
            )
          );
        }
      }
    } else {
      $proxyMethods = $class->getMethods(ReflectionMethod::IS_PROTECTED);
      if (!(is_array($proxyMethods) && count($proxyMethods) > 0)) {
        throw new PHPUnit_Framework_Exception(
          sprintf(
            'Class "%s" has no protected methods.',
            $proxyClassName['fullClassName']
          )
        );
      }
    }

    $proxiedMethods = '';
    foreach ($proxyMethods as $method) {
      $proxiedMethods .= self::generateProxiedMethodDefinition(
        $templateDir, $method
      );
    }

    if (!empty($proxyClassName['namespaceName'])) {
      $prologue = 'namespace '.$proxyClassName['namespaceName'].";\n\n";
    }

    $classTemplate->setVar(
      array(
        'prologue' => isset($prologue) ? $prologue : '',
        'class_declaration' => $proxyClassName['proxyClassName'].' extends '.$originalClassName,
        'methods' => $proxiedMethods
      )
    );

    return array(
      'code' => $classTemplate->render(),
      'proxyClassName' => $proxyClassName['proxyClassName']
    );
  }

  /**
  * @param  string $originalClassName
  * @param  string $proxyClassName
  * @return array
  */
  protected static function generateProxyClassName($originalClassName, $proxyClassName) {
    $classNameParts = explode('\\', $originalClassName);

    if (count($classNameParts) > 1) {
      $originalClassName = array_pop($classNameParts);
      $namespaceName = join('\\', $classNameParts);
      $fullClassName = $namespaceName.'\\'.$originalClassName;
    } else {
      $namespaceName = '';
      $fullClassName = $originalClassName;
    }

    if ($proxyClassName == '') {
      do {
        $proxyClassName = 'Proxy_'.$originalClassName.'_'.substr(md5(microtime()), 0, 8);
      } while (class_exists($proxyClassName, FALSE));
    }

    return array(
      'proxyClassName' => $proxyClassName,
      'className' => $originalClassName,
      'fullClassName' => $fullClassName,
      'namespaceName' => $namespaceName
    );
  }

  /**
   * @param string $templateDir
   * @param ReflectionMethod $method
   * @return string
   */
  protected static function generateProxiedMethodDefinition($templateDir, $method) {
    if ($method->returnsReference()) {
      $reference = '&';
    } else {
      $reference = '';
    }

    $template = self::createTemplateObject(
      $templateDir . 'proxied_method.tpl'
    );

    $template->setVar(
      array(
        'arguments_declaration' => PHPUnit_Util_Class::getMethodParameters($method),
        'arguments' => self::getMethodCallParameters($method),
        'method_name' => $method->getName(),
        'reference'   => $reference
      )
    );

    return $template->render();
  }

  public static function getMethodCallParameters($method) {
    $parameters = array();
    foreach ($method->getParameters() as $i => $parameter) {
      $parameters[] = '$'.$parameter->getName();
    }
    return join(', ', $parameters);
  }

  /**
  * @param ReflectionMethod $method
  * @return boolean
  */
  protected static function canProxyMethod(ReflectionMethod $method) {
    if ($method->isConstructor() ||
        $method->isFinal() ||
        $method->isStatic() ||
      isset(self::$blacklistedMethodNames[$method->getName()])) {
      return FALSE;
    } elseif ($method->isProtected()) {
      return TRUE;
    }
    return FALSE;
  }

  /**
  * @param ReflectionMethod $method
  * @return boolean
  */
  protected static function createTemplateObject($file) {
    if (version_compare(PHPUnit_Runner_Version::id(), '3.5', '>=')) {
      include_once('Text/Template.php');
      return new Text_Template($file);
    } else {
      include_once('PHPUnit/Util/Template.php');
      return new PHPUnit_Util_Template($file);
    }
  }
}
