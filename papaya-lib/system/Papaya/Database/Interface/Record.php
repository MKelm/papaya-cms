<?php

interface PapayaDatabaseInterfaceRecord extends PapayaDatabaseInterfaceAccess {

  function assign($data);

  function toArray();

  function load($filter);

  function save();

  function delete();
}

