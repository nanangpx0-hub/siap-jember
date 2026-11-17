# Izinkan akses LAN ke aplikasi SIAP Jember (port 80).
# Klik kanan file ini > "Run with PowerShell", lalu setujui UAC bila diminta.

# Self-elevate bila PowerShell belum berjalan sebagai Administrator.
$principal = New-Object Security.Principal.WindowsPrincipal([Security.Principal.WindowsIdentity]::GetCurrent())
if (-not $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)) {
    Start-Process powershell.exe "-NoProfile -ExecutionPolicy Bypass -File `"$PSCommandPath`"" -Verb RunAs
    exit
}

$name = 'SIAP Jember - HTTP LAN (port 80)'
$existing = Get-NetFirewallRule -DisplayName $name -ErrorAction SilentlyContinue
if ($existing) {
    Remove-NetFirewallRule -DisplayName $name
}

New-NetFirewallRule `
    -DisplayName $name `
    -Description 'Akses LAN ke aplikasi rekrutmen BPS Jember (192.168.10.254)' `
    -Direction Inbound `
    -Protocol TCP `
    -LocalPort 80 `
    -RemoteAddress '192.168.10.0/24' `
    -Action Allow `
    -Profile Any | Out-Null

Write-Host "OK: aturan firewall '$name' aktif untuk subnet 192.168.10.0/24." -ForegroundColor Green
Write-Host "Akses aplikasi: http://192.168.10.254/ atau http://rekrutment.test/" -ForegroundColor Cyan
Read-Host 'Tekan Enter untuk menutup'
