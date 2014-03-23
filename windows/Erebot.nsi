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
  !define REPO_BASE       "http://packages.erebot.net"
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

  ; Built-in scripts.
  !include "MultiUser.nsh"  ; Multiuser mode
  !include "MUI2.nsh"       ; Modern UI
  !include "FileFunc.nsh"   ; Compute install size

  ; Custom scripts.
  !include "version.nsh"    ; PHP version comparison
;  !include "StrChr.nsh"     ; Substring index


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
  Page custom prepareModulesPage
  !insertmacro MUI_PAGE_COMPONENTS

  ;Start Menu Folder Page Configuration
  !define MUI_STARTMENUPAGE_REGISTRY_ROOT       "HKCU"
  !define MUI_STARTMENUPAGE_REGISTRY_KEY        "${SW_KEY}"
  !define MUI_STARTMENUPAGE_REGISTRY_VALUENAME  "Start Menu Folder"
  !define MUI_PAGE_CUSTOMFUNCTION_PRE skipIfAlreadyHasMenu
  !insertmacro MUI_PAGE_STARTMENU               Application $StartMenuFolder
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
;    Push $0
;    Call getModuleName
;    Pop $0
;    ${If} $0 != ""
;      Push $0
;      Call dlModule
;    ${EndIf}
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

Function prepareModulesPage
  InitPluginsDir
  Call checkPrerequisites
  Call retrieveModuleList
  Call listModules
FunctionEnd

Function checkPrerequisites
  SetOutPath "$PLUGINSDIR"
  File "check_php.bat"

  ClearErrors
  MessageBox MB_OK "Executing $PLUGINSDIR\check_php.bat"
  Push $0 ; Save $0
  nsExec::ExecToStack '"$PLUGINSDIR\check_php.bat"'
  Pop $0 ; Return code
  ${If} $0 != 0
    Pop $0 ; Output
    MessageBox MB_ICONSTOP|MB_OK "$0"
    Quit
  ${EndIf}
  Pop $0 ; Output
  Pop $0 ; Restore $0
FunctionEnd

Function retrieveModuleList
  SetOutPath "$PLUGINSDIR"
  inetc::get /CAPTION "Erebot" /POPUP "" /RESUME "" \
    "${REPO_BASE}/packages.json" "packages.json" /END
  Pop $0
  SetOutPath "$INSTDIR"
FunctionEnd

Function getVersion
  Pop $0
  nsExec::ExecToStack /OEM 'php.exe -d detect_unicode=Off -f "$0.phar" commit'
  Pop $0
  ${If} $0 != 0
    Pop $0
    Push ''
  ${EndIf}
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
    "${REPO_BASE}/get/Erebot-latest.phar" "Erebot.phar" \
    "${REPO_BASE}/get/Erebot-latest.phar.pubkey" "Erebot.phar.pubkey" \
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


;-------------------------------
; Must be kept at end of file

Function listModules
  ClearErrors
  nsJSON::Set /file "$PLUGINSDIR\packages.json"
  StrCpy $0 1 ; Package iterator
  StrCpy $3 1 ; Sections iterator
  loop:
    nsJSON::Get /key "packages" /index $0 /end
    IfErrors endloop
    Pop $1
    IntOp $0 $0 + 1

    StrCpy $2 "$1" 7 ; Look for "erebot/" prefix
    StrCmp $2 "erebot/" +1 loop

    ; Check whether the current package refers to a module.
    ; Look for "-module" suffix.
    StrCpy $2 "$1" "" -7
    StrCmp $2 "-module" +2 loop

    module:
      ; Remove "-module" suffix.
      StrLen $2 "$1"
      IntOp $2 $2 - 7
      StrCpy $1 "$1" $2

      ; Remove "erebot/" prefix.
      StrCpy $1 "$1" "" 7

      ; Prepare the new section.
      IntOp $2 ${module_1} + $3
      SectionSetText  $2 "$1"
;      SectionSetFlags $2 $1

;      IntOp $0 $0 / 1024
;      SectionSetSize  $4 $0

      ; No more sections available.
      IntOp $3 $3 + 1
      ${If} $3 > ${NB_SECTIONS}
        Goto endloop
      ${EndIf}
    Goto loop
  endloop:
FunctionEnd

