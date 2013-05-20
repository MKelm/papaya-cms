<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:func="http://exslt.org/functions"
  extension-element-prefixes="func"
  exclude-result-prefixes="#default"
>

<!--
  This file wraps the language templates into functions.
  This depends on EXSLT, so it works only with xslt processors supporting it.
  We do not use it in default templates, but you can use them in project specific templates.

  To use the function you have to add the func namespace (http://exslt.org/functions) to your xslt file.
-->

<xsl:import href="./language.xsl" />

<func:function name="func:language-text">
  <xsl:param name="text"/>
  <xsl:param name="userText"></xsl:param>
  <func:result>
    <xsl:call-template name="language-text">
      <xsl:with-param name="text" select="$text" />
      <xsl:with-param name="userText" select="$userText" />
    </xsl:call-template>
  </func:result>
</func:function>

<func:function name="func:format-currency">
  <xsl:param name="float"/>
  <xsl:param name="pattern">#,##0.00</xsl:param>
  <func:result>
    <xsl:call-template name="format-currency">
      <xsl:with-param name="float" select="$float"/>
      <xsl:with-param name="pattern" select="$pattern"/>
    </xsl:call-template>
  </func:result>
</func:function>

<func:function name="func:format-number">
  <xsl:param name="float"/>
  <xsl:param name="pattern">#,##0.00</xsl:param>
  <func:result>
    <xsl:call-template name="format-number">
      <xsl:with-param name="float" select="$float"/>
      <xsl:with-param name="pattern" select="$pattern"/>
    </xsl:call-template>
  </func:result>
</func:function>

<func:function name="func:format-date-time">
  <xsl:param name="dateTime" />
  <xsl:param name="outputTime" select="true()" />
  <xsl:param name="showSeconds" select="false()" />
  <xsl:param name="format" select="$DATETIME_DEFAULT_FORMAT" />
  <func:result>
    <xsl:call-template name="format-date-time">
      <xsl:with-param name="dateTime" select="$dateTime"/>
      <xsl:with-param name="outputTime" select="$outputTime" />
      <xsl:with-param name="showSeconds" select="$showSeconds" />
      <xsl:with-param name="format" select="$format" />
    </xsl:call-template>
  </func:result>
</func:function>

<func:function name="func:format-rfc2822">
  <xsl:param name="dateTime" />
  <func:result>
    <xsl:call-template name="format-rfc2822">
      <xsl:with-param name="dateTime" select="$dateTime"/>
    </xsl:call-template>
  </func:result>
</func:function>

<func:function name="func:format-date">
  <xsl:param name="date" />
  <xsl:param name="format" select="$DATETIME_DEFAULT_FORMAT" />
  <func:result>
    <xsl:call-template name="format-date">
      <xsl:with-param name="date" select="$date"/>
    </xsl:call-template>
  </func:result>
</func:function>

<func:function name="func:format-time">
  <xsl:param name="time" />
  <xsl:param name="showSeconds" select="false()" />
  <xsl:param name="format" select="$DATETIME_DEFAULT_FORMAT" />
  <func:result>
    <xsl:call-template name="format-time">
      <xsl:with-param name="time" select="$time"/>
      <xsl:with-param name="showSeconds" select="$showSeconds" />
    </xsl:call-template>
  </func:result>
</func:function>

</xsl:stylesheet>