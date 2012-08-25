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
    /// Stores the LibXMLError errors raised during validation.
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

    /**
     * Proceed to the actual Schematron validation.
     *
     * \param string $source
     *      Source of the Schematron rules:
     *      - 'file' when $data refers to a filename.
     *      - 'string' when $data refers to an in-memory string.
     *
     * \param string $data
     *      Schematron data. The interpretation of this parameter
     *      depends on the value of the $source parameter.
     *
     * \param string $schemaSource
     *      The original schema type used to validate the document.
     *      This is "XSD" for XML Schema documents or "RNG" for
     *      RelaxNG schemas.
     *
     * \param bool $success
     *      Whether the original validation process succeeded
     *      (TRUE) or not (FALSE). This is used to abort the
     *      Schematron validation process earlier.
     *
     * \param bool $schematron
     *      Whether a Schematron validation pass should occur
     *      (TRUE) or not (FALSE).
     *
     * \retval bool
     *      Whether the overall validation passed (TRUE)
     *      or not (FALSE).
     *
     * \note
     *      In case validation failed, Erebot_DOM::getErrors()
     *      may be used to retrieve further information on why
     *      it failed.
     */
    protected function _schematronValidation(
        $source,
        $data,
        $schemaSource,
        $success,
        $schematron
    )
    {
        $xslDir = '@data_dir@';
        if ($xslDir == '@'.'data_dir'.'@') {
            $xslDir = dirname(dirname(dirname(__FILE__))) .
                DIRECTORY_SEPARATOR . 'data';
            // Running from PHAR.
            if (!strncmp(__FILE__, 'phar://', 7)) {
                $xslDir .=
                    DIRECTORY_SEPARATOR . 'pear.erebot.net' .
                    DIRECTORY_SEPARATOR . 'Erebot';
            }
        }
        else
            $xslDir .=
                DIRECTORY_SEPARATOR . 'pear.erebot.net' .
                DIRECTORY_SEPARATOR . 'Erebot';

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
            $xslDir . DIRECTORY_SEPARATOR .
            $schemaSource . "2Schtrn.xsl"
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
            $error->message = '';
            $error->file    = $this->documentURI;
            $error->line    = 0;
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
     * \param bool $schematron
     *      (optional) The value of this parameter is currently
     *      unused as there is no way to embed Schematron rules
     *      into a DTD. This parameter is provided only to make
     *      the API uniform accross the different methods of this
     *      class.
     *
     * \retval bool
     *      Returns TRUE if the document validates, FALSE otherwise.
     *
     * \note
     *      This method is the same as the regular DomDocument::validate()
     *      method excepts that it captures errors so they can be later
     *      retrieved with the Erebot_DOM::getErrors() method.
     *
     * \note
     *      There is currently no way to embed Schematron rules
     *      into a Document Type Declaration. Therefore, the
     *      value of the $schematron parameter is always ignored.
     */
    public function validate($schematron=FALSE)
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
     * \retval array
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
