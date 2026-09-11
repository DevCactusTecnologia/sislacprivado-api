# SISLAC Private API

Backend Laravel API-only do SISLAC Privado.

## Escopo

Este serviço existe somente para operações server-side que exigem segredos, integrações externas, jobs ou privilégio administrativo. O Supabase permanece fonte de verdade para Postgres, Auth, RLS, Storage, Realtime e migrations.

Não é objetivo deste repositório recriar o SISLAC em Laravel, duplicar o schema Supabase, substituir o Supabase Auth ou mover o frontend React para Laravel.

## Base

- Laravel 13
- PHP >= 8.3
- Supabase Auth/Data API/RPC via HTTP
- API JSON, sem Blade/Inertia/Livewire

A fundação inicial contém apenas `/api/health`, validação de usuário Supabase e `/api/me`.

## Teste em localhost no Windows

O fluxo local é separado do deploy de produção. Ele não executa migrations, filas ou banco Laravel e não usa `SUPABASE_SECRET_KEY`.

Pré-requisitos:

- PHP >= 8.3 disponível no `PATH`;
- Composer disponível no `PATH`;
- PowerShell.

Na raiz do projeto, execute:

```powershell
.\local\start.ps1
```

Na primeira execução, o script cria `.env` a partir de `.env.local.example` e encerra para que sejam preenchidas apenas estas variáveis:

```dotenv
SUPABASE_URL=https://SEU-PROJETO.supabase.co
SUPABASE_PUBLISHABLE_KEY=sb_publishable_...
```

Depois execute novamente:

```powershell
.\local\start.ps1
```

O script instala as dependências, gera `APP_KEY` quando necessário, limpa o cache de configuração e inicia a API em:

```text
http://127.0.0.1:8000
```

Em outro PowerShell, valide o básico:

```powershell
.\local\smoke.ps1
```

Resultados esperados:

- `/up` -> HTTP 200;
- `/api/health` -> HTTP 200;
- `/api/me` sem token -> HTTP 401.

Para validar também a autenticação Supabase com um JWT real de usuário:

```powershell
.\local\smoke.ps1 -BearerToken "<JWT_SUPABASE>"
```

Nesse caso, `/api/me` autenticado deve retornar HTTP 200.

Se a política do Windows bloquear scripts PowerShell, use explicitamente:

```powershell
powershell -ExecutionPolicy Bypass -File .\local\start.ps1
```
