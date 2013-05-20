<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:func="http://exslt.org/functions"
  xmlns:string="http://exslt.org/strings"
  extension-element-prefixes="func string"
>

<!--
  Transform ascii string from lowercase to uppercase and the other way around
 -->

<xsl:variable name="CHARACTERS_LOWERCASE" select="'abcdefghijklmnopqrstuvwxyz'" />
<xsl:variable name="CHARACTERS_UPPERCASE" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZ'" />

<func:function name="string:upper">
  <xsl:param name="string"/>
  <func:result select="translate($string, $CHARACTERS_LOWERCASE, $CHARACTERS_UPPERCASE)" />
</func:function>

<func:function name="string:lower">
  <xsl:param name="string"/>
  <func:result select="translate($string, $CHARACTERS_UPPERCASE, $CHARACTERS_LOWERCASE)" />
</func:function>

</xsl:stylesheet>