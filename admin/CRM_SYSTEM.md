# CRM System Documentation

## Overview

The admin panel now includes a complete CRM (Customer Relationship Management) system integrated directly into the client management workflow.

## Features Implemented

### 1. **Activity Timeline**

Track all interactions with clients:

- **Activity Types**: Phone calls, emails, meetings, notes, tasks, quote sent, invoice sent, payment received, other
- **Details**: Subject, description, date/time, duration
- **Auto-logging**: Automatically updates `last_contact_date` when activities are logged
- **Delete**: Remove activities as needed

**API Endpoint**: `admin/api/activities.php`
**API Endpoint**: `admin/api/activities.php`

- GET: Retrieve activities (filter by client_id, activity_type)
- POST: Create new activity
- DELETE: Remove activity

### 2. **Client Notes**

Keep important information about clients:

- **Rich text notes** with timestamps
- **Importance flagging** (⭐ Important)
- **Visual highlighting** for important notes
- **Sorted display** (important notes first, then by date)

**API Endpoint**: `admin/api/notes.php`

- GET: Retrieve notes (filter by client_id)
- POST: Create new note
- DELETE: Remove note

### 3. **Task Management**

Complete to-do system with client linking:

- **Priority Levels**: Low, Medium, High, Urgent (color-coded)
- **Status Tracking**: Pending, In Progress, Completed, Cancelled
- **Due Dates**: Set deadlines and track overdue tasks
- **Client Association**: Link tasks to specific clients or create general tasks
- **Statistics Dashboard**: View pending, overdue, and urgent task counts
- **Filtering**: Filter tasks by status

**API Endpoint**: `admin/api/tasks.php`

- GET: Retrieve tasks (filter by client_id, status, priority)
- POST: Create new task
- PUT: Update task (including mark as complete)
- DELETE: Remove task

### 4. **CRM Dashboard Analytics**

Comprehensive statistics endpoint:

- Total clients and revenue
- Pipeline value and probability
- Task statistics (total, pending, overdue, completed)
- Follow-ups due count
- Lead source breakdown
- Status distribution
- Recent activities (last 10)
- Upcoming tasks (next 7 days)

**API Endpoint**: `admin/api/crm_dashboard.php`

- GET: Retrieve all CRM analytics

## Database Schema

### New Tables Created

#### `activities`

```sql
- id (INT, PRIMARY KEY)
- client_id (INT, FOREIGN KEY → quotes.id)
- activity_type (ENUM: call, email, meeting, note, task, quote_sent, invoice_sent, payment_received, other)
- subject (VARCHAR 255)
- description (TEXT)
- activity_date (DATETIME)
- duration_minutes (INT)
- created_at (TIMESTAMP)
- created_by (INT)
```

#### `tasks`

```sql
- id (INT, PRIMARY KEY)
- title (VARCHAR 255)
- description (TEXT)
- client_id (INT, FOREIGN KEY → quotes.id, nullable)
- assigned_to (INT)
- priority (ENUM: low, medium, high, urgent)
- status (ENUM: pending, in_progress, completed, cancelled)
- due_date (DATE)
- completed_at (DATETIME)
- created_at, updated_at (TIMESTAMP)
- created_by (INT)
```

#### `client_notes`

```sql
- id (INT, PRIMARY KEY)
- client_id (INT, FOREIGN KEY → quotes.id)
- note_text (TEXT)
- is_important (BOOLEAN)
- created_at, updated_at (TIMESTAMP)
- created_by (INT)
```

#### `client_tags`

```sql
- id (INT, PRIMARY KEY)
- client_id (INT, FOREIGN KEY → quotes.id)
- tag_name (VARCHAR 50)
- created_at (TIMESTAMP)
- UNIQUE constraint on (client_id, tag_name)
```

### Enhanced `quotes` Table

New columns added:

- `lead_source` (VARCHAR 100) - Where the client came from
- `expected_value` (DECIMAL 10,2) - Expected revenue
- `probability` (INT) - Probability of closing (0-100%)
- `next_follow_up` (DATE) - Next scheduled follow-up
- `last_contact_date` (DATE) - Last interaction date
- `client_tags` (TEXT) - Comma-separated tags

## User Interface

### Client Modal - Tabbed Interface

The client modal now features 4 tabs:

1. **📋 Details & Billing** (default)
   - Client information
   - Services & pricing
   - Payment tracking
   - Address details

2. **📅 Activity Timeline**
   - View all client interactions
   - Log new activities
   - Color-coded by activity type
   - Show duration and timestamps

3. **📝 Notes**
   - View all client notes
   - Add new notes
   - Flag important notes
   - Quick delete

4. **✅ Tasks**
   - View client-specific tasks
   - Create tasks for this client
   - Mark tasks complete
   - Priority and due date display

### Tasks & To-Do Section

New sidebar menu item with:

- **Statistics Cards**: Pending, Overdue, Urgent counts
- **Filter Dropdown**: All, Pending, In Progress, Completed
- **Task List**: Priority-coded, status badges, due dates
- **Task Modal**: Full CRUD with client selection

## CSS Styling

### Tab System

- `.crm-tabs` - Tab navigation container
- `.crm-tab` - Individual tab button
- `.crm-tab.active` - Active tab highlighting
- `.client-tab-content` - Tab content container
- `.client-tab-content.active` - Visible tab content

### Activity Items

- `.activity-item` - Activity card
- `.activity-item.type-{call|email|meeting}` - Color-coded borders
- `.activity-type` - Activity type badge
- `.activity-subject`, `.activity-description` - Content styling
- `.activity-meta` - Metadata (date, duration, user)

### Note styles

- `.note-item` - Note card (yellow background)
- `.note-item.important` - Important note (red background)
- `.note-important-badge` - "IMPORTANT" badge
- `.note-text` - Note content
- `.note-meta` - Timestamp and actions

### Task styles

- `.task-item` - Main task card
- `.task-item.completed` - Completed/cancelled tasks (faded)
- `.client-task-item` - Client tab task card
- `.task-title`, `.task-description` - Task content
- `.task-meta` - Priority, status, due date
- `.task-actions` - Button container

## JavaScript Functions

### Tab Management

- `switchClientTab(tabName)` - Switch between client modal tabs
- `currentClientId` - Global variable tracking active client

### Activities

- `loadClientActivities(clientId)` - Load activities for a client
- `openLogActivityModal()` - Open activity creation modal
- `closeActivityModal()` - Close activity modal
- `saveActivity(event)` - Save new activity
- `deleteActivity(activityId)` - Delete an activity

### Notes

- `loadClientNotes(clientId)` - Load notes for a client
- `openAddNoteModal()` - Open note creation modal
- `closeNoteModal()` - Close note modal
- `saveNote(event)` - Save new note
- `deleteNote(noteId)` - Delete a note

### Task functions

- `loadTasks()` - Load all tasks with filtering
- `loadClientTasks(clientId)` - Load client-specific tasks
- `renderTasksList(tasks)` - Render task list HTML
- `filterTasks(status)` - Filter tasks by status
- `openAddTaskModal()` - Open task modal (new task)
- `editTask(taskId)` - Open task modal (edit mode)
- `closeTaskModal()` - Close task modal
- `saveTask(event)` - Save task (create/update)
- `markTaskComplete(taskId)` - Mark task as completed
- `deleteTask(taskId)` - Delete a task
- `openAddClientTaskModal()` - Open task modal pre-filled with client

### Helper

- `showNotification(message, type)` - Display notifications (console log for now)

## API Usage Examples

### Create Activity

```javascript
POST /admin/api/activities.php
{
  "client_id": 123,
  "activity_type": "call",
  "subject": "Discussed project requirements",
  "description": "Client wants to add 3 more pages",
  "activity_date": "2026-02-07T14:30:00",
  "duration_minutes": 25
}
```

### Get Client Activities

```javascript
GET /admin/api/activities.php?client_id=123
```

### Create Task

```javascript
POST /admin/api/tasks.php
{
  "title": "Follow up on quote",
  "description": "Client requested changes to pricing",
  "client_id": 123,
  "priority": "high",
  "due_date": "2026-02-10",
  "status": "pending"
}
```

### Get Tasks by Status

```javascript
GET /admin/api/tasks.php?status=pending
```

### Create Note

```javascript
POST /admin/api/notes.php
{
  "client_id": 123,
  "note_text": "Prefers email communication after 2pm",
  "is_important": true
}
```

### Get CRM Dashboard Stats

```javascript
GET /admin/api/crm_dashboard.php
```

- Database migration available: `admin/migrations/2026-02-13_add_crm_fields.sql` (adds optional `lead_source`, `next_follow_up`, `expected_value`).
- Run migrations on staging/production with the CLI helper: `php admin/setup/run_migrations.php` (or run the SQL directly).
- CRM cache TTL reduced for near-real-time updates (now 60s).
- UI updated: `Client Details` and `Add New Client` now expose `lead_source`, `next_follow_up`, and `expected_value` fields in the admin UI.

## Setup Instructions

### Database Setup

The CRM tables are automatically created when you run:

```bash
mysql -h 127.0.0.1 -u petertmooney -p'68086500aA!' -D Content_Catalogz < admin/setup_crm.sql
```

Or use the provided setup file:

- File: `/workspaces/content_catalogz/admin/setup_crm.sql`

### Access

1. Navigate to: `http://localhost:8083/admin/`
2. Login with: admin / admin123
3. All CRM features are now available in:
   - Client modals (click any client to edit)
   - Tasks & To-Do section (sidebar menu)

## Future Enhancements

Potential additions:

- Email integration for activity logging
- Calendar view for tasks and follow-ups
- Client segmentation and filtering by tags
- Pipeline visualization
- Revenue forecasting
- Task assignment to team members
- Notification system for due tasks
- Export/reporting features
- Mobile-responsive improvements

## Files Modified/Created

### New Files

- `admin/api/activities.php` - Activity management API
- `admin/api/tasks.php` - Task management API
- `admin/api/notes.php` - Notes management API
- `admin/api/crm_dashboard.php` - CRM analytics API
- `admin/setup_crm.sql` - Database setup script
- `admin/CRM_SYSTEM.md` - This documentation

### Modified Files

- `admin/dashboard.php` - Added:
  - Tasks section UI
  - Task modal
  - Client modal tabs
  - CRM CSS styles
  - All CRM JavaScript functions
  - Tab switching logic

## Support

For issues or questions about the CRM system, refer to this documentation or check the API endpoints directly.

---
**Version**: 1.0
**Last Updated**: February 7, 2026
**Status**: ✅ Production Ready
