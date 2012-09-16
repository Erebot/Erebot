<?xml version="1.0" ?>
<!--
    Taken from http://www.phpbuilder.com/columns/adam_delves20060719.php3
    with slight modifications from François Poirotte.
-->

<!-- root elements and namespace declarations
     This is an XSL style sheet that transforms froms an XML Schematron schema
     to XSL. The axsl prefix is the transformed XSL -->
<xsl:stylesheet
   version="1.0"
   xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
   xmlns:axsl="http://www.w3.org/1999/XSL/TransformAlias"
   xmlns:sch="http://www.ascc.net/xml/schematron" >
   
<!-- the bulk of the transformation is carried out by the skeleton stylsheet -->   
<xsl:import href="@xsl_skeleton@"/>

<!-- the validation output will be as XML -->
<xsl:template name="process-prolog">
   <axsl:output method="xml" />
</xsl:template>

<!--
    the root of the valdation XML is <report>.
    The original "schematron-custom.xsl" used
    <formXmlSchematron> as the root instead.
-->
<xsl:template name="process-root">
    <xsl:param name="title" />
    <xsl:param name="contents"/>
    <report>
        <xsl:copy-of select="$contents"/>
    </report>
</xsl:template>

<!-- this template is called when an assertion is processed, it creates a <failedAssert> elemeny-->
<xsl:template name="process-assert">
    <xsl:param name="test" />
    <xsl:param name="diagnostics" />
    <!--
        We use "assertionFailure" instead of the original "assertFailed"
        because it fits better with our terminology (and that of PHPUnit).
    -->
    <assertionFailure test="{$test}">         
        <axsl:element name="location"><axsl:apply-templates select="." mode="schematron-get-full-path" /></axsl:element>
        <description><xsl:apply-templates mode="text"/></description>
    </assertionFailure>
</xsl:template>

<!-- this template is called when a report is processed, it creates a <reportFact> element -->
<xsl:template name="process-report">
    <xsl:param name="test" />
    <xsl:param name="diagnostics" />
    <!-- "reportFact" is simply "fact" for us. -->
    <fact test="{$test}">
        <axsl:element name="location"><axsl:apply-templates select="." mode="schematron-get-full-path" /></axsl:element>
        <description><xsl:apply-templates mode="text"/></description>
    </fact>
</xsl:template>

<!-- this overrides the default value-of processor in the skeleton which is only processed when the output of validation is text -->
<xsl:template match="sch:value-of | value-of">
    <xsl:if test="not(@select)">
        <xsl:message>Markup Error: no select attribute in &lt;value-of></xsl:message>
    </xsl:if>
    
    <xsl:call-template name="IamEmpty" />
    <axsl:text xml:space="preserve"> </axsl:text>
    
    <xsl:choose>
        <xsl:when test="@select">
            <xsl:call-template name="process-value-of">
                <xsl:with-param name="select" select="@select"/>  
                                   <!-- will saxon have problem with this too?? -->
                </xsl:call-template>
        </xsl:when>
        <xsl:otherwise >
            <xsl:call-template name="process-value-of"
                ><xsl:with-param name="select" select="'.'"/>
            </xsl:call-template>
        </xsl:otherwise>
        </xsl:choose>
    <axsl:text xml:space="preserve"> </axsl:text>
</xsl:template>

<!-- the template processor for the let element appears to be missing in the skeleton, so here it is -->
<xsl:template match="sch:let | let">
    <xsl:if test="not(@value)">
        <xsl:message>Markup Error: no value attribute in &lt;let></xsl:message>
    </xsl:if>
    
    <xsl:if test="not(@name)">
        <xsl:message>Markup Error: no name attribute in &lt;let></xsl:message>
    </xsl:if>

    <xsl:call-template name="IamEmpty" />
        <axsl:text xml:space="preserve"> </axsl:text>
            <xsl:call-template name="process-let">
                <xsl:with-param name="select" select="@value"/>  
                <xsl:with-param name="name" select="@name" />
            </xsl:call-template>
        <axsl:text xml:space="preserve"> </axsl:text>
    </xsl:template>

    <xsl:template name="process-let">
        <xsl:param name="select" />
        <xsl:param name="name" />
        <axsl:variable select="{$select}" name="{$name}" />
    </xsl:template>
</xsl:stylesheet>
