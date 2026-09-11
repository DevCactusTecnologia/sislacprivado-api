param(
    [string] $BaseUrl = 'http://127.0.0.1:8000',
    [string] $BearerToken = ''
)

$ErrorActionPreference = 'Stop'
$BaseUrl = $BaseUrl.TrimEnd('/')

function Get-Status([string] $Url, [hashtable] $Headers = @{}) {
    try {
        $response = Invoke-WebRequest -Uri $Url -Headers $Headers -UseBasicParsing
        return [int] $response.StatusCode
    }
    catch {
        if ($null -ne $_.Exception.Response -and $null -ne $_.Exception.Response.StatusCode) {
            return [int] $_.Exception.Response.StatusCode
        }

        throw
    }
}

function Assert-Status([string] $Path, [int] $Expected, [hashtable] $Headers = @{}) {
    $status = Get-Status "$BaseUrl$Path" $Headers

    if ($status -ne $Expected) {
        throw "${Path}: esperado HTTP $Expected, recebido $status."
    }

    Write-Host "${Path}: HTTP $status"
}

Assert-Status '/up' 200
Assert-Status '/api/health' 200
Assert-Status '/api/me' 401

if ([string]::IsNullOrWhiteSpace($BearerToken)) {
    Write-Host 'JWT não informado. O teste autenticado de /api/me foi ignorado.'
    Write-Host 'Para validá-lo: .\local\smoke.ps1 -BearerToken "<JWT_SUPABASE>"'
    exit 0
}

Assert-Status '/api/me' 200 @{ Authorization = "Bearer $BearerToken" }
