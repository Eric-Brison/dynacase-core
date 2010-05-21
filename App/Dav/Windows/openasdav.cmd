@echo off
@FOR /F "tokens=1* delims=:" %%a IN ("%~1") DO %windir%\system32\rundll32.exe %windir%\system32\shell32.dll,OpenAs_RunDLL http:%%b