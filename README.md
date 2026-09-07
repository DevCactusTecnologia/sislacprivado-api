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