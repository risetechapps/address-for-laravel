# 🏠 Laravel Address

**Laravel Address** é um pacote para Laravel que permite gerenciar diferentes tipos de endereços (padrão, entrega e cobrança) associados aos seus models de forma simples e eficiente.

---

## 📦 Instalação

### ✅ Requisitos

* PHP >= 8.0
* Laravel >= 10
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

## 🧪 Testes

Para rodar os testes, execute:

```bash
  php artisan test
```

Ou usando PHPUnit diretamente:

```bash
  ./vendor/bin/phpunit
```

Certifique-se de que todas as dependências estão instaladas e o ambiente `.env.testing` está configurado corretamente.

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
