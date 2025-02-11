# Laravel Address

## 📌 Sobre o Projeto
O **Address for Laravel** é um package para Laravel que você consegue gerenciar endereços para os models.

## ✨ Funcionalidades
- 🏷 **Address** crie um endereço padrão para o seu model
- 🏷 **Address Delivery** crie múltiplos endereços de entrega para o seu model
- 🏷 **Address Billing** crie múltiplos endereços de cobrança para o seu model

---

## 🚀 Instalação

### 1️⃣ Requisitos
Antes de instalar, certifique-se de que seu projeto atenda aos seguintes requisitos:
- PHP >= 8.0
- Laravel >= 10
- Composer instalado

### 2️⃣ Instalação do Package
Execute o seguinte comando no terminal:
```bash
composer require risetechapps/address-for-laravel
```

### 3️⃣ Configure seu Model
```php
  
  use RiseTechApps\Address\Traits\HasAddress\HasAddress;
  use RiseTechApps\Address\Traits\HasAddress\HasAddressBilling;
  use RiseTechApps\Address\Traits\HasAddress\HasAddressDelivery;
  
  class Client extends Model
  {
    use HasFactory, HasAddress, HasAddressDelivery, HasAddressBilling;
  }
```

### 4️⃣ Rodar Migrations
```bash
php artisan migrate
```
---

## 🛠 Contribuição
Sinta-se à vontade para contribuir! Basta seguir estes passos:
1. Faça um fork do repositório
2. Crie uma branch (`feature/nova-funcionalidade`)
3. Faça um commit das suas alterações
4. Envie um Pull Request

---

## 📜 Licença
Este projeto é distribuído sob a licença MIT. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

---

💡 **Desenvolvido por [Rise Tech](https://risetech.com.br)**

