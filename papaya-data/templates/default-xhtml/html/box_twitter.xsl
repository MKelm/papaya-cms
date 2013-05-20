<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>
<!-- @papaya:modules TwitterBox -->

<!-- import main layout rules, this will import the default rules, too -->
<xsl:import href="./base/boxes.xsl" />

<!-- import rules for language-specific date and time conversion -->
<xsl:import href="../_lang/datetime.xsl" />

<!-- import module specific rules, this overrides the content-area and other default rules -->
<xsl:import href="./modules/free/Twitter/Box.xsl"/>

<!-- to change the output, redefine the imported rules here -->
</xsl:stylesheet>
