# Team Assignment & Milestone Tracking API

## Overview

This document describes the newly implemented features for Project Team Management and Milestone Tracking in the DA-PMIS backend.

## Features Implemented

### 1. Team Assignment
- Assign users to projects with specific roles
- View all team members for a project
- Update team member roles
- Remove team members from projects
- Prevent duplicate team member assignments

### 2. Milestone Tracking
- Create project milestones with target dates
- Track milestone status (pending, in_progress, completed, cancelled)
- Mark milestones as completed (auto-sets completion_date)
- Calculate milestone completion rate per project
- View milestone timeline ordered by target date

---

## API Endpoints

### Project Team Members

All team member endpoints require authentication (`auth:sanctum` middleware).

#### List Team Members
```
GET /api/projects/{project}/team-members
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "user": {
        "id": 5,
        "username": "jsmith",
        "full_name": "John Smith",
        "email": "jsmith@da.gov.ph"
      },
      "role": "Project Manager",
      "created_at": "2026-01-28T10:30:00.000000Z",
      "updated_at": "2026-01-28T10:30:00.000000Z"
    }
  ]
}
```

#### Add Team Member
```
POST /api/projects/{project}/team-members
```

**Request Body:**
```json
{
  "user_id": 5,
  "role": "Project Manager"
}
```

**Validation Rules:**
- `user_id`: required, must exist in users table
- `role`: optional, string, max 255 characters

**Common Roles:**
- Project Manager
- Engineer
- Coordinator
- Technical Lead
- Field Officer
- Administrator

**Response:** 201 Created
```json
{
  "data": {
    "id": 1,
    "user": {
      "id": 5,
      "username": "jsmith",
      "full_name": "John Smith"
    },
    "role": "Project Manager",
    "created_at": "2026-01-28T10:30:00.000000Z",
    "updated_at": "2026-01-28T10:30:00.000000Z"
  }
}
```

**Error Cases:**
- 422: User is already a team member
- 404: Project not found
- 422: User does not exist

#### View Team Member
```
GET /api/projects/{project}/team-members/{teamMember}
```

**Response:** 200 OK (same structure as add response)

#### Update Team Member Role
```
PUT /api/projects/{project}/team-members/{teamMember}
```

**Request Body:**
```json
{
  "role": "Senior Engineer"
}
```

**Validation Rules:**
- `role`: required, string, max 255 characters

**Response:** 200 OK

#### Remove Team Member
```
DELETE /api/projects/{project}/team-members/{teamMember}
```

**Response:** 200 OK
```json
{
  "message": "Team member removed successfully"
}
```

---

### Project Milestones

All milestone endpoints require authentication (`auth:sanctum` middleware).

#### List Milestones
```
GET /api/projects/{project}/milestones
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Site Preparation",
      "description": "Clear and prepare the project site",
      "target_date": "2026-02-15",
      "completion_date": null,
      "status": "in_progress",
      "created_at": "2026-01-28T10:30:00.000000Z",
      "updated_at": "2026-01-28T10:30:00.000000Z"
    }
  ],
  "meta": {
    "completion_rate": 33.33
  }
}
```

**Note:** Milestones are ordered by `target_date` ascending.

#### Create Milestone
```
POST /api/projects/{project}/milestones
```

**Request Body:**
```json
{
  "title": "Site Preparation",
  "description": "Clear and prepare the project site",
  "target_date": "2026-02-15",
  "status": "pending"
}
```

**Validation Rules:**
- `title`: required, string, max 255 characters
- `description`: optional, string
- `target_date`: optional, valid date (format: YYYY-MM-DD)
- `status`: optional, one of: `pending`, `in_progress`, `completed`, `cancelled`

**Default Values:**
- `status`: "pending" (set in database migration)

**Response:** 201 Created

#### View Milestone
```
GET /api/projects/{project}/milestones/{milestone}
```

**Response:** 200 OK

#### Update Milestone
```
PUT /api/projects/{project}/milestones/{milestone}
```

**Request Body:**
```json
{
  "title": "Updated Title",
  "description": "Updated description",
  "target_date": "2026-03-01",
  "status": "in_progress"
}
```

**Validation Rules:**
- `title`: optional (but required if provided), string, max 255 characters
- `description`: optional, string
- `target_date`: optional, valid date
- `completion_date`: optional, valid date
- `status`: optional, one of: `pending`, `in_progress`, `completed`, `cancelled`

**Response:** 200 OK

#### Delete Milestone
```
DELETE /api/projects/{project}/milestones/{milestone}
```

**Response:** 200 OK
```json
{
  "message": "Milestone deleted successfully"
}
```

#### Mark Milestone as Completed
```
POST /api/projects/{project}/milestones/{milestone}/complete
```

**No Request Body Required**

This endpoint automatically:
- Sets `status` to "completed"
- Sets `completion_date` to current timestamp

**Response:** 200 OK
```json
{
  "data": {
    "id": 1,
    "title": "Site Preparation",
    "description": "Clear and prepare the project site",
    "target_date": "2026-02-15",
    "completion_date": "2026-01-28",
    "status": "completed",
    "created_at": "2026-01-28T10:30:00.000000Z",
    "updated_at": "2026-01-28T11:45:00.000000Z"
  }
}
```

---

## Milestone Completion Rate Calculation

The completion rate is automatically calculated when listing milestones:

**Formula:**
```
completion_rate = (completed_milestones / total_milestones) × 100
```

**Behavior:**
- Returns `0` if project has no milestones
- Rounded to 2 decimal places
- Only counts milestones with `status = 'completed'`

**Example:**
- Total milestones: 6
- Completed: 2
- Completion rate: 33.33%

---

## Database Schema

### project_team_members Table
```sql
CREATE TABLE project_team_members (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    role VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY unique_project_user (project_id, user_id),
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### project_milestones Table
```sql
CREATE TABLE project_milestones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    target_date DATE NULL,
    completion_date DATE NULL,
    status VARCHAR(255) DEFAULT 'pending',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);
```

---

## Example Usage Workflow

### 1. Create a Project and Add Team
```bash
# Create project
POST /api/projects
{
  "title": "Rice Production Enhancement",
  "department_id": 1,
  ...
}

# Add team members
POST /api/projects/1/team-members
{ "user_id": 5, "role": "Project Manager" }

POST /api/projects/1/team-members
{ "user_id": 7, "role": "Field Engineer" }
```

### 2. Define Project Milestones
```bash
POST /api/projects/1/milestones
{
  "title": "Site Survey",
  "target_date": "2026-02-01"
}

POST /api/projects/1/milestones
{
  "title": "Equipment Procurement",
  "target_date": "2026-02-15"
}

POST /api/projects/1/milestones
{
  "title": "Training Completion",
  "target_date": "2026-03-01"
}
```

### 3. Track Progress
```bash
# Mark milestone as completed
POST /api/projects/1/milestones/1/complete

# Check completion rate
GET /api/projects/1/milestones
# Returns: completion_rate: 33.33
```

### 4. View Project with Team and Milestones
```bash
GET /api/projects/1
# Returns project with teamMembers and milestones relations loaded
```

---

## Architecture

### Files Created

**Interfaces:**
- [app/Interfaces/ProjectTeamMemberRepositoryInterface.php](../app/Interfaces/ProjectTeamMemberRepositoryInterface.php)
- [app/Interfaces/ProjectMilestoneRepositoryInterface.php](../app/Interfaces/ProjectMilestoneRepositoryInterface.php)

**Repositories:**
- [app/Repositories/ProjectTeamMemberRepository.php](../app/Repositories/ProjectTeamMemberRepository.php)
- [app/Repositories/ProjectMilestoneRepository.php](../app/Repositories/ProjectMilestoneRepository.php)

**Services:**
- [app/Services/ProjectTeamMemberService.php](../app/Services/ProjectTeamMemberService.php)
- [app/Services/ProjectMilestoneService.php](../app/Services/ProjectMilestoneService.php)

**Controllers:**
- [app/Http/Controllers/ProjectTeamMemberController.php](../app/Http/Controllers/ProjectTeamMemberController.php)
- [app/Http/Controllers/ProjectMilestoneController.php](../app/Http/Controllers/ProjectMilestoneController.php)

**Form Requests:**
- [app/Http/Requests/ProjectTeamMember/StoreProjectTeamMemberRequest.php](../app/Http/Requests/ProjectTeamMember/StoreProjectTeamMemberRequest.php)
- [app/Http/Requests/ProjectTeamMember/UpdateProjectTeamMemberRequest.php](../app/Http/Requests/ProjectTeamMember/UpdateProjectTeamMemberRequest.php)
- [app/Http/Requests/ProjectMilestone/StoreProjectMilestoneRequest.php](../app/Http/Requests/ProjectMilestone/StoreProjectMilestoneRequest.php)
- [app/Http/Requests/ProjectMilestone/UpdateProjectMilestoneRequest.php](../app/Http/Requests/ProjectMilestone/UpdateProjectMilestoneRequest.php)

**Service Providers:**
- [app/Providers/ProjectTeamMemberServiceProvider.php](../app/Providers/ProjectTeamMemberServiceProvider.php)
- [app/Providers/ProjectMilestoneServiceProvider.php](../app/Providers/ProjectMilestoneServiceProvider.php)

**Existing Files (Already Implemented):**
- [app/Models/ProjectTeamMember.php](../app/Models/ProjectTeamMember.php)
- [app/Models/ProjectMilestone.php](../app/Models/ProjectMilestone.php)
- [app/Http/Resources/ProjectTeamMemberResource.php](../app/Http/Resources/ProjectTeamMemberResource.php)
- [app/Http/Resources/ProjectMilestoneResource.php](../app/Http/Resources/ProjectMilestoneResource.php)
- [database/migrations/2024_01_01_000028_create_project_team_members_table.php](../database/migrations/2024_01_01_000028_create_project_team_members_table.php)
- [database/migrations/2024_01_01_000029_create_project_milestones_table.php](../database/migrations/2024_01_01_000029_create_project_milestones_table.php)

---

## Authentication

All endpoints require the `auth:sanctum` middleware. Include the bearer token in requests:

```bash
Authorization: Bearer {your-token}
```

---

## Error Handling

All endpoints return standard Laravel JSON error responses:

**404 Not Found:**
```json
{
  "message": "Not found"
}
```

**422 Validation Error:**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "user_id": ["User is already a team member of this project"]
  }
}
```

**500 Server Error:**
```json
{
  "message": "Failed to create milestone",
  "error": "Detailed error message"
}
```

---

## Integration with Existing Features

### ProjectResource
Team members and milestones are automatically loaded when viewing project details:

```php
// app/Http/Resources/ProjectResource.php
if ($this->shouldShowInternal()) {
    $data['team_members'] = ProjectTeamMemberResource::collection(
        $this->whenLoaded('teamMembers')
    );
    $data['milestones'] = ProjectMilestoneResource::collection(
        $this->whenLoaded('milestones')
    );
}
```

### Project Model Relationships
```php
// app/Models/Project.php
public function teamMembers(): BelongsToMany
public function projectTeamMembers(): HasMany
public function milestones(): HasMany
```

---

## Next Steps / Future Enhancements

1. **Email Notifications:**
   - Notify users when added to a project team
   - Alert team when milestones are approaching deadline
   - Notify on milestone completion

2. **Permissions:**
   - Only project managers can add/remove team members
   - Only team members can update milestones

3. **Statistics:**
   - Average milestone completion time
   - Team member workload (projects per user)
   - Overdue milestones report

4. **Bulk Operations:**
   - Add multiple team members at once
   - Import milestones from template

5. **Dashboard Integration:**
   - Show milestone completion rates across all projects
   - Team member assignment statistics

---

## Testing

To test the implementation:

```bash
# View routes
php artisan route:list --path=team-members
php artisan route:list --path=milestones

# Run database migrations (already done)
php artisan migrate

# Optional: Create test data via seeders
# (Seeders not included in this implementation)
```

---

**Version:** 1.0
**Date:** 2026-01-28
**Author:** DA-PMIS Development Team
