%name Erebot_Styling_Parser_
%declare_class {class Erebot_Styling_Parser}
%syntax_error { throw new Erebot_InvalidValueException(); }
%token_prefix TK_
%include_class {
    private $_tplVariables;
    private $_result = NULL;
    public function getResult() { return $this->_result; }

    public function __construct($vars)
    {
        $this->_tplVariables = $vars;
    }

    private function _extractNumber($var)
    {
        if (is_int($var) || is_float($var))
            return $var;

        if ($var instanceof Erebot_Interface_Styling_Integer)
            return $var->getValue();

        if ($var instanceof Erebot_Interface_Styling_Float)
            return $var->getValue();

        return NULL;
    }
}

%left OP_ADD OP_SUB.
%left OP_COUNT.

result ::= expr(e).                         { $this->_result = e; }

expr(res) ::= PAR_OPEN expr(e) PAR_CLOSE.   { res = e; }

expr(res) ::= expr(opd1) OP_ADD expr(opd2). {
    if (is_array(opd1) && is_array(opd2)) {
        res = array_merge(opd1, opd2);
        return;
    }

    $val1 = $this->_extractNumber(opd1);
    $val2 = $this->_extractNumber(opd2);

    if ($val1 === NULL || $val2 === NULL) {
        throw new Erebot_NotImplementedException(
            'Addition between types "'.gettype(opd1).'" '.
            'and "'.gettype(opd2).'" not supported'
        );
    }

    res = $val1 + $val2;
}

expr(res) ::= expr(opd1) OP_SUB expr(opd2). {
    $val1 = $this->_extractNumber(opd1);
    $val2 = $this->_extractNumber(opd2);

    if ($val1 === NULL || $val2 === NULL) {
        throw new Erebot_NotImplementedException(
            'Subtraction between types "'.gettype(opd1).'" '.
            'and "'.gettype(opd2).'" not supported'
        );
    }

    res = $val1 - $val2;
}

expr(res) ::= OP_COUNT expr(opd).       {
    res = count(opd);
}

expr(res) ::= VARIABLE(opd).                {
    if (!isset($this->_tplVariables[opd]))
        throw new Erebot_NotFoundException('No such variable: '.opd);

    res = $this->_tplVariables[opd];
}

expr(res) ::= OP_ADD NUMBER(x). { res = x; }
expr(res) ::= OP_SUB NUMBER(x). { res = -x; }
expr(res) ::= NUMBER(i).        { res = i; }

