%name CountdownParser_
%declare_class {class CountdownParser}
%syntax_error { throw new ECountdownSyntaxError(); }
%token_prefix TK_
%include_class {
    private $formulaResult = NULL;
    public function getResult() { return $this->formulaResult; }
}

%left OP_ADD OP_SUB.
%left OP_MUL OP_DIV.

formula ::= expr(e).                        { $this->formulaResult = e; }

expr(res) ::= PAR_OPEN expr(e) PAR_CLOSE.   { res = e; }
expr(res) ::= expr(opd1) OP_ADD expr(opd2). { res = opd1 + opd2; }
expr(res) ::= expr(opd1) OP_SUB expr(opd2). { res = opd1 - opd2; }
expr(res) ::= expr(opd1) OP_MUL expr(opd2). { res = opd1 * opd2; }
expr(res) ::= expr(opd1) OP_DIV expr(opd2). {
    if (!opd2)
        throw new ECountdownDivisionByZero();

    if (opd1 % opd2)
        throw new ECountdownNonIntegralDivision();

    res = opd1 / opd2;
}
expr(res) ::= INTEGER(i).                   { res = i; }

