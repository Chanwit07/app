# Maintenance Insight Platform - Asset & Supply Management System

### 🛠 Project Overview for AI Agents
This project is a web-based management system for the State Railway of Thailand (SRT), designed to streamline the process of requesting Asset IDs, Units, and Supply Codes. The system features a PHP-based backend, a MySQL database, and a Kanban-style tracking interface.

---

## 📂 Project Architecture & Context

### 1. Database Schema Specification (MySQL)
The agent should refer to these two primary tables:

#### Table: `asset_requests` (For Asset/Unit Requests)
| Column | Type | Description |
| :--- | :--- | :--- |
| `id` | INT (PK) | Auto-increment ID |
| `department` | VARCHAR | Owning department |
| `asset_id` | VARCHAR | Asset number (เลขที่สินทรัพย์) |
| `asset_group` | VARCHAR | Asset group/Unit (กลุ่มสินทรัพย์) |
| `serial_number` | VARCHAR | Hardware Serial Number |
| `account_type` | VARCHAR | Type of account (ประเภทบัญชี) |
| `status` | ENUM | 'Pending', 'Processing', 'Completed' |
| `created_at` | TIMESTAMP | Request date |
| `finished_at` | TIMESTAMP | Completion date |

#### Table: `supply_requests` (For Supply Code/Edit Requests)
| Column | Type | Description |
| :--- | :--- | :--- |
| `id` | INT (PK) | Auto-increment ID |
| `request_type` | ENUM | 'new_code' or 'edit_detail' |
| `item_number` | VARCHAR | Item number (for edit requests) |
| `item_name` | TEXT | Description (or old description) |
| `new_item_name`| TEXT | New description (for edit requests) |
| `unit` | VARCHAR | Unit of measurement (หน่วยนับ) |
| `annual_usage` | INT | Optional: Yearly consumption |
| `max_min` | VARCHAR | Optional: Max-Min stock levels |
| `status` | ENUM | 'Pending', 'Processing', 'Completed' |

---

## 🛠 Features & Logic Requirements

### 1. Form Logic
- **Form A (Asset):** Mandatory fields: Department, Asset ID, Unit, Serial, Account Type.
- **Form B (Supply New):** Mandatory: Item Name, Unit. Optional: Annual Usage, Max-Min.
- **Form C (Supply Edit):** Mandatory: Item Number, Old Name, New Name, Unit.

### 2. Kanban Workflow
- **Column 1 (Pending):** `WHERE status = 'Pending'`. Display all new requests.
- **Column 2 (Processing):** `WHERE status = 'Processing'`. Triggered when admin acknowledges.
- **Column 3 (Completed):** `WHERE status = 'Completed'`. Records `finished_at` timestamp.

### 3. Integration Goals
- **Line Notify:** Trigger a notification to the admin group on every new submission.
- **History Tracking:** Users must be able to view `created_at` and `finished_at` for transparency.

---

## 🏗 Directory Structure (Standard PHP)
```text
/maintenance-platform
├── config/
│   └── db.php           # PDO Database connection
├── actions/
│   ├── save_asset.php    # Process Asset Form
│   ├── save_supply.php   # Process Supply Form (New/Edit)
│   └── update_status.php # Update Kanban Status via AJAX
├── assets/
│   ├── css/style.css    # Kanban & UI styling
│   └── js/kanban.js     # Drag-and-drop or status toggle logic
├── includes/
│   ├── header.php
│   └── footer.php
├── index.php             # Landing Page
├── form-asset.php        # UI for Asset Request
├── form-supply.php       # UI for Supply Request (Tabs)
└── kanban.php            # Visual Status Tracking Board