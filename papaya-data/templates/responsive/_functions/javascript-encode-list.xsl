<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:func="http://exslt.org/functions"
  xmlns:exsl="http://exslt.org/common"
  xmlns:papaya-fn="http://www.papaya-cms.com/ns/functions"
  extension-element-prefixes="func"
  exclude-result-prefixes="#default papaya-fn"
>

<!--
Encode a simple list of tags into an json object, each tag needs
to be unique.

<name>value</name>
to
{"name":"value"}
-->

<xsl:import href="./javascript-escape-string.xsl" />

<func:function name="papaya-fn:javascript-encode-list">
  <xsl:param name="values"/>
  <xsl:param name="quoteChar">"</xsl:param>
  <func:result>
    <xsl:call-template name="javascript-encode-list">
      <xsl:with-param name="values" select="$values"/>
      <xsl:with-param name="quoteChar" select="$quoteChar"/>
    </xsl:call-template>
  </func:result>
</func:function>

<xsl:template name="javascript-encode-list">
  <xsl:param name="values"/>
  <xsl:param name="quoteChar">"</xsl:param>
  <xsl:variable name="list" select="exsl:node-set($values)/*"/>
  <xsl:if test="count($list) &gt; 0">
    <xsl:text>{</xsl:text>
    <xsl:for-each select="$list">
      <xsl:if test="position() &gt; 1">
        <xsl:text>,</xsl:text>
      </xsl:if>
      <xsl:value-of select="papaya-fn:javascript-escape-string(name(), $quoteChar, true())"/>
      <xsl:text>:</xsl:text>
      <xsl:value-of select="papaya-fn:javascript-escape-string(text(), $quoteChar, true())"/>
    </xsl:for-each>
    <xsl:text>}</xsl:text>
  </xsl:if>
</xsl:template>

</xsl:stylesheet>