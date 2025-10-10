@echo off
echo ============================================
echo    XAMPP MySQL Fix Script
echo ============================================
echo.

echo 1. Checking if MySQL process is running...
tasklist | findstr mysqld
if %errorlevel% == 0 (
    echo MySQL process found. Killing it...
    taskkill /f /im mysqld.exe
    timeout /t 3
) else (
    echo No MySQL process running.
)

echo.
echo 2. Checking port 3306...
netstat -ano | findstr :3306
if %errorlevel% == 0 (
    echo Port 3306 is in use. This might cause issues.
    echo Please check which process is using it.
) else (
    echo Port 3306 is available.
)

echo.
echo 3. Instructions to fix MySQL in XAMPP:
echo.
echo A. Close XAMPP completely
echo B. Go to your XAMPP installation folder (usually C:\xampp)
echo C. Navigate to mysql\data folder
echo D. Find and delete these files if they exist:
echo    - ib_logfile0
echo    - ib_logfile1
echo    - ibdata1 (BACKUP this file first!)
echo.
echo E. Restart XAMPP as Administrator
echo F. Start MySQL service
echo.
echo 4. Alternative: Try changing MySQL port
echo    - In XAMPP Control Panel, click Config next to MySQL
echo    - Change port from 3306 to 3307
echo    - Update your .env file to use port 3307
echo.

echo ============================================
echo Press any key to continue...
pause > nul
