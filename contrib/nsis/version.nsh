; vim: et sw=2

Function sign
  Exch $0
  ${If} $0 < 0
    IntOp $0 0 - 1
  ${ElseIf} $0 > 0
    IntOp $0 0 + 1
  ${Else}
    IntOp $0 0 + 0
  ${EndIf}
  Exch $0
FunctionEnd

!macro sign res n
  Push ${n}
  Call sign
  Pop ${res}
!macroend
!define sign "!insertmacro sign"

Function isdig
  Exch $0
  ${If} $0 == '0'
  ${OrIf} $0 == '1'
  ${OrIf} $0 == '2'
  ${OrIf} $0 == '3'
  ${OrIf} $0 == '4'
  ${OrIf} $0 == '5'
  ${OrIf} $0 == '6'
  ${OrIf} $0 == '7'
  ${OrIf} $0 == '8'
  ${OrIf} $0 == '9'
    IntOp $0 0 + 1
  ${Else}
    IntOp $0 0 + 0
  ${EndIf}
  Exch $0
FunctionEnd

!macro isdig res x
  Push ${x}
  Call isdig
  Pop ${res}
!macroend
!define isdig "!insertmacro isdig"
!define ctype_digit "!insertmacro isdig"

Function isndig
  Exch $0
  ${If} $0 == ''
    IntOp $0 0 + 1
  ${Else}
    ${If} $0 != '.'
    ${AndIf} $0 != '0'
    ${AndIf} $0 != '1'
    ${AndIf} $0 != '2'
    ${AndIf} $0 != '3'
    ${AndIf} $0 != '4'
    ${AndIf} $0 != '5'
    ${AndIf} $0 != '6'
    ${AndIf} $0 != '7'
    ${AndIf} $0 != '8'
    ${AndIf} $0 != '9'
      IntOp $0 0 + 1
    ${Else}
      IntOp $0 0 + 0
    ${EndIf}
  ${EndIf}
  Exch $0
FunctionEnd

!macro isndig res x
  Push ${x}
  Call isndig
  Pop ${res}
!macroend
!define isndig "!insertmacro isndig"

Function isspecialver
  Exch $0
  ${If} $0 == '-'
  ${OrIf} $0 == '_'
  ${OrIf} $0 == '+'
    IntOp $0 0 + 1
  ${Else}
    IntOp $0 0 + 0
  ${EndIf}
  Exch $0
FunctionEnd

!macro isspecialver res x
  Push ${x}
  Call isspecialver
  Pop ${res}
!macroend
!define isspecialver "!insertmacro isspecialver"

Function intval
  ; $0 = res
  ; $1 = n
  ; $2 = base
  ; $3 = sign (-1 or +1)
  ; $4 = $1[0]
  Exch $1
  Exch
  Exch $2
  Push $0
  Push $3
  Push $4
  IntOp $0 0 + 0
  IntOp $3 0 + 1

  StrCpy $4 $1 1
  ${If} $4 == "+"
    StrCpy $1 $1 "" 1
  ${ElseIf} $4 == "-"
    IntOp $3 0 - 1
    StrCpy $1 $1 "" 1
  ${EndIf}

  ${DoWhile} $1 != ""
    StrCpy $4 $1 1
    ${If}   $4 == "0"
    ${OrIf} $4 == "1"
    ${OrIf} $4 == "2"
    ${OrIf} $4 == "3"
    ${OrIf} $4 == "4"
    ${OrIf} $4 == "5"
    ${OrIf} $4 == "6"
    ${OrIf} $4 == "7"
    ${OrIf} $4 == "8"
    ${OrIf} $4 == "9"
      IntOp $0 $0 * $2
      IntOp $0 $0 + $4
      StrCpy $1 $1 "" 1
    ${Else}
      ${Break}
    ${EndIf}
  ${Loop}

  IntOp $1 $0 * $3
  Pop $4
  Pop $3
  Pop $0
  Pop $2
  Exch $1
FunctionEnd

!macro intval res n base
  Push ${base}
  Push ${n}
  Call intval
  Pop ${res}
!macroend
!define intval "!insertmacro intval"

Function ctype_alnum
  ; $0 = x
  Exch $0
  StrCmp $0 "a" isalnum
  StrCmp $0 "b" isalnum
  StrCmp $0 "c" isalnum
  StrCmp $0 "d" isalnum
  StrCmp $0 "e" isalnum
  StrCmp $0 "f" isalnum
  StrCmp $0 "g" isalnum
  StrCmp $0 "h" isalnum
  StrCmp $0 "i" isalnum
  StrCmp $0 "j" isalnum
  StrCmp $0 "k" isalnum
  StrCmp $0 "l" isalnum
  StrCmp $0 "m" isalnum
  StrCmp $0 "n" isalnum
  StrCmp $0 "o" isalnum
  StrCmp $0 "p" isalnum
  StrCmp $0 "q" isalnum
  StrCmp $0 "r" isalnum
  StrCmp $0 "s" isalnum
  StrCmp $0 "t" isalnum
  StrCmp $0 "u" isalnum
  StrCmp $0 "v" isalnum
  StrCmp $0 "w" isalnum
  StrCmp $0 "x" isalnum
  StrCmp $0 "y" isalnum
  StrCmp $0 "z" isalnum
  StrCmp $0 "1" isalnum
  StrCmp $0 "2" isalnum
  StrCmp $0 "3" isalnum
  StrCmp $0 "4" isalnum
  StrCmp $0 "5" isalnum
  StrCmp $0 "6" isalnum
  StrCmp $0 "7" isalnum
  StrCmp $0 "8" isalnum
  StrCmp $0 "9" isalnum
  StrCmp $0 "0" isalnum
    IntOp $0 0 + 0
    Goto leave_function
  isalnum:
    IntOp $0 0 + 1
  leave_function:
    Exch $0
FunctionEnd

!macro ctype_alnum res x
  Push ${x}
  Call ctype_alnum
  Pop ${res}
!macroend
!define ctype_alnum "!insertmacro ctype_alnum"

Function php_canonicalize_version
  ; $0 = version
  ; $1 = len
  ; $2 = p
  ; $3 = q
  ; $4 = lp
  ; $5 = lq
  ; others = temporary variables
  Exch $0
  Push $1
  Push $2
  Push $3
  Push $4
  Push $5
  Push $7
  Push $8
  Push $9

  StrLen $1 "$0"
  IntCmp $1 0 leave_function

  StrCpy $3 "$0" 1
  StrCpy $4 "$0" 1
  IntOp $2 0 + 1

  ${DoWhile} $2 < $1
    StrCpy $5 $3 1 -1
    StrCpy $9 $0 1 $2
    ${isspecialver} $8 $9
    ${If} $8 == 1
      ${If} $5 != '.'
        StrCpy $3 "$3."
      ${Endif}
    ${Else}
      ${isndig} $8 $4
      ${isdig}  $7 $9
      ${If} $8 == 1
      ${AndIf} $7 == 1
        ${If} $5 != '.'
          StrCpy $3 "$3."
        ${Endif}
        StrCpy $3 "$3$9"
      ${Else}
        ${isdig}  $8 $4
        ${isndig} $7 $9
        ${If} $8 == 1
        ${AndIf} $7 == 1
          ${If} $5 != '.'
            StrCpy $3 "$3."
          ${Endif}
          StrCpy $3 "$3$9"
        ${Else}
          ${ctype_alnum} $8 $9
          ${If} $2 > $1
          ${OrIf} $8 == 0
            ${If} $5 != '.'
              StrCpy $3 "$3."
            ${Endif}
          ${Else}
            StrCpy $3 "$3$9"
          ${EndIf}
        ${EndIf}
      ${EndIf}
    ${EndIf}
    StrCpy $4 $9
    IntOp $2 $2 + 1
  ${Loop}
  StrCpy $0 $3

  leave_function:
    Pop $9
    Pop $8
    Pop $7
    Pop $5
    Pop $4
    Pop $3
    Pop $2
    Pop $1
    Exch $0
FunctionEnd

!macro php_canonicalize_version res version
  Push ${version}
  Call php_canonicalize_version
  Pop ${res}
!macroend
!define php_canonicalize_version "!insertmacro php_canonicalize_version"

Function find_special_version_form
  ; $0 = version
  ; $1 = index
  ; $2 = temporary variable
  Exch $0
  Push $1
  Push $2
  IntOp $1 0 - 1

  StrCpy $2 $0 5
  StrCmpS "alpha" $2 0 +3
  IntOp $1 0 + 1
  Goto leave_function

  StrCpy $2 $0 4
  StrCmpS "beta" $2 0 +3
  IntOp $1 0 + 2
  Goto leave_function

  StrCpy $2 $0 3
  StrCmpS "dev" $2 0 +3
  IntOp $1 0 + 0
  Goto leave_function

  StrCpy $2 $0 2
  StrCmpS "pl" $2 0 +3
  IntOp $1 0 + 5
  Goto leave_function

  StrCmpS "RC" $2 +1
  StrCmpS "rc" $2 0 +3
  IntOp $1 0 + 3
  Goto leave_function

  StrCpy $2 $0 1
  StrCmpS "a" $2 0 +3
  IntOp $1 0 + 1
  Goto leave_function

  StrCmpS "b" $2 0 +3
  IntOp $1 0 + 2
  Goto leave_function

  StrCmpS "#" $2 0 +3
  IntOp $1 0 + 4
  Goto leave_function

  StrCmpS "p" $2 0 +2
  IntOp $1 0 + 5

  leave_function:
    IntOp $0 0 + $1
    Pop $2
    Pop $1
    Exch $0
FunctionEnd

!macro find_special_version_form res x
  Push "${x}"
  Call find_special_version_form
  Pop ${res}
!macroend
!define find_special_version_form "!insertmacro find_special_version_form"

Function compare_special_version_forms
  ; $0 = form1 then found1
  ; $1 = form2 then found2
  Exch $0
  Exch
  Exch $1

  ${find_special_version_form} $0 "$0"
  ${find_special_version_form} $1 "$1"

  IntOp $0 $0 - $1
  ${sign} $0 $0

  Pop $1
  Exch $0
FunctionEnd

!macro compare_special_version_forms res form1 form2
  Push "${form2}"
  Push "${form1}"
  Call compare_special_version_forms
  Pop ${res}
!macroend
!define compare_special_version_forms "!insertmacro compare_special_version_forms"

Function IndexOf
Exch $R0
Exch
Exch $R1
Push $R2
Push $R3
 
 StrCpy $R3 $R0
 StrCpy $R0 -1
 IntOp $R0 $R0 + 1
  StrCpy $R2 $R3 1 $R0
  StrCmp $R2 "" +2
  StrCmp $R2 $R1 +2 -3
 
 StrCpy $R0 -1
 
Pop $R3
Pop $R2
Pop $R1
Exch $R0
FunctionEnd
 
!macro IndexOf Var Str Char
Push "${Char}"
Push "${Str}"
 Call IndexOf
Pop "${Var}"
!macroend
!define IndexOf "!insertmacro IndexOf"

Function php_version_compare
  ; $0 = compare
  ; $1 = ver1
  ; $2 = ver2
  ; $3 = len1
  ; $4 = len2
  ; $5 = p1
  ; $6 = p2
  ; $7 = n1
  ; $8 = n2
  ; $r1 = substr($1, $5)
  ; $r2 = substr($2, $6)
  ; $r3 = $1[$5]
  ; $r4 = $2[$6]
  Exch $1
  Exch
  Exch $2
  Push $0
  Push $3
  Push $4
  Push $5
  Push $6
  Push $7
  Push $8
  Push $r1
  Push $r2
  Push $r3
  Push $r4
  Push $r8
  Push $r9

  IntOp $0 0 + 0
  ${If} "$1" == ""
  ${OrIf} "$2" == ""
    ${If} "$1" == ""
    ${AndIf} "$2" == ""
      ;empty
    ${ElseIf} $1 != ""
      IntOp $0 0 + 1
    ${Else}
      IntOp $0 0 - 1
    ${EndIf}
    Goto leave_function
  ${EndIf}

  StrCpy $r3 $1 1
  ${If} $r3 != "#"
    ${php_canonicalize_version} $1 $1
  ${EndIf}

  StrCpy $r4 $2 1
  ${If} $r4 != "#"
    ${php_canonicalize_version} $2 $2
  ${EndIf}

  StrLen $3 $1
  StrLen $4 $2
  IntOp $5 0 + 0
  IntOp $6 0 + 0
  IntOp $7 0 + 0
  IntOp $8 0 + 0

  ${Do}
    ${If} $5 >= $3
    ${OrIf} $6 >= $4
    ${OrIf} $7 < 0
    ${OrIf} $8 < 0
      ${Break}
    ${EndIf}

    StrCpy $r1 $1 "" $5
    ${IndexOf} $7 $r1 "."
    StrCpy $r2 $2 "" $6
    ${IndexOf} $8 $r2 "."
    StrCpy $r3 $1 1 $5
    StrCpy $r4 $2 1 $6

    ${ctype_digit} $r9 $r3
    ${ctype_digit} $r8 $r4


    ${If} $r9 == 1
    ${AndIf} $r8 == 1
      ${intval} $r9 $r1 10
      ${intval} $r8 $r2 10
      IntOp $r9 $r9 - $r8
      ${sign} $0 $r9
    ${ElseIf} $r9 == 0
    ${AndIf} $r8 == 0
      ${compare_special_version_forms} $0 $r1 $r2
    ${ElseIf} $9 == 1
      ${compare_special_version_forms} $0 "#N#" $r2
    ${Else}
      ${compare_special_version_forms} $0 $r1 "#N#"
    ${EndIf}

    ${If} $0 != 0
      ${Break}
    ${EndIf}
    ${If} $7 > -1
      IntOp $5 $5 + $7
      IntOp $5 $5 + 1
    ${EndIf}
    ${If} $8 > -1
      IntOp $6 $6 + $8
      IntOp $6 $6 + 1
    ${EndIf}
  ${Loop}

  ${If} $0 == 0
    ${If} $7 > -1
      StrCpy $r3 $1 1 $5
      ${ctype_digit} $r9 $r3
      ${If} $r9 == 1
        IntOp $0 0 + 1
      ${Else}
        ; We can't use the macro here as it is not yet defined.
        StrCpy $r1 $1 "" $5
        Push "#N#"
        Push $r1
        Call php_version_compare
        Pop $0
      ${EndIf}
    ${ElseIf} $8 > -1
      StrCpy $r4 $2 1 $6
      ${ctype_digit} $r9 $r4
      ${If} $r9 == 1
        IntOp $0 0 - 1
      ${Else}
        ; We can't use the macro here as it is not yet defined.
        StrCpy $r2 $2 "" $6
        Push $r2
        Push "#N#"
        Call php_version_compare
        Pop $0
      ${EndIf}
    ${EndIf}
  ${EndIf}

  leave_function:
    IntOp $1 $0 + 0
    Pop $r9
    Pop $r8
    Pop $r4
    Pop $r3
    Pop $r2
    Pop $r1
    Pop $8
    Pop $7
    Pop $6
    Pop $5
    Pop $4
    Pop $3
    Pop $0
    Pop $2
    Exch $1
FunctionEnd

!macro php_version_compare res ver1 ver2
  Push ${ver2}
  Push ${ver1}
  Call php_version_compare
  Pop ${res}
!macroend
!define php_version_compare "!insertmacro php_version_compare"

