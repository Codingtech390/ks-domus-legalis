[CmdletBinding()]
param(
    [Parameter(Mandatory = $true)]
    [ValidateSet('create', 'reset-password')]
    [string] $Mode,

    [Parameter(Mandatory = $true)]
    [string] $Email,

    [string] $Name,

    [string] $PhpPath = 'C:\xampp\php\php.exe'
)

$phpScript = Join-Path $PSScriptRoot 'manage-admin.php'

if (-not (Test-Path -LiteralPath $PhpPath -PathType Leaf)) {
    throw "PHP executable not found at '$PhpPath'. Pass the XAMPP php.exe path with -PhpPath."
}

if ($Mode -eq 'create' -and [string]::IsNullOrWhiteSpace($Name)) {
    throw 'Provide -Name when creating an administrator.'
}

$securePassword = Read-Host 'Enter the new administrator password (minimum 16 characters)' -AsSecureString
$passwordPointer = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($securePassword)
$plainPassword = $null

try {
    $plainPassword = [Runtime.InteropServices.Marshal]::PtrToStringBSTR($passwordPointer)
    $OutputEncoding = [System.Text.UTF8Encoding]::new($false)

    $arguments = @($phpScript, $Mode, $Email)
    if ($Mode -eq 'create') {
        $arguments += $Name
    }

    $plainPassword | & $PhpPath @arguments
    if ($LASTEXITCODE -ne 0) {
        throw "Administrator account command failed with exit code $LASTEXITCODE."
    }
}
finally {
    if ($passwordPointer -ne [IntPtr]::Zero) {
        [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($passwordPointer)
    }
    $plainPassword = $null
    if ($securePassword) {
        $securePassword.Dispose()
    }
}
