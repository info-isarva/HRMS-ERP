# Company Settings API - Single Comprehensive Endpoint

This document describes the unified Company Settings API endpoint similar to the employees API structure.

## 🎯 Main API Endpoint

**Single Comprehensive Endpoint:** `GET /api/company-settings`

This endpoint provides all company settings data in a structured format, similar to how the employees API works.

## 🔗 Base URL
```
http://your-domain.com/api
```

## 📊 Main Endpoint Usage

### Get All Company Settings
```bash
GET /api/company-settings
```

### Get Specific Fields Only
```bash
GET /api/company-settings?fields=company_name,logo_url,favicon_url
GET /api/company-settings?fields=contact,assets
GET /api/company-settings?fields=address,system
```

## 📋 Response Structure

```json
{
    "success": true,
    "message": "Company settings retrieved successfully",
    "data": {
        "id": 1,
        "company_name": "Your Company Name",
        "contact_person": "John Doe",
        "email": "company@example.com",
        "phone_number": "+1234567890",
        "mobile_number": "+1234567891",
        "fax": "+1234567892",
        "website_url": "https://company.com",
        
        "address": {
            "street": "123 Business Street",
            "city": "Business City",
            "state_province": "Business State",
            "country": "Business Country",
            "postal_code": "12345",
            "full_address": "123 Business Street, Business City, Business State, Business Country 12345"
        },
        
        "assets": {
            "logo": {
                "url": "http://your-domain.com/assets/company_image/logo.png",
                "path": "assets/company_image/logo.png",
                "exists": true
            },
            "favicon": {
                "url": "http://your-domain.com/assets/company_image/favicon.ico",
                "path": "assets/company_image/favicon.ico",
                "exists": true
            }
        },
        
        "logo_url": "http://your-domain.com/assets/company_image/logo.png",
        "favicon_url": "http://your-domain.com/assets/company_image/favicon.ico",
        "logo_path": "assets/company_image/logo.png",
        "favicon_path": "assets/company_image/favicon.ico",
        
        "system": {
            "base_url": "http://your-domain.com",
            "source_system": "payroll",
            "api_version": "1.0",
            "last_updated": "2024-01-01T12:00:00.000000Z",
            "created": "2024-01-01T10:00:00.000000Z"
        },
        
        "contact": {
            "primary_email": "company@example.com",
            "primary_phone": "+1234567890",
            "mobile_phone": "+1234567891",
            "fax_number": "+1234567892",
            "contact_person": "John Doe",
            "website": "https://company.com"
        },
        
        "created_at": "2024-01-01T10:00:00.000000Z",
        "updated_at": "2024-01-01T12:00:00.000000Z",
        "last_sync": "2024-01-01T14:30:00.000000Z"
    },
    "meta": {
        "total": 1,
        "base_url": "http://your-domain.com",
        "timestamp": "2024-01-01T14:30:00.000000Z",
        "version": "1.0",
        "requested_fields": "all",
        "source": "payroll_system"
    }
}
```

## 🔍 Field Filtering

You can request specific fields using the `fields` query parameter:

### Available Field Groups:

1. **Basic Info:** `company_name`, `contact_person`, `email`, `phone_number`, `mobile_number`, `fax`, `website_url`
2. **Address:** `address` (returns structured address object)
3. **Assets:** `assets` (returns structured logo/favicon object)
4. **Quick URLs:** `logo_url`, `favicon_url`, `logo_path`, `favicon_path`
5. **Contact Info:** `contact` (returns structured contact object)
6. **System Info:** `system` (returns metadata and timestamps)
7. **Timestamps:** `created_at`, `updated_at`, `last_sync`

### Examples:

```bash
# Get only company name and logo
GET /api/company-settings?fields=company_name,logo_url

# Get contact information and assets
GET /api/company-settings?fields=contact,assets

# Get address and system information
GET /api/company-settings?fields=address,system
```

## 💻 Usage Examples

### JavaScript/Fetch
```javascript
// Get all company settings
fetch('/api/company-settings')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Company:', data.data.company_name);
            console.log('Logo URL:', data.data.logo_url);
            console.log('Full Address:', data.data.address.full_address);
            console.log('Contact Info:', data.data.contact);
        }
    });

// Get specific fields only
fetch('/api/company-settings?fields=company_name,assets,contact')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Company:', data.data.company_name);
            console.log('Logo exists:', data.data.assets.logo.exists);
            console.log('Primary email:', data.data.contact.primary_email);
        }
    });
```

### cURL
```bash
# Get all company settings
curl -X GET "http://your-domain.com/api/company-settings" \
     -H "Accept: application/json"

# Get specific fields
curl -X GET "http://your-domain.com/api/company-settings?fields=company_name,logo_url,contact" \
     -H "Accept: application/json"
```

### PHP
```php
// Get all company settings
$response = file_get_contents('http://your-domain.com/api/company-settings');
$data = json_decode($response, true);

if ($data['success']) {
    echo "Company: " . $data['data']['company_name'];
    echo "Logo URL: " . $data['data']['logo_url'];
    echo "Full Address: " . $data['data']['address']['full_address'];
}

// Get specific fields
$response = file_get_contents('http://your-domain.com/api/company-settings?fields=company_name,assets');
$data = json_decode($response, true);

if ($data['success']) {
    echo "Company: " . $data['data']['company_name'];
    echo "Logo URL: " . $data['data']['assets']['logo']['url'];
}
```

## ⚠️ Error Responses

```json
{
    "success": false,
    "message": "Company settings not found",
    "data": null,
    "meta": {
        "total": 0,
        "timestamp": "2024-01-01T14:30:00.000000Z",
        "version": "1.0"
    }
}
```

## 🔧 Integration with Attendance System

### Example Integration Code:

```javascript
class CompanySettingsAPI {
    constructor(baseUrl) {
        this.baseUrl = baseUrl;
    }
    
    async getAllSettings() {
        const response = await fetch(`${this.baseUrl}/api/company-settings`);
        return await response.json();
    }
    
    async getBasicInfo() {
        const response = await fetch(`${this.baseUrl}/api/company-settings?fields=company_name,contact,assets`);
        return await response.json();
    }
    
    async getAssetsOnly() {
        const response = await fetch(`${this.baseUrl}/api/company-settings?fields=assets,logo_url,favicon_url`);
        return await response.json();
    }
    
    async syncToAttendance(companyData) {
        // Use the comprehensive data for attendance system
        console.log('Syncing company data:', companyData);
        
        // Update attendance system with:
        // - Company name: companyData.company_name
        // - Logo URL: companyData.assets.logo.url
        // - Favicon URL: companyData.assets.favicon.url
        // - Full address: companyData.address.full_address
        // - Contact info: companyData.contact
    }
}

// Usage
const api = new CompanySettingsAPI('http://payroll-domain.com');

api.getAllSettings().then(response => {
    if (response.success) {
        api.syncToAttendance(response.data);
    }
});
```

## 🎯 Benefits of Single Endpoint Approach

1. **Consistency**: Similar to employees API structure
2. **Flexibility**: Get all data or specific fields as needed
3. **Performance**: Single request instead of multiple API calls
4. **Structured Data**: Organized response with logical grouping
5. **Easy Integration**: Simple to consume in attendance system
6. **Backward Compatibility**: Legacy endpoints still available

## 🚀 Quick Start

1. **Get all company data:**
   ```bash
   curl http://your-domain.com/api/company-settings
   ```

2. **Get only logo and company name for UI:**
   ```bash
   curl "http://your-domain.com/api/company-settings?fields=company_name,logo_url"
   ```

3. **Get contact info for sync:**
   ```bash
   curl "http://your-domain.com/api/company-settings?fields=contact,address"
   ```

This single comprehensive endpoint provides everything the attendance system needs while maintaining simplicity and consistency with your existing API patterns! 🎉