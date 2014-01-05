; vim: et sw=2

;--------------------------------
;Variables

  Var StartMenuFolder


;--------------------------------
;Constants

  !define EREBOT_VERSION  "$%EREBOT_VERSION%"
  !define EREBOT_COPY     "(c) 2012 - Francois Poirotte"
  !define EREBOT_DESC     "A modular IRC bot written in PHP"
  !define UNINST_KEY      "Software\Microsoft\Windows\CurrentVersion\Uninstall\Erebot"
  !define SW_KEY          "Software\Erebot"
  !define PEAR_BASE       "http://pear.erebot.net"
  !define INST_FILE       "Erebot.exe"
  !define URL_HOMEPAGE    "http://www.erebot.net/"
  !define URL_SUPPORT     "https://github.com/Erebot/Erebot/issues"
  !define URL_DOC         "http://erebot.github.com/Erebot/"
  !define NB_SECTIONS     0


;--------------------------------
;Includes

  ; Pre-defines for MultiUser.
  !define MULTIUSER_INSTALLMODE_COMMANDLINE
  !define MULTIUSER_MUI
  !define MULTIUSER_EXECUTIONLEVEL                          "Highest"
  !define MULTIUSER_INSTALLMODE_INSTDIR                     "Erebot"
  !define MULTIUSER_INSTALLMODE_INSTDIR_REGISTRY_KEY        "${SW_KEY}"
  !define MULTIUSER_INSTALLMODE_INSTDIR_REGISTRY_VALUENAME  ""

  !include "MultiUser.nsh"  ; Multiuser mode
  !include "MUI2.nsh"       ; Modern UI
  !include "FileFunc.nsh"   ; Compute install size
  !include "XML.nsh"        ; XML manipulation
  !include "version.nsh"    ; PHP version comparison


;--------------------------------
;General

  ;Name and file
  Name              "Erebot"
  OutFile           "../build/${INST_FILE}"
  XPStyle           on
  ShowInstDetails   show
  ShowUnInstDetails show

  ;Request an Unicode installer
  ;Requires NSIS >= 2.50 (which is not available yet).
;  TargetMinimalOS 5.0

  ; Software information
  VIAddVersionKey ProductName     "Erebot"
  VIAddVersionKey ProductVersion  "${EREBOT_VERSION}" 
  VIAddVersionKey FileVersion     "${EREBOT_VERSION}" 
  VIAddVersionKey LegalCopyright  "${EREBOT_COPY}"
  VIAddVersionKey Comments        "${EREBOT_DESC}" 
  VIAddVersionKey FileDescription "${EREBOT_DESC}" 
  VIProductVersion                "${EREBOT_VERSION}" 


;--------------------------------
;Interface Settings

  !define MUI_ICON                        "Erebot.ico"
  !define MUI_FINISHPAGE_NOAUTOCLOSE
  !define MUI_UNFINISHPAGE_NOAUTOCLOSE
  !define MUI_ABORTWARNING
  !define MUI_UNABORTWARNING
  !define MUI_PAGE_HEADER_TEXT            "Erebot"
  !define MUI_PAGE_HEADER_SUBTEXT         "$(ErebotDescription)"
  !define MUI_FINISHPAGE_SHOWREADME       "${URL_DOC}"
  !define MUI_FINISHPAGE_SHOWREADME_NOTCHECKED
  !define MUI_LANGDLL_ALWAYSSHOW


;--------------------------------
;Pages

  ;Install pages
  !insertmacro MUI_PAGE_WELCOME
  !insertmacro MULTIUSER_PAGE_INSTALLMODE
  !insertmacro MUI_PAGE_LICENSE "../LICENSE"
  !define MUI_PAGE_CUSTOMFUNCTION_PRE skipIfAlreadyInstalled
  !insertmacro MUI_PAGE_DIRECTORY
  Page custom retrieveModuleList
  !insertmacro MUI_PAGE_COMPONENTS
  ;Start Menu Folder Page Configuration
  !define MUI_STARTMENUPAGE_REGISTRY_ROOT       "HKCU"
  !define MUI_STARTMENUPAGE_REGISTRY_KEY        "${SW_KEY}"
  !define MUI_STARTMENUPAGE_REGISTRY_VALUENAME  "Start Menu Folder"
  !define MUI_PAGE_CUSTOMFUNCTION_PRE skipIfAlreadyHasMenu
  !insertmacro MUI_PAGE_STARTMENU               Application $StartMenuFolder
  !define MUI_PAGE_CUSTOMFUNCTION_PRE checkPrerequisites
  !insertmacro MUI_PAGE_INSTFILES
  !insertmacro MUI_PAGE_FINISH

  ;Uninstall pages
  !insertmacro MUI_UNPAGE_WELCOME
  !insertmacro MUI_UNPAGE_CONFIRM
  !insertmacro MUI_UNPAGE_INSTFILES
  !insertmacro MUI_UNPAGE_FINISH


;--------------------------------
;Languages

  !include "i18n/English.nsh"
  !include "i18n/French.nsh"
  !insertmacro MUI_RESERVEFILE_LANGDLL


;--------------------------------
;Macros

!macro FocusProgram
  BringToFront
  ; Check if already running
  ; If so don't open another but bring to front
  System::Call "kernel32::CreateMutexW(i 0, i 0, t '$(^Name)') i .r0 ?e"
  Pop $0
  StrCmp $0 0 launch
   StrLen $0 "$(^Name)"
   IntOp $0 $0 + 1
  loop:
    FindWindow $1 '#32770' '' 0 $1
    IntCmp $1 0 +5
    System::Call "user32::GetWindowText(i r1, t .r2, i r0) i."
    StrCmp $2 "$(^Name)" 0 loop
    System::Call "user32::ShowWindow(i r1,i 9) i."         ; If minimized then restore
    System::Call "user32::SetForegroundWindow(i r1) i."    ; Bring to front
    Abort
  launch:
!macroend

!macro AddModule
  Section "" "module_${NB_SECTIONS}"
    SectionIn 1
    SectionGetText "${module_${NB_SECTIONS}}" $0
    Push $0
    Call getModuleName
    Pop $0
    ${If} $0 != ""
      Push $0
      Call dlModule
    ${EndIf}
  SectionEnd
  !define OLD_NB_SECTIONS ${NB_SECTIONS}
  !undef NB_SECTIONS
  !define /math NB_SECTIONS ${OLD_NB_SECTIONS} + 1
  !undef OLD_NB_SECTIONS
!macroend

!macro Add10Modules
  !insertmacro AddModule
  !insertmacro AddModule
  !insertmacro AddModule
  !insertmacro AddModule
  !insertmacro AddModule
  !insertmacro AddModule
  !insertmacro AddModule
  !insertmacro AddModule
  !insertmacro AddModule
  !insertmacro AddModule
!macroend


;--------------------------------
;Functions

Function .onInit
  !insertmacro MULTIUSER_INIT
  !insertmacro FocusProgram
  !insertmacro MUI_LANGDLL_DISPLAY
FunctionEnd

Function un.onInit
  !insertmacro MULTIUSER_UNINIT
  !insertmacro FocusProgram
  !insertmacro MUI_UNGETLANGUAGE
FunctionEnd

Function getVersion ; (string component)
  Pop $2
  ${xml::LoadFile} "$PLUGINSDIR\summary.xml" $0
  ${xml::RootElement} $1 $0
  ${xml::XPathNode} "/packages/p/n[text()='$2']" $0
  ${xml::Parent} $1 $0
  ${xml::FirstChildElement} "v" $1 $0
  ${xml::GetText} $0 $1
  ${xml::Unload}
  Push $0
FunctionEnd

Function dlModule
  Pop $0
  Push $0 ; Save module name (no prefix)

  DetailPrint "Downloading Erebot_Module_$0..."
  inetc::get /CAPTION "Erebot_Module_$0" /POPUP "" /RESUME "" \
    "${PEAR_BASE}/get/Erebot_Module_$0-latest.phar" \
    "Erebot_Module_$0-latest.phar" \
    "${PEAR_BASE}/get/Erebot_Module_$0-latest.phar.pubkey" \
    "Erebot_Module_$0-latest.phar.pubkey" \
    /END
  Pop $0 # return value = exit code, "OK" if OK

  Pop $0  ; Restore module name (no prefix)
  Push $0
  Push "Erebot_Module_$0"
  Call getVersion
  Pop $0  ; Version
  Pop $1  ; Module name (no prefix)
  WriteRegStr HKCU "${SW_KEY}\versions" "Erebot_Module_$1" "$0"
FunctionEnd

Function retrieveModuleList
  InitPluginsDir
  SetOutPath "$PLUGINSDIR"
  inetc::get /CAPTION "Erebot" /POPUP "" /RESUME "" \
    "${PEAR_BASE}/summary.xml" "summary.xml" /END
  SetOutPath "$INSTDIR"
  Call listModules
FunctionEnd

Function skipIfAlreadyInstalled
  ; @FIXME: $INSTDIR lacks the colon when read back.
  ; Apparently, this was made by design ($INSTDIR is
  ; validated and invalid characters get stripped).
;  ReadRegStr $0 HKCU "${UNINST_KEY}" "InstallLocation"
;  ClearErrors
;  ${If} "$0" != ""
;    StrCpy $INSTDIR "$0"
;    Abort
;  ${EndIf}
FunctionEnd

Function skipIfAlreadyHasMenu
  ReadRegStr $0 HKCU "${SW_KEY}" "Start Menu Folder"
  ClearErrors
  ${If} "$0" != ""
    StrCpy $StartMenuFolder "$0"
    Abort
  ${EndIf}
FunctionEnd

Function checkPrerequisites
  SetOutPath "$PLUGINSDIR"
  File "check_php.bat"

  ClearErrors
  ExecWait 'cmd.exe /C "$PLUGINSDIR\check_php.bat"'
  IfErrors 0 noerror
    ClearErrors
    MessageBox MB_ICONSTOP|MB_OK "Could not check prerequisites"
    Quit
  noerror:
    IfFileExists "$PLUGINSDIR\phperror.log" 0 noerrlog
      FileOpen  $0 "$PLUGINSDIR\phperror.log" r
      StrCpy $1 ""
      ${Do}
        FileRead  $0 $2
        StrCpy $1 "$1$2"
      ${LoopUntil} "$2" == ""
      FileClose $0
      MessageBox MB_ICONSTOP|MB_OK "$1"
      Quit
  noerrlog:
    SetOutPath "$INSTDIR"
FunctionEnd

!define StrChr "!insertmacro StrChr"
 
!macro StrChr ResultVar String SubString StartPoint
  Push "${String}"
  Push "${SubString}"
  Push "${StartPoint}"
  Call StrChr
  Pop "${ResultVar}"
!macroend
 
Function StrChr
  ;Get input from user
  Exch $R0
  Exch
  Exch $R1
  Exch 2
  Exch $R2
  Push $R3
  Push $R4
  Push $R5
  Push $R6
 
  ;Get "String" and "SubString" length
  StrLen $R3 $R1
  StrLen $R4 $R2
  ;Start "StartCharPos" counter
  StrCpy $R5 0
 
  ;Loop until "SubString" is found or "String" reaches its end
  ${Do}
    ;Remove everything before and after the searched part ("TempStr")
    StrCpy $R6 $R2 $R3 $R5
 
    ;Compare "TempStr" with "SubString"
    ${If} $R6 == $R1
      ${If} $R0 == `<`
        IntOp $R6 $R3 + $R5
        IntOp $R0 $R4 - $R6
      ${Else}
        StrCpy $R0 $R5
      ${EndIf}
      ${ExitDo}
    ${EndIf}
    ;If not "SubString", this could be "String"'s end
    ${If} $R5 >= $R4
      StrCpy $R0 ``
      ${ExitDo}
    ${EndIf}
    ;If not, continue the loop
    IntOp $R5 $R5 + 1
  ${Loop}
 
  ;Return output to user
  Pop $R6
  Pop $R5
  Pop $R4
  Pop $R3
  Pop $R2
  Exch
  Pop $R1
  Exch $R0
FunctionEnd

Function getModuleName
  Pop $0
  Push $0
  ${StrChr} $1 $0 " " ">"
  Pop $0
  StrCpy $0 "$0" $1
  Push $0
FunctionEnd


;--------------------------------
;Installer Sections

InstType "$(FullInstall)"
;InstType /COMPONENTSONLYONCUSTOM

Section "Erebot" section_Erebot
  SectionIn 1 RO

  ;Create uninstaller
  Delete            "$INSTDIR\uninstall.exe"
  WriteUninstaller  "$INSTDIR\uninstall.exe"

  File "${MUI_ICON}"
  File "launch.bat"
  File "Erebot.xml"
  File "../data/defaults.xml"

  !insertmacro MUI_STARTMENU_WRITE_BEGIN Application
    ;Create shortcuts
    CreateDirectory "$SMPROGRAMS\$StartMenuFolder"
    CreateShortCut  "$SMPROGRAMS\$StartMenuFolder\Start Erebot.lnk" \
                    "$INSTDIR\launch.bat" "" "$INSTDIR\Erebot.ico" 0 \
                    SW_SHOWNORMAL "" "$(ErebotDescription)"
    nsisStartMenu::RegenerateFolder
    CreateShortCut  "$SMPROGRAMS\$StartMenuFolder\Online Documentation.lnk" \
                    "${URL_DOC}" "" "%SystemRoot%\system32\SHELL32.dll" 23 \
                    SW_SHOWNORMAL "" "Help"
    CreateShortCut  "$SMPROGRAMS\$StartMenuFolder\Online Documentation.lnk" \
                    "${URL_DOC}" "" "%SystemRoot%\system32\SHELL32.dll" 23 \
                    SW_SHOWNORMAL "" "Help"
    CreateShortCut  "$SMPROGRAMS\$StartMenuFolder\Uninstall.lnk" \
                    "$INSTDIR\uninstall.exe"
  !insertmacro MUI_STARTMENU_WRITE_END

  WriteRegStr HKCU "${UNINST_KEY}" "DisplayName"           "Erebot"
  WriteRegStr HKCU "${UNINST_KEY}" "UninstallString"       "$\"$INSTDIR\uninstall.exe$\" /$MultiUser.InstallMode"
  WriteRegStr HKCU "${UNINST_KEY}" "QuietUninstallString"  "$\"$INSTDIR\uninstall.exe$\" /$MultiUser.InstallMode /S"
  WriteRegStr HKCU "${UNINST_KEY}" "InstallLocation"       "$\"$INSTDIR$\""
  WriteRegStr HKCU "${UNINST_KEY}" "ModifyPath"            "$\"$EXEDIR\${INST_FILE}$\""
  WriteRegStr HKCU "${UNINST_KEY}" "Readme"                "${URL_DOC}"
  WriteRegStr HKCU "${UNINST_KEY}" "HelpLink"              "${URL_SUPPORT}"
  WriteRegStr HKCU "${UNINST_KEY}" "URLInfoAbout"          "${URL_HOMEPAGE}"
  WriteRegStr HKCU "${UNINST_KEY}" "DisplayVersion"        "${EREBOT_VERSION}"

  DetailPrint "Downloading Erebot..."
  inetc::get /CAPTION "Erebot" /POPUP "" /RESUME "" \
    "${PEAR_BASE}/get/Erebot-latest.phar" "Erebot.phar" \
    "${PEAR_BASE}/get/Erebot-latest.phar.pubkey" "Erebot.phar.pubkey" \
    /END
  Push "Erebot"
  Call getVersion
  Pop $0
  WriteRegStr HKCU "${SW_KEY}\versions" "Erebot" "$0"

  SetOutPath "$INSTDIR\modules"
SectionEnd

SectionGroup /e "Additional Modules" section_Modules
  !insertmacro Add10Modules
  !insertmacro Add10Modules
  !insertmacro Add10Modules
  !insertmacro Add10Modules
  !insertmacro Add10Modules
SectionGroupEnd

Section "-Finalization"
  SectionIn 1 RO
  ;Write info about installation size to the registry.
  ${GetSize} "$INSTDIR" "/S=0K" $0 $1 $2
  IntFmt $0 "0x%08X" $0
  WriteRegDWORD HKCU "${UNINST_KEY}" "EstimatedSize" "$0"
SectionEnd


;--------------------------------
;Uninstaller Section

Section "Uninstall"
  Delete "$INSTDIR\uninstall.exe"
  Delete "$INSTDIR\Erebot.ico"
  Delete "$INSTDIR\launch.bat"
  Delete "$INSTDIR\Erebot.phar"
  Delete "$INSTDIR\Erebot.phar.pubkey"
  Delete "$INSTDIR\modules\*.phar"
  Delete "$INSTDIR\modules\*.phar.pubkey"
  Delete "$INSTDIR\Erebot.xml"
  Delete "$INSTDIR\defaults.xml"
  RMDir /r /REBOOTOK "$INSTDIR\conf.d"
  RMDir /REBOOTOK "$INSTDIR\modules"
  RMDir /REBOOTOK "$INSTDIR"

  !insertmacro MUI_STARTMENU_GETFOLDER Application $StartMenuFolder
  Delete "$SMPROGRAMS\$StartMenuFolder\Start Erebot.lnk"
  Delete "$SMPROGRAMS\$StartMenuFolder\Online Documentation.lnk"
  Delete "$SMPROGRAMS\$StartMenuFolder\Uninstall.lnk"
  RMDir /REBOOTOK "$SMPROGRAMS\$StartMenuFolder"

  DeleteRegKey HKCU "${MULTIUSER_INSTALLMODE_INSTDIR_REGISTRY_KEY}"
  DeleteRegKey HKCU "${UNINST_KEY}"
SectionEnd


;--------------------------------
;Other functions that must stay
;at the end of this file
Function listModules
  ; $0 = component name
  ; $1 = component prefix
  ; $2 = modules counter
  ; $3 = required modules counter
  Push $0
  Push $1
  Push $2
  Push $3
  Push $4
  Push $5
  Push $6
  ${xml::LoadFile} "$PLUGINSDIR\summary.xml" $0
  ${xml::RootElement} $1 $0
  ${xml::XPathNode} "/packages/p[1]/n[1]" $0
  IntOp $2 0 + 0
  IntOp $3 0 + 0
  loop:
    ${xml::GetText} $0 $1

    StrCpy $1 "$0" 14 ; Look for "Erebot_Module_" prefix
    ${If} "$1" == "Erebot_Module_"
      ReadRegStr $6 HKCU "${SW_KEY}\versions" "$0"
      ClearErrors

      ; Required modules are handled in a special way.
      ${If} "$0" == "Erebot_Module_AutoConnect"
      ${OrIf} "$0" == "Erebot_Module_IrcConnector"
      ${OrIf} "$0" == "Erebot_Module_PingReply"
        IntOp $1 ${SF_SELECTED} | ${SF_RO}
        IntOp $3 $3 + 1
      ${EndIf}

      ${xml::NextSiblingElement} "v" $4 $5
      ${xml::GetText} $4 $5
      StrCpy $0 "$0 ($4)" "" 14 ; remove the prefix and add version info

      ${If} "$6" != ""
        IntOp $1 $1 | ${SF_SELECTED}
        ${php_version_compare} $6 $4 $6
        ${If} $6 > 0
          IntOp $1 $1 | ${SF_BOLD}
        ${EndIf}
      ${EndIf}

      IntOp $4 ${module_1} + $2
      SectionSetText  $4 "$0"
      SectionSetFlags $4 $1

      ${xml::NextSiblingElement} "s" $5 $6
      ${xml::GetText} $0 $1
      IntOp $0 $0 / 1024
      SectionSetSize  $4 $0

      IntOp $2 $2 + 1
      ; There are more module than sections.
      ${If} $2 > ${NB_SECTIONS}
        Goto endloop
      ${EndIf}

    ${ElseIf} "$0" == "Erebot"
      IntOp $1 ${SF_SELECTED} | ${SF_RO}
      ReadRegStr $6 HKCU "${SW_KEY}\versions" "$0"
      ClearErrors

      ${xml::NextSiblingElement} "v" $4 $5
      ${xml::GetText} $4 $5
      StrCpy $0 "$0 ($4)"

      ${If} "$6" != ""
        ${php_version_compare} $6 $4 $6
        ${If} $6 > 0
          IntOp $1 $1 | ${SF_BOLD}
        ${EndIf}
      ${EndIf}

      SectionSetText ${section_Erebot} "$0"
      SectionSetFlags ${section_Erebot} $1

      ${xml::NextSiblingElement} "s" $5 $6
      ${xml::GetText} $0 $1
      IntOp $0 $0 / 1024
      SectionSetSize  ${section_Erebot} $0
    ${EndIf}

    ${xml::Parent} $1 $0
    ${xml::NextSiblingElement} "p" $1 $0
    ${If} $0 != 0
      Goto endloop
    ${EndIf}

    ${xml::FirstChildElement} "n" $1 $0
    ${If} $0 == 0
      Goto loop
    ${EndIf}

  endloop:
    ${xml::Unload}
    ${If} $3 < 3
      MessageBox MB_ICONSTOP|MB_OK "Could not find required modules"
      SetErrors
      Quit
    ${EndIf}

    Pop $6
    Pop $5
    Pop $4
    Pop $3
    Pop $2
    Pop $1
    Pop $0
FunctionEnd

