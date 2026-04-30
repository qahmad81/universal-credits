# Universal Credits

![Tests Passing](https://img.shields.io/badge/tests-passing-brightgreen)

Open-source unified payment layer for microservices. Universal Credits provides a robust, token-based microtransaction system allowing multiple vendors to reserve and confirm payments from a client's unified balance.

## Features

- **Microtransactions**: Precise balance management with unit-based arithmetic to prevent rounding errors.
- **Token-based Auth**: Secure vendor and client tokens for API access.
- **Multi-vendor Support**: Designed for ecosystems where multiple services charge against a single user balance.
- **Admin Panel**: Comprehensive Filament-powered management interface.
- **Client Dashboard**: User-friendly dashboard for managing tokens, top-ups, and transaction history.
- **Security Hardened**: Rate limiting, SHA-256 token hashing, and mass assignment protection.

## Requirements

- PHP 8.2+
- MySQL 5.7+
- Composer

## Installation

1. **Clone the repository**:
   ```bash
   git clone https://github.com/awda-it/universal-credits.git
   cd universal-credits
   ```

2. **Install dependencies**:
   ```bash
   composer install
   npm install
   npm run build
   ```

3. **Configure environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Update `.env` with your database credentials.*

4. **Initialize database**:
   ```bash
   php artisan migrate --seed
   ```

5. **Serve the application**:
   ```bash
   php artisan serve
   ```

## Default Admin

Access the admin panel at `/admin`:
- **Email**: `admin@uc.local`
- **Password**: `password`

## API Quick Start

### 1. Reserve Credits
```bash
POST /api/v1/reserve
Authorization: Bearer <vendor_token>
{
    "client_token": "ct_...",
    "amount": 10.00,
    "description": "Coffee purchase"
}
```

### 2. Confirm Payment
```bash
POST /api/v1/confirm
Authorization: Bearer <vendor_token>
{
    "reservation_id": 123,
    "actual_amount": 8.50
}
```

Full API documentation available at `/docs`.

## Contributing

Brief guidelines:
1. Fork the repository.
2. Create your feature branch.
3. Ensure all tests pass.
4. Submit a pull request.

## License

MIT License - Copyright (c) 2024 Awda IT
