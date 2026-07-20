# Changelog

Todas as alterações notáveis neste projeto serão documentadas neste arquivo.
O formato é baseado em [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), e este projeto segue o [Versionamento Semântico](https://semver.org/lang/pt-BR/) (SemVer).

## [2.0.0] - 2026-07-20

### Changed
- **`syncAddressBilling()` e `syncAddressDelivery()` passam a fazer DIFF** em vez de delete-all + recreate. Antes, cada sync apagava (soft-delete) TODOS os endereços do tipo e recriava — acumulando linhas mortas na tabela (churn) e disparando N× o observer. Agora atualiza os existentes por `id`, cria os novos e remove só os ausentes do payload.

### Fixed
- **`AddressObserver` deixou de gastar 1 query por escrita** validando o `user_id` contra a tabela `users` hardcoded. Em apps multi-tenant (usuário em `authentications`, não `users`) o check sempre falhava — `user_id` do histórico ficava sempre null E gastava a query. Agora a tabela vem de `config('address.history.user_table')` (default `'users'`); use `null` para pular a validação (zero query) ou aponte para a tabela real do usuário. Novo arquivo de config publicável (`config/address.php`).

## [1.8.2] - 2026-07-19

### Performance
- **Removidos 8 índices ociosos da tabela `addresses`** (migration `drop_unused_indexes_from_addresses`). O planner do PostgreSQL avaliava todos os índices ao planejar cada query: com 8 sem uso (`idx_scan = 0`), o **planejamento** de uma busca de endereço custava ~11ms (220 buffers de catálogo) enquanto a **execução** leva 0.06ms — os 14-26ms observados por request eram planning, não busca. Mantidos apenas a PK e o índice composto `(address_type, address_id, type, is_default)`, o único usado no caminho quente. O `(address_type, address_id)` (uuidMorphs) era prefixo redundante do composto. Drop via `CONCURRENTLY` (não trava a tabela). `down()` recria os índices.
  > Se a app passar a filtrar por `state`/`city`/`zip_code`/`usage_count` etc. em produção, recrie o índice específico.

## [1.8.1] - 2026-06-01

### Fixed
- Corrigido `syncAddressBilling()` e `syncAddressDelivery()` que lançavam erro fatal: faltava o `use` de `AddressPayloadResolver` e o resolver só aceitava `Request`
- `AddressPayloadResolver::single()` e `::multiple()` agora aceitam tanto `Request` quanto `array`
- Garantido que `type` (BILLING/DELIVERY) e os campos morph não sejam sobrescritos pelo payload ao criar endereços
- Campos vazios/nulos do payload de billing e delivery agora são descartados antes do `create()`

## [1.8.0] - 2026-06-01
- Corrigido parametros de country

## [1.7.0] - 2026-05-09
- Corrigido morphs

## [1.6.0] - 2026-05-09
- Atualizado packages

## [1.5.0] - 2026-05-01

### Added
- Novo método `syncForModel()` no Model `Address` para sincronização flexível de endereços
- Novo método `syncAddress()` no trait `HasAddress` para sincronização explícita
- Novo método `syncAddressBilling()` no trait `HasAddressBilling` para múltiplos endereços de cobrança
- Novo método `syncAddressDelivery()` no trait `HasAddressDelivery` para múltiplos endereços de entrega
- Suporte a múltiplos formatos de payload (`person.address`, `address`, ou array direto)
- Constantes `TYPE_DEFAULT`, `TYPE_DELIVERY`, `TYPE_BILLING` no Model `Address`
- Validação de FK em `AddressObserver` para evitar erros quando tabela `users` difere de `authentications`

### Changed
- **BREAKING**: Removido evento automático `bootHasAddress` - agora requer chamada explícita de `syncAddress()`
- **BREAKING**: Removido evento automático `bootHasAddressBilling` - agora requer chamada explícita de `syncAddressBilling()`
- **BREAKING**: Removido evento automático `bootHasAddressDelivery` - agora requer chamada explícita de `syncAddressDelivery()`
- Relações atualizadas para usar constantes em vez de strings hardcoded

### Fixed
- Corrigido violação de FK em `address_histories.user_id` quando usuário está em tabela diferente
- Corrigido erro de transaction abortada causado por duplicação de execução (evento + método manual)
- Corrigido erro `Undefined constant` ao usar `Address::TYPE_*` via Facade

## [1.4.0] - 2026-04-28

### Added
- Adicionado suporte para histórico de endereço usados
- Adicionado suporte para histórico de alterações de endereços
- Adicionado suporte para filtragem de countries, states, cities e districts
- Adicionado método `creating` no `AddressObserver` para definir `is_default = true` automaticamente no primeiro endereço de cada tipo

## [1.3.1] - 2025-12-30
- Atualizado packages

## [1.3.0] - 2025-12-30
- Corrigido relacionamento

## [1.2.0] - 2025-12-30
- Atualizado packages.

## [1.1.0] - 2025-12-10
- Atualizado packages.

## [1.0.0] - 2025-12-10
### Added
- Lançamento inicial (Primeira versão estável).
