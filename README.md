# 🚀 Marketplace Order Management System

## 📋 O projekcie

**Marketplace Order Management System** to aplikacja B2B do zarządzania zamówieniami w modelu SaaS, stworzona z myślą o firmach hurtowych. System umożliwia automatyczne przyjmowanie i przetwarzanie zamówień online, zarządzanie klientami, produktami oraz pełną integrację poprzez API REST.

### 🎯 Kluczowe funkcjonalności

- 📦 **Zarządzanie zamówieniami** - Pełny cykl życia zamówienia od złożenia do realizacji
- 🛍️ **Katalog produktów** - Zarządzanie stanami magazynowymi i cenami
- 👥 **System klientów B2B** - Limity kredytowe, rabaty, historia transakcji
- 📊 **Dashboard analityczny** - Statystyki sprzedaży w czasie rzeczywistym
- 🔌 **API REST** - Pełna integracja z systemami zewnętrznymi
- ⚡ **Komponenty Livewire** - Reaktywny interfejs bez przeładowania strony
- 📱 **Responsive Design** - Działa na wszystkich urządzeniach
- 🔐 **Autentykacja API** - Bezpieczny dostęp przez klucze API
- 📄 **Generowanie faktur** - Automatyczne faktury VAT
- 🌐 **Webhooks** - Powiadomienia o zdarzeniach w systemie

## 🛠️ Stack technologiczny

### Backend
- **Laravel 10** - Framework PHP
- **MySQL 8.0** - Baza danych
- **Redis** - Cache i kolejki
- **PHP 8.3** - Język programowania

### Frontend
- **Livewire 3.0** - Reaktywne komponenty
- **Tailwind CSS 3.0** - Framework CSS
- **Alpine.js** - Lekki framework JS
- **Chart.js** - Wykresy i statystyki

### Tests
- **PHPUnit** - Testy


## 🚀 Szybki start

### Wymagania
- PHP >= 8.3
- Composer >= 2.0
- Node.js >= 18.0
- MySQL >= 8.0
- Redis (opcjonalnie)

### Instalacja krok po kroku

```bash
# 1. Klonowanie repozytorium
git clone https://github.com/Jacolos/marketplace-demo.git
cd marketplace-demo

# 2. Instalacja zależności PHP
composer install

# 3. Instalacja zależności JavaScript
npm install

# 4. Konfiguracja środowiska
cp .env.example .env
php artisan key:generate

# 5. Konfiguracja bazy danych
# Edytuj plik .env i ustaw dane dostępowe do MySQL

# 6. Utworzenie bazy danych
mysql -u root -p
CREATE DATABASE marketplace_orders;
exit

# 7. Migracje i seedery
php artisan migrate --seed

# 8. Link do storage
php artisan storage:link

# 9. Kompilacja assetów
npm run build

# 10. Uruchomienie serwera
php artisan serve
```

Aplikacja dostępna pod adresem: http://localhost:8000


## 🔌 API REST

### Autentykacja
Wszystkie zapytania API wymagają nagłówka:
```http
X-API-Key: your_api_key_here
```

### Główne endpointy

#### Zamówienia
```http
GET    /api/orders           # Lista zamówień
POST   /api/orders           # Utworzenie zamówienia
GET    /api/orders/{number}  # Szczegóły zamówienia
PUT    /api/orders/{number}  # Aktualizacja zamówienia
DELETE /api/orders/{number}  # Anulowanie zamówienia
```

#### Produkty
```http
GET    /api/products         # Lista produktów
GET    /api/products/{id}    # Szczegóły produktu
POST   /api/products         # Dodanie produktu
PUT    /api/products/{id}    # Aktualizacja produktu
```

### Przykład zapytania

```javascript
// Utworzenie zamówienia
const response = await fetch('http://localhost:8000/api/orders', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-API-Key': 'your_api_key_here'
  },
  body: JSON.stringify({
    items: [
      { product_id: 1, quantity: 5 },
      { product_id: 3, quantity: 2 }
    ],
    shipping_address: {
      street: 'ul. Przykładowa 123',
      city: 'Warszawa',
      postal_code: '00-001'
    },
    payment_method: 'transfer'
  })
});

const order = await response.json();
```

### Rate Limiting
- 100 zapytań na minutę per klucz API
- Nagłówki zwrotne informują o limitach

## ⚡ Komponenty Livewire

### ProductSearch
Dynamiczne wyszukiwanie produktów z filtrowaniem:
```blade
<livewire:product-search 
    :categories="$categories" 
    :min-stock="10" 
/>
```

### OrderManager
Kreator zamówień z walidacją w czasie rzeczywistym:
```blade
<livewire:order-manager 
    :customer="$customer" 
    :products="$products" 
/>
```

### DashboardStats
Statystyki odświeżające się automatycznie:
```blade
<livewire:dashboard-stats 
    :period="30" 
    :refresh="60" 
/>
```

## 🧪 Testowanie

### Uruchomienie testów
```bash
# Wszystkie testy
php artisan test

# Testy z pokryciem kodu
php artisan test --coverage

# Tylko testy jednostkowe
php artisan test --testsuite=Unit

# Tylko testy funkcjonalne
php artisan test --testsuite=Feature

# Konkretny test
php artisan test tests/Feature/OrderApiTest.php
```

### Przykład testu

```php
public function test_can_create_order_via_api()
{
    $customer = Customer::factory()->create();
    $product = Product::factory()->create(['stock' => 100]);

    $response = $this->withHeaders([
        'X-API-Key' => $customer->api_key,
    ])->postJson('/api/orders', [
        'items' => [
            ['product_id' => $product->id, 'quantity' => 5]
        ]
    ]);

    $response->assertStatus(201)
             ->assertJsonStructure(['order_number', 'total_amount']);
}
```

## 📊 Funkcjonalności szczegółowe

### 🛒 System zamówień
- Wieloetapowy proces składania zamówienia
- Automatyczna walidacja stanów magazynowych
- Rabaty dla klientów VIP
- Limity kredytowe
- Historia zmian statusów
- Generowanie faktur PDF

### 📦 Zarządzanie produktami
- Kategorie i podkategorie
- Śledzenie stanów magazynowych
- Ceny hurtowe i detaliczne
- Export CSV
- Galeria zdjęć
- Warianty produktów

### 👥 System klientów B2B
- Profile firm
- Limity kredytowe
- Indywidualne rabaty
- Historia zamówień
- Analiza zakupów
- Generowanie kluczy API
- Segmentacja klientów

### 📈 Dashboard analityczny
- Wykresy sprzedaży
- Top produkty
- Najlepsi klienci
- Trendy sprzedażowe
- Alerty magazynowe
- KPI w czasie rzeczywistym


## 🚀 Deployment

### Produkcja

```bash
# Optymalizacja dla produkcji
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Kompilacja assetów
npm run build

# Uruchomienie kolejek
php artisan queue:work --daemon
```



## 📄 Licencja

Projekt stworzony jako demonstracja umiejętności

## 👨‍💻 Autor

**Jacek Wiśniewski**
- 📧 Email: jacolos@jacolos.pl

## 🙏 Podziękowania

- Laravel Team za świetny framework
- Livewire za reaktywność bez JavaScript
- Tailwind CSS za piękny design
