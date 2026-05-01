# Changelog

Todas as alterações notáveis neste projeto serão documentadas neste arquivo.
O formato é baseado em [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), e este projeto segue o [Versionamento Semântico](https://semver.org/lang/pt-BR/) (SemVer).

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
