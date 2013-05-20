<?php

interface PapayaDatabaseInterfaceAccess {

  public function getDatabaseAccess();

  public function setDatabaseAccess(PapayaDatabaseAccess $access);
}