<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

<!--
  IMPORTANT! DO NOT CHANGE THIS FILE!

  If you need to change one of the templates just define a template with the
  same match value in your xsl file. This will override the imported template
  from this file.

  This is the only file which contains match templates in the base directory.
-->

<xsl:import href="../../_functions/javascript-encode-list.xsl"/>


<!-- pass through for unknown tags in the xml tree -->
<xsl:template match="*">
  <xsl:element name="{local-name()}">
    <xsl:copy-of select="@*"/>
    <xsl:apply-templates select="node()" />
  </xsl:element>
</xsl:template>

<!-- this template takes an object tag for flash and adds ie compatibility and swfobject -->
<xsl:template match="object[@type='application/x-shockwave-flash']">
  <xsl:choose>
    <xsl:when test="local-name(..) != 'object'">
      <xsl:variable name="objectId">
        <xsl:choose>
          <xsl:when test="@id"><xsl:value-of select="@id"/></xsl:when>
          <xsl:otherwise>flash<xsl:value-of select="generate-id(.)"/></xsl:otherwise>
        </xsl:choose>
      </xsl:variable>
      <object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" id="{$objectId}">
        <xsl:choose>
          <xsl:when test="@data-swfobject">
            <xsl:copy-of select="@data-swfobject" />
          </xsl:when>
          <xsl:when test="@version">
            <xsl:attribute name="data-swfobject">
              <xsl:call-template name="javascript-encode-list">
                <xsl:with-param name="values">
                   <version><xsl:value-of select="@version" /></version>
                   <installer>
                     <xsl:value-of select="$PAGE_THEME_PATH" />
                     <xsl:text>papaya/swfobject/expressInstall.swf</xsl:text>
                   </installer>
                </xsl:with-param>
              </xsl:call-template>
            </xsl:attribute>
          </xsl:when>
        </xsl:choose>
        <xsl:copy-of select="@width" />
        <xsl:copy-of select="@height" />
        <xsl:copy-of select="param" />
        <param name="movie" value="{@data}" />
        <xsl:comment><xsl:text disable-output-escaping="yes">[if !IE]&gt;</xsl:text></xsl:comment>
          <object>
            <xsl:copy-of select="@*[name() != 'version']" />
            <xsl:copy-of select="param" />
            <xsl:comment><xsl:text disable-output-escaping="yes">&lt;![endif]</xsl:text></xsl:comment>
              <xsl:apply-templates select="./*[name() != 'param']|./text()"/>
            <xsl:comment><xsl:text disable-output-escaping="yes">[if !IE]&gt;</xsl:text></xsl:comment>
          </object>
        <xsl:comment><xsl:text disable-output-escaping="yes">&lt;![endif]</xsl:text></xsl:comment>
      </object>
    </xsl:when>
    <xsl:otherwise>
      <xsl:comment><xsl:text disable-output-escaping="yes">[if !IE]&gt;</xsl:text></xsl:comment>
      <object>
        <xsl:copy-of select="@*[name() != 'version']" />
        <xsl:copy-of select="param" />
        <xsl:comment><xsl:text disable-output-escaping="yes">&lt;![endif]</xsl:text></xsl:comment>
          <xsl:apply-templates select="./*[name() != 'param']|./text()"/>
        <xsl:comment><xsl:text disable-output-escaping="yes">[if !IE]&gt;</xsl:text></xsl:comment>
      </object>
      <xsl:comment><xsl:text disable-output-escaping="yes">&lt;![endif]</xsl:text></xsl:comment>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="script">
  <script>
    <xsl:copy-of select="@*" />
    <xsl:comment>
    <xsl:copy-of select="text()"/><xsl:text> //</xsl:text></xsl:comment>
  </script>
</xsl:template>

</xsl:stylesheet>
