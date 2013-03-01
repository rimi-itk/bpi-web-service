<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    
    <xsl:output version="5.0" method="html" encoding="UTF-8" omit-xml-declaration="yes" />
    
    <xsl:template match="/">
    
        <html>
            <head>
                
            </head>
            <body>    
              <div style="font: 14px 'Helvetica Neue', Helvetica, Arial, sans-serif;">
                <xsl:apply-templates select="bpi/entity"/>
              </div>
            </body>
        </html>
       
    </xsl:template>
            
    <xsl:template match="entity">
        <h4>
            <xsl:value-of select="./@name" />
        </h4>
        <ul>
            <xsl:apply-templates select="./links"/>
            <xsl:apply-templates select="./properties"/>
        </ul>
        <blockquote>
            <xsl:apply-templates select="./entity"/>
        </blockquote>
    </xsl:template>

    <xsl:template match="links">

        <xsl:for-each select="./link">
            <li>
                <a target="_parent">
                    <xsl:attribute name="href"><xsl:value-of select="./@href" />.html</xsl:attribute>
                    <xsl:value-of select="./@rel" />
                </a>
            </li>
        </xsl:for-each>

    </xsl:template>

    <xsl:template match="entity/properties">

        <xsl:for-each select="./property">
            <li>
                <strong>
                    <xsl:value-of select="./@name" />
                </strong>:
                <xsl:value-of select="." />
            </li>
        </xsl:for-each>

    </xsl:template>

</xsl:stylesheet>