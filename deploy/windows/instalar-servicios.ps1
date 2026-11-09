param(
    [string] $AppDir = "C:\inetpub\licencias",
    [string] $Php = "C:\php\php.exe",
    [string] $Nssm = "C:\nssm\nssm.exe",
    [string] $ServiceName = "LicenciasWorker",
    [string] $TaskName = "LicenciasScheduler"
)

$ErrorActionPreference = "Stop"

if (-not (Test-Path $Php)) {
    throw "No se encontró PHP en '$Php'. Pasá la ruta correcta con -Php."
}

if (-not (Test-Path $Nssm)) {
    throw "No se encontró NSSM en '$Nssm'. Descargalo de https://nssm.cc/ y pasá la ruta con -Nssm."
}

if (-not (Test-Path (Join-Path $AppDir "artisan"))) {
    throw "No se encontró artisan en '$AppDir'. Pasá la ruta del proyecto con -AppDir."
}

$workerArgs = "artisan queue:work --queue=legajos,default --tries=1 --max-time=3600 --sleep=3"
$logDir = Join-Path $AppDir "storage\logs"

# Worker como servicio de Windows (se reinicia solo).
& $Nssm install $ServiceName $Php $workerArgs
& $Nssm set $ServiceName AppDirectory $AppDir
& $Nssm set $ServiceName Start SERVICE_AUTO_START
& $Nssm set $ServiceName AppStdout (Join-Path $logDir "worker.log")
& $Nssm set $ServiceName AppStderr (Join-Path $logDir "worker-error.log")
& $Nssm set $ServiceName AppRotateFiles 1
& $Nssm set $ServiceName AppRotateBytes 10485760
& $Nssm start $ServiceName

# Planificador cada minuto. /ru SYSTEM permite que corra sin sesión abierta.
$schtasks = "`"$Php`" `"$(Join-Path $AppDir 'artisan')`" schedule:run"
schtasks /create /tn $TaskName /tr $schtasks /sc minute /mo 1 /ru SYSTEM /rl HIGHEST /f

Write-Host ""
Write-Host "Listo."
Write-Host "  Servicio:  $ServiceName"
Write-Host "  Tarea:     $TaskName"
Write-Host "  Logs:      $logDir"
Write-Host ""
Write-Host "Verificá con: Get-Service $ServiceName ; schtasks /query /tn $TaskName"
