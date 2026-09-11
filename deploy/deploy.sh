#!/usr/bin/env bash
set -euo pipefail

if ! command -v php >/dev/null 2>&1; then
    echo "PHP não encontrado." >&2
    exit 1
fi

if ! command -v composer >/dev/null 2>&1; then
    echo "Composer não encontrado." >&2
    exit 1
fi

php -r 'if (version_compare(PHP_VERSION, "8.3.0", "<")) { fwrite(STDERR, "PHP >= 8.3 é obrigatório.\n"); exit(1); }'

composer install --no-dev --no-interaction --prefer-dist --no-progress --optimize-autoloader

php -r '
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

if (config("app.env") !== "production") {
    fwrite(STDERR, "APP_ENV deve ser production.\n");
    exit(1);
}

if (config("app.debug") !== false) {
    fwrite(STDERR, "APP_DEBUG deve ser false.\n");
    exit(1);
}

if ((string) config("services.supabase.url") === "" || (string) config("services.supabase.publishable_key") === "") {
    fwrite(STDERR, "SUPABASE_URL e SUPABASE_PUBLISHABLE_KEY são obrigatórios.\n");
    exit(1);
}
'

php artisan optimize
