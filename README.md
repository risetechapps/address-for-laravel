# 🏠 Laravel Address

**Laravel Address** é um pacote para Laravel que permite gerenciar diferentes tipos de endereços (padrão, entrega e cobrança) associados aos seus models de forma simples e eficiente.

---

## 📦 Instalação

### ✅ Requisitos

* PHP >= 8.3
* Laravel >= 12
* Composer

### ⚙️ Passo a Passo

1. Instale o package via Composer:

```bash
  composer require risetechapps/address-for-laravel
```

2. Adicione as traits ao seu model:

```php
use RiseTechApps\Address\Traits\HasAddress\HasAddress;
use RiseTechApps\Address\Traits\HasAddress\HasAddressBilling;
use RiseTechApps\Address\Traits\HasAddress\HasAddressDelivery;

class Client extends Model
{
    use HasFactory, HasAddress, HasAddressDelivery, HasAddressBilling;
}
```

3. Execute as migrations:

```bash
  php artisan migrate
```

---

## ✨ Funcionalidades

* 🏷 **Address:** Endereço padrão para qualquer model.
* 🏷 **Address Delivery:** Suporte a múltiplos endereços de entrega.
* 🏷 **Address Billing:** Suporte a múltiplos endereços de cobrança.

---

## 💡 Exemplos de Uso

### Criar um endereço padrão

```php
$client = Client::find(1);

$client->address()->create([
    'street' => 'Rua Exemplo',
    'number' => '123',
    'city' => 'São Paulo',
    'state' => 'SP',
    'zipcode' => '01234-567',
]);
```

### Adicionar endereço de entrega

```php
$client->deliveryAddresses()->create([
    'street' => 'Av. das Entregas',
    'number' => '456',
    'city' => 'Campinas',
    'state' => 'SP',
    'zipcode' => '13000-000',
]);
```

### Adicionar endereço de cobrança

```php
$client->billingAddresses()->create([
    'street' => 'Rua da Cobrança',
    'number' => '789',
    'city' => 'Ribeirão Preto',
    'state' => 'SP',
    'zipcode' => '14000-000',
]);
```

### Definir endereço principal (default)

```php
$address = $client->billingAddresses()->create([...]);
$address->setAsDefault();

// Ou buscar o endereço padrão
$defaultBilling = $client->billingAddressDefault();
$defaultDelivery = $client->deliveryAddressDefault();
```

---

## 📊 Histórico de Uso

Rastreie quantas vezes cada endereço foi utilizado:

```php
// Registrar uso de um endereço
$address->incrementUsage();
$address->incrementUsage('delivery', ['order_id' => 123]);

// Ver contador
$address->usage_count;      // 15
$address->last_used_at;     // 2025-04-27 14:30:00

// Endereços mais usados
Address::mostUsed(10)->get();
$client->mostUsedBillingAddresses(5);
$client->mostUsedDeliveryAddress(); // O mais usado

// Scopes úteis
Address::mostUsed()->get();           // Ordenado por uso
Address::recentlyUsed(30)->get();    // Usados nos últimos 30 dias
Address::neverUsed()->get();          // Nunca usados
Address::usedMoreThan(10)->get();     // Mais de 10 vezes

// Logs detalhados
$address->usageLogs()->get();
$address->usageLogs()->byAction('delivery')->get();
$address->usageLogs()->lastDays(7)->get();
```

### Enviando um request com endereço incluído

Caso envie um payload contendo `address`, `address_billing` ou `address_delivery`, os dados serão automaticamente persistidos com o model:

```json
{
  "name": "João da Silva",
  "email": "joao@example.com",
  "address": {
    "street": "Rua Principal",
    "number": "100",
    "city": "São Paulo",
    "state": "SP",
    "zipcode": "01000-000"
  },
  "address_billing": [
    {
      "street": "Rua da Fatura",
      "number": "200",
      "city": "São Paulo",
      "state": "SP",
      "zipcode": "02000-000"
    }
  ],
  "address_delivery": [
    {
      "street": "Av. das Entregas",
      "number": "300",
      "city": "Campinas",
      "state": "SP",
      "zipcode": "13000-000"
    }
  ]
}
```

Esse comportamento é automático desde que seu controller/model esteja configurado para aceitar os relacionamentos e realizar a persistência corretamente.

---

## 🔍 Scopes de Consulta

O pacote inclui diversos scopes para facilitar consultas:

```php
// Buscar por estado
Address::byState('SP')->get();

// Buscar por cidade
Address::byCity('São Paulo')->get();

// Buscar por CEP
Address::byZipCode('01310-100')->first();

// Buscar por país
Address::byCountry('BR')->get();

// Buscar por bairro
Address::byDistrict('Centro')->get();

// Combinar scopes
Address::byState('SP')->byCity('São Paulo')->byDistrict('Jardins')->get();

// Buscar endereços padrão
Address::default()->get();
```

---

## 📝 Histórico de Alterações

Todas as alterações em endereços são automaticamente registradas:

```php
// Ver histórico de um endereço
$history = $address->history()->get();

// Última alteração
$latest = $address->latestHistory();

// Filtrar por tipo de ação
$createdEntries = $address->history()->created()->get();
$updatedEntries = $address->history()->updated()->get();
$deletedEntries = $address->history()->deleted()->get();

// Ver mudanças específicas
foreach ($history as $entry) {
    echo $entry->description; // "Endereço atualizado"
    print_r($entry->changes);   // ['city' => ['old' => 'SP', 'new' => 'RJ']]
}
```

O histórico inclui:
- **Ação**: created, updated, deleted, restored
- **Valores antigos e novos**
- **IP e User Agent** do usuário
- **ID do usuário** que fez a alteração
- **Data/hora** da alteração

---

## 🤝 Como Contribuir

Contribuições são super bem-vindas! Para colaborar:

1. Faça um fork do repositório
2. Crie uma branch com sua feature (`feature/nome-da-feature`)
3. Faça o commit das suas alterações
4. Envie um Pull Request

---

## 📄 Licença

Este projeto é licenciado sob a licença MIT. Consulte o arquivo [LICENSE](LICENSE) para mais detalhes.

---

## 💡 Autor

Desenvolvido com 💙 por [Rise Tech](https://risetech.com.br)
