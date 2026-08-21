# Company Settings API Documentation

## Overview
This API provides access to company settings data from the Payroll system for the Attendance system integration. It includes full URLs for logo and favicon images.

## Base URL
```
https://your-payroll-domain.com/api
```

## Endpoints

### 1. Get Company Settings (Public)
**Endpoint:** `GET /company-settings`

**Description:** Retrieves company settings with full URLs for logo and favicon.

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Response:**
```json
{
    "success": true,
    "message": "Company settings retrieved successfully",
    "data": {
        "id": 1,
        "company_name": "Your Company Name",
        "contact_person": "John Doe",
        "address": "123 Business Street",
        "country": "United States",
        "city": "New York",
        "state_province": "NY",
        "postal_code": "10001",
        "email": "contact@company.com",
        "phone_number": "+1-555-123-4567",
        "mobile_number": "+1-555-987-6543",
        "fax": "+1-555-123-4568",
        "website_url": "https://company.com",
        "logo_url": "https://your-payroll-domain.com/assets/company_image/logo_image.1634567890.png",
        "favicon_url": "https://your-payroll-domain.com/assets/company_image/favicon_image1634567891.ico",
        "logo_path": "assets/company_image/logo_image.1634567890.png",
        "favicon_path": "assets/company_image/favicon_image1634567891.ico",
        "created_at": "2023-01-01T00:00:00.000000Z",
        "updated_at": "2023-12-01T10:30:00.000000Z"
    },
    "meta": {
        "base_url": "https://your-payroll-domain.com",
        "timestamp": "2024-01-15T14:30:00.000Z",
        "version": "1.0"
    }
}
```

### 2. Get Company Settings (Secured with JWT)
**Endpoint:** `GET /company-settings/secure`

**Description:** Same as above but requires JWT authentication for secure access.

**Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer YOUR_JWT_TOKEN
```

**Response:** Same as above endpoint.

**Error Response (401 Unauthorized):**
```json
{
    "success": false,
    "message": "JWT token required",
    "data": null
}
```

**Error Response (Invalid JWT):**
```json
{
    "success": false,
    "message": "Invalid JWT token",
    "data": null
}
```

## Error Responses

### 404 Not Found
```json
{
    "success": false,
    "message": "Company settings not found",
    "data": null
}
```

### 500 Internal Server Error
```json
{
    "success": false,
    "message": "Failed to retrieve company settings",
    "error": "Error details (only in debug mode)",
    "data": null
}
```

## Data Fields

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Company settings ID |
| `company_name` | string | Official company name |
| `contact_person` | string | Main contact person |
| `address` | string | Company address |
| `country` | string | Country name |
| `city` | string | City name |
| `state_province` | string | State or province |
| `postal_code` | string | Postal/ZIP code |
| `email` | string | Company email address |
| `phone_number` | string | Company phone number |
| `mobile_number` | string | Company mobile number |
| `fax` | string | Company fax number |
| `website_url` | string | Company website URL |
| `logo_url` | string | Full URL to company logo image |
| `favicon_url` | string | Full URL to company favicon |
| `logo_path` | string | Relative path to logo image |
| `favicon_path` | string | Relative path to favicon |
| `created_at` | datetime | Record creation timestamp |
| `updated_at` | datetime | Record last update timestamp |

## Integration with Attendance System

### Example Usage in Attendance System

```javascript
// Using fetch API
async function getCompanySettings() {
    try {
        const response = await fetch('https://payroll-domain.com/api/company-settings', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            // Use the company settings
            console.log('Company Name:', data.data.company_name);
            console.log('Logo URL:', data.data.logo_url);
            console.log('Favicon URL:', data.data.favicon_url);
            
            // Update UI with company info
            updateCompanyInfo(data.data);
        } else {
            console.error('API Error:', data.message);
        }
    } catch (error) {
        console.error('Network Error:', error);
    }
}

// For secured endpoint with JWT
async function getCompanySettingsSecure(jwtToken) {
    try {
        const response = await fetch('https://payroll-domain.com/api/company-settings/secure', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${jwtToken}`
            }
        });
        
        const data = await response.json();
        // Handle response...
    } catch (error) {
        console.error('Error:', error);
    }
}
```

### PHP cURL Example

```php
<?php
// Public endpoint
$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://payroll-domain.com/api/company-settings',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Accept: application/json'
    ),
));

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

if ($httpCode === 200) {
    $data = json_decode($response, true);
    if ($data['success']) {
        echo "Company: " . $data['data']['company_name'] . "\n";
        echo "Logo URL: " . $data['data']['logo_url'] . "\n";
        echo "Favicon URL: " . $data['data']['favicon_url'] . "\n";
    }
} else {
    echo "Error: HTTP $httpCode\n";
}
?>
```

## Environment Configuration

Make sure to set the following in your `.env` file:

```env
# For JWT authentication (if using secured endpoint)
JWT_SECRET=your_secret_jwt_token_here

# App URL for generating full URLs
APP_URL=https://your-payroll-domain.com
```

## Notes

1. **Logo and Favicon URLs**: The API automatically generates full URLs by combining the base URL with the stored image paths.

2. **Image Paths**: Both relative paths and full URLs are provided for flexibility.

3. **Error Handling**: Always check the `success` field in the response before using the data.

4. **Caching**: Consider implementing caching in the attendance system since company settings don't change frequently.

5. **CORS**: Make sure CORS is properly configured to allow requests from the attendance system domain.

6. **SSL**: Use HTTPS for production deployments to secure the data transmission.

## Testing

Test the API endpoints using curl:

```bash
# Test public endpoint
curl -X GET "https://your-payroll-domain.com/api/company-settings" \
     -H "Accept: application/json" \
     -H "Content-Type: application/json"

# Test secured endpoint
curl -X GET "https://your-payroll-domain.com/api/company-settings/secure" \
     -H "Accept: application/json" \
     -H "Content-Type: application/json" \
     -H "Authorization: Bearer YOUR_JWT_TOKEN"
```