@echo off
set minmajor=5
set minminor=2
set minpatch=1

for %%F in ("%0") do set dirname=%%~dpF
php.exe -r "echo phpversion().PHP_EOL;" > "%dirname%\phpversion.txt"
if %ERRORLEVEL% neq 0 (
    echo PHP %minmajor%.%minminor%.%minpatch% or later must be installed    >   "%dirname%\phperror.log"
    echo for Erebot to work properly.                                       >>  "%dirname%\phperror.log"
    echo PHP not found
    exit /B 1
)

for /f "tokens=1,2,3* delims=.-" %%a in ('type "%dirname%\phpversion.txt"') do (
    set vmajor=%%a
    set vminor=%%b
    set vpatch=%%c
    set vend=%%d
)

del "%dirname%\phperror.log" 2>NUL
if %vmajor% equ %minmajor% goto supported
if %vmajor% equ %minmajor% if %vminor% gtr %minminor% goto supported
if %vmajor% equ %minmajor% if %vminor% equ %minminor% if %vpatch% geq %minpatch% goto supported

echo Unsupported PHP version
echo PHP %minmajor%.%minminor%.%minpatch% or later must be installed    >   "%dirname%\phperror.log"
echo for Erebot to work properly.                                       >>  "%dirname%\phperror.log"
exit /B 1

:supported

set haserrors=0
setlocal ENABLEDELAYEDEXPANSION
for %%a in (DOM iconv intl libxml Phar Reflection SimpleXML sockets SPL) do (
    echo.| set /p =Checking for presence of PECL extension "%%a" ... 

    set found=0
    for /f %%b in ('php.exe -m') do if /I "%%a" == "%%b" set found=1

    if !found! equ 0 (
        set haserrors=1
        echo NOT FOUND
        echo PECL extension "%%a" not found. >>  "%dirname%\phperror.log"
    ) else (
        echo ok
    )
)
if !haserrors! equ 1 (
    echo. >> "%dirname%\phperror.log"
    echo Please install and enable the extensions above in your php.ini file first. >> "%dirname%\phperror.log"
    echo Once those errors have been corrected, you may run the installer again. >> "%dirname%\phperror.log"
    exit /B 1
)
exit /B 0
endlocal

