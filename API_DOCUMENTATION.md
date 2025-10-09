# Inventory Management System API v1

## Prehľad

REST API pre inventárny systém poskytuje endpoint pre správu majetku, inventúrne procesy a získavanie dát.

**Base URL:** `/api/v1`

## Autentifikácia

API používa Laravel Sanctum pre autentifikáciu. Väčšina endpointov vyžaduje Bearer token v Authorization hlavičke.

```
Authorization: Bearer your-token-here
```

## Verejné endpointy

### GET /app-info
Získa informácie o aplikácii a dostupných funkciách.

### GET /health
Health check endpoint pre monitoring.

## Chránené endpointy

### Používateľské info

#### GET /user
Vráti informácie o aktuálne prihlásenom používateľovi.

### Assets API

#### GET /assets
Získa zoznam všetkého majetku s možnosťou filtrovania a paginácie.

**Query parametre:**
- `category_id` - Filter podľa kategórie
- `location_id` - Filter podľa lokácie  
- `search` - Hľadanie v názve a inventárnom čísle
- `per_page` - Počet položiek na stránku (max 100)
- `page` - Číslo stránky

#### POST /assets
Vytvorí nový majetok.

**Request body:**
```json
{
    "inventory_number": "INV001",
    "name": "Notebook Dell",
    "category_id": 1,
    "location_id": 2,
    "acquisition_date": "2024-01-15",
    "acquisition_cost": 1200.50,
    "description": "Popis majetku",
    "condition": "new"
}
```

#### GET /assets/{id}
Získa detail konkrétneho majetku.

#### PUT /assets/{id}
Aktualizuje existujúci majetok.

#### DELETE /assets/{id}
Vymaže majetok.

#### POST /assets/scan
Vyhľadá majetok podľa naskenovaného QR/čiarového kódu.

**Request body:**
```json
{
    "code": "INV001"
}
```

### Inventory API

#### GET /inventory/plans
Získa zoznam inventárnych plánov.

**Query parametre:**
- `status` - Filter podľa stavu (active, completed, etc.)

#### GET /inventory/plans/{id}
Detail inventárneho plánu vrátane priradených skupín.

#### GET /inventory/user-groups
Získa inventárne skupiny pre konkrétneho používateľa.

**Query parametre:**
- `user_id` (required) - ID používateľa

#### GET /inventory/groups/{id}/counts
Inventárne počty pre danú skupinu.

#### POST /inventory/counts
Zazamenáva inventárny počet.

**Request body:**
```json
{
    "inventory_group_id": 1,
    "asset_id": 123,
    "counted_quantity": 1,
    "condition": "good",
    "notes": "Poznámky",
    "location_found": 5,
    "photo": "base64-encoded-image"
}
```

#### GET /inventory/groups/{id}/overview
Prehľad pokroku inventúry pre skupinu.

#### GET /inventory/dashboard/stats
Štatistiky pre dashboard.

### Data API (Master Data)

#### GET /data/categories
Všetky kategórie.

#### GET /data/locations
Všetky lokácie.

#### GET /data/locations/category/{categoryId}
Lokácie pre danú kategóriu.

#### GET /data/users
Všetci používatelia.

#### GET /data/commissions
Všetky komisie.

## Response formát

Všetky endpointy vracajú odpoveď v konzistentnom formáte:

### Úspešná odpoveď
```json
{
    "success": true,
    "data": {...},
    "message": "Optional success message"
}
```

### Chybová odpoveď
```json
{
    "success": false,
    "message": "Error description",
    "errors": {...},
    "error": "Detailed error (only in debug mode)"
}
```

### Paginated odpoveď
```json
{
    "success": true,
    "data": [...],
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 5,
        "per_page": 15,
        "to": 15,
        "total": 75
    }
}
```

## HTTP Status kódy

- `200` - OK
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Internal Server Error
- `503` - Service Unavailable (health check failed)

## Rate Limiting

API má implementované rate limiting pre ochranu pred zneužitím:
- 60 požiadaviek za minútu pre autentifikovaných používateľov
- 20 požiadaviek za minútu pre neautentifikovaných

## Mobilná aplikácia

API je navrhnuté pre mobilnú aplikáciu s podporou:
- Offline režim
- Synchronizácia dát
- QR/čiarový kód skenovanie
- Upload fotografií
- Real-time inventúrne záznamy

## Chybové kódy

### Asset errory
- `ASSET_NOT_FOUND` - Majetok nenájdený
- `INVALID_INVENTORY_NUMBER` - Neplatné inventárne číslo
- `DUPLICATE_INVENTORY_NUMBER` - Duplicitné inventárne číslo

### Inventory errory  
- `INVENTORY_GROUP_NOT_FOUND` - Inventárna skupina nenájdená
- `INVALID_COUNT_DATA` - Neplatné dáta počtu
- `COUNT_ALREADY_EXISTS` - Počet už existuje

### Validation errory
- `VALIDATION_FAILED` - Validačné chyby (detail v `errors` objekte)

## Príklady použitia

### Mobilná inventúra workflow

1. **Prihlásenie a získanie tokenov**
2. **Stiahnutie inventárnych skupín pre používateľa**
   ```
   GET /inventory/user-groups?user_id=123
   ```
3. **Skenovanie QR kódu majetku**
   ```
   POST /assets/scan
   {"code": "INV001"}
   ```
4. **Zaznamenanie inventárneho počtu**
   ```
   POST /inventory/counts
   {
     "inventory_group_id": 1,
     "asset_id": 456,
     "counted_quantity": 1,
     "condition": "good"
   }
   ```
5. **Kontrola pokroku**
   ```
   GET /inventory/groups/1/overview
   ```

### Správa majetku

1. **Získanie zoznamu majetku s filtrom**
   ```
   GET /assets?category_id=2&search=notebook&per_page=20
   ```
2. **Vytvorenie nového majetku**
   ```
   POST /assets
   {
     "inventory_number": "NB2024001",
     "name": "Dell Latitude 5520",
     "category_id": 2,
     "location_id": 15,
     "acquisition_date": "2024-01-15",
     "acquisition_cost": 1299.99
   }
   ```