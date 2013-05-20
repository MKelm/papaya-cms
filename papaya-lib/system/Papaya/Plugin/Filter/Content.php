<?php

interface PapayaPluginFilterContent extends PapayaXmlAppendable {

  function prepare($content, $options = array());

  function applyTo($content);
}