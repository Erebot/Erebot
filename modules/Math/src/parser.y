%name math_
%declare_class {class MathParser}
%syntax_error { throw new EMathSyntaxError(); }
%token_prefix TK_
%include_class {
    private $formulaResult = NULL;
    public function getResult() { return $this->formulaResult; }
}

%left OP_ADD OP_SUB.
%left OP_MUL OP_DIV OP_MOD.
%left OP_POW.

formula ::= exprPar(e).                         { $this->formulaResult = e; }

exprPar(res) ::= PAR_OPEN exprPar(e) PAR_CLOSE.         { res = e; }
exprPar(res) ::= exprPar(opd1) OP_ADD exprPar(opd2).    { res = opd1 + opd2; }
exprPar(res) ::= exprPar(opd1) OP_SUB exprPar(opd2).    { res = opd1 - opd2; }
exprPar(res) ::= exprPar(opd1) OP_MUL exprPar(opd2).    { res = opd1 * opd2; }

exprPar(res) ::= exprPar(opd1) OP_DIV exprPar(opd2).    {
    if (!opd2)
        throw new EMathDivisionByZero();

    res = opd1 / opd2; 
}

exprPar(res) ::= exprPar(opd1) OP_MOD exprPar(opd2).    {
    if (!is_int(opd1) || !is_int(opd2))
        throw new EMathNoModulusOnReals();

    if (!opd2)
        throw new EMathDivisionByZero();

    res = opd1 % opd2;
}

exprPar(res) ::= exprPar(opd1) OP_POW exprPar(opd2).       {
    if (opd2 < 0)
        throw new EMathNegativeExponent();

    /// \FIXME This doesn't make sense... but still :)
    // Should we use gmp to compute big numbers ?...
    if (opd2 > 30)
        throw new EMathExponentTooBig();

    else
        res = pow(opd1, opd2);
}

exprPar(res)    ::= OP_ADD NUMBER(x).   { res = x; }
exprPar(res)    ::= OP_SUB NUMBER(x).   { res = -x; }
exprPar(res)    ::= NUMBER(x).          { res = x; }

