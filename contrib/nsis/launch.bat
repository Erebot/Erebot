@echo off

echo Launching Erebot. Press Ctrl+C to stop the bot...
rem The block is necessary to workaround issues in cmd.exe
rem when running with codepage 65001 (UTF-8).
(
    chcp 65001 > NUL
    php.exe -f Erebot.phar
    chcp 850 > NUL
    echo Press Ctrl+C to exit...
    pause > NUL
)

