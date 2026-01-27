# DA-CARAGA PMIS Postman Collection Guide

## Import Instructions

1. Open Postman
2. Click **Import** button
3. Select the file: `PMIS_Postman_Collection.json`
4. The collection will be imported with all endpoints organized

## Collection Overview

The collection contains **52+ API endpoints** organized into 7 main categories:

### 1. Authentication (2 endpoints)
- **Login** - Get authentication token
- **Logout** - Invalidate token

### 2. Projects (6 endpoints)
- Get All Projects (Public & Authenticated views)
- Get Project by ID
- Create Project
- Update Project
- Delete Project

### 3. Progress Reports (5 endpoints)
- Get All Progress Reports
- Get Progress Report by ID
- Create Progress Report
- Update Progress Report
- Delete Progress Report

### 4. Agricultural Data (10 endpoints)
#### Crop Production (5 endpoints)
- Get All, Get by ID, Create, Update, Delete

#### Livestock Statistics (5 endpoints)
- Get All, Get by ID, Create, Update, Delete

### 5. News & Documents (10 endpoints)
#### News Updates (5 endpoints)
- Get All News, Get by ID, Create, Update, Delete

#### Documents (5 endpoints)
- Get All Documents, Get by ID, Create, Update, Delete

### 6. User Engagement (8 endpoints)
#### Contact Inquiries (4 endpoints)
- Get All, Submit (Public), Update Status, Delete

#### Newsletter Subscriptions (4 endpoints)
- Get All, Subscribe (Public), Update, Delete

### 7. Audit Logs (2 endpoints)
- Get All Audit Logs (with filters)
- Get Audit Log by ID

---

## Getting Started

### Step 1: Set Base URL
The collection uses a variable `{{base_url}}` which is set to:
```
http://localhost:8000
```

If your Laravel app runs on a different URL/port, update this variable in:
- Collection Settings → Variables → `base_url`

### Step 2: Login to Get Token

1. Expand **Authentication** folder
2. Click **Login** request
3. The body already contains default credentials:
   ```json
   {
       "username": "admin",
       "password": "Password123!"
   }
   ```
4. Click **Send**
5. The token will be **automatically saved** to the `access_token` variable

### Step 3: Test Authenticated Endpoints

All protected endpoints will automatically use the saved token via Bearer authentication.

---

## Key Features

### Automatic Token Management
The **Login** request has a test script that automatically:
- Extracts the token from the response
- Saves it to `{{access_token}}` variable
- Uses it for all authenticated requests

### Request Examples with Sample Data
Every request includes realistic sample data based on CARAGA Region:
- Projects with actual CARAGA coordinates
- Crop production data (Rice, Corn, Coconut, etc.)
- Livestock statistics (Cattle, Carabao, Swine, etc.)
- News updates about CARAGA agricultural initiatives

### Public vs Protected Endpoints
- **No Auth Icon** = Public endpoints (no token needed)
- **Lock Icon** = Protected endpoints (requires authentication)

### Query Parameters
Endpoints with filters include pre-configured query parameters:
- `per_page` - Pagination
- `department_id` - Filter by department
- `project_status_id` - Filter by status
- `fiscal_year` - Filter by year
- `crop_name` - Filter by crop
- `livestock_type` - Filter by livestock type

---

## Testing Workflow

### 1. Authentication Flow
```
Login → Get Token → Test Protected Endpoints → Logout
```

### 2. CRUD Operations Flow
```
GET All → GET by ID → CREATE → UPDATE → DELETE
```

### 3. Public Access Flow (No Auth Required)
- Get All Projects (Public)
- Get All Crop Production
- Get All Livestock Statistics
- Get All News
- Get All Documents
- Submit Contact Inquiry
- Subscribe to Newsletter

---

## Sample Test Scenarios

### Scenario 1: View Public Project Data
1. **Authentication:** None required
2. **Request:** GET All Projects (Public)
3. **Expected:** Limited data (no budget, no team members)

### Scenario 2: View Full Project Data
1. **Authentication:** Login first
2. **Request:** GET All Projects (Authenticated)
3. **Expected:** Full data including budget and financials

### Scenario 3: Create New Project
1. **Authentication:** Login with admin credentials
2. **Request:** POST Create Project
3. **Body:** Modify sample data (title, budget, dates, location)
4. **Expected:** 201 Created with project ID

### Scenario 4: Submit Contact Inquiry (Public)
1. **Authentication:** None required
2. **Request:** POST Submit Contact Inquiry
3. **Body:** Your name, email, subject, message
4. **Expected:** 201 Created

### Scenario 5: Filter Agricultural Data
1. **Request:** GET All Crop Production
2. **Query Params:**
   - `region_id=2` (Agusan del Norte)
   - `crop_name=Rice`
   - `fiscal_year=2025`
3. **Expected:** Filtered results

---

## Response Formats

### Success Responses
```json
{
    "id": 1,
    "title": "Project Title",
    "description": "Project description",
    ...
}
```

### Paginated Responses
```json
{
    "data": [...],
    "links": {
        "first": "...",
        "last": "...",
        "prev": null,
        "next": "..."
    },
    "meta": {
        "current_page": 1,
        "per_page": 15,
        "total": 50
    }
}
```

### Error Responses
```json
{
    "message": "Error message",
    "errors": {
        "field": ["Validation error"]
    }
}
```

---

## Common HTTP Status Codes

- **200 OK** - Request successful
- **201 Created** - Resource created successfully
- **204 No Content** - Resource deleted successfully
- **400 Bad Request** - Invalid request data
- **401 Unauthorized** - No authentication token
- **403 Forbidden** - Insufficient permissions
- **404 Not Found** - Resource not found
- **422 Unprocessable Entity** - Validation errors
- **500 Internal Server Error** - Server error

---

## Tips & Best Practices

### 1. Use Environments
Create different environments for:
- **Local** - http://localhost:8000
- **Staging** - https://staging.da-caraga.gov.ph
- **Production** - https://pmis.da-caraga.gov.ph

### 2. Test Authentication First
Always start by testing the **Login** endpoint to ensure:
- Database is seeded
- User credentials are correct
- Token generation works

### 3. Use Variables
Leverage variables for dynamic values:
- `{{base_url}}` - Base API URL
- `{{access_token}}` - Authentication token
- `{{project_id}}` - Store created resource IDs

### 4. Check Console
Use Postman Console (View → Show Postman Console) to debug:
- Request headers
- Response data
- Token values

### 5. Save Responses as Examples
After successful requests, save responses as examples for documentation.

---

## Troubleshooting

### Issue: "Unauthenticated" Error
**Solution:** Run the Login request first to get a fresh token

### Issue: "Token Expired"
**Solution:** Login again to get a new token

### Issue: "Validation Error"
**Solution:** Check request body matches the required format

### Issue: "Resource Not Found"
**Solution:** Verify the resource ID exists in the database

### Issue: "CORS Error"
**Solution:** Add CORS headers in Laravel backend

---

## Database Seeded Data Reference

### Available Users
- **admin** / Password123! (System Administrator)
- **mrodriguez** / Password123! (Regional Director)
- **rvillanueva** / Password123! (Rice Program Head)

### Available Regions
1. CARAGA (ID: 1)
2. Agusan del Norte (ID: 2)
3. Agusan del Sur (ID: 3)
4. Surigao del Norte (ID: 4)
5. Surigao del Sur (ID: 5)
6. Dinagat Islands (ID: 6)

### Available Project Types
1. Crop Development Program
2. Livestock Development Program
3. Fisheries and Aquaculture
4. Agricultural Infrastructure
5. High-Value Crops Development
... (10 total)

### Available Project Statuses
1. Planning
2. On Track
3. At Risk
4. Delayed
5. Completed
6. On Hold
7. Cancelled

---

## Next Steps

1. **Import Collection** - Import the JSON file into Postman
2. **Test Authentication** - Run Login request
3. **Explore Public Endpoints** - Test without authentication
4. **Test CRUD Operations** - Create, read, update, delete resources
5. **Test Filters** - Use query parameters to filter data
6. **Save Examples** - Save successful responses for documentation
7. **Create Test Scripts** - Add automated tests for critical flows

---

## Support

For issues or questions:
- Check Laravel logs: `storage/logs/laravel.log`
- Review API documentation: `SETUP_GUIDE.md`
- Check seeder data: `SEEDER_DOCUMENTATION.md`

---

**Collection Version:** 1.0
**Last Updated:** 2025-10-06
**Total Endpoints:** 52+
