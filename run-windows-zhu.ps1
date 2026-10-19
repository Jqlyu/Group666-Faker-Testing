# Run Zhu's independent six-case PHP test on native Windows.
param([string]$PhpExe = "php")
$ErrorActionPreference = "Stop"
Set-Location -LiteralPath $PSScriptRoot
if (-not (Test-Path 'vendor\autoload.php')) {
    throw "Missing dependencies. Run composer install first."
}
New-Item -ItemType Directory -Force -Path 'evidence\results' | Out-Null
& $PhpExe 'tests\member-windows-zhu\ZhuF17-F22_Corrected.php' 2>&1 |
    Tee-Object -FilePath 'evidence\results\zhu-standalone.log'
exit $LASTEXITCODE
