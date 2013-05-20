<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="#default"
>

<!--
  date and time formatting for different standards
-->

<!-- use international date time formatting, ISO 8601 -->
<xsl:param name="DATETIME_USE_ISO8601" select="false()" />
<!-- char between date and time (ISO 8601 = T, default = &#160;) -->
<xsl:param name="DATETIME_SEPARATOR">&#160;</xsl:param>
<!-- default date time format: short, medium or large -->
<xsl:param name="DATETIME_DEFAULT_FORMAT">short</xsl:param>

<xsl:param name="PAGE_LANGUAGE"></xsl:param>
<xsl:param name="PAGE_LANGUAGE_GROUP" select="substring-before($PAGE_LANGUAGE, '-')" />

<!-- generic templates -->
<xsl:template name="format-date">
  <xsl:param name="date" />
  <xsl:param name="format" select="$DATETIME_DEFAULT_FORMAT" />
  <xsl:call-template name="format-date-time">
    <xsl:with-param name="dateTime" select="$date" />
    <xsl:with-param name="format" select="$format" />
    <xsl:with-param name="outputTime" select="false()" />
  </xsl:call-template>
</xsl:template>

<xsl:template name="format-time">
  <xsl:param name="time" />
  <xsl:param name="showSeconds" select="false()" />
  <xsl:param name="timeSeparator" select="':'"/>
  <xsl:param name="hoursConvention" select="'24'"/>
  <xsl:param name="suffix" select="''" />
  <xsl:param name="isRecursion" select="false()" />
  <xsl:choose>
    <xsl:when test="$isRecursion">
      <xsl:variable name="hour" select="substring($time, 1, 2)"/>
      <xsl:variable name="minutes" select="substring($time, 4, 2)"/>
      <xsl:variable name="seconds" select="substring($time, 7, 2)"/>
      <xsl:if test="$hour and $hour != '' and $minutes and $minutes != ''">
        <xsl:choose>
          <xsl:when test="$hoursConvention = '12' and $hour &gt; 12">
            <xsl:value-of select="format-number($hour - 12, '00')"/>
          </xsl:when>
          <xsl:when test="$hoursConvention = '12' and $hour = '00'">
            <xsl:text>12</xsl:text>
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="$hour" />
          </xsl:otherwise>
        </xsl:choose>
        <xsl:value-of select="$timeSeparator" />
        <xsl:value-of select="$minutes" />
        <xsl:if test="$showSeconds and $seconds and $seconds != ''">
          <xsl:value-of select="$timeSeparator" />
          <xsl:value-of select="$seconds" />
        </xsl:if>
        <xsl:choose>
          <xsl:when test="$hoursConvention = '12' and $suffix = '[english]' and $hour &lt; 12">
            <xsl:text> am</xsl:text>
          </xsl:when>
          <xsl:when test="$hoursConvention = '12' and $suffix = '[english]'">
            <xsl:text> pm</xsl:text>
          </xsl:when>
          <xsl:otherwise><xsl:text> </xsl:text><xsl:value-of select="$suffix" /></xsl:otherwise>
        </xsl:choose>
      </xsl:if>
    </xsl:when>
    <xsl:when test="$DATETIME_USE_ISO8601">
      <!-- international date ISO8601-->
      <xsl:call-template name="format-time">
        <xsl:with-param name="time" select="$time" />
        <xsl:with-param name="showSeconds" select="true()" />
        <xsl:with-param name="timeSeparator">:</xsl:with-param>
        <xsl:with-param name="hoursConvention" select="12"/>
        <xsl:with-param name="isRecursion" select="true()" />
      </xsl:call-template>
    </xsl:when>
    <xsl:when test="$LANGUAGE_FORMATS/time/format[@language = $PAGE_LANGUAGE]">
      <xsl:variable name="timeFormat" select="$LANGUAGE_FORMATS/time/format[@language = $PAGE_LANGUAGE]" />
      <xsl:call-template name="format-time">
        <xsl:with-param name="time" select="$time" />
        <xsl:with-param name="showSeconds" select="$showSeconds" />
        <xsl:with-param name="timeSeparator" select="$timeFormat/@separator"/>
        <xsl:with-param name="hoursConvention" select="$timeFormat/@hours-convention"/>
        <xsl:with-param name="suffix" select="$timeFormat/@suffix" />
        <xsl:with-param name="isRecursion" select="true()" />
      </xsl:call-template>
    </xsl:when>
    <xsl:when test="$LANGUAGE_FORMATS/time/format[@language = $PAGE_LANGUAGE_GROUP]">
      <xsl:variable name="timeFormat" select="$LANGUAGE_FORMATS/time/format[@language = $PAGE_LANGUAGE_GROUP]" />
      <xsl:call-template name="format-time">
        <xsl:with-param name="time" select="$time" />
        <xsl:with-param name="showSeconds" select="$showSeconds" />
        <xsl:with-param name="timeSeparator" select="$timeFormat/@separator"/>
        <xsl:with-param name="hoursConvention" select="$timeFormat/@hours-convention"/>
        <xsl:with-param name="suffix" select="$timeFormat/@suffix" />
        <xsl:with-param name="isRecursion" select="true()" />
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:call-template name="format-time">
        <xsl:with-param name="time" select="$time" />
        <xsl:with-param name="showSeconds" select="$showSeconds" />
        <xsl:with-param name="timeSeparator" select="$timeSeparator"/>
        <xsl:with-param name="hoursConvention" select="$hoursConvention"/>
        <xsl:with-param name="suffix" select="$suffix" />
        <xsl:with-param name="isRecursion" select="true()" />
      </xsl:call-template>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="format-date-time">
  <xsl:param name="dateTime" />
  <xsl:param name="outputTime" select="true()" />
  <xsl:param name="showSeconds" select="false()" />
  <xsl:param name="format" select="$DATETIME_DEFAULT_FORMAT" />
  <xsl:param name="showOffset" select="false()" />
  <xsl:param name="formatOptions" />
  <xsl:param name="isRecursion" select="false()" />
  <xsl:variable name="date" select="substring($dateTime, 1, 10)" />
  <xsl:choose>
    <xsl:when test="$DATETIME_USE_ISO8601">
      <!-- international date ISO8601-->
      <xsl:call-template name="format-date-ymd">
         <xsl:with-param name="date" select="$date" />
         <xsl:with-param name="format" select="$format" />
      </xsl:call-template>
    </xsl:when>
    <xsl:when test="$formatOptions">
      <xsl:variable name="dateSeparator">
        <xsl:choose>
          <xsl:when test="$formatOptions/@separator = ' '">&#160;</xsl:when>
          <xsl:when test="$formatOptions/@separator != ''"><xsl:value-of select="$formatOptions/@separator" /></xsl:when>
          <xsl:otherwise>-</xsl:otherwise>
        </xsl:choose>
      </xsl:variable>
      <xsl:choose>
        <xsl:when test="$formatOptions/@order = 'dmy'">
          <xsl:call-template name="format-date-dmy">
            <xsl:with-param name="date" select="$date" />
            <xsl:with-param name="format" select="$format" />
            <xsl:with-param name="dateSeparator" select="$dateSeparator" />
            <xsl:with-param name="daySuffix" select="$formatOptions/@day-suffix" />
            <xsl:with-param name="monthSuffix" select="$formatOptions/@month-suffix" />
            <xsl:with-param name="yearSuffix" select="$formatOptions/@year-suffix" />
            <xsl:with-param name="abbrSuffix" select="$formatOptions/@abbr-suffix" />
          </xsl:call-template>
        </xsl:when>
        <xsl:when test="$formatOptions/@order = 'mdy'">
          <xsl:call-template name="format-date-mdy">
            <xsl:with-param name="date" select="$date" />
            <xsl:with-param name="format" select="$format" />
            <xsl:with-param name="dateSeparator" select="$dateSeparator" />
            <xsl:with-param name="daySuffix" select="$formatOptions/@day-suffix" />
            <xsl:with-param name="monthSuffix" select="$formatOptions/@month-suffix" />
            <xsl:with-param name="yearSuffix" select="$formatOptions/@year-suffix" />
            <xsl:with-param name="abbrSuffix" select="$formatOptions/@abbr-suffix" />
          </xsl:call-template>
        </xsl:when>
        <xsl:otherwise>
          <xsl:call-template name="format-date-ymd">
            <xsl:with-param name="date" select="$date" />
            <xsl:with-param name="format" select="$format" />
            <xsl:with-param name="dateSeparator" select="$dateSeparator" />
            <xsl:with-param name="daySuffix" select="$formatOptions/@day-suffix" />
            <xsl:with-param name="monthSuffix" select="$formatOptions/@month-suffix" />
            <xsl:with-param name="yearSuffix" select="$formatOptions/@year-suffix" />
            <xsl:with-param name="abbrSuffix" select="$formatOptions/@abbr-suffix" />
          </xsl:call-template>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:when>
    <xsl:when test="$isRecursion">
      <!-- international date -->
      <xsl:call-template name="format-date-ymd">
         <xsl:with-param name="date" select="$date" />
         <xsl:with-param name="format" select="$format" />
      </xsl:call-template>
    </xsl:when>
    <xsl:when test="$LANGUAGE_FORMATS/date/*[local-name() = $format]/format[@language = $PAGE_LANGUAGE]">
      <xsl:call-template name="format-date-time">
        <xsl:with-param name="formatOptions" select="$LANGUAGE_FORMATS/date/*[local-name() = $format]/format[@language = $PAGE_LANGUAGE]"/>
        <xsl:with-param name="dateTime" select="$dateTime" />
        <xsl:with-param name="format" select="$format" />
        <xsl:with-param name="outputTime" select="false()" />
        <xsl:with-param name="isRecursion" select="true()" />
      </xsl:call-template>
    </xsl:when>
    <xsl:when test="$LANGUAGE_FORMATS/date/*[local-name() = $format]/format[@language = $PAGE_LANGUAGE_GROUP]">
      <xsl:call-template name="format-date-time">
        <xsl:with-param name="formatOptions" select="$LANGUAGE_FORMATS/date/*[local-name() = $format]/format[@language = $PAGE_LANGUAGE_GROUP]"/>
        <xsl:with-param name="dateTime" select="$dateTime" />
        <xsl:with-param name="format" select="$format" />
        <xsl:with-param name="outputTime" select="false()" />
        <xsl:with-param name="isRecursion" select="true()" />
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <!-- international date -->
      <xsl:call-template name="format-date-ymd">
         <xsl:with-param name="date" select="$date" />
         <xsl:with-param name="format" select="$format" />
      </xsl:call-template>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:if test="$outputTime">
    <xsl:variable name="time" select="substring($dateTime, 12)"/>
    <xsl:if test="$time and $time != ''">
      <xsl:variable name="separator">
        <xsl:choose>
          <xsl:when test="$DATETIME_USE_ISO8601">
            <xsl:text>T</xsl:text>
          </xsl:when>
          <xsl:when test="$LANGUAGE_FORMATS/date/*[local-name() = $format]/format[@language = $PAGE_LANGUAGE]/@datetime-separator">
            <xsl:value-of select="$LANGUAGE_FORMATS/date/*[local-name() = $format]/format[@language = $PAGE_LANGUAGE]/@datetime-separator" />
          </xsl:when>
          <xsl:when test="$LANGUAGE_FORMATS/date/*[local-name() = $format]/format[@language = $PAGE_LANGUAGE_GROUP]/@datetime-separator">
            <xsl:value-of select="$LANGUAGE_FORMATS/date/*[local-name() = $format]/format[@language = $PAGE_LANGUAGE_GROUP]/@datetime-separator" />
          </xsl:when>
          <xsl:otherwise><xsl:value-of select="$DATETIME_SEPARATOR"/></xsl:otherwise>
        </xsl:choose>
      </xsl:variable>
      <xsl:value-of select="translate($separator, ' ', '&#160;')" />
      <xsl:call-template name="format-time">
        <xsl:with-param name="time" select="$time" />
        <xsl:with-param name="showSeconds" select="$showSeconds" />
      </xsl:call-template>
    </xsl:if>
    <xsl:if test="$showOffset and (substring($dateTime, 20, 5) != '')">
      <xsl:value-of select="substring($dateTime, 20, 5)"/>
    </xsl:if>
  </xsl:if>
</xsl:template>

<xsl:template name="format-date-ymd">
  <xsl:param name="date" />
  <xsl:param name="format">short</xsl:param>
  <xsl:param name="dateSeparator">-</xsl:param>
  <xsl:param name="daySuffix"></xsl:param>
  <xsl:param name="monthSuffix"></xsl:param>
  <xsl:param name="yearSuffix"></xsl:param>
  <xsl:param name="abbrSuffix"></xsl:param>
  <xsl:variable name="year" select="substring($date, 1, 4)"/>
  <xsl:variable name="month" select="substring($date, 6, 2)"/>
  <xsl:variable name="day" select="substring($date, 9, 2)"/>
  <xsl:choose>
    <xsl:when test="$format = 'large'">
      <!-- large -->
      <xsl:call-template name="format-year">
        <xsl:with-param name="year" select="$year" />
        <xsl:with-param name="suffix" select="$yearSuffix" />
      </xsl:call-template>
      <xsl:value-of select="$dateSeparator" />
      <xsl:call-template name="language-month">
        <xsl:with-param name="month" select="$month" />
        <xsl:with-param name="monthSuffix" select="$monthSuffix" />
      </xsl:call-template>
      <xsl:value-of select="$dateSeparator" />
      <xsl:call-template name="format-day">
        <xsl:with-param name="day" select="$day" />
        <xsl:with-param name="usePrefix" select="false()" />
        <xsl:with-param name="suffix" select="$daySuffix" />
      </xsl:call-template>
    </xsl:when>
    <xsl:when test="$format = 'medium'">
      <!-- medium -->
      <xsl:call-template name="format-year">
        <xsl:with-param name="year" select="$year" />
        <xsl:with-param name="suffix" select="$yearSuffix" />
      </xsl:call-template>
      <xsl:value-of select="$dateSeparator" />
      <xsl:call-template name="language-month-abbr">
        <xsl:with-param name="month" select="$month" />
        <xsl:with-param name="monthSuffix" select="$monthSuffix" />
        <xsl:with-param name="abbrSuffix" select="$abbrSuffix" />
      </xsl:call-template>
      <xsl:value-of select="$dateSeparator" />
      <xsl:call-template name="format-day">
        <xsl:with-param name="day" select="$day" />
        <xsl:with-param name="usePrefix" select="false()" />
        <xsl:with-param name="suffix" select="$daySuffix" />
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <!-- short -->
      <xsl:call-template name="format-year">
        <xsl:with-param name="year" select="$year" />
        <xsl:with-param name="suffix" select="$yearSuffix" />
      </xsl:call-template>
      <xsl:value-of select="$dateSeparator" />
      <xsl:value-of select="$month" />
      <xsl:value-of select="$dateSeparator" />
      <xsl:call-template name="format-day">
        <xsl:with-param name="day" select="$day" />
        <xsl:with-param name="suffix" select="$daySuffix" />
      </xsl:call-template>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="format-date-mdy">
  <xsl:param name="date" />
  <xsl:param name="format">short</xsl:param>
  <xsl:param name="dateSeparator">-</xsl:param>
  <xsl:param name="daySuffix"></xsl:param>
  <xsl:param name="monthSuffix"></xsl:param>
  <xsl:param name="yearSuffix"></xsl:param>
  <xsl:param name="abbrSuffix"></xsl:param>
  <xsl:variable name="year" select="substring($date, 1, 4)"/>
  <xsl:variable name="month" select="substring($date, 6, 2)"/>
  <xsl:variable name="day" select="substring($date, 9, 2)"/>
  <xsl:choose>
    <xsl:when test="$format = 'large'">
      <!-- large -->
      <xsl:call-template name="language-month">
        <xsl:with-param name="month" select="$month" />
        <xsl:with-param name="monthSuffix" select="$monthSuffix" />
      </xsl:call-template>
      <xsl:value-of select="$dateSeparator" />
      <xsl:call-template name="format-day">
        <xsl:with-param name="day" select="$day" />
        <xsl:with-param name="usePrefix" select="false()" />
        <xsl:with-param name="suffix" select="$daySuffix" />
      </xsl:call-template>
      <xsl:value-of select="$dateSeparator" />
      <xsl:call-template name="format-year">
        <xsl:with-param name="year" select="$year" />
        <xsl:with-param name="suffix" select="$yearSuffix" />
      </xsl:call-template>
    </xsl:when>
    <xsl:when test="$format = 'medium'">
      <!-- medium -->
      <xsl:call-template name="language-month-abbr">
        <xsl:with-param name="month" select="$month" />
        <xsl:with-param name="monthSuffix" select="$monthSuffix" />
        <xsl:with-param name="abbrSuffix" select="$abbrSuffix" />
      </xsl:call-template>
      <xsl:value-of select="$dateSeparator" />
      <xsl:call-template name="format-day">
        <xsl:with-param name="day" select="$day" />
        <xsl:with-param name="usePrefix" select="false()" />
        <xsl:with-param name="suffix" select="$daySuffix" />
      </xsl:call-template>
      <xsl:value-of select="$dateSeparator" />
      <xsl:call-template name="format-year">
        <xsl:with-param name="year" select="$year" />
        <xsl:with-param name="suffix" select="$yearSuffix" />
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <!-- short -->
      <xsl:value-of select="$month" />
      <xsl:value-of select="$dateSeparator" />
      <xsl:call-template name="format-day">
        <xsl:with-param name="day" select="$day" />
        <xsl:with-param name="suffix" select="$daySuffix" />
      </xsl:call-template>
      <xsl:value-of select="$dateSeparator" />
      <xsl:call-template name="format-year">
        <xsl:with-param name="year" select="$year" />
        <xsl:with-param name="suffix" select="$yearSuffix" />
      </xsl:call-template>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="format-date-dmy">
  <xsl:param name="date" />
  <xsl:param name="format">short</xsl:param>
  <xsl:param name="dateSeparator">-</xsl:param>
  <xsl:param name="daySuffix"></xsl:param>
  <xsl:param name="monthSuffix"></xsl:param>
  <xsl:param name="yearSuffix"></xsl:param>
  <xsl:param name="abbrSuffix"></xsl:param>
  <xsl:variable name="year" select="substring($date, 1, 4)"/>
  <xsl:variable name="month" select="substring($date, 6, 2)"/>
  <xsl:variable name="day" select="substring($date, 9, 2)"/>
  <xsl:choose>
    <xsl:when test="$format = 'large'">
      <!-- large -->
      <xsl:call-template name="format-day">
        <xsl:with-param name="day" select="$day" />
        <xsl:with-param name="usePrefix" select="false()" />
        <xsl:with-param name="suffix" select="$daySuffix" />
      </xsl:call-template>
      <xsl:value-of select="$dateSeparator" />
      <xsl:call-template name="language-month">
        <xsl:with-param name="month" select="$month" />
        <xsl:with-param name="monthSuffix" select="$monthSuffix" />
      </xsl:call-template>
      <xsl:value-of select="$dateSeparator" />
      <xsl:call-template name="format-year">
        <xsl:with-param name="year" select="$year" />
        <xsl:with-param name="suffix" select="$yearSuffix" />
      </xsl:call-template>
    </xsl:when>
    <xsl:when test="$format = 'medium'">
      <!-- medium -->
      <xsl:call-template name="format-day">
        <xsl:with-param name="day" select="$day" />
        <xsl:with-param name="usePrefix" select="false()" />
        <xsl:with-param name="suffix" select="$daySuffix" />
      </xsl:call-template>
      <xsl:value-of select="$dateSeparator" />
      <xsl:call-template name="language-month-abbr">
        <xsl:with-param name="month" select="$month" />
        <xsl:with-param name="monthSuffix" select="$monthSuffix" />
        <xsl:with-param name="abbrSuffix" select="$abbrSuffix" />
      </xsl:call-template>
      <xsl:value-of select="$dateSeparator" />
      <xsl:call-template name="format-year">
        <xsl:with-param name="year" select="$year" />
        <xsl:with-param name="suffix" select="$yearSuffix" />
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <!-- short -->
      <xsl:call-template name="format-day">
        <xsl:with-param name="day" select="$day" />
        <xsl:with-param name="suffix" select="$daySuffix" />
      </xsl:call-template>
      <xsl:value-of select="$dateSeparator" />
      <xsl:value-of select="$month" />
      <xsl:value-of select="$monthSuffix" />
      <xsl:value-of select="$dateSeparator" />
      <xsl:call-template name="format-year">
        <xsl:with-param name="year" select="$year" />
        <xsl:with-param name="suffix" select="$yearSuffix" />
      </xsl:call-template>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="format-day">
  <xsl:param name="day" />
  <xsl:param name="usePrefix" select="true()" />
  <xsl:param name="suffix"></xsl:param>
  <xsl:variable name="dayString">
    <xsl:choose>
      <xsl:when test="$usePrefix and (string-length($day) = 1)">
        <xsl:text>0</xsl:text>
        <xsl:value-of select="$day"/>
      </xsl:when>
      <xsl:when test="not($usePrefix) and starts-with($day, '0')">
        <xsl:value-of select="substring($day, 2)"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="$day"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:variable>
  <xsl:choose>
    <xsl:when test="$suffix = '[english]'">
      <xsl:value-of select="$dayString" />
      <xsl:choose>
        <xsl:when test="$day = '1'">st</xsl:when>
        <xsl:when test="$day = '2'">nd</xsl:when>
        <xsl:when test="$day = '3'">rd</xsl:when>
        <xsl:when test="(substring($day, 2, 1) = '1') and (not($day = '11'))">st</xsl:when>
        <xsl:when test="(substring($day, 2, 1) = '2') and (not($day = '12'))">nd</xsl:when>
        <xsl:when test="(substring($day, 2, 1) = '3') and (not($day = '13'))">rd</xsl:when>
        <xsl:otherwise>th</xsl:otherwise>
      </xsl:choose>
    </xsl:when>
    <xsl:when test="$suffix != ''">
      <xsl:value-of select="$dayString" />
      <xsl:value-of select="$suffix" />
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="$dayString" />
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="format-month">
  <xsl:param name="monthName"/>
  <xsl:param name="monthSuffix"></xsl:param>
  <xsl:choose>
    <xsl:when test="$monthSuffix = '[russian]'">
      <xsl:variable name="length" select="string-length($monthName)" />
      <xsl:variable name="lastChar" select="substring($monthName, $length)" />
      <xsl:choose>
        <xsl:when test="$lastChar = '&#1090;'">
          <xsl:value-of select="$monthName" />
          <xsl:text>&#1072;</xsl:text>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="substring($monthName, 1, $length -1)" />
          <xsl:text>&#1103;</xsl:text>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:when>
    <xsl:when test="$monthSuffix != ''">
      <xsl:value-of select="$monthName" />
      <xsl:value-of select="$monthSuffix" />
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="$monthName" />
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="format-year">
  <xsl:param name="year" />
  <xsl:param name="suffix"></xsl:param>
  <xsl:choose>
    <xsl:when test="$suffix != ''">
      <xsl:value-of select="$year" />
      <xsl:value-of select="$suffix" />
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="$year" />
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="language-month-abbr">
  <xsl:param name="month"/>
  <xsl:param name="monthSuffix"></xsl:param>
  <xsl:param name="abbrSuffix"></xsl:param>
  <xsl:variable name="abbreviation">
    <xsl:call-template name="language-month">
      <xsl:with-param name="month" select="$month" />
      <xsl:with-param name="identPrefix">MONTH_ABBR</xsl:with-param>
    </xsl:call-template>
  </xsl:variable>
  <xsl:variable name="full">
    <xsl:call-template name="language-month">
      <xsl:with-param name="month" select="$month" />
    </xsl:call-template>
  </xsl:variable>
  <xsl:choose>
    <xsl:when test="$abbreviation = $full">
      <xsl:call-template name="format-month">
        <xsl:with-param name="monthName" select="$full" />
        <xsl:with-param name="monthSuffix" select="$monthSuffix" />
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="$abbreviation" />
      <xsl:value-of select="$abbrSuffix" />
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="language-month">
  <xsl:param name="month"/>
  <xsl:param name="monthSuffix"></xsl:param>
  <xsl:param name="identPrefix">MONTH_NAME</xsl:param>
  <xsl:variable name="name">
    <xsl:choose>
      <xsl:when test="number($month) = 1">JANUARY</xsl:when>
      <xsl:when test="number($month) = 2">FEBRUARY</xsl:when>
      <xsl:when test="number($month) = 3">MARCH</xsl:when>
      <xsl:when test="number($month) = 4">APRIL</xsl:when>
      <xsl:when test="number($month) = 5">MAY</xsl:when>
      <xsl:when test="number($month) = 6">JUNE</xsl:when>
      <xsl:when test="number($month) = 7">JULY</xsl:when>
      <xsl:when test="number($month) = 8">AUGUST</xsl:when>
      <xsl:when test="number($month) = 9">SEPTEMBER</xsl:when>
      <xsl:when test="number($month) = 10">OCTOBER</xsl:when>
      <xsl:when test="number($month) = 11">NOVEMBER</xsl:when>
      <xsl:when test="number($month) = 12">DECEMBER</xsl:when>
      <xsl:otherwise></xsl:otherwise>
    </xsl:choose>
  </xsl:variable>
  <xsl:if test="$name != ''">
    <xsl:call-template name="format-month">
      <xsl:with-param name="monthName">
        <xsl:call-template name="language-text">
          <xsl:with-param name="text">
            <xsl:value-of select="$identPrefix"/>
            <xsl:text>_</xsl:text>
            <xsl:value-of select="$name"/>
          </xsl:with-param>
        </xsl:call-template>
      </xsl:with-param>
      <xsl:with-param name="monthSuffix" select="$monthSuffix" />
    </xsl:call-template>
  </xsl:if>
</xsl:template>

<xsl:template name="language-weekday-abbr">
  <xsl:param name="weekDay"/>
  <xsl:param name="abbrSuffix"></xsl:param>
  <xsl:variable name="abbreviation">
    <xsl:call-template name="language-weekday">
      <xsl:with-param name="weekDay" select="$weekDay" />
      <xsl:with-param name="identPrefix">DAY_ABBR</xsl:with-param>
    </xsl:call-template>
  </xsl:variable>
  <xsl:variable name="full">
    <xsl:call-template name="language-weekday">
      <xsl:with-param name="weekDay" select="$weekDay" />
    </xsl:call-template>
  </xsl:variable>
  <xsl:value-of select="$abbreviation" />
  <xsl:if test="$abbreviation != $full">
    <xsl:value-of select="$abbrSuffix" />
  </xsl:if>
</xsl:template>

<xsl:template name="language-weekday">
  <xsl:param name="weekDay"/>
  <xsl:param name="identPrefix">DAY_NAME</xsl:param>
  <xsl:variable name="name">
    <xsl:choose>
      <xsl:when test="number($weekDay) = 0">SUNDAY</xsl:when>
      <xsl:when test="number($weekDay) = 1">MONDAY</xsl:when>
      <xsl:when test="number($weekDay) = 2">TUESDAY</xsl:when>
      <xsl:when test="number($weekDay) = 3">WEDNESDAY</xsl:when>
      <xsl:when test="number($weekDay) = 4">THURSDAY</xsl:when>
      <xsl:when test="number($weekDay) = 5">FRIDAY</xsl:when>
      <xsl:when test="number($weekDay) = 6">SATURDAY</xsl:when>
      <xsl:when test="number($weekDay) = 7">SUNDAY</xsl:when>
      <xsl:otherwise></xsl:otherwise>
    </xsl:choose>
  </xsl:variable>
  <xsl:if test="$name != ''">
    <xsl:call-template name="language-text">
      <xsl:with-param name="text">
        <xsl:value-of select="$identPrefix"/>
        <xsl:text>_</xsl:text>
        <xsl:value-of select="$name"/>
      </xsl:with-param>
    </xsl:call-template>
  </xsl:if>
</xsl:template>

<xsl:template name="format-rfc2822">
  <xsl:param name="dateTime"/>
  <xsl:param name="separator" select="' '"/>
  <xsl:variable name="weekDay" select="substring($dateTime, 26, 3)"/>
  <xsl:if test="$weekDay != ''">
    <xsl:value-of select="$weekDay"/>
    <xsl:text>,</xsl:text>
    <xsl:value-of select="$separator" />
  </xsl:if>
  <xsl:value-of select="number(substring($dateTime, 9, 2))" />
  <xsl:value-of select="$separator" />
  <xsl:variable name="month" select="number(substring($dateTime, 6, 2))"/>
  <xsl:choose>
    <xsl:when test="$month = 1">Jan</xsl:when>
    <xsl:when test="$month = 2">Feb</xsl:when>
    <xsl:when test="$month = 3">Mar</xsl:when>
    <xsl:when test="$month = 4">Apr</xsl:when>
    <xsl:when test="$month = 5">May</xsl:when>
    <xsl:when test="$month = 6">Jun</xsl:when>
    <xsl:when test="$month = 7">Jul</xsl:when>
    <xsl:when test="$month = 8">Aug</xsl:when>
    <xsl:when test="$month = 9">Sep</xsl:when>
    <xsl:when test="$month = 10">Oct</xsl:when>
    <xsl:when test="$month = 11">Nov</xsl:when>
    <xsl:when test="$month = 12">Dec</xsl:when>
  </xsl:choose>
  <xsl:value-of select="$separator" />
  <xsl:value-of select="substring($dateTime, 1, 4)"/>
  <xsl:value-of select="$separator" />
  <xsl:value-of select="substring($dateTime, 12, 8)"/>
  <xsl:variable name="offset" select="substring($dateTime, 20, 5)"/>
  <xsl:if test="$offset != ''">
    <xsl:value-of select="$separator" />
    <xsl:value-of select="$offset"/>
  </xsl:if>
</xsl:template>

</xsl:stylesheet>