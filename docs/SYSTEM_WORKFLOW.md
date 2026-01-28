# DA-PMIS Complete System Workflow

## Overview
This document outlines the complete lifecycle workflow of agricultural projects in the DA-PMIS (Department of Agriculture - Performance Management Information System) from creation to completion.

---

## Workflow Stages

```
┌─────────────────────────────────────────────────────────────────────────┐
│                          PROJECT LIFECYCLE                               │
└─────────────────────────────────────────────────────────────────────────┘

 1. PROJECT CREATION          (Draft/Planning Phase)
 2. APPROVAL WORKFLOW         (Multi-level Approval)
 3. PROJECT IMPLEMENTATION    (Execution Phase)
 4. FINANCIAL MANAGEMENT      (Disbursements)
 5. PROGRESS MONITORING       (Reporting & Tracking)
 6. PROJECT COMPLETION        (Closure)
```

---

## 1. PROJECT CREATION & INITIALIZATION

### Status
- **Approval Status**: `draft`
- **Project Status**: Typically `Planning`

### Process
1. **User creates a new project** via `POST /api/projects`
   - Provide project details (title, description, budget, dates)
   - Assign to department
   - Select project type (Infrastructure, Livelihood, Research, etc.)
   - Set location (region, latitude/longitude)
   - Define budget allocation

2. **Configure project details**
   - Add team members with roles (Project Manager, Technical Lead, etc.)
   - Define project milestones with target dates
   - Upload project images/documents
   - Set public visibility (`is_public` flag)

3. **Edit and refine** via `PUT /api/projects/{id}`
   - Project remains in draft mode
   - Can be updated freely
   - No approval required for changes

### Endpoints
- `POST /api/projects` - Create project
- `PUT /api/projects/{id}` - Update project
- `GET /api/projects/{id}` - View project details
- `DELETE /api/projects/{id}` - Delete draft project

### Database Fields
```php
approval_status: 'draft'
project_status_id: 6 (Planning)
is_public: false
submitted_by: null
submitted_at: null
```

---

## 2. APPROVAL WORKFLOW (Multi-Level)

### Approval Levels
The system implements a **three-tier approval process** based on Philippine government structure:

```
draft → pending_municipal → pending_provincial → pending_regional → approved
                ↓                    ↓                    ↓
             rejected            rejected            rejected
```

### Stage 2.1: Submit for Approval
**Endpoint**: `POST /api/projects/{id}/submit-for-approval`

**Business Rules**:
- Only projects in `draft` or `rejected` status can be submitted
- Sets `submitted_by` to current user
- Sets `submitted_at` to current timestamp
- Changes `approval_status` to `pending_municipal`

**Notifications**:
- Municipal officers receive notification
- Project submitter receives confirmation

### Stage 2.2: Municipal Approval
**Endpoint**: `POST /api/projects/{id}/approve`

**Authorized Users**: Municipal Officers

**Actions**:
- **Approve**: Changes status to `pending_provincial`
- **Reject**: Changes status to `rejected` (requires reason)
- **Request Changes**: Returns to `draft` (requires comments)

**Data Logged** (ProjectApproval model):
```php
project_id: {id}
approver_id: {user_id}
approval_level: 'municipal'
action: 'approved' | 'rejected' | 'request_changes'
comments: 'Optional comments'
reason: 'Rejection reason if rejected'
action_taken_at: {timestamp}
```

### Stage 2.3: Provincial Approval
**Endpoint**: `POST /api/projects/{id}/approve`

**Authorized Users**: Provincial Officers

**Actions**:
- **Approve**: Changes status to `pending_regional`
- **Reject**: Changes status to `rejected`
- **Request Changes**: Returns to `draft`

### Stage 2.4: Regional Approval (Final)
**Endpoint**: `POST /api/projects/{id}/approve`

**Authorized Users**: Regional Officers

**Actions**:
- **Approve**: Changes status to `approved` ✅
  - Project is now ready for implementation
  - Budget is officially allocated
  - Can proceed with disbursements
- **Reject**: Changes status to `rejected`
- **Request Changes**: Returns to `draft`

### Approval Workflow Endpoints
- `POST /api/projects/{id}/submit-for-approval` - Submit project
- `POST /api/projects/{id}/approve` - Approve project
- `POST /api/projects/{id}/reject` - Reject project (requires reason)
- `POST /api/projects/{id}/request-changes` - Request changes
- `GET /api/projects/pending-approval` - List projects pending approval for current user
- `GET /api/projects/{id}/approval-history` - View approval trail
- `GET /api/projects/approval-statistics` - Approval statistics dashboard
- `GET /api/projects/by-approval-status?status=pending_municipal` - Filter by approval status

### Approval History Tracking
Every approval action is logged in the `project_approvals` table:
- Approver name and role
- Action taken (approved/rejected/request_changes)
- Timestamp
- Comments/reasons
- Status transitions (from → to)

---

## 3. PROJECT IMPLEMENTATION

### Status
- **Approval Status**: `approved` ✅
- **Project Status**: Changes based on progress

### Project Status Lifecycle
```
Planning → On Track → [Delayed] → [Critical] → Completed
              ↓
          On Hold (temporary suspension)
              ↓
         Under Review
```

### Status Definitions

| Status | Color | Description | When to Use |
|--------|-------|-------------|-------------|
| **Planning** | Blue (#007bff) | Initial planning phase | After approval, before execution |
| **On Track** | Green (#28a745) | Progressing as scheduled | Normal execution, meeting milestones |
| **Delayed** | Yellow (#ffc107) | Behind schedule | Missing deadlines, needs attention |
| **Critical** | Red (#dc3545) | Major challenges | Significant issues, urgent action needed |
| **On Hold** | Gray (#6c757d) | Temporarily suspended | Budget issues, policy decisions pending |
| **Under Review** | Purple (#6f42c1) | Under evaluation | Management review, audit in progress |
| **Completed** | Teal (#17a2b8) | Successfully finished | All deliverables met, project closed |

### Implementation Activities
1. **Team Assignment**
   - Assign project team members via pivot table `project_team_members`
   - Define roles (Project Manager, Engineer, Coordinator, etc.)
   - Team can view and update project details

2. **Milestone Tracking**
   - Define milestones with target dates
   - Mark milestones as completed
   - Track milestone completion rate

3. **Project Updates**
   - Update project status as work progresses
   - Upload progress photos/documents
   - Update location data if needed

### Endpoints
- `PUT /api/projects/{id}` - Update project details
- `GET /api/projects/{id}` - View project with team & milestones

---

## 4. FINANCIAL MANAGEMENT (Disbursements)

### Status
- **Project**: `approved`
- **Disbursement Status**: `pending` → `completed` | `cancelled`

### Disbursement Categories
```php
- Equipment & Tools
- Personnel/Labor
- Materials & Supplies
- Transportation
- Professional Services
- Utilities
- Training & Seminars
- Others
```

### Process Flow

#### Step 4.1: Create Disbursement Request
**Endpoint**: `POST /api/projects/{project}/disbursements`

**Required Fields**:
```json
{
  "amount": 50000.00,
  "category": "Equipment & Tools",
  "disbursement_date": "2026-02-15",
  "description": "Purchase of irrigation pumps",
  "receipt_number": "RCV-2026-001",
  "disbursed_to": "Supplier Name"
}
```

**Automatic Fields**:
- `status`: Set to `pending`
- `created_by`: Current user ID
- `project_id`: From URL parameter

#### Step 4.2: Approve Disbursement
**Endpoint**: `POST /api/projects/{project}/disbursements/{disbursement}/approve`

**Business Rules**:
- Only `pending` disbursements can be approved
- Sets `status` to `completed`
- Records `approved_by` user ID
- Records `approved_at` timestamp
- Updates project's total disbursed amount
- Cannot be modified after approval

**Budget Validation**:
- System calculates `total_disbursed` = sum of completed disbursements
- `remaining_budget` = project.budget - total_disbursed
- `utilization_rate` = (total_disbursed / budget) × 100
- Warns if utilization exceeds budget

#### Step 4.3: Cancel Disbursement (Optional)
**Endpoint**: `POST /api/projects/{project}/disbursements/{disbursement}/cancel`

**Business Rules**:
- Only `pending` disbursements can be cancelled
- Cannot cancel `completed` disbursements
- Sets `status` to `cancelled`

### Financial Reports & Analytics

#### Project Financial Summary
**Endpoint**: `GET /api/projects/{project}/financial-summary`

**Returns**:
```json
{
  "project_id": 1,
  "budget": 1000000.00,
  "total_disbursed": 650000.00,
  "remaining_budget": 350000.00,
  "utilization_rate": 65.00,
  "disbursements_by_status": {
    "completed": 8,
    "pending": 2,
    "cancelled": 1
  }
}
```

#### Disbursements by Category
**Endpoint**: `GET /api/projects/{project}/disbursements-by-category`

**Returns**:
```json
{
  "Equipment & Tools": 200000.00,
  "Materials & Supplies": 150000.00,
  "Personnel/Labor": 300000.00
}
```

#### Monthly Spending Analysis
**Endpoint**: `GET /api/projects/{project}/monthly-spending?year=2026`

**Returns**:
```json
{
  "year": 2026,
  "monthly_spending": {
    "January": 80000.00,
    "February": 120000.00,
    "March": 95000.00
  }
}
```

### Disbursement Endpoints Summary
- `GET /api/projects/{project}/disbursements` - List all disbursements
- `POST /api/projects/{project}/disbursements` - Create disbursement
- `GET /api/projects/{project}/disbursements/{id}` - View disbursement
- `PUT /api/projects/{project}/disbursements/{id}` - Update pending disbursement
- `DELETE /api/projects/{project}/disbursements/{id}` - Delete pending disbursement
- `POST /api/projects/{project}/disbursements/{id}/approve` - Approve disbursement
- `POST /api/projects/{project}/disbursements/{id}/cancel` - Cancel disbursement
- `GET /api/projects/{project}/financial-summary` - Financial overview
- `GET /api/projects/{project}/disbursements-by-category` - Category breakdown
- `GET /api/projects/{project}/monthly-spending` - Monthly spending report
- `GET /api/disbursement-categories` - Available categories

---

## 5. PROGRESS MONITORING & REPORTING

### Status
- **Project**: `approved`
- **Progress Status**: `on_track` | `at_risk` | `delayed` | `ahead`

### Progress Report Structure

#### Report Fields
```php
project_id          // Associated project
department_id       // Department
reporting_date      // Report date
report_period       // 'weekly' | 'monthly' | 'quarterly'
physical_progress   // 0-100 (percentage completion)
financial_progress  // 0-100 (budget utilization)
progress_status     // Auto-calculated or manual
accomplishments     // What was done
issues              // Problems encountered
risks               // Potential risks
recommendations     // Suggested actions
created_by          // Reporter ID
```

### Process Flow

#### Step 5.1: Create Progress Report
**Endpoint**: `POST /api/progress-reports`

**Example**:
```json
{
  "project_id": 1,
  "department_id": 3,
  "reporting_date": "2026-02-28",
  "report_period": "monthly",
  "physical_progress": 45.5,
  "financial_progress": 52.0,
  "accomplishments": "Completed irrigation system installation",
  "issues": "Delayed delivery of equipment",
  "risks": "Weather may affect timeline",
  "recommendations": "Engage backup supplier"
}
```

**Progress Status Auto-Calculation**:
The system automatically determines progress status based on variance between physical and financial progress:

```php
variance = physical_progress - financial_progress

if (variance <= -10):     status = 'delayed'   // Financial ahead, work behind
else if (variance < -5):  status = 'at_risk'   // Minor lag
else if (variance > 10):  status = 'ahead'     // Work ahead of spending
else:                     status = 'on_track'  // Balanced progress
```

#### Step 5.2: Monitor Progress Timeline
**Endpoint**: `GET /api/projects/{project}/progress-timeline?limit=12`

**Returns**:
```json
{
  "project_id": 1,
  "project_title": "Irrigation Modernization",
  "timeline": [
    {
      "id": 15,
      "date": "2026-01-31",
      "physical_progress": 30.0,
      "financial_progress": 28.0,
      "status": "on_track",
      "variance": 2.0
    },
    {
      "id": 16,
      "date": "2026-02-28",
      "physical_progress": 45.5,
      "financial_progress": 52.0,
      "status": "at_risk",
      "variance": -6.5
    }
  ],
  "current_physical_progress": 45.5,
  "current_financial_progress": 52.0,
  "current_status": "at_risk",
  "average_physical_progress": 37.8,
  "average_financial_progress": 40.0,
  "total_reports": 6
}
```

#### Step 5.3: Identify Problem Reports
**Endpoint**: `GET /api/progress-reports/with-issues`

Returns all reports that have:
- Non-empty `issues` field, OR
- Non-empty `risks` field

Used for management attention and intervention.

#### Step 5.4: Progress Statistics
**Endpoint**: `GET /api/progress-reports/statistics`

**Returns**:
```json
{
  "total_reports": 156,
  "by_status": {
    "on_track": 98,
    "at_risk": 32,
    "delayed": 18,
    "ahead": 8
  },
  "average_physical_progress": 62.3,
  "average_financial_progress": 58.7,
  "average_variance": 3.6,
  "recent_reports_count": 45,
  "reports_with_issues": 23,
  "reports_with_risks": 17
}
```

### Progress Metrics

Projects can also track custom metrics via `report_metrics` table:
- Beneficiaries served
- Hectares covered
- Training participants
- Equipment deployed
- etc.

### Progress Report Endpoints
- `GET /api/progress-reports` - List all reports (with filters)
- `POST /api/progress-reports` - Create report
- `GET /api/progress-reports/{id}` - View report details
- `PUT /api/progress-reports/{id}` - Update report
- `DELETE /api/progress-reports/{id}` - Delete report
- `GET /api/projects/{project}/progress-timeline` - Project timeline
- `GET /api/progress-reports/with-issues` - Problem reports
- `GET /api/progress-reports/statistics` - Progress statistics

---

## 6. PROJECT COMPLETION & CLOSURE

### Status
- **Approval Status**: `approved`
- **Project Status**: `Completed` ✅

### Completion Process

#### Step 6.1: Final Progress Report
Create a final progress report showing:
- `physical_progress`: 100%
- `financial_progress`: 100% (or actual utilization)
- Final accomplishments summary
- Lessons learned
- Final recommendations

#### Step 6.2: Close Remaining Disbursements
- Approve all pending disbursements, OR
- Cancel disbursements that are no longer needed

#### Step 6.3: Update Project Status
**Endpoint**: `PUT /api/projects/{id}`

```json
{
  "project_status_id": 4  // "Completed"
}
```

#### Step 6.4: Final Validation
System validates:
- ✅ All milestones completed
- ✅ Final progress report submitted
- ✅ No pending disbursements
- ✅ Budget fully utilized or documented variance
- ✅ All required documents uploaded

#### Step 6.5: Project Archive
Completed projects:
- Remain in database (soft delete not applied)
- Appear in historical reports
- Used for analytics and benchmarking
- Cannot be edited (read-only)
- Available for public viewing (if `is_public = true`)

---

## 7. AUDIT TRAIL & NOTIFICATIONS

### Audit Logging (Auditable Trait)
Every significant action is logged via the `Auditable` trait:

**Logged Events**:
- Project created/updated/deleted
- Approval actions (submit, approve, reject)
- Disbursement changes
- Progress report submissions
- Team member assignments

**Audit Log Fields**:
```php
auditable_type    // 'Project', 'ProjectDisbursement', etc.
auditable_id      // Record ID
user_id           // Who performed the action
event             // 'created', 'updated', 'deleted'
old_values        // JSON of old values
new_values        // JSON of new values
ip_address        // User's IP address
user_agent        // Browser/client info
created_at        // When action occurred
```

### Notification System

**Notification Triggers**:

1. **Project Submitted for Approval**
   - Notifies: Municipal officers
   - Message: "New project pending your approval: {project_title}"

2. **Project Approved**
   - Notifies: Project submitter, next-level approvers
   - Message: "Project approved at {level} level"

3. **Project Rejected**
   - Notifies: Project submitter, project team
   - Message: "Project rejected: {reason}"

4. **Changes Requested**
   - Notifies: Project submitter
   - Message: "Changes requested: {comments}"

5. **Disbursement Approved**
   - Notifies: Project manager, finance team
   - Message: "Disbursement of ₱{amount} approved"

6. **Progress Report Flagged**
   - Notifies: Department heads, regional officers
   - Message: "Project {title} status: {status}"

**Notification Endpoints**:
- `GET /api/notifications` - List user notifications
- `GET /api/notifications/unread-count` - Count unread
- `POST /api/notifications/{id}/mark-read` - Mark as read
- `POST /api/notifications/mark-all-read` - Mark all read
- `DELETE /api/notifications/clear-all` - Clear notifications

---

## 8. DASHBOARD & ANALYTICS

### Dashboard Endpoints

#### Overview Statistics
**Endpoint**: `GET /api/dashboard/overview`

```json
{
  "total_projects": 156,
  "total_investment": 125000000.00,
  "average_success_rate": 87.5,
  "active_projects": 89,
  "completed_projects": 45,
  "projects_at_risk": 12
}
```

#### Budget Allocation by Region
**Endpoint**: `GET /api/dashboard/budget-allocation`

```json
[
  {
    "region": "CARAGA",
    "allocated": 125000000.00,
    "utilized": 87500000.00,
    "utilization_rate": 70.0,
    "project_count": 156
  }
]
```

#### Project Status Distribution
**Endpoint**: `GET /api/dashboard/project-status-distribution`

```json
{
  "On Track": 98,
  "Delayed": 32,
  "Critical": 8,
  "Completed": 45,
  "On Hold": 5
}
```

#### National Performance Metrics
**Endpoint**: `GET /api/dashboard/national-performance`

```json
{
  "rice_production_mt": 125000.50,
  "corn_production_mt": 85000.30,
  "fish_production_mt": 45000.00,
  "livestock_heads": 250000
}
```

#### Recent Project Updates
**Endpoint**: `GET /api/dashboard/recent-updates`

Returns recent projects with latest progress reports and status changes.

#### Monthly Progress by Department
**Endpoint**: `GET /api/dashboard/monthly-progress`

Tracks monthly performance across all departments.

---

## 9. ROLE-BASED ACCESS CONTROL (RBAC)

### User Roles & Permissions

| Role | Create Project | Submit for Approval | Approve (Municipal) | Approve (Provincial) | Approve (Regional) | Manage Disbursements | View All Projects |
|------|----------------|---------------------|---------------------|----------------------|--------------------|---------------------|-------------------|
| **Admin** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| **Regional Officer** | ✅ | ✅ | ❌ | ❌ | ✅ | ✅ | ✅ |
| **Provincial Officer** | ✅ | ✅ | ❌ | ✅ | ❌ | ✅ | Region-wide |
| **Municipal Officer** | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ | Municipality-wide |
| **Project Manager** | ✅ | ✅ | ❌ | ❌ | ❌ | View only | Own projects |
| **Public User** | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | Public projects only |

### Data Visibility Rules

**Public Users** (`is_public = true` filter):
- Can view: Title, Description, Status, Location
- Cannot view: Budget, Team Members, Disbursements, Approval History

**Authenticated Users**:
- Can view all project details
- Can view disbursements for authorized projects
- Can view approval history

**Project Resources** (app/Http/Resources/ProjectResource.php):
```php
public function toArray($request)
{
    $data = [
        'id' => $this->id,
        'title' => $this->title,
        'description' => $this->description,
        'status' => $this->projectStatus->name,
    ];

    if ($this->shouldShowInternal($request)) {
        $data['budget'] = $this->budget;
        $data['team_members'] = $this->teamMembers;
        $data['disbursements'] = $this->disbursements;
        $data['approval_status'] = $this->approval_status;
    }

    return $data;
}
```

---

## 10. INTEGRATION POINTS

### External Systems Integration

1. **Geographic Information System (GIS)**
   - Location data (`location_lat`, `location_lng`)
   - Location hierarchy (Region → Province → Municipality)

2. **Financial Management System**
   - Disbursement tracking
   - Budget utilization reports
   - Financial summaries

3. **Document Management**
   - Upload project documents
   - Track downloads
   - Featured documents

4. **Agricultural Statistics**
   - Crop production data (`crop_productions` table)
   - Livestock statistics (`livestock_statistics` table)
   - Integration with national statistics

5. **Public Portal**
   - News updates
   - Newsletter subscriptions
   - Contact inquiries
   - Public project viewing

---

## 11. COMPLETE WORKFLOW DIAGRAM

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                                                                              │
│  STAGE 1: PROJECT CREATION                                                   │
│  ─────────────────────────                                                   │
│                                                                              │
│  [User] ──> Create Project ──> Draft Status ──> Add Details                 │
│                                      │                                       │
│                                      ├─> Team Members                        │
│                                      ├─> Milestones                          │
│                                      ├─> Documents                           │
│                                      └─> Budget                              │
│                                                                              │
└────────────────────────────┬─────────────────────────────────────────────────┘
                             │
                             ▼ Submit for Approval
                             │
┌────────────────────────────┴─────────────────────────────────────────────────┐
│                                                                              │
│  STAGE 2: APPROVAL WORKFLOW (Multi-Level)                                    │
│  ─────────────────────────────────────────                                   │
│                                                                              │
│  ┌─────────────────┐       ┌──────────────────┐       ┌─────────────────┐  │
│  │   MUNICIPAL     │       │   PROVINCIAL     │       │    REGIONAL     │  │
│  │    APPROVAL     │──────>│    APPROVAL      │──────>│    APPROVAL     │  │
│  │  (Level 1)      │       │   (Level 2)      │       │   (Level 3)     │  │
│  └─────────────────┘       └──────────────────┘       └─────────────────┘  │
│          │                          │                          │            │
│          │ Approve                  │ Approve                  │ Approve    │
│          │ Reject                   │ Reject                   │ Reject     │
│          │ Request Changes          │ Request Changes          │ Req Changes│
│          │                          │                          │            │
│          └──────────┬───────────────┴─────────────────────────┬────────────┘
│                     │                                         │
│                     ▼ Rejected/Changes                        ▼ APPROVED ✅
│               Back to Draft                                   │
│                                                              │
└──────────────────────────────────────────────────────────────┼──────────────┘
                                                                │
┌───────────────────────────────────────────────────────────────┴──────────────┐
│                                                                              │
│  STAGE 3: PROJECT IMPLEMENTATION                                             │
│  ──────────────────────────────                                              │
│                                                                              │
│  Project Status Progression:                                                 │
│                                                                              │
│  Planning ──> On Track ──> [Delayed] ──> [Critical] ──> Completed           │
│                   │                                           ▲              │
│                   └──> On Hold ──> Under Review ─────────────┘              │
│                                                                              │
│  Parallel Activities:                                                        │
│  • Team executes milestones                                                  │
│  • Status updates as work progresses                                         │
│  • Documents and photos uploaded                                             │
│                                                                              │
└────────────────┬────────────────────────┬──────────────────────┬─────────────┘
                 │                        │                      │
                 ▼                        ▼                      ▼
┌────────────────────────┐  ┌─────────────────────────┐  ┌──────────────────────┐
│                        │  │                         │  │                      │
│  STAGE 4: FINANCIAL    │  │  STAGE 5: PROGRESS      │  │  STAGE 6: AUDIT &    │
│  MANAGEMENT            │  │  MONITORING             │  │  NOTIFICATIONS       │
│  ─────────────────     │  │  ──────────────         │  │  ─────────────────   │
│                        │  │                         │  │                      │
│  1. Create            │  │  1. Create Progress     │  │  • Approval actions  │
│     Disbursement      │  │     Report              │  │  • Disbursements     │
│     ↓                 │  │     ↓                   │  │  • Status changes    │
│  2. Status: Pending   │  │  2. Track Metrics:      │  │  • Team updates      │
│     ↓                 │  │     - Physical Progress │  │  • All logged to     │
│  3. Approve           │  │     - Financial Progress│  │    audit_logs table  │
│     Disbursement      │  │     - Issues & Risks    │  │                      │
│     ↓                 │  │     ↓                   │  │  • Notifications     │
│  4. Status: Completed │  │  3. Auto-calculate:     │  │    sent to relevant  │
│     ↓                 │  │     - on_track          │  │    stakeholders      │
│  5. Update Budget     │  │     - at_risk           │  │                      │
│     Utilization       │  │     - delayed           │  │                      │
│                        │  │     - ahead             │  │                      │
│  Repeat for each      │  │     ↓                   │  │                      │
│  disbursement         │  │  4. Flag issues for     │  │                      │
│                        │  │     management          │  │                      │
│                        │  │                         │  │                      │
│  Reports:             │  │  Reports:               │  │                      │
│  • Financial Summary  │  │  • Progress Timeline    │  │                      │
│  • By Category        │  │  • Reports with Issues  │  │                      │
│  • Monthly Spending   │  │  • Statistics           │  │                      │
│                        │  │                         │  │                      │
└────────────────────────┘  └─────────────────────────┘  └──────────────────────┘
                 │                        │
                 └────────────┬───────────┘
                              │
                              ▼ When 100% complete
                              │
┌─────────────────────────────┴────────────────────────────────────────────────┐
│                                                                              │
│  STAGE 7: PROJECT COMPLETION & CLOSURE                                       │
│  ──────────────────────────────────────                                      │
│                                                                              │
│  1. Final Progress Report (100% physical & financial)                        │
│  2. Close all disbursements (approved or cancelled)                          │
│  3. Update project_status to "Completed"                                     │
│  4. System validation:                                                       │
│     ✅ All milestones completed                                              │
│     ✅ Final report submitted                                                │
│     ✅ No pending disbursements                                              │
│     ✅ Budget documented                                                     │
│  5. Project archived (read-only, available for analytics)                    │
│                                                                              │
│  Project now visible in:                                                     │
│  • Dashboard statistics                                                      │
│  • Historical reports                                                        │
│  • Public portal (if is_public = true)                                       │
│  • Benchmarking data                                                         │
│                                                                              │
└──────────────────────────────────────────────────────────────────────────────┘
```

---

## 12. KEY BUSINESS RULES SUMMARY

### Project Creation
- ✅ Any authenticated user can create projects
- ✅ Projects start in `draft` status
- ✅ Draft projects can be freely edited
- ✅ No approval required for drafts

### Approval Process
- ✅ Must go through all three levels (Municipal → Provincial → Regional)
- ✅ Any level can reject or request changes
- ✅ Rejection returns project to `rejected` status
- ✅ Request changes returns project to `draft` for revision
- ✅ Each approval logged with timestamp, approver, and comments

### Financial Management
- ✅ Disbursements only allowed for `approved` projects
- ✅ Pending disbursements can be edited or deleted
- ✅ Completed disbursements are immutable
- ✅ Cannot exceed project budget (warning issued)
- ✅ All disbursements require approval

### Progress Monitoring
- ✅ Progress reports can be submitted anytime during implementation
- ✅ Physical vs financial progress variance auto-calculated
- ✅ Reports with issues automatically flagged
- ✅ Custom metrics can be tracked per report

### Project Completion
- ✅ Requires final progress report
- ✅ All milestones should be completed
- ✅ No pending disbursements
- ✅ Project becomes read-only after completion

---

## 13. API ENDPOINT REFERENCE

### Quick Reference by Stage

| Stage | Endpoint | Method | Description |
|-------|----------|--------|-------------|
| **1. Creation** | `/api/projects` | POST | Create project |
| | `/api/projects/{id}` | PUT | Update draft |
| **2. Approval** | `/api/projects/{id}/submit-for-approval` | POST | Submit for approval |
| | `/api/projects/{id}/approve` | POST | Approve project |
| | `/api/projects/{id}/reject` | POST | Reject project |
| | `/api/projects/{id}/request-changes` | POST | Request changes |
| | `/api/projects/pending-approval` | GET | My pending approvals |
| | `/api/projects/{id}/approval-history` | GET | Approval trail |
| **3. Implementation** | `/api/projects/{id}` | PUT | Update status |
| **4. Disbursements** | `/api/projects/{id}/disbursements` | POST | Create disbursement |
| | `/api/projects/{id}/disbursements/{id}/approve` | POST | Approve disbursement |
| | `/api/projects/{id}/financial-summary` | GET | Financial report |
| **5. Progress** | `/api/progress-reports` | POST | Create report |
| | `/api/projects/{id}/progress-timeline` | GET | Progress timeline |
| | `/api/progress-reports/with-issues` | GET | Flagged reports |
| **6. Completion** | `/api/projects/{id}` | PUT | Mark completed |

---

## 14. DATA MODEL RELATIONSHIPS

```
projects
├── department (belongsTo)
├── projectType (belongsTo)
├── projectStatus (belongsTo)
├── submitter (belongsTo User)
├── teamMembers (belongsToMany User)
├── milestones (hasMany)
├── approvals (hasMany) ──> project_approvals
│   ├── approver (belongsTo User)
│   └── approval_level: municipal/provincial/regional
├── disbursements (hasMany) ──> project_disbursements
│   ├── createdBy (belongsTo User)
│   ├── approvedBy (belongsTo User)
│   └── status: pending/completed/cancelled
├── progressReports (hasMany) ──> progress_reports
│   ├── creator (belongsTo User)
│   └── metrics (hasMany) ──> report_metrics
├── images (hasMany)
└── fundingDistributions (hasMany)
```

---

## 15. COMMON SCENARIOS & WORKFLOWS

### Scenario A: New Infrastructure Project
1. Municipal Engineer creates project (draft)
2. Adds team members and milestones
3. Submits for approval (pending_municipal)
4. Municipal Officer approves (pending_provincial)
5. Provincial Officer approves (pending_regional)
6. Regional Director approves (approved) ✅
7. Project Manager changes status to "On Track"
8. Finance creates first disbursement (pending)
9. Finance Officer approves disbursement (completed)
10. Monthly progress reports submitted
11. Project completed after 12 months
12. Final report submitted
13. Status updated to "Completed"

### Scenario B: Project Rejection
1. Project submitted for approval (pending_municipal)
2. Municipal Officer reviews and finds budget issues
3. Municipal Officer rejects with reason
4. Notification sent to submitter
5. Submitter reviews rejection reason
6. Submitter updates budget and details
7. Submitter resubmits for approval
8. Approval workflow resumes

### Scenario C: Project Delayed
1. Project running for 6 months, on track
2. Monthly progress report shows physical: 40%, financial: 55%
3. System flags as "at_risk" (variance: -15%)
4. Next month: physical: 42%, financial: 60%
5. System flags as "delayed"
6. Report includes issues: "Equipment delivery delayed"
7. Management receives notification
8. Project status manually updated to "Delayed"
9. Corrective actions taken
10. Subsequent reports show improvement
11. Status returns to "On Track"

---

## 16. NOTIFICATIONS MATRIX

| Event | Notified Users | Notification Type |
|-------|----------------|-------------------|
| Project Submitted | Municipal Officers | In-app + Email |
| Municipal Approval | Provincial Officers | In-app + Email |
| Provincial Approval | Regional Officers | In-app + Email |
| Regional Approval (Final) | Project Team | In-app + Email |
| Project Rejected | Submitter, Team | In-app + Email |
| Changes Requested | Submitter | In-app + Email |
| Disbursement Created | Finance Officer | In-app |
| Disbursement Approved | Project Manager | In-app |
| Progress Report Flagged | Department Head, Regional Officers | In-app + Email |
| Project Delayed | Management Team | In-app + Email |
| Project Completed | All Stakeholders | In-app + Email |

---

## 17. REPORTING & ANALYTICS

### Available Reports

1. **Dashboard Overview**
   - Total projects, investment, success rate
   - Active vs completed projects
   - Projects at risk

2. **Budget Reports**
   - Allocation by region
   - Utilization rates
   - Disbursement categories
   - Monthly spending trends

3. **Progress Reports**
   - Progress timeline
   - Variance analysis
   - Reports with issues
   - Department performance

4. **Approval Statistics**
   - Projects by approval status
   - Average approval time
   - Rejection rates
   - Approval funnel

5. **Department Reports**
   - Budget utilization by department
   - Monthly progress by department
   - KPI summary
   - Department rankings

6. **Agricultural Statistics**
   - Crop production by project
   - Livestock statistics
   - Funding distribution
   - National performance metrics

---

## CONCLUSION

The DA-PMIS system provides a comprehensive workflow for managing agricultural projects from conception to completion. The system ensures:

✅ **Accountability** - Multi-level approval and audit logging
✅ **Transparency** - Public access to project information
✅ **Efficiency** - Streamlined approval and financial processes
✅ **Monitoring** - Real-time progress tracking and reporting
✅ **Compliance** - Built-in validations and notifications
✅ **Analytics** - Comprehensive dashboards and reports

The workflow supports the Department of Agriculture's mission to effectively manage agricultural development projects across the CARAGA Region (Region XIII), Philippines.

---

**Document Version**: 1.0
**Last Updated**: 2026-01-28
**System Version**: DA-PMIS v2.0
**Region**: CARAGA (Region XIII), Philippines
**Department**: Department of Agriculture
