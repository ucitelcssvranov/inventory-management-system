# Microsoft 365 OAuth Integration - Setup Guide

## 1. Azure AD Application Registration

Pre úspešné fungovanie Microsoft 365 prihlásenia je potrebné zaregistrovať aplikáciu v Azure Active Directory:

### Kroky:

1. **Prejdite na Azure Portal**
   - Otvorte https://portal.azure.com
   - Prihláste sa pomocou Microsoft 365 admin účtu

2. **Prejdite na Azure Active Directory**
   - V ľavom menu kliknite na "Azure Active Directory"
   - Alebo použite vyhľadávanie: "Azure Active Directory"

3. **App registrations**
   - V ľavom menu kliknite na "App registrations"
   - Kliknite na "New registration"

4. **Zaregistrujte aplikáciu**
   - **Name**: `Inventarizacia CSŠ Vranov`
   - **Supported account types**: 
     - Pre školu: "Accounts in this organizational directory only"
     - Pre všeobecné použitie: "Accounts in any organizational directory"
   - **Redirect URI**: 
     - Type: Web
     - URL: `http://127.0.0.1:8080/login/microsoft/callback`
     - Pre produkciu: `https://yourdomain.com/login/microsoft/callback`

5. **Získajte Client ID**
   - Po registrácii skopírujte "Application (client) ID"
   - Vložte do `.env` súboru: `MICROSOFT_CLIENT_ID=your_application_id`

6. **Vytvorte Client Secret**
   - V ľavom menu kliknite na "Certificates & secrets"
   - Kliknite na "New client secret"
   - Zadajte popis: "Laravel App Secret"
   - Vyberte expiration: "24 months"
   - Skopírujte hodnotu secret (iba raz sa zobrazí!)
   - Vložte do `.env` súboru: `MICROSOFT_CLIENT_SECRET=your_secret_value`

7. **Nastavte API permissions**
   - V ľavom menu kliknite na "API permissions"
   - Kliknite "Add a permission"
   - Vyberte "Microsoft Graph"
   - Vyberte "Delegated permissions"
   - Pridajte tieto permissions:
     - `User.Read` (základné info o používateľovi)
     - `email` (prístup k emailu)
     - `profile` (prístup k profilu)
   - Kliknite "Grant admin consent" (ak ste admin)

## 2. Laravel Configuration

### .env súbor:
```
MICROSOFT_CLIENT_ID=your_application_client_id_here
MICROSOFT_CLIENT_SECRET=your_client_secret_here
MICROSOFT_REDIRECT_URI=http://127.0.0.1:8080/login/microsoft/callback
```

### Pre konkrétnu organizáciu:
V Azure AD aplikácii môžete obmedziť prihlásenie len na vašu organizáciu výberom "Accounts in this organizational directory only" pri registrácii aplikácie.

## 3. Testing

1. Otvorte `http://127.0.0.1:8080/login`
2. Kliknite na "Prihlásiť sa cez Microsoft 365"
3. Budete presmerovaný na Microsoft prihlásenie
4. Po úspešnom prihlásení budete presmerovaný späť do aplikácie

## 4. Production Setup

Pre produkciu:
1. Zmeňte redirect URI v Azure AD na vašu produkčnú doménu
2. Aktualizujte `MICROSOFT_REDIRECT_URI` v `.env` súbore
3. Uistite sa, že máte HTTPS na produkčnom serveri

## 5. Troubleshooting

**Chyba "redirect_uri mismatch":**
- Skontrolujte, či redirect URI v Azure AD presne zodpovedá URL v `.env`

**Chyba "invalid_client":**
- Skontrolujte Client ID a Client Secret v `.env`
- Uistite sa, že Client Secret nevypršal

**Používateľ sa nemôže prihlásiť:**
- Skontrolujte API permissions v Azure AD
- Uistite sa, že je povolený "User.Read" permission