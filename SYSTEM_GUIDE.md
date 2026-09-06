# 🏗️ St. Bilfrid Development Corporation - System Operations Manual & Feature Guide

Welcome to **St. Bilfrid Development Corporation** (Construction Project Monitoring & Control Management System). This guide documents all system capabilities, workflows, and operational procedures.

---

## 🔐 1. Multi-Role User Authentication & Accounts

| Role | Email | Password | Access Level |
|---|---|---|---|
| **Master Administrator** | `admin@newconstuc.firm` | `admin123` | **Full Master System Access** (All Hubs, Reports, Costs, Estimates, Project Deletions) |
| **Roofing Transfer Officer** | `roofing@newconstuc.firm` | `roofing123` | **Dedicated Roofing Transfer Hub** (Stock Dispatch, Site Transfers, Excess Return) |
| **Windows & Doors Officer** | `windows.doors@newconstuc.firm` | `windows123` | **Dedicated Windows & Doors Transfer Hub** (Stock Dispatch, Site Transfers, Excess Return) |

> [!TIP]
> On the login page (`/login`), click any **"⚡ 1-Click Fill Credentials"** button to populate credentials and sign in instantly.

---

## 🚀 2. How to Launch the System Locally

### Option A: Caddy Web Server (Recommended)
- **1-Click Launch**: Double-click [run-caddy.bat](file:///c:/Users/Carin%20Benjamin/Desktop/NewConstuc.FIRM/run-caddy.bat) or [run.bat](file:///c:/Users/Carin%20Benjamin/Desktop/NewConstuc.FIRM/run.bat)
- **PowerShell**: `.\run-caddy.ps1`
- **Manual**:
  ```powershell
  # 1. Start PHP FastCGI:
  & 'C:\xampp\php\php-cgi.exe' -b 127.0.0.1:9000
  # 2. Start Caddy:
  caddy run --config Caddyfile
  ```

### Option B: Laravel Artisan Server
```powershell
& 'C:\xampp\php\php.exe' artisan serve --host=127.0.0.1 --port=8000
```

Open your browser at 👉 **[http://localhost:8000](http://localhost:8000)** or **[http://127.0.0.1:8000](http://127.0.0.1:8000)**

---

## 🧭 3. Complete Feature Modules & Capabilities

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

### 🎨 1. Project Blueprints, 3D Renders & Site Photo Gallery
- **Categorized Visual Gallery**:
  - 📐 **Technical Blueprints & CAD Plans**: Foundation grids, elevator shear walls, and floor elevation plans.
  - 🎨 **3D Architectural Concept Renders**: High-fidelity renders showing what the client wants / target design.
  - 💡 **Client Design Inspiration**: Client mood boards and design references.
  - 📸 **Actual On-Site Progress Photos**: Live progress photographs of concrete pouring, tower cranes, rebar, and scaffolding.
  - 🏗️ **Structural & Foundation Works**
  - ✨ **Turnkey Architectural Finishes**
- **Features**:
  - Direct file upload (JPG, PNG, WebP up to 10MB) or media URL.
  - Set any photo as the project's primary hero banner image.
  - Interactive full-screen Lightbox Modal for side-by-side design comparison.

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

## 🛠️ 4. Maintenance & Diagnostic Commands

| Action | Command |
|---|---|
| **Run Development Server** | `& 'C:\xampp\php\php.exe' artisan serve --port=8000` |
| **Verify Route List** | `& 'C:\xampp\php\php.exe' artisan route:list` |
| **Re-seed Clean System Data** | `& 'C:\xampp\php\php.exe' artisan migrate:fresh --seed` |
| **Clear Application Cache** | `& 'C:\xampp\php\php.exe' artisan cache:clear` |
