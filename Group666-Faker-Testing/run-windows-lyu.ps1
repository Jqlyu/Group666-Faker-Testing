# Run Lyu's native Windows F13-F16 and N01-N02 tests.
# Requirements: Windows 11, PHP 7.4.x, composer install completed.
param([string]$PhpExe = "php")
$ErrorActionPreference = "Stop"
Set-Location -LiteralPath $PSScriptRoot
if ([Environment]::OSVersion.Platform -ne [PlatformID]::Win32NT) {
    throw "Use native Windows PowerShell, not WSL."
}
$version = & $PhpExe -r 'echo PHP_VERSION;'
if ($LASTEXITCODE -ne 0 -or $version -notmatch '^7\.4\.') {
    throw "PHP 7.4.x is required. Found $version"
}
$runner = Join-Path $PSScriptRoot 'vendor\phpunit\phpunit\phpunit'
if (-not (Test-Path $runner)) {
    throw "Missing dependencies. Run composer install first."
}
New-Item -ItemType Directory -Force -Path 'evidence\results' | Out-Null
Write-Host "Faker v1.9.2 | Jiaqing Lyu | Windows | PHP $version"
& $PhpExe $runner --configuration phpunit.xml --testsuite WindowsLyu --testdox --log-junit evidence\results\lyu-junit.xml 2>&1 |
    Tee-Object -FilePath evidence\results\lyu-phpunit.log
$exitCode = $LASTEXITCODE
if ($exitCode -ne 0) {
    Write-Host "Test execution failed. Inspect evidence/results/lyu-phpunit.log" -ForegroundColor Red
}
exit $exitCode
