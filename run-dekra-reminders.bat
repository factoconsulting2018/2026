@echo off
setlocal

cd /d "%~dp0"

rem Genera los recordatorios faltantes de Dekra usando la configuracion guardada.
call "%~dp0yii.bat" dekra

endlocal
