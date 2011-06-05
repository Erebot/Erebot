<?php
/*
    This file is part of Erebot.

    Erebot is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Erebot is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Erebot.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * \brief
 *      An extension of PHP's DomDocument class that implements
 *      Schematron validation on top of RelaxNG/XML Schema.
 *
 * This class also makes it easier to deal with libxml errors,
 * by providing methods to clear or retrieve errors directly.
 *
 * \see
 *      http://php.net/domdocument
 *
 * \see
 *      http://www.schematron.com/
 */
class   Erebot_DOM
extends DomDocument
{
    protected $_errors;

    /**
     * Constructs a new DOM document.
     *
     * \param string $version
     *      (optional) XML version to use.
     *
     * \param string $encoding
     *      (optional) Encoding for the document.
     */
    public function __construct($version = NULL, $encoding = NULL)
    {
        $this->clearErrors();
        if ($version === NULL && $encoding === NULL)
            parent::__construct();
        else if ($encoding === NULL)
            parent::__construct($version);
        else
            parent::__construct($version, $encoding);
    }

    /**
     * Validates the current document against a RelaxNG schema,
     * optionally validates embedded Schematron rules too.
     *
     * \param string $filename
     *      Path to the RelaxNG schema to use for validation.
     *
     * \param bool $schematron
     *      (optional) Whether embedded Schematron rules
     *      should be validated too (TRUE) or not (FALSE).
     *      The default is to also do $schematron validation.
     *
     * \retval bool
     *      Returns TRUE if the document validates, FALSE otherwise.
     */
    public function relaxNGValidate($filename, $schematron=TRUE)
    {
        $success = parent::relaxNGValidate($filename);
        return $this->_schematronValidation(
            'file',
            $filename,
            'RNG',
            $success,
            $schematron
        );
    }

    /**
     * Validates the current document against a RelaxNG schema,
     * optionally validates embedded Schematron rules too.
     *
     * \param string $source
     *      Source of the RelaxNG schema to use for validation.
     *
     * \param bool $schematron
     *      (optional) Whether embedded Schematron rules
     *      should be validated too (TRUE) or not (FALSE).
     *      The default is to also do $schematron validation.
     *
     * \retval bool
     *      Returns TRUE if the document validates, FALSE otherwise.
     */
    public function relaxNGValidateSource($source, $schematron=TRUE)
    {
        $success = parent::relaxNGValidateSource($source);
        return $this->_schematronValidation(
            'string',
            $source,
            'RNG',
            $success,
            $schematron
        );
    }

    /**
     * Validates the current document against an XML schema,
     * optionally validates embedded Schematron rules too.
     *
     * \param string $filename
     *      Path to the XML schema to use for validation.
     *
     * \param bool $schematron
     *      (optional) Whether embedded Schematron rules
     *      should be validated too (TRUE) or not (FALSE).
     *      The default is to also do $schematron validation.
     *
     * \retval bool
     *      Returns TRUE if the document validates, FALSE otherwise.
     */
    public function schemaValidate($filename, $schematron=TRUE)
    {
        $success = parent::schemaValidate($filename);
        return $this->_schematronValidation(
            'file',
            $filename,
            'XSD',
            $success,
            $schematron
        );
    }

    /**
     * Validates the current document against an XML schema,
     * optionally validates embedded Schematron rules too.
     *
     * \param string $source
     *      Source of the XML schema to use for validation.
     *
     * \param bool $schematron
     *      (optional) Whether embedded Schematron rules
     *      should be validated too (TRUE) or not (FALSE).
     *      The default is to also do $schematron validation.
     *
     * \retval bool
     *      Returns TRUE if the document validates, FALSE otherwise.
     */
    public function schemaValidateSource($source, $schematron=TRUE)
    {
        $success = parent::schemaValidateSource($source);
        return $this->_schematronValidation(
            'string',
            $source,
            'XSD',
            $success,
            $schematron
        );
    }

    protected function _schematronValidation(
        $source,
        $data,
        $schemaSource,
        $success,
        $schematron
    )
    {
        $xslDir = dirname(dirname(dirname(__FILE__)));
        if (basename($xslDir) == 'trunk')
            $xslDir .= '/data';
        else
            $xslDir .= '/data/pear.erebot.net/Erebot';

        $xslDir     = str_replace('/', DIRECTORY_SEPARATOR, $xslDir);
        $quiet      = !libxml_use_internal_errors(); 
        if (!$quiet) {
            $this->_errors = array_merge($this->_errors, libxml_get_errors());
            libxml_clear_errors();
        }
        if (!$success || !$schematron)
            return $success;

        $schema     = new DomDocument();
        if ($source == 'file')
            $success = $schema->load($data);
        else
            $success = $schema->loadXML($data);
        if (!$quiet) {
            $this->_errors = array_merge($this->_errors, libxml_get_errors());
            libxml_clear_errors();
        }
        if (!$success)
            return FALSE;

        $processor  = new XSLTProcessor();
        $extractor  = new DomDocument();
        $success    = $extractor->load(
            $xslDir . DIRECTORY_SEPARATOR . $schemaSource . "2Schtrn.xsl"
        );
        if (!$quiet) {
            $this->_errors = array_merge($this->_errors, libxml_get_errors());
            libxml_clear_errors();
        }
        if (!$success)
            return FALSE;

        $processor->importStylesheet($extractor);
        $result = $processor->transformToDoc($schema);
        if ($result === FALSE)
            return FALSE;

        $validator  = new DomDocument();
        $success    = $validator->load(
            $xslDir . DIRECTORY_SEPARATOR .
            "schematron-custom.xsl"
        );
        if (!$quiet) {
            $this->_errors = array_merge($this->_errors, libxml_get_errors());
            libxml_clear_errors();
        }
        if (!$success)
            return FALSE;

        $processor->importStylesheet($validator);
        $result = $processor->transformToDoc($result);
        if ($result === FALSE)
            return FALSE;

        $processor = new XSLTProcessor();
        $processor->importStylesheet($result);
        $result = $processor->transformToDoc($this);
        if ($result === FALSE)
            return FALSE;

        $root   = $result->firstChild;
        $valid  = TRUE;
        foreach ($root->childNodes as $child) {
            if ($child->nodeType != XML_ELEMENT_NODE)
                continue;
            if ($child->localName != 'assertionFailure')
                continue;
            $valid = FALSE;

            // If running in quiet mode, don't report errors.
            if ($quiet)
                continue;

            $error = new LibXMLError();
            $error->level   = LIBXML_ERR_ERROR;
            $error->code    = 0;
            $error->column  = 0;
            $error->file    = $this->documentURI;
            $error->line    = 0;
            $error->message = '';
            $error->path    = '';

            foreach ($child->childNodes as $subchild) {
                if ($subchild->nodeType != XML_ELEMENT_NODE)
                    continue;
                if ($subchild->localName == 'description' &&
                    $error->message == '')
                    $error->message = $subchild->textContent;
                else if ($subchild->localName == 'location' &&
                    $error->path == '')
                    $error->path = $subchild->textContent;
            }
            $this->_errors[] = $error;
        }
        return $valid;
    }

    /**
     * Validates the document against its DTD.
     *
     * \retval bool
     *      Returns TRUE if the document validates, FALSE otherwise.
     */
    public function validate()
    {
        $success    = parent::validate();
        $quiet      = !libxml_use_internal_errors(); 
        if (!$quiet) {
            $this->_errors = array_merge($this->_errors, libxml_get_errors());
            libxml_clear_errors();
        }
        return $success;
    }

    /**
     * Returns an array of errors raised during validation.
     *
     * \param array
     *      Array of LibXMLError objets raised during validation.
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * Clears all validation errors.
     */
    public function clearErrors()
    {
        $this->_errors = array();
    }
}
