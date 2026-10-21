# 🏗️ St. Bilfrid Development Corporation - System Operations Manual & Feature Guide

Welcome to **St. Bilfrid Development Corporation** (Construction Project Monitoring & Control Management System). This guide documents all system capabilities, workflows, and operational procedures.

---

## 🔐 1. Multi-Role User Authentication & Accounts

| Role | Email | Password | Access Level |
|---|---|---|---|
| **Master Administrator** | `admin@newconstuc.firm` | `admin123` | **Full Master System Access** (All Hubs, Reports, Costs, Estimates, Project Deletions) |
| **Site Project Engineer** | `engineer@newconstuc.firm` | `engineer123` | **Active Project Tracker & Site Updates** (Can access project tracker like Admin, but can only modify projects where the selected engineer is assigned) |
| **Roofing Transfer Officer** | `roofing@newconstuc.firm` | `roofing123` | **Dedicated Roofing Transfer Hub** (Stock Dispatch, Site Transfers, Excess Return) |
| **Windows & Doors Officer** | `windows.doors@newconstuc.firm` | `windows123` | **Dedicated Windows & Doors Transfer Hub** (Stock Dispatch, Site Transfers, Excess Return) |

> [!TIP]
> On the login page (`/login`), click any **"⚡ 1-Click Fill Credentials"** button to populate credentials and sign in instantly.

---

## 🚀 2. How to Launch the System Locally

### Option A: Laravel Artisan Server (Standard Development)
```powershell
php artisan serve
```

### Option B: Caddy Web Server (High Performance)
1. Start PHP FastCGI:
   ```powershell
   & 'C:\xampp\php\php-cgi.exe' -b 127.0.0.1:9000
   ```
2. Start Caddy:
   ```powershell
   caddy run --config Caddyfile
   ```

Open your browser at 👉 **[http://localhost:8000](http://localhost:8000)** or **[http://127.0.0.1:8000](http://127.0.0.1:8000)**

---

## 🧭 3. Complete Feature Modules & Capabilities

hat 
```
📁 St. Bilfrid Development Corporation System Architecture
├── 🔐 User Gateway (/login) ────────────────── Role-Based Multi-Account Portal (Admin, Roofing, Windows/Doors)
├── 🏠 Roofing Transfer Station (/roofing-transfer) ── Dedicated Roofing Stock Dispatch & Inter-Project Moves
├── 🚪 Windows & Doors Station (/windows-doors-transfer) ── Dedicated Windows & Doors Dispatch & Excess Returns
├── 📊 Executive Dashboard (/) ──────────────── Sales Command, Gross Margins, Trade Progression Averages
├── 🏗️ Active Project Tracker (/projects) ────── Blueprints, Phases, Schedule Health, Project Deletion Modal
├── 📋 Master Project Control (/projects/{id}) ── Photos, Schedule, Progression Bases, BOM, Tasks, Receipts, Delete Project
├── 📐 Blueprints & 3D Renders Gallery ──────── CAD Technical Drawings, 3D Concept Renders, Site Progress Photos
├── 📦 Central Inventory & Excess Returns (/bom & /inventory) ── 1-Click Return of Unused Site Materials to Stock
├── 💳 Payment Receipts & Official Voucher (/payments) ── Proof of Settlement Upload & Printable OR Vouchers
├── 🖨️ Official Accomplishment Report (/projects/{id}/print-report) ── Printable Certificate with 4 Signature Blocks
├── 📅 Clarified Project Schedule & Phases ── Duration, Elapsed vs Remaining Days, Critical Phase Gates
├── 💰 Project Costing Hub (/costing) ──────── 7 Cost Heads, Margin %, Unit Cost Rate (₱/m²), Auto-Sync
└── 📐 Service Estimator (/estimation) ─────── Land & Floor Area Pricing Estimator with 1-Click Init
```

---

### 📋 0. Master Project Workspace Layout (BOM First, Schedule Second)
- **Top Priority Tab Order**:
  - 🥇 **Tab 1: Bill of Materials & Detailed Unit Price Analysis (DUPA)** (`/projects/{id}#bom`):
    - Set as the **primary default view** on page load.
    - Displays full Philippine standard breakdown: Materials (A) + Labor (B) + Equipment (C) + 15% Contingency + 6% Taxes + 10% Profit.
  - 🥈 **Tab 2: Master Schedule & Execution Timeline** (`/projects/{id}#scheduling`):
    - Positioned second immediately after BOM.
    - Displays scheduled duration window, elapsed site days, days to target handover, and milestone progress.
  - **Remaining Tabs**: Executive Summary, Design & Blueprints, Trade Progression, Scheduled Tasks, Workforce & Manpower, Financials & Payments, Milestone Audit.
- **Fixed Baseline BOM with Dynamic Additions**:
  - **Fixed Baseline**: Every project is automatically seeded on creation or viewing with a calibrated baseline BOM template (18 standard engineering items spanning Foundation, Structural Framing, Masonry, Roofing, Electrical, Plumbing, and Architectural Finishes).
  - **Project & Task Additions**:
    - **"+ Add Scope Item / Addition"** button allows adding change orders or new scope items directly to the BOM.
    - **"+ Add Line"** on each scope item allows inserting additional Material, Labor, or Equipment entries anytime.
    - **Scheduled Tasks Auto-Sync**: Entering materials when scheduling a new task (`+ Add Checklist Task`) automatically creates a synchronized Scope Item (`ADDITIONAL TASK: [TASK NAME]`) in the master BOM and recalculates the grand project cost dynamically.

---

### 🎨 1. Project Blueprints, Engineering Plans & Progress Photo Gallery
- **Categorized Technical Blueprints & Plans**:
  - 🏛️ **Architectural Blueprints**: Floor plans, elevations, building sections, reflected ceiling layouts.
  - 🏗️ **Civil & Structural Blueprints**: Footing & foundation grids, column schedules, structural framing, shear walls, retaining walls.
  - ⚡ **Electrical CAD Plans**: Power distribution, lighting layouts, auxiliary lines, single-line diagrams, panelboard schedules.
  - 🚰 **Plumbing & Sanitary Blueprints**: Potable water lines, sanitary drainage, storm drain systems, septic tank details.
  - 📐 **Other Related Engineering Plans**: HVAC mechanical layouts, fire suppression & sprinkler networks, specialized engineering drawings.
  - 🎨 **3D Architectural Concept Renders**: High-fidelity 3D visual renders defining client expectations and target finishes.
  - 💡 **Client Design Inspiration**: Client mood boards, material palettes, and architectural references.
  - 📸 **Actual On-Site Progress Photos**: Field photos of concrete pouring, rebar assembly, masonry, and equipment operations.
- **Strict JPEG Blueprint Format Standard**:
  - Technical CAD Blueprints **must strictly be uploaded in JPEG format (`.jpg`, `.jpeg`)**.
  - **PNG files are strictly rejected** by both browser-level checks and server-side validation with explicit error advisories.
  - Non-blueprint categories (3D renders, site progress) continue to support general image formats (JPG, PNG, WebP up to 10MB).
- **Gallery Organization & Discipline Filter Tabs**:
  - Filter tabs allow 1-click filtering by discipline (`Architectural`, `Civil`, `Electrical`, `Plumbing`, `Other Plans`, `All Blueprints`, `3D Renders`, `Site Progress`).
  - Set any photo/blueprint as the project's primary hero banner.
  - High-resolution Lightbox modal for technical inspection and side-by-side design reviews.

---

### 📸 2. Progress Checklist Tasks Multi-Photo Verification (2 or More Images)
- **Multi-Image Proof of Completion**:
  - Checklist tasks support attaching **2 or more images** per task for comprehensive verification of field milestones.
  - Hold **Ctrl** or **Shift** to select multiple photo files simultaneously in the upload modal.
  - Live interactive thumbnail grid previews all selected images before submission.
- **Visual Task Table Indicators**:
  - Dual-stacked thumbnail cards with `+X more` badges for tasks with 2 or more attached photos.
  - `📸 X Photos` badge with quick-click access to view the multi-photo gallery.
- **Interactive Lightbox Carousel**:
  - Full-screen lightbox with `❮ Previous` and `❯ Next` controls, keyboard navigation (Left/Right arrow keys, Escape).
  - Top-right counter badge displaying current photo index (e.g. `Photo 2 of 4`).
  - Bottom clickable thumbnail strip for quick jumping between proof photos.
- **Individual Photo Management**:
  - **"+ Add/Manage"** modal displays all currently attached photos in a management grid.
  - Individual **"× Del"** buttons allow removing a single photo without deleting the rest of the task's proofs.
  - **"Delete All Photos"** option for full reset.
  - Primary photo synchronization keeps backward compatibility with master progress and reports.

---

### 📦 2. Excess Material Return & Central Inventory Reconciliation
- **Unused Site Material Reclaim**:
  - Calculates remaining unconsumed materials on-site ($Allocated - Used - AlreadyReturned$).
  - **"↩ Return Excess to Central Inventory"** action button in Bill of Materials (BOM) tables.
  - Automatically credits the unused materials back into the master warehouse catalog stock (`materials.stock_quantity`).
  - Records an **Inventory Transaction Log** with transaction type `excess_return`, reference ID (e.g. `RET-202604-001`), timestamp, and engineering reason.
  - Updates project net material expenditure so projects are accurately reconciled.

---

### 💳 3. Payment Receipts & Official Receipt (OR) Voucher Generator
- **Official Billing Settlement Ledger**:
  - Tracks payments with Official Receipt numbers (e.g. `OR-202601-8812`), invoice references, payer entity name, settlement dates, and bank references.
  - Proof-of-payment attachment upload (bank deposit slips, RTGS wire confirmations, checks).
- **Printable Official Receipt Voucher**:
  - Dedicated route: `/payments/{id}/receipt` (Printable format).
  - Features official company letterhead, BIR TIN, PCAB license, amount in figures & words, payment stage, bank stamp, and authorized signature lines.

---

### 📊 4. Engineering Progression Bases & Weighted Trade Formula
- **Transparent Mathematical Basis**:
  $$\text{Overall Progress} = (S \times w_s) + (E \times w_e) + (P \times w_p) + (F \times w_f)$$
- **Trade Disciplines & Default Weights**:
  - 🔵 **Structural Works & Foundation**: 40%
  - 🟡 **Electrical Conduits & High Voltage**: 25%
  - 🟢 **Piping, Plumbing & Sanitary**: 20%
  - 🌸 **Architectural & Turnkey Finishes**: 15%
- **"⚙️ Progression Bases" Modal**: Allows customizing trade weight shares per project with engineering criteria notes.

---

### 🖨️ 5. Official Accomplishment Report with 4 Signature Blocks
- **Executive Certificate of Progress & Accomplishment**:
  - Accessible via **"🖨️ Official Accomplishment Report"** button or `/projects/{id}/print-report`.
  - Formatted with `@media print` CSS for standard A4 / Letter hardcopy or PDF export.
  - Includes: Document Control ID, project specifications, commercial audit, weighted progression table, schedule health, BOM & excess reconciliation, and workforce headcount.
- **Official Sign-off Section ("Signed Place")**:
  - ✍️ **Prepared & Certified By**: Site / Structural Engineer (Name, Title, PRC License No., Signature Line, Date)
  - ✍️ **Checked & Verified By**: Lead Principal Architect / QA-QC Officer (Name, Title, PRC License No., Signature Line, Date)
  - ✍️ **Approved By**: Managing Director & Principal (Name, Title, Signature Line, Date)
  - ✍️ **Conforme & Accepted By**: Client Authorized Representative (Client Name, Designation, Signature Line, Date)

---

### 📅 6. Clarified Project Master Schedule & Timeline
- **Execution Timeline**:
  - Total Scheduled Window (in calendar days).
  - Elapsed Days vs Days to Target Handover.
  - Timeline Consumption Gauge (% consumed).
  - Active Construction Phase & Accomplishment Progress.
  - Live Schedule Health Indicator: `Ahead of Schedule`, `On Schedule Target`, `Schedule Lagging`, `Overdue`.

---

### 🔍 7. Comprehensive Monitoring of Tasks, Services & Budgets
- **Tasks Tracker**: Real-time tracking of tasks with assigned engineers, trade disciplines, start/due dates, allocated budget vs actual cost, and progress sliders.
- **Service Estimator**: Land & floor area pricing estimator (`/estimation`) with 1-click project initialization.
- **Budget Health Control**: Cost variance analysis across 7 cost heads with automatic budget overrun warnings (> 85% near ceiling, > 100% overrun).

---

### 👷 8. Site Project Engineer Portal & Acting Engineer Authorization Control
- **Purpose**: A dedicated site account (`engineer@newconstuc.firm` / `engineer123`) engineered specifically for site progress reporting, checklist management, material tracking, and field updates.
- **Access Scope**:
  - Full visibility into the **Active Project Tracker** (`/projects` & `/projects/{id}`), Central Inventory (`/inventory`), Bill of Materials (`/bom`), and Engineers Roster (`/personnel`), identical to administrative viewing capability.
  - Destructive project deletion (`DELETE /projects/{id}`) remains strictly locked to the Master Administrator.
- **Acting Site Engineer Selector & Assignment Enforcement**:
  - **Operating Engineer Bar**: A prominent indicator at the top of the project matrix and project details displays the currently active site engineer session.
  - **Pre-Update Engineer Selection**: Before submitting updates to tasks, trade progression, schedule, manpower, BOM scope items, or site photos, the user can choose which registered engineer is authorizing the changes (`selectActingEngineerModal`).
  - **Project Assignment Rule**: Only if the selected engineer is assigned to that specific project (`$project->personnel`) are modifications permitted.
  - **Read-Only Mode for Unassigned Projects**: If an engineer is selected who is not assigned to the site, all project data remains visible, but modifications are blocked both client-side (advisory modal/toast) and server-side (HTTP 403 Forbidden with helpful assignment instructions).
  - **Master Admin Override**: The Master Administrator (`admin@newconstuc.firm`) retains full unrestricted editing capability across all active projects regardless of engineer assignments.

---

## 🛠️ 4. Maintenance & Diagnostic Commands

| Action | Command |
|---|---|
| **Run Development Server** | `& 'C:\xampp\php\php.exe' artisan serve --port=8000` |
| **Verify Route List** | `& 'C:\xampp\php\php.exe' artisan route:list` |
| **Re-seed Clean System Data** | `& 'C:\xampp\php\php.exe' artisan migrate:fresh --seed` |
| **Clear Application Cache** | `& 'C:\xampp\php\php.exe' artisan cache:clear` |
