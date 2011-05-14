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

class   Erebot_DOM
extends DomDocument
{
    protected $_errors;

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
        if (basename(dirname(dirname(dirname(__FILE__)))) == 'trunk')
            $xslDir = 'data';
        else
            $xslDir = 'data/pear.erebot.net/Erebot';

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

    public function getErrors()
    {
        return $this->_errors;
    }

    public function clearErrors()
    {
        $this->_errors = array();
    }
}
