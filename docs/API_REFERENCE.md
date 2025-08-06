# AI Inventory Agent - API Reference

## REST API Endpoints

The plugin provides REST API endpoints for external integrations.

### Authentication

All API requests require authentication using WordPress application passwords or JWT tokens.

```bash
curl -X GET https://your-site.com/wp-json/aia/v1/inventory \
  -H "Authorization: Basic base64(username:app_password)"
```

### Base URL

```
https://your-site.com/wp-json/aia/v1/
```

## Inventory Endpoints

### Get Inventory Summary

```http
GET /inventory/summary
```

**Response:**
```json
{
  "success": true,
  "data": {
    "total_products": 150,
    "total_value": 25000.50,
    "in_stock": 120,
    "low_stock": 20,
    "out_of_stock": 10,
    "last_updated": "2024-01-15T10:30:00Z"
  }
}
```

### Get Product Stock

```http
GET /inventory/product/{product_id}
```

**Parameters:**
- `product_id` (required): WooCommerce product ID

**Response:**
```json
{
  "success": true,
  "data": {
    "product_id": 123,
    "name": "Product Name",
    "sku": "PROD-123",
    "stock_quantity": 45,
    "stock_status": "instock",
    "price": 29.99,
    "stock_value": 1349.55
  }
}
```

### Update Stock

```http
POST /inventory/update
```

**Request Body:**
```json
{
  "product_id": 123,
  "stock_quantity": 50,
  "reason": "Manual adjustment"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "product_id": 123,
    "old_stock": 45,
    "new_stock": 50,
    "updated_at": "2024-01-15T10:35:00Z"
  }
}
```

## AI Chat Endpoints

### Send Message

```http
POST /ai/chat
```

**Request Body:**
```json
{
  "message": "What products are running low on stock?",
  "session_id": "optional-session-id",
  "context": {
    "include_recommendations": true
  }
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "response": "Based on current inventory levels, 5 products are running low...",
    "session_id": "generated-session-id",
    "tokens_used": 150,
    "processing_time": 1.23
  }
}
```

### Get Conversation History

```http
GET /ai/conversation/{session_id}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "session_id": "abc123",
    "messages": [
      {
        "role": "user",
        "content": "What products are running low?",
        "timestamp": "2024-01-15T10:30:00Z"
      },
      {
        "role": "assistant",
        "content": "Based on current inventory...",
        "timestamp": "2024-01-15T10:30:05Z"
      }
    ]
  }
}
```

## Analytics Endpoints

### Get Sales Analytics

```http
GET /analytics/sales
```

**Query Parameters:**
- `period` (optional): daily, weekly, monthly, yearly (default: monthly)
- `start_date` (optional): YYYY-MM-DD format
- `end_date` (optional): YYYY-MM-DD format

**Response:**
```json
{
  "success": true,
  "data": {
    "period": "monthly",
    "total_sales": 15000.00,
    "total_orders": 250,
    "average_order_value": 60.00,
    "top_products": [
      {
        "product_id": 123,
        "name": "Top Product",
        "quantity_sold": 50,
        "revenue": 2500.00
      }
    ]
  }
}
```

### Get Stock Movement

```http
GET /analytics/stock-movement
```

**Query Parameters:**
- `days` (optional): Number of days to analyze (default: 30)
- `product_id` (optional): Filter by specific product

**Response:**
```json
{
  "success": true,
  "data": {
    "movements": [
      {
        "date": "2024-01-15",
        "product_id": 123,
        "type": "sale",
        "quantity": -5,
        "balance": 45
      }
    ]
  }
}
```

## Forecasting Endpoints

### Get Demand Forecast

```http
GET /forecast/demand/{product_id}
```

**Query Parameters:**
- `days` (optional): Forecast period in days (default: 30)

**Response:**
```json
{
  "success": true,
  "data": {
    "product_id": 123,
    "current_stock": 45,
    "forecast_period": 30,
    "predicted_demand": 120,
    "recommended_reorder": 75,
    "confidence_score": 0.85,
    "stockout_risk": "medium"
  }
}
```

### Bulk Forecast

```http
POST /forecast/bulk
```

**Request Body:**
```json
{
  "product_ids": [123, 124, 125],
  "forecast_days": 30
}
```

## Supplier Endpoints

### Get Suppliers

```http
GET /suppliers
```

**Response:**
```json
{
  "success": true,
  "data": {
    "suppliers": [
      {
        "supplier_id": "SUP001",
        "name": "Supplier Name",
        "reliability_score": 0.92,
        "average_lead_time": 5,
        "product_count": 25
      }
    ]
  }
}
```

### Get Supplier Performance

```http
GET /suppliers/{supplier_id}/performance
```

**Response:**
```json
{
  "success": true,
  "data": {
    "supplier_id": "SUP001",
    "metrics": {
      "on_time_delivery_rate": 0.95,
      "quality_score": 0.88,
      "response_time": 24,
      "risk_level": "low"
    }
  }
}
```

## Notifications Endpoints

### Get Active Alerts

```http
GET /notifications/alerts
```

**Query Parameters:**
- `type` (optional): low_stock, out_of_stock, overstock
- `status` (optional): active, resolved, dismissed

**Response:**
```json
{
  "success": true,
  "data": {
    "alerts": [
      {
        "alert_id": 1,
        "type": "low_stock",
        "product_id": 123,
        "message": "Product XYZ is running low (5 units remaining)",
        "severity": "warning",
        "created_at": "2024-01-15T10:00:00Z"
      }
    ]
  }
}
```

### Dismiss Alert

```http
POST /notifications/alerts/{alert_id}/dismiss
```

## Reports Endpoints

### Generate Report

```http
POST /reports/generate
```

**Request Body:**
```json
{
  "type": "inventory_summary",
  "period": "monthly",
  "format": "pdf",
  "email": "user@example.com"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "report_id": "RPT-2024-001",
    "status": "processing",
    "estimated_time": 60
  }
}
```

### Get Report Status

```http
GET /reports/{report_id}/status
```

## Webhooks

The plugin supports webhooks for real-time notifications.

### Available Events

- `aia.stock.low` - Triggered when stock falls below threshold
- `aia.stock.out` - Triggered when product goes out of stock
- `aia.order.completed` - Triggered when order affects inventory
- `aia.forecast.alert` - Triggered for forecast warnings

### Webhook Payload Example

```json
{
  "event": "aia.stock.low",
  "timestamp": "2024-01-15T10:30:00Z",
  "data": {
    "product_id": 123,
    "product_name": "Product XYZ",
    "current_stock": 5,
    "threshold": 10
  }
}
```

## Error Responses

All endpoints return consistent error responses:

```json
{
  "success": false,
  "error": {
    "code": "invalid_product",
    "message": "Product not found",
    "details": {
      "product_id": 999
    }
  }
}
```

### Common Error Codes

- `unauthorized` - Authentication failed
- `forbidden` - Insufficient permissions
- `not_found` - Resource not found
- `invalid_request` - Invalid request parameters
- `rate_limited` - Too many requests
- `server_error` - Internal server error

## Rate Limiting

API requests are rate limited to prevent abuse:

- **Default limit**: 100 requests per minute
- **Bulk operations**: 10 requests per minute

Rate limit information is included in response headers:

```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1705315200
```

## PHP Integration

### Using the API internally

```php
// Get plugin instance
$plugin = \AIA\Core\Plugin::get_instance();

// Get inventory data
$inventory = $plugin->get_module('inventory_analysis');
$summary = $inventory->get_inventory_summary();

// Process AI chat
$ai_chat = $plugin->get_module('ai_chat');
$response = $ai_chat->process_message('What are my low stock items?');

// Get forecasting
$forecasting = $plugin->get_module('demand_forecasting');
$forecast = $forecasting->forecast_product_demand($product_id, 30);
```

### Custom Endpoints

Register custom endpoints:

```php
add_action('rest_api_init', function() {
    register_rest_route('aia/v1', '/custom/endpoint', [
        'methods' => 'GET',
        'callback' => 'my_custom_callback',
        'permission_callback' => function() {
            return current_user_can('manage_woocommerce');
        }
    ]);
});
```

## JavaScript SDK

Use the JavaScript SDK for frontend integration:

```javascript
// Initialize the SDK
const aiaAPI = new AIAAPI({
    apiUrl: '/wp-json/aia/v1',
    nonce: aia_ajax.nonce
});

// Get inventory summary
aiaAPI.inventory.getSummary().then(response => {
    console.log(response.data);
});

// Send AI chat message
aiaAPI.ai.sendMessage({
    message: 'Show me low stock products',
    session_id: sessionId
}).then(response => {
    console.log(response.data.response);
});
```