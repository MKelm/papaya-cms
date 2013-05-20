<?xml version="1.0" encoding="iso-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:variable name="FORM_FIELDS" select="//input|//textarea|//select"/>

<xsl:variable
  name="HAS_CONTROLS_RICHTEXT"
  select="count($FORM_FIELDS[contains(concat(' ', normalize-space(@class), ' '), ' dialogRichtext ')]) &gt; 0"/>

<xsl:variable
  name="HAS_CONTROLS_SIMPLERICHTEXT"
  select="count($FORM_FIELDS[contains(concat(' ', normalize-space(@class), ' '), ' dialogSimpleRichtext ')]) &gt; 0"/>

<xsl:variable
  name="HAS_CONTROLS_INDIVIDUALRICHTEXT"
  select="count($FORM_FIELDS[contains(concat(' ', normalize-space(@class), ' '), ' dialogIndividualRichtext ')]) &gt; 0"/>
  
<xsl:variable name="HAS_RTE" select="$FORM_FIELDS[@data-rte]"/>

<xsl:variable name="EMBED_TINYMCE" select="$HAS_RTE or $HAS_CONTROLS_RICHTEXT or $HAS_CONTROLS_SIMPLERICHTEXT or $HAS_CONTROLS_INDIVIDUALRICHTEXT" />

<xsl:variable name="EMBED_JQUERY" select="true()" />

</xsl:stylesheet>