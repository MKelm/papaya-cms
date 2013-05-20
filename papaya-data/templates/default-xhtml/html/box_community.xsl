<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>
<!--
  @papaya:modules actionbox_surfers_online, actionbox_contact_list, actionbox_contact_status
-->


<!-- import main layout rules, this will import the default rules, too -->
<xsl:import href="./base/boxes.xsl" />

<!-- import module specific rules, this overrides the content-area and other default rules -->
<xsl:import href="./modules/_base/community/actionbox_community.xsl"/>

<!-- to change the output, redefine the imported rules here -->

</xsl:stylesheet>