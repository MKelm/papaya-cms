<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>
<!--
  @papaya:modules actionbox_pagezoom_style
-->

<xsl:import href="./base/boxes.xsl" />

<xsl:template match="zoom">
  <style type="text/css">
    <xsl:text>body { font-size: </xsl:text>
      <xsl:value-of select="."/>
    <xsl:text>em; }</xsl:text>
  </style>
</xsl:template>

</xsl:stylesheet>
