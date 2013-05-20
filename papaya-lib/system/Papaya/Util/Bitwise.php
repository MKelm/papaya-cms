<?php

class PapayaUtilBitwise {

  public static function inBitmask($bit, $bitmask) {
    return ($bitmask & $bit) == $bit;
  }
}