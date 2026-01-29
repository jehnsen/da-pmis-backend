# DA-CARAGA PMIS Postman Collection Guide

## Import Instructions

1. Open Postman
2. Click **Import** button
3. Select the file: `PMIS_Postman_Collection.json`
4. The collection will be imported with all endpoints organized

## Collection Overview

The collection contains **60+ API endpoints** organized into 11 main categories:

### 1. Authentication (4 endpoints)
- **Register** - Register new user account
- **Login** - Get authentication token
- **Get Current User** - Get authenticated user details
- **Logout** - Invalidate token

### 2. Dashboard (6 endpoints)
- Overview Stats
- Budget Allocation
- Project Status Distribution
- National Performance
- Recent Updates
- Monthly Progress

### 3. Locations (9 endpoints)
- Get All Regions/Provinces/Municipalities
- Get by ID (Region/Province/Municipality)
- Location Hierarchy
- Search Locations
- Location Statistics

### 4. Projects (6 endpoints)
- Get All Projects (Public & Authenticated views)
- Get Project by ID
- Create Project
- Update Project
- Delete Project

### 5. Project Approval Workflow (8 endpoints)
- Get Pending Approvals
- Get Approval Statistics
- Get Projects by Approval Status
- Submit for Approval
- Approve Project
- Reject Project
- Request Changes
- Get Approval History
- **Get Project Audit Logs** (NEW)

### 6. Project Disbursements (10 endpoints)
- Get Disbursement Categories
- Get/Create/Update/Delete Disbursements
- Approve/Cancel Disbursement
- Financial Summary
- Disbursements by Category
- Monthly Spending

### 7. Project Files & Images (4 endpoints) **NEW**
- **Get Project Images** - With pagination and filtering
- **Upload Project Images** - Multiple upload support
- **Update Image Details** - Caption, type, display order
- **Delete Project Image** - Remove image and file

### 8. Progress Reports (8 endpoints)
- Get All Progress Reports
- Get Reports with Issues
- Get Progress Report Statistics
- Get Progress Report by ID
- Create Progress Report
- Update Progress Report
- Delete Progress Report
- Get Project Progress Timeline

### 9. Departments (9 endpoints)
- Get All Departments
- Get/Create/Update/Delete Department
- Get Department Reports
- Get Budget Utilization
- Get Department Monthly Progress
- Get Department KPI Summary

### 10. Agricultural Data (8 endpoints)
#### Crop Production (4 endpoints)
- Get All, Create, Update, Delete

#### Livestock Statistics (4 endpoints)
- Get All, Create, Update, Delete

### 11. News & Documents (11 endpoints)
#### News Updates (5 endpoints)
- Get All News, Get by ID, Create, Update, Delete

#### Documents (6 endpoints)
- Get All Documents, Get Featured, Get by ID, Download, Create, Update, Delete

### 12. User Management (7 endpoints)
- Get All Users
- Get User Statistics
- Get/Create/Update/Delete User
- Toggle User Status

### 13. Notifications (6 endpoints)
- Get All Notifications
- Get Unread Count
- Mark as Read
- Mark All as Read
- Delete Notification
- Clear All Notifications

### 14. User Engagement (8 endpoints)
#### Contact Inquiries (5 endpoints)
- Get All, Get by ID, Submit (Public), Update Status, Delete

#### Newsletter Subscriptions (5 endpoints)
- Get All, Get by ID, Subscribe (Public), Update, Delete

---

## New Features (2026-01-29)

### Project Audit Logs Endpoint
**GET** `/api/projects/{id}/audit-logs`

Track all changes made to a project with full audit history:
- **Pagination**: 1-100 items per page
- **Filter by action**: Search for specific actions (created, updated, deleted)
- **Filter by user**: See changes made by specific users
- **Date range filtering**: Filter by date_from and date_to
- **User details included**: Automatically includes username and full name

**Example Request:**
```
GET /api/projects/1/audit-logs?per_page=20&action=updated&date_from=2024-01-01
```

**Response Format:**
```json
{
  "success": true,
  "data": [
    {
      "id": 123,
      "user_id": 1,
      "action": "project_updated",
      "old_values": {...},
      "new_values": {...},
      "ip_address": "127.0.0.1",
      "created_at": "2024-03-15T10:30:00Z",
      "user": {
        "id": 1,
        "username": "admin",
        "full_name": "Juan Dela Cruz"
      }
    }
  ],
  "meta": {...},
  "links": {...}
}
```

### Project Images Endpoint (Enhanced)
**GET** `/api/projects/{id}/images`

Retrieve and manage project images with advanced filtering:
- **Pagination**: 1-100 images per page
- **Filter by type**: cover, progress, documentation, before, after, other
- **Ordered display**: Sorted by display_order automatically
- **Uploader details**: Includes who uploaded each image
- **Full URLs**: Returns complete image URLs ready for display

**Image Types:**
- `cover` - Cover/featured image for the project
- `progress` - Progress photos during implementation
- `documentation` - Documentation and reference images
- `before` - Before photos (baseline)
- `after` - After photos (completed)
- `other` - Other/miscellaneous images

**Upload Support:**
- Maximum 10 images per upload
- Max file size: 5MB per image
- Supported formats: JPEG, JPG, PNG, GIF, WebP
- Automatic file storage and URL generation

**Example Request:**
```
GET /api/projects/1/images?per_page=10&image_type=progress
```

**Example Upload:**
```
POST /api/projects/1/images
Content-Type: multipart/form-data

images[]: [file1.jpg, file2.png]
captions[]: ["Site preparation", "Foundation work"]
image_types[]: ["progress", "progress"]
```

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
- `per_page` - Pagination (1-100 items)
- `department_id` - Filter by department
- `project_status_id` - Filter by status
- `fiscal_year` - Filter by year
- `crop_name` - Filter by crop
- `livestock_type` - Filter by livestock type
- **`action`** - Filter audit logs by action type (NEW)
- **`user_id`** - Filter audit logs by user (NEW)
- **`date_from`** / **`date_to`** - Date range filtering (NEW)
- **`image_type`** - Filter images by type (NEW)

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

### Scenario 6: Track Project Changes (Audit Logs)
1. **Authentication:** Login with admin credentials
2. **Request:** GET Project Audit Logs
3. **URL:** `/api/projects/1/audit-logs`
4. **Query Params:**
   - `per_page=20`
   - `action=updated`
   - `date_from=2024-01-01`
5. **Expected:** Paginated audit log entries with user details

### Scenario 7: Upload and Manage Project Images
1. **Authentication:** Login first
2. **Request:** POST Upload Project Images
3. **URL:** `/api/projects/1/images`
4. **Body (form-data):**
   - `images[]` - Select multiple image files
   - `captions[]` - "Before construction", "Site preparation"
   - `image_types[]` - "before", "progress"
5. **Expected:** 201 Created with uploaded image details
6. **Follow-up:** GET Project Images to verify upload

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

**Collection Version:** 2.0
**Last Updated:** 2026-01-29
**Total Endpoints:** 100+

## Changelog

### Version 2.0 (2026-01-29)
- ✅ Added Project Audit Logs endpoint with filtering
- ✅ Enhanced Project Images endpoint with pagination and type filtering
- ✅ Added 11 main endpoint categories (was 7)
- ✅ Migrated from Passport to Sanctum authentication
- ✅ Added Project Team Members endpoints (CRUD)
- ✅ Added Project Milestones endpoints (CRUD + completion tracking)
- ✅ Added comprehensive Dashboard analytics endpoints
- ✅ Added Location management endpoints (hierarchy, search, statistics)
- ✅ Added Project Approval Workflow endpoints (multi-level approval)
- ✅ Added Project Disbursements & Financial tracking
- ✅ Added User Management endpoints
- ✅ Added Notifications system
- ✅ Total endpoints: 100+

### Version 1.0 (2025-10-06)
- Initial collection release
- Basic CRUD operations for Projects, Progress Reports, Agricultural Data
- Authentication with Laravel Passport
- Total endpoints: 52
