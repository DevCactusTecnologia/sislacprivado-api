$ErrorActionPreference = 'Stop'

$projectRoot = Resolve-Path (Join-Path $PSScriptRoot '..')
Set-Location $projectRoot

function Assert-Command([string] $Name) {
    if (-not (Get-Command $Name -ErrorAction SilentlyContinue)) {
        throw "$Name não encontrado no PATH."
    }
}

function Get-DotEnvValue([string] $Name) {
    $match = [regex]::Match((Get-Content '.env' -Raw), "(?m)^$([regex]::Escape($Name))=(.*)$")
    if (-not $match.Success) {
        return ''
    }

    return $match.Groups[1].Value.Trim().Trim('"')
}

Assert-Command 'php'
Assert-Command 'composer'

$phpVersion = (& php -r 'echo PHP_VERSION;').Trim()
if ([version] $phpVersion -lt [version] '8.3.0') {
    throw "PHP >= 8.3 é obrigatório. Versão atual: $phpVersion"
}

if (-not (Test-Path '.env')) {
    Copy-Item '.env.local.example' '.env'
    Write-Host 'Arquivo .env local criado a partir de .env.local.example.'
    Write-Host 'Preencha SUPABASE_URL e SUPABASE_PUBLISHABLE_KEY e execute novamente.'
    exit 2
}

& composer install --no-interaction --prefer-dist
if ($LASTEXITCODE -ne 0) {
    exit $LASTEXITCODE
}

if ([string]::IsNullOrWhiteSpace((Get-DotEnvValue 'APP_KEY'))) {
    & php artisan key:generate --force
    if ($LASTEXITCODE -ne 0) {
        exit $LASTEXITCODE
    }
}

$supabaseUrl = Get-DotEnvValue 'SUPABASE_URL'
$publishableKey = Get-DotEnvValue 'SUPABASE_PUBLISHABLE_KEY'

if (-not $supabaseUrl.StartsWith('https://')) {
    throw 'SUPABASE_URL deve ser preenchida com uma URL HTTPS válida do projeto Supabase.'
}

if (-not $publishableKey.StartsWith('sb_publishable_')) {
    throw 'SUPABASE_PUBLISHABLE_KEY deve usar a chave publishable moderna (sb_publishable_...).'
}

& php artisan config:clear
if ($LASTEXITCODE -ne 0) {
    exit $LASTEXITCODE
}

Write-Host 'SISLAC Private API local: http://127.0.0.1:8000'
& php artisan serve --host=127.0.0.1 --port=8000
exit $LASTEXITCODE
