# Universal Credits API Documentation

Integrate Universal Credits into your application to enable seamless, low-cost microtransactions.

## Authentication

All vendor API requests must include your API key in the `Authorization` header:

```http
Authorization: Bearer YOUR_VENDOR_KEY
```

Additionally, client-side requests must include the `client_token` representing the user's wallet.

## Number System

Universal Credits (UC) use a high-precision decimal system internally. For display, we use integers where 10,000 UC = $1.00 USD.

- **1 UC** = $0.0001 USD
- **10,000 UC** = $1.00 USD
- **1,000,000 UC** = $100.00 USD

## Endpoints

### POST /api/v1/reserve
Reserve a portion of the user's balance.

**Body:**
```json
{
  "client_token": "token_abc123",
  "amount": 500,
  "description": "Optional description"
}
```

**Response:**
```json
{
  "reservation_id": "res_abc123",
  "amount_reserved": 500,
  "expires_at": "2026-05-01T12:00:00Z"
}
```

### POST /api/v1/confirm
Settle a previous reservation.

**Body:**
```json
{
  "reservation_id": "res_abc123",
  "actual_amount": 450
}
```

**Response:**
```json
{
  "success": true,
  "amount_charged": 450,
  "refunded": 50
}
```

### POST /api/v1/cancel
Cancel a reservation and release all held funds.

**Body:**
```json
{
  "reservation_id": "res_abc123"
}
```

## HTTP Status Codes

- **401**: Unauthorized - Invalid API key
- **402**: Payment Required - Insufficient balance
- **404**: Not Found - Reservation does not exist
- **409**: Conflict - Reservation already settled or cancelled
- **410**: Gone - Reservation expired
- **429**: Too Many Requests - Rate limit exceeded

## Integration Steps

1. **Get Vendor Key**: Sign up and create a vendor profile in the dashboard.
2. **Configure Gateway**: Set the API endpoint URL in your application.
3. **Reserve**: Call `/reserve` before providing service to hold funds.
4. **Confirm/Cancel**: Call `/confirm` after service delivery to settle, or `/cancel` to release funds.
