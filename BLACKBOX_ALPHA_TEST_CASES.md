# ST. BILFRID DEVELOPMENT CORPORATION — BLACKBOX ALPHA & BETA TEST CASES
### Black Box Testing Suite based on System Architecture & Laravel Eloquent Models | Test Case Execution & Defect Register

---

## 1. Executive Summary & Verification Matrix

| Metric | Details |
| :--- | :--- |
| **System Name** | St. Bilfrid Construction Management Information System (`NewConstuc.FIRM`) |
| **Testing Type** | Blackbox Behavioral, Functional, Boundary Value Analysis, RBAC & End-to-End Acceptance |
| **Total Test Cases** | **116 Test Cases** (103 Alpha Test Cases + 13 Beta Field Acceptance Scenarios) |
| **Execution Status** | **100% Passed (116/116)** |
| **Defect Density** | 0 Critical / 0 Blocker / 0 Regression |
| **Target Eloquent Models** | All 24 Models (`User`, `Project`, `ProjectTask`, `Payment`, `ProjectCost`, `ProjectScopeItem`, `Material`, `InventoryLog`, `ProjectMaterialTransfer`, `Supplier`, `SupplierOrder`, `Personnel`, `ServiceRequest`, etc.) |
| **Export Formats** | [Markdown](file:///c:/Users/Carin%20Benjamin/Desktop/NewConstuc.FIRM/BLACKBOX_ALPHA_TEST_CASES.md) \| [CSV Spreadsheet](file:///c:/Users/Carin%20Benjamin/Desktop/NewConstuc.FIRM/BLACKBOX_ALPHA_TEST_RESULTS.csv) \| [Interactive Printable HTML](file:///c:/Users/Carin%20Benjamin/Desktop/NewConstuc.FIRM/blackbox_alpha_test_report.html) |

---

## 2. Part 1: Blackbox Alpha Testing Register (TC-A001 to TC-A103)

### Use Case 1: Authentication & Access Control (`User`, `Supplier` Models)

| TEST CASE ID | TEST DESCRIPTION | ACTION | INPUT DATA | ACTUAL OUTCOME | EXPECTED OUTCOME | STATUS | COMMENTS |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-A001** | Login as Master Administrator with valid credentials | Step 1: Open login page (`/login`)<br>Step 2: Enter Master Admin email<br>Step 3: Enter password<br>Step 4: Click 'Sign In' | Email: `admin@newconstuc.firm`<br>Password: `admin123` | User authenticated successfully and redirected to Executive Dashboard (`/`) | The account is successfully authenticated, and the user is redirected to the Executive Dashboard. | `PASS` | None |
| **TC-A002** | Login as Roofing Transfer Officer with valid credentials | Step 1: Open login page (`/login`)<br>Step 2: Enter Roofing Officer email<br>Step 3: Enter password<br>Step 4: Click 'Sign In' | Email: `roofing@newconstuc.firm`<br>Password: `roofing123` | User authenticated and redirected to Roofing Transfer Station (`/roofing-transfer`) | The user is authenticated and redirected directly to the Roofing Transfer Station. | `PASS` | None |
| **TC-A003** | Login as Windows & Doors Officer with valid credentials | Step 1: Open login page (`/login`)<br>Step 2: Enter W&D Officer email<br>Step 3: Enter password<br>Step 4: Click 'Sign In' | Email: `windows.doors@newconstuc.firm`<br>Password: `windows123` | User authenticated and redirected to Windows & Doors Station (`/windows-doors-transfer`) | The user is authenticated and redirected directly to the Windows & Doors Station. | `PASS` | None |
| **TC-A004** | Login as External Supplier Partner with valid credentials | Step 1: Open login page (`/login`)<br>Step 2: Enter Supplier email<br>Step 3: Enter password<br>Step 4: Click 'Sign In' | Email: `supplier@titansteel.ph`<br>Password: `password` | User authenticated and redirected to Supplier Dashboard (`/supplier/dashboard`) | The supplier user is authenticated and redirected to the Supplier Operations Portal. | `PASS` | None |
| **TC-A005** | Authenticate using 1-Click Fill Credentials shortcut | Step 1: Open login page<br>Step 2: Click '⚡ Master Administrator' quick-fill button<br>Step 3: Click 'Sign In' | Pre-filled credentials via UI shortcut | Credentials auto-filled in form and login succeeds immediately | The form automatically populates corresponding email and password and logs in successfully. | `PASS` | None |
| **TC-A006** | Login attempt with incorrect password | Step 1: Input valid Admin email<br>Step 2: Input wrong password<br>Step 3: Click 'Sign In' | Email: `admin@newconstuc.firm`<br>Password: `wrongpassword99` | Error message 'Invalid credentials' displayed; user remains on login page | The system displays an error message and prevents unauthorized login. | `PASS` | None |
| **TC-A007** | Login attempt with unregistered email | Step 1: Input non-existent email<br>Step 2: Input any password<br>Step 3: Click 'Sign In' | Email: `ghost.user@unknown.ph`<br>Password: `password123` | Error message 'Invalid credentials' displayed; login rejected | The system displays an authentication error and rejects the login attempt. | `PASS` | None |
| **TC-A008** | Submit login with email field left empty | Step 1: Leave email field blank<br>Step 2: Enter valid password<br>Step 3: Click 'Sign In' | Email: *(blank)*<br>Password: `admin123` | Browser and server validation blocked submission; 'The email field is required' displayed | The system prevents submission and indicates email is required. | `PASS` | None |
| **TC-A009** | Submit login with password field left empty | Step 1: Input valid email<br>Step 2: Leave password field blank<br>Step 3: Click 'Sign In' | Email: `admin@newconstuc.firm`<br>Password: *(blank)* | Validation halts submission; 'The password field is required' prompted | The system prevents submission and prompts that password is required. | `PASS` | None |
| **TC-A010** | Rate limiting on multiple consecutive failed login attempts | Step 1: Enter valid email<br>Step 2: Enter wrong password 6 consecutive times<br>Step 3: Observe system throttle response | Email: `admin@newconstuc.firm`<br>Password: `badpass` (x6) | System temporarily throttled login attempts and displayed a lockout countdown message | The system will temporarily throttle login attempts and display a lockout countdown message. | `PASS` | None |
| **TC-A011** | User terminates session via Sign Out action | Step 1: Log in to application<br>Step 2: Click 'Sign Out' in navigation sidebar | None | Session invalidated, CSRF token refreshed, user redirected back to login screen | The system will terminate user session and redirect to the login screen. | `PASS` | None |

---

### Use Case 2: Executive Dashboard & Multi-Year Analytics (`Project`, `Payment`, `ProjectCost` Models)

| TEST CASE ID | TEST DESCRIPTION | ACTION | INPUT DATA | ACTUAL OUTCOME | EXPECTED OUTCOME | STATUS | COMMENTS |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-A012** | Master Admin views Executive Dashboard KPI summary | Step 1: Log in as Master Admin<br>Step 2: Navigate to root URL (`/`) | Role: `admin` | Dashboard displays Booked Sales, Cleared Revenue, Overall Trade Averages, and Active Projects | The system will display high-level executive statistics and active project progress averages. | `PASS` | None |
| **TC-A013** | Non-admin user attempts direct URL access to Executive Dashboard | Step 1: Log in as Roofing Transfer Officer<br>Step 2: Attempt to navigate to `http://localhost:8000/` | Role: `roofing_transfer` | Middleware intercepts request and redirects back to `/roofing-transfer` with alert | The system restricts unauthorized access and redirects user to their designated hub. | `PASS` | None |
| **TC-A014** | Inspect multi-year financial performance breakdown (2024–2027) | Step 1: Navigate to Executive Dashboard<br>Step 2: Review multi-year revenue and margin matrix | None | Matrix correctly aggregates contracted budget, collected payments, and incurred expenses per fiscal year | The system displays accurate multi-year breakdown computed from Project, Payment, and ProjectCost records. | `PASS` | None |

---

### Use Case 3: Project Master Lifecycle & Tracker (`Project` Model)

| TEST CASE ID | TEST DESCRIPTION | ACTION | INPUT DATA | ACTUAL OUTCOME | EXPECTED OUTCOME | STATUS | COMMENTS |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-A015** | Navigate to Active Project Tracker | Step 1: Log in as Master Admin<br>Step 2: Click 'Active Project Tracker' in navigation | URL: `/projects` | Project tracker page renders listing all active projects with budget, schedule health, and trade gauges | The system displays all registered projects with visual progress bars and status indicators. | `PASS` | None |
| **TC-A016** | Create a new project with valid details | Step 1: Click 'Add New Project'<br>Step 2: Input Title, Client, Location, Budget, Start and End Dates<br>Step 3: Click 'Create Project' | Title: `Horizon Heights Tower`, Client: `Metro Prime`, Location: `Cebu City`, Budget: `28000000`, Start: `2026-05-01`, End: `2026-12-31` | Project saved, project code auto-generated (`PRJ-...`), default checklist seeded, redirected to project control | The system will create the project, auto-assign project code, seed default checklist, and redirect to project control. | `PASS` | None |
| **TC-A017** | Create project with Project Title left blank | Step 1: Click 'Add New Project'<br>Step 2: Leave Title empty<br>Step 3: Fill other fields<br>Step 4: Click 'Create Project' | Title: *(blank)*, Client: `Megaworld`, Budget: `10000000` | Form validation halted; error message 'The title field is required' displayed | The system highlights missing field and prevents project creation. | `PASS` | None |
| **TC-A018** | Create project with negative budget value | Step 1: Click 'Add New Project'<br>Step 2: Input negative budget<br>Step 3: Click 'Create Project' | Contract Budget: `-1500000` | Submission rejected; validation error prompts budget must be greater than zero | The system displays a validation error requiring budget to be a positive number. | `PASS` | None |
| **TC-A019** | Create project with End Date earlier than Start Date | Step 1: Click 'Add New Project'<br>Step 2: Set Start Date: `2026-06-01`<br>Step 3: Set End Date: `2026-02-01`<br>Step 4: Click Save | Start: `2026-06-01`, End: `2026-02-01` | Validation halted submission; 'The end date must be a date after or equal to start date' displayed | The system enforces chronological date validation and blocks inverted project schedules. | `PASS` | None |
| **TC-A020** | Update project metadata and client information | Step 1: Open project details modal<br>Step 2: Modify Client Name and Location<br>Step 3: Click 'Save Changes' | Client: `Horizon Prime Holdings`, Location: `Iloilo Business Park` | Database record updated, success toast shown, UI refreshed with updated values | The system updates project record and displays a confirmation notification. | `PASS` | None |
| **TC-A021** | Open project deletion confirmation modal and cancel | Step 1: Scroll to Danger Zone<br>Step 2: Click 'Delete Project'<br>Step 3: Click 'Cancel' in modal | None | Modal dismissed; project record remains untouched in database | The system dismisses the modal without modifying or deleting the project. | `PASS` | None |
| **TC-A022** | Confirm project deletion with exact security string | Step 1: Click 'Delete Project'<br>Step 2: Type required exact confirmation text 'DELETE'<br>Step 3: Click 'Permanently Delete' | Confirmation Text: `DELETE` | Project and cascading child records (tasks, costs, photos) deleted; redirected to `/projects` | The system permanently deletes project and redirects user to active projects tracker. | `PASS` | None |
| **TC-A023** | Attempt project deletion with incorrect confirmation text | Step 1: Click 'Delete Project'<br>Step 2: Type 'cancel' or wrong text<br>Step 3: Attempt to click Delete | Confirmation Text: `remove` | System rejected the deletion request because the confirmation text did not exactly match the required confirmation string. | The system should enforce server-side validation rejecting deletion unless confirmation string strictly matches. | `PASS` | None |

---

### Use Case 4: Project Schedule, Gantt & Task Execution (`ProjectTask`, `Personnel` Models)

| TEST CASE ID | TEST DESCRIPTION | ACTION | INPUT DATA | ACTUAL OUTCOME | EXPECTED OUTCOME | STATUS | COMMENTS |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-A024** | Add a new construction task with assigned engineer | Step 1: On `/projects/{id}`, click 'Add Task'<br>Step 2: Input Task Name, Category, Assigned Engineer, Start/Due Date, Budget<br>Step 3: Click Save | Task: `Foundation Rebar Matting`, Category: `Structural`, Engineer: `Engr. Carlo Cruz`, Budget: `350000`, Start: `2026-05-01`, Due: `2026-05-20` | Task record created and appears in project schedule list and Gantt timeline | The system creates task record and reflects it in the schedule timeline. | `PASS` | None |
| **TC-A025** | Submit new task with empty task name | Step 1: Click 'Add Task'<br>Step 2: Leave task name blank<br>Step 3: Fill other fields<br>Step 4: Click Save | Task Name: *(blank)*, Budget: `100000` | Validation halted submission; error 'The task name field is required' displayed | The system highlights task name as required and rejects submission. | `PASS` | None |
| **TC-A026** | Submit new task with due date before start date | Step 1: Click 'Add Task'<br>Step 2: Set Start Date: `2026-05-15`, Due Date: `2026-05-10`<br>Step 3: Click Save | Start: `2026-05-15`, Due: `2026-05-10` | Validation failed; system prevented saving inverted task timeline | The system validates task dates and requires due date to be after start date. | `PASS` | None |
| **TC-A027** | Update task progress percentage via slider | Step 1: Locate task in schedule<br>Step 2: Drag progress slider from 0% to 75%<br>Step 3: Release slider | Progress: `75%` | AJAX request sent, progress saved in database, progress bar rendered at 75% | The system updates task completion percentage and reflects state visually in real time. | `PASS` | None |
| **TC-A028** | Verify weighted overall project progress recalculation | Step 1: Change Structural trade progress to 80%<br>Step 2: Observe overall project progress badge | Structural = 80%, Electrical = 20%, Piping = 10%, Finishing = 0% | Overall progress automatically recalculated using weighted engineering formula: `(80*0.40)+(20*0.25)+(10*0.20)+(0*0.15) = 39%` | The system automatically recalculates overall progression based on weighted trade formula. | `PASS` | None |
| **TC-A029** | Toggle task checklist item completion | Step 1: Locate task in checklist view<br>Step 2: Click checkbox next to task item | Task ID: `14` | Checklist state toggled to checked; strikethrough styling applied and state persisted | The system toggles task checklist status and persists state across page reloads. | `PASS` | None |
| **TC-A030** | Delete an existing project task | Step 1: Click delete trash icon on task row<br>Step 2: Confirm deletion in prompt | Task ID: `12` | Task removed from database and immediately removed from schedule table | The system permanently removes task and refreshes schedule view. | `PASS` | None |
| **TC-A031** | Assign personnel without active license or qualification | Step 1: Open task assignment dropdown<br>Step 2: Inspect personnel list for license status | Personnel without license | System flagged personnel with an expired or missing license using an advisory badge and prevented improper assignment. | The system should flag an advisory badge when assigning personnel with expired engineering licenses. | `PASS` | None |

---

### Use Case 5: Blueprints, 3D Renders & Site Photo Gallery (`ProjectPhoto` Model)

| TEST CASE ID | TEST DESCRIPTION | ACTION | INPUT DATA | ACTUAL OUTCOME | EXPECTED OUTCOME | STATUS | COMMENTS |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-A032** | Upload valid architectural blueprint image (JPG/PNG) | Step 1: Navigate to Project Gallery<br>Step 2: Select Category 'Technical Blueprints & CAD'<br>Step 3: Choose valid JPG file (4MB)<br>Step 4: Click Upload | Category: `blueprint`, File: `foundation_grid_rev2.jpg` (4.2MB) | File uploaded to `/storage/projects/photos`, thumbnail generated, added to gallery grid | The system successfully uploads the image and renders it in the designated gallery category. | `PASS` | None |
| **TC-A033** | Upload 3D concept render image | Step 1: Select Category '3D Concept Renders'<br>Step 2: Choose PNG file<br>Step 3: Click Upload | Category: `3d_render`, File: `facade_daylight_view.png` | Render saved and displayed under 3D Concept Renders tab | The system categorizes and stores render image properly. | `PASS` | None |
| **TC-A034** | Upload photo exceeding 10MB file size limit | Step 1: Select high-resolution drone photo (14.5MB)<br>Step 2: Click Upload | File: `site_aerial_orthomosaic.jpg` (14.5MB) | Upload blocked by validation; error message 'File size cannot exceed 10MB' displayed | The system rejects oversized uploads and displays an error message. | `PASS` | None |
| **TC-A035** | Upload disallowed file extension (`.exe` / `.sh`) | Step 1: Attempt to upload executable file disguised as photo<br>Step 2: Click Upload | File: `blueprint_patch.exe` | Validation halted upload; 'The photo must be an image of type: jpeg, png, webp' displayed | The system validates MIME type and blocks non-image file extensions. | `PASS` | None |
| **TC-A036** | Designate uploaded photo as Primary Hero Banner | Step 1: Hover over gallery photo card<br>Step 2: Click '★ Set Primary' button | Photo ID: `8` | `is_primary` flag set to true on photo record, previous primary cleared, project hero banner updated | The system sets photo as primary header image and updates project display. | `PASS` | None |
| **TC-A037** | Delete gallery photo | Step 1: Click delete button on photo card<br>Step 2: Confirm deletion | Photo ID: `8` | Database record deleted and storage file removed from disk | The system removes photo record and deletes file from server storage. | `PASS` | None |
| **TC-A038** | Open photo in full-screen Lightbox modal | Step 1: Click on photo thumbnail in gallery grid<br>Step 2: Verify high-res lightbox view | None | Lightbox modal opens with high-resolution image, title, and upload timestamp | The system renders an interactive full-screen modal showing full image details. | `PASS` | None |

---

### Use Case 6: Official Accomplishment Report & Signature Blocks (`Project`, `ProjectTask`, `ProjectMaterial` Models)

| TEST CASE ID | TEST DESCRIPTION | ACTION | INPUT DATA | ACTUAL OUTCOME | EXPECTED OUTCOME | STATUS | COMMENTS |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-A039** | Generate Official Project Accomplishment Report | Step 1: On `/projects/{id}`, click '🖨 Official Accomplishment Report'<br>Step 2: Verify printable document view loads | URL: `/projects/{id}/print-report` | Print-ready report renders with company letterhead, PCAB license, project specs, and trade progress | The system generates an official printable accomplishment report with all project analytics. | `PASS` | None |
| **TC-A040** | Verify four distinct formal signature blocks on report | Step 1: Scroll to bottom of Accomplishment Report<br>Step 2: Inspect signature block section | None | Report renders exactly four formal signature lines: Prepared By (Site Engr), Checked By (Project Mgr), Approved By (Managing Director), Conforme (Client) | The system displays all four mandatory engineering signature blocks. | `PASS` | None |
| **TC-A041** | Verify report print stylesheet layout (CSS `@media print`) | Step 1: Press `Ctrl+P` on report page<br>Step 2: Inspect print preview layout | None | Page margins align properly, navigation bars hidden, signature blocks anchored at page footer | The print preview cleanly formats content without cutoffs or overlapping headers. | `PASS` | None |

---

### Use Case 7: Project Costing Hub & Overrun Alerts (`ProjectCost`, `Project` Models)

| TEST CASE ID | TEST DESCRIPTION | ACTION | INPUT DATA | ACTUAL OUTCOME | EXPECTED OUTCOME | STATUS | COMMENTS |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-A042** | Record new project expenditure under specific Cost Head | Step 1: Navigate to `/costing`<br>Step 2: Select Project<br>Step 3: Select Cost Head 'Materials'<br>Step 4: Enter Description, Amount, Incurred Date<br>Step 5: Click Save | Project: `Horizon Heights`, Head: `Materials`, Desc: `Ready-mix concrete batch 4`, Amount: `185000`, Date: `2026-05-12` | Cost entry created, Materials head subtotal increments by ₱185,000, total project cost recalculated | The system records expenditure under selected cost head and updates financial totals. | `PASS` | None |
| **TC-A043** | Record expenditure with blank amount field | Step 1: Click 'Add Expense'<br>Step 2: Leave Amount field empty<br>Step 3: Click Save | Amount: *(blank)* | Submission blocked; validation message 'The amount field is required' displayed | The system prevents submission and indicates amount is required. | `PASS` | None |
| **TC-A044** | Record expenditure with negative amount value | Step 1: Click 'Add Expense'<br>Step 2: Enter negative amount<br>Step 3: Click Save | Amount: `-25000` | Validation failed; 'Amount must be greater than zero' prompted | The system blocks negative cost entries. | `PASS` | None |
| **TC-A045** | Update existing expenditure details and amount | Step 1: Click edit icon on cost item<br>Step 2: Change amount from ₱50,000 to ₱62,000<br>Step 3: Click Update | New Amount: `62000` | Cost record updated; cost head total and project gross margin recalculated immediately | The system updates record and refreshes financial totals. | `PASS` | None |
| **TC-A046** | Delete expenditure entry | Step 1: Click delete icon on cost item<br>Step 2: Confirm deletion | Cost ID: `5` | Cost item deleted; parent cost head total decrements accordingly | The system deletes record and updates parent totals. | `PASS` | None |
| **TC-A047** | Execute Cost Auto-Sync to synchronize project actual expenses | Step 1: On Costing Hub, click '⚡ Auto-Sync'<br>Step 2: Verify calculation results | None | System aggregated all BOM material usages, labor items, and logged costs into project `spent_budget` | The system synchronizes all internal costs and updates project financial spent figures. | `PASS` | None |
| **TC-A048** | Verify >85% Budget Warning indicator trigger | Step 1: Record costs bringing total spent to 88% of contract budget<br>Step 2: Click 'Auto-Sync'<br>Step 3: Observe budget health badge | Budget: `10,000,000`, Spent: `8,800,000` (88%) | Warning badge '⚠ Budget Alert: 88% Utilized' displayed with amber highlight | The system triggers a visual advisory when expenses cross 85% of budget ceiling. | `PASS` | None |
| **TC-A049** | Verify >100% Critical Budget Overrun warning trigger | Step 1: Record costs bringing total spent to 105% of contract budget<br>Step 2: Click 'Auto-Sync'<br>Step 3: Observe budget health badge | Budget: `10,000,000`, Spent: `10,500,000` (105%) | Critical badge '🚨 CRITICAL OVERRUN: 105%' displayed with pulsing red banner | The system displays a prominent critical alert indicating contract budget has been breached. | `PASS` | None |

---

### Use Case 8: Itemized DUPA Scope of Works & Construction Templates (`ProjectScopeItem`, `ProjectScopeLine` Models)

| TEST CASE ID | TEST DESCRIPTION | ACTION | INPUT DATA | ACTUAL OUTCOME | EXPECTED OUTCOME | STATUS | COMMENTS |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-A050** | Create a new Scope Item under project | Step 1: In Project Control, navigate to DUPA Scope tab<br>Step 2: Enter Scope Name 'Structural Concrete Works', Category 'Structural'<br>Step 3: Click 'Add Scope Item' | Scope Name: `Structural Concrete Works`, Category: `Structural` | Scope item created and displayed in DUPA breakdown tree | The system creates parent scope item under project. | `PASS` | None |
| **TC-A051** | Add itemized Scope Line (material/labor rate) to Scope Item | Step 1: Under Scope Item, click 'Add Line Item'<br>Step 2: Input Description 'Class A Ready Mix Concrete 3000 PSI', Qty: 45, Unit: 'cu.m', Unit Cost: 4800<br>Step 3: Click Save | Desc: `Class A Concrete`, Qty: `45`, Unit: `cu.m`, Unit Cost: `4800` | Line item created; subtotal calculated as ₱216,000 (45 * 4800) and added to scope total | The system computes line subtotal and updates parent scope item total. | `PASS` | None |
| **TC-A052** | Load pre-configured 2BR Bungalow construction template | Step 1: Click 'Load Template' dropdown<br>Step 2: Select '2-Bedroom Standard Bungalow Template'<br>Step 3: Confirm template loading | Template: `2BR Bungalow` | Pre-configured DUPA scope items and lines (Earthworks, Rebar, Masonry, Roofing) auto-populated into project | The system seeds complete standardized bill of quantities from template. | `PASS` | None |
| **TC-A053** | Load pre-configured Duplex Housing construction template | Step 1: Click 'Load Template'<br>Step 2: Select 'Duplex Housing Model Template'<br>Step 3: Confirm loading | Template: `Duplex Housing` | Duplex standardized scope items, quantities, and line rates successfully populated | The system imports all duplex scope specifications. | `PASS` | None |
| **TC-A054** | Generate printable itemized BOM sheet | Step 1: Click '🖨 Print Detailed BOM'<br>Step 2: Verify print view | URL: `/projects/{id}/print-bom` | Printable tabular format with scope items, unit costs, quantities, and grand total rendered | The system formats DUPA scope into clean printable engineering estimate. | `PASS` | None |

---

### Use Case 9: Central Inventory & Material Catalog (`Material`, `InventoryLog` Models)

| TEST CASE ID | TEST DESCRIPTION | ACTION | INPUT DATA | ACTUAL OUTCOME | EXPECTED OUTCOME | STATUS | COMMENTS |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-A055** | Register a new master material in central catalog | Step 1: Navigate to `/inventory`<br>Step 2: Click 'Register New Material'<br>Step 3: Input Material Code, Name, Category, Unit, Unit Cost, Initial Stock<br>Step 4: Click Save | Code: `MAT-STL-016`, Name: `16mm Deformed Steel Bar Grade 40`, Category: `Structural`, Unit: `pcs`, Cost: `485`, Stock: `500` | Material saved in catalog; initial stock audit log created in `inventory_logs` table | The system registers material in catalog and initializes inventory log record. | `PASS` | None |
| **TC-A056** | Register material with duplicate Material Code | Step 1: Click 'Register New Material'<br>Step 2: Enter an existing material code<br>Step 3: Click Save | Code: `MAT-STL-016` *(already exists)* | Validation blocked creation; error 'The material code has already been taken' displayed | The system enforces uniqueness on material code. | `PASS` | None |
| **TC-A057** | Update central warehouse stock via manual Restock | Step 1: Locate material in inventory list<br>Step 2: Click 'Restock'<br>Step 3: Enter Restock Qty: 250, Reason: 'Bulk Supplier Delivery'<br>Step 4: Click Confirm | Material: `16mm Rebar`, Qty: `250`, Reason: `Bulk Delivery` | Stock incremented from 500 to 750; inventory log recorded with type 'restock' | The system increments stock quantity and logs the restocking transaction. | `PASS` | None |
| **TC-A058** | Inspect inventory transaction history log | Step 1: Navigate to Inventory Audit Logs tab<br>Step 2: Filter by transaction type | Type: `All` | Full chronological table displayed showing timestamps, material, quantity change, user, and engineering remarks | The system displays complete tamper-evident inventory audit trail. | `PASS` | None |

---

### Use Case 10: Bill of Materials (BOM) & Daily Consumption (`ProjectMaterial`, `DailyMaterialUsage` Models)

| TEST CASE ID | TEST DESCRIPTION | ACTION | INPUT DATA | ACTUAL OUTCOME | EXPECTED OUTCOME | STATUS | COMMENTS |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-A059** | Allocate materials from central inventory to project BOM | Step 1: Navigate to `/bom/project/{id}`<br>Step 2: Select Material from dropdown<br>Step 3: Enter Allocated Quantity: 150<br>Step 4: Click 'Allocate Material' | Material: `Portland Cement Type 1`, Allocated Qty: `150` | Central stock deducted by 150; project BOM shows allocated_qty: 150, used_qty: 0; log recorded | The system deducts warehouse stock and credits materials to project BOM. | `PASS` | None |
| **TC-A060** | Attempt allocating quantity exceeding warehouse available stock | Step 1: Select material with warehouse stock of 50 units<br>Step 2: Attempt to allocate 120 units<br>Step 3: Click 'Allocate Material' | Available Stock: `50`, Requested Qty: `120` | Transaction blocked; error 'Insufficient warehouse stock! Available: 50 units' displayed | The system blocks allocation and notifies user of insufficient stock. | `PASS` | None |
| **TC-A061** | Record daily material consumption on job site | Step 1: In project BOM table, locate allocated material<br>Step 2: Click 'Log Daily Usage'<br>Step 3: Enter Qty Used: 35, Date: `2026-05-14`, Remarks: 'Ground floor slab casting'<br>Step 4: Click Save | Qty: `35`, Date: `2026-05-14`, Remarks: `Ground floor slab casting` | DailyMaterialUsage record created; ProjectMaterial used_qty updated from 0 to 35; remaining unconsumed recalculated to 115 | The system records daily usage log and increments cumulative consumed quantity. | `PASS` | None |
| **TC-A062** | Record daily usage exceeding total allocated BOM quantity | Step 1: Project has 20 remaining allocated units<br>Step 2: Attempt to log daily usage of 30 units<br>Step 3: Click Save | Allocated Remaining: `20`, Logged Usage: `30` | Transaction blocked; error 'Usage cannot exceed remaining allocated quantity' displayed | The system prevents logging consumption greater than allocated balance. | `PASS` | None |

---

### Use Case 11: Excess Material Return & Warehouse Reconciliation (`ProjectMaterial`, `Material`, `InventoryLog` Models)

| TEST CASE ID | TEST DESCRIPTION | ACTION | INPUT DATA | ACTUAL OUTCOME | EXPECTED OUTCOME | STATUS | COMMENTS |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-A063** | Return unused site materials to central warehouse inventory | Step 1: Locate completed project BOM item with unconsumed balance<br>Step 2: Click '↩ Return Excess to Central Inventory'<br>Step 3: Enter Return Qty: 20, Reason: 'Project completed with excess stock' | Material: `Portland Cement`, Allocated: `100`, Used: `80`, Return Qty: `20` | Warehouse central stock credited by +20; ProjectMaterial updated; inventory_log recorded with type 'excess_return' and reference 'RET-202605-001' | The system returns unused materials to warehouse stock and creates an excess return audit log. | `PASS` | None |
| **TC-A064** | Attempt returning quantity greater than unconsumed site balance | Step 1: Unconsumed site balance is 15 units<br>Step 2: Enter Return Qty: 25<br>Step 3: Click Submit | Unconsumed Balance: `15`, Return Qty: `25` | Submission rejected; error message 'Return quantity cannot exceed remaining site balance (15)' displayed | The system validates that returned quantity does not exceed unconsumed site balance. | `PASS` | None |
| **TC-A065** | Attempt returning zero or negative quantity of materials | Step 1: Enter Return Qty: 0 or -5<br>Step 2: Click Submit | Return Qty: `0` | Validation halted submission; error requires quantity to be at least 1 unit | The system requires positive integer for return quantity. | `PASS` | None |

---

### Use Case 12: Financial Billing, Proof Upload & Official Receipt Voucher (`Payment`, `Project` Models)

| TEST CASE ID | TEST DESCRIPTION | ACTION | INPUT DATA | ACTUAL OUTCOME | EXPECTED OUTCOME | STATUS | COMMENTS |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-A066** | Record client milestone billing payment with proof attachment | Step 1: Navigate to `/payments`<br>Step 2: Click 'Record Payment'<br>Step 3: Select Project, Invoice No, OR No, Payer, Amount, Stage, Date<br>Step 4: Upload bank slip JPG | Invoice: `INV-2026-008`, OR: `OR-202605-014`, Payer: `Vanguard Corp`, Amount: `1250000`, Stage: `30% Milestone`, File: `deposit_slip.jpg` | Payment record created; slip saved to `/uploads/receipts`; project collected revenue updated | The system records payment transaction and stores proof-of-payment attachment. | `PASS` | None |
| **TC-A067** | Record payment missing Invoice Number | Step 1: Click 'Record Payment'<br>Step 2: Leave Invoice No empty<br>Step 3: Click Save | Invoice No: *(blank)*, Amount: `500000` | Validation blocked submission; 'The invoice no field is required' displayed | The system requires invoice number and halts submission. | `PASS` | None |
| **TC-A068** | Record payment with negative amount | Step 1: Click 'Record Payment'<br>Step 2: Enter Amount: -75000<br>Step 3: Click Save | Amount: `-75000` | Validation blocked submission; amount must be greater than zero | The system blocks negative payment amounts. | `PASS` | None |
| **TC-A069** | Upload disallowed attachment file type (`.bat` / `.exe`) on payment | Step 1: Click 'Record Payment'<br>Step 2: Attach executable script file as receipt<br>Step 3: Click Save | File: `payment_script.bat` | Validation rejected upload; error indicates only PDF, JPG, PNG files allowed | The system blocks dangerous file types on payment attachments. | `PASS` | None |
| **TC-A070** | Update payment status from Pending to Paid / Cleared | Step 1: In payments table, locate pending payment<br>Step 2: Change status dropdown to 'paid'<br>Step 3: Confirm update | Payment ID: `4`, New Status: `paid` | Status updated; `payment_first_cleared` flag set to true; dashboard collected revenue increments | The system updates status and recalculates collected revenue. | `PASS` | None |
| **TC-A071** | Generate printable Official Receipt (OR) Voucher | Step 1: In payments table, click 'Print Receipt' on payment record<br>Step 2: Inspect generated voucher | URL: `/payments/{id}/receipt` | Official company voucher loads displaying letterhead, BIR TIN, PCAB license, amount in figures & words, and signature lines | The system renders a legally-compliant printable official receipt voucher. | `PASS` | None |
| **TC-A072** | Verify duplicate Official Receipt Number rejection | Step 1: Record payment using existing OR number<br>Step 2: Click Save | OR: `OR-202605-014` *(already registered)* | System rejected the payment and displayed a warning that the Official Receipt Number already exists. The duplicate OR number was not saved. | The system should enforce unique constraint on official receipt numbers to prevent tax compliance conflicts. | `PASS` | None |

---

### Use Case 13: Service Cost Estimator & 1-Click Init (`ServiceRequest`, `Project` Models)

| TEST CASE ID | TEST DESCRIPTION | ACTION | INPUT DATA | ACTUAL OUTCOME | EXPECTED OUTCOME | STATUS | COMMENTS |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-A073** | Calculate construction cost estimate with valid parameters | Step 1: Navigate to `/estimation`<br>Step 2: Enter Client Name, Land Area (300 sqm), Floor Area (220 sqm)<br>Step 3: Select Service Tier 'Residential Build'<br>Step 4: Click 'Calculate Estimate' | Land: `300`, Floor: `220`, Tier: `Residential Build`, Rate: `₱25,000/sqm` | Estimated total cost calculated as ₱5,500,000 (220 * 25,000) with detailed trade breakdown | The system computes estimated construction cost and displays trade breakdown. | `PASS` | None |
| **TC-A074** | Calculate estimate with negative or zero area inputs | Step 1: Enter Land Area: -50, Floor Area: 0<br>Step 2: Click 'Calculate Estimate' | Land Area: `-50`, Floor Area: `0` | Validation failed; error requires positive numerical area values | The system rejects negative or zero area values. | `PASS` | None |
| **TC-A075** | Initialize active project from estimate via 1-Click Init | Step 1: On saved service estimate, click '⚡ 1-Click Init Project'<br>Step 2: Confirm project creation in modal | Estimate Code: `EST-2026-003` | Service request status converted to 'approved'; new Project record created with matching budget, client, and areas; redirected to project control | The system converts estimate into active project and redirects user to project control. | `PASS` | None |

---

### Use Case 14: Centralized Supplier Hub & Admin Purchase Orders (`Supplier`, `SupplierOrder` Models)

| TEST CASE ID | TEST DESCRIPTION | ACTION | INPUT DATA | ACTUAL OUTCOME | EXPECTED OUTCOME | STATUS | COMMENTS |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-A076** | Admin places purchase order to accredited supplier | Step 1: Navigate to `/suppliers/orders`<br>Step 2: Click 'Create Purchase Order'<br>Step 3: Select Supplier 'Titan Steel', Project 'Horizon Heights', Delivery Date, Location<br>Step 4: Add Line Items: 200 pcs 12mm Rebar | Supplier: `Titan Steel`, Project: `Horizon Heights`, Qty: `200 pcs @ ₱380`, Total: `₱76,000` | Purchase order created with code `ORD-2026-001`; status set to 'pending'; order pushed to supplier portal | The system creates purchase order, calculates totals, and routes order to supplier. | `PASS` | None |
| **TC-A077** | Create purchase order without selecting a supplier | Step 1: Open Create PO form<br>Step 2: Leave Supplier unselected<br>Step 3: Fill items and submit | Supplier: *(none)* | Validation halted submission; 'The supplier id field is required' displayed | The system requires supplier selection and blocks submission. | `PASS` | None |
| **TC-A078** | Admin marks delivered purchase order as 'Received' and syncs to inventory | Step 1: Locate delivered supplier order<br>Step 2: Click 'Receive & Sync to Warehouse'<br>Step 3: Confirm receipt | Order ID: `ORD-2026-001` | Order status updated to 'completed'; material stock in materials table incremented; inventory_log created | The system marks order completed and automatically credits delivered items to warehouse catalog. | `PASS` | None |
| **TC-A079** | Admin toggles supplier account status (Active / Inactive) | Step 1: Navigate to `/suppliers`<br>Step 2: Click 'Deactivate' on supplier record<br>Step 3: Observe status change | Supplier ID: `3` | Supplier status changed to 'inactive'; supplier cannot receive new purchase orders | The system updates supplier active status and restricts PO creation for inactive suppliers. | `PASS` | None |

---

### Use Case 15: Dedicated Roofing Material Transfer Station (`Material`, `ProjectMaterialTransfer` Models)

| TEST CASE ID | TEST DESCRIPTION | ACTION | INPUT DATA | ACTUAL OUTCOME | EXPECTED OUTCOME | STATUS | COMMENTS |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-A080** | Roofing Officer dispatches stock from warehouse to job site | Step 1: Log in as Roofing Transfer Officer<br>Step 2: Navigate to `/roofing-transfer`<br>Step 3: In Dispatch from Stock form, select Material 'Rib-Type Long Span Roofing'<br>Step 4: Select Destination Project, enter Qty: 80 sheets | Material: `Long Span Roofing 0.5mm`, Project: `Horizon Heights`, Qty: `80` | Warehouse roofing stock deducted by 80; destination project BOM credited by +80; voucher generated; inventory log created | The system dispatches stock to project BOM and logs transfer transaction. | `PASS` | None |
| **TC-A081** | Roofing Officer attempts stock dispatch exceeding warehouse availability | Step 1: Warehouse stock is 40 sheets<br>Step 2: Enter Dispatch Qty: 60 sheets<br>Step 3: Click 'Dispatch to Project' | Stock: `40`, Dispatch Qty: `60` | Dispatch rejected; error 'Insufficient warehouse stock! Current available: 40' displayed | The system blocks dispatch when quantity exceeds warehouse stock. | `PASS` | None |
| **TC-A082** | Roofing Officer executes Inter-Project transfer between two active sites | Step 1: In Inter-Project Transfer form, select Source Project 'Azure Villas', Destination Project 'Horizon Heights'<br>Step 2: Select Material 'C-Purlins 2x4'<br>Step 3: Enter Qty: 40 pcs | Source: `Azure Villas`, Dest: `Horizon Heights`, Qty: `40` | Source project BOM balance deducted by 40; destination project BOM balance credited by +40; inter-project transfer log created | The system transfers material between projects and maintains balanced project accounts. | `PASS` | None |
| **TC-A083** | Attempt inter-project transfer where source and destination projects are identical | Step 1: Select Source Project 'Azure Villas'<br>Step 2: Select Destination Project 'Azure Villas'<br>Step 3: Click 'Transfer Between Sites' | Source: `Azure Villas`, Dest: `Azure Villas` | Submission rejected; error 'Destination project must be different from source project' displayed | The system prevents transferring material to the same project. | `PASS` | None |
| **TC-A084** | Roofing Officer processes excess roofing material return to stock | Step 1: Select project with unconsumed roofing materials<br>Step 2: Enter Return Qty: 15 sheets<br>Step 3: Click 'Return Excess to Stock' | Project: `Horizon Heights`, Qty: `15 sheets` | Warehouse roofing stock credited by +15; project BOM allocated reduced; transaction log recorded | The system returns excess materials to central inventory. | `PASS` | None |
| **TC-A085** | Master Admin attempts to execute stock dispatch in Roofing Station | Step 1: Log in as Master Admin<br>Step 2: Navigate to `/roofing-transfer`<br>Step 3: Attempt to submit dispatch form | Role: `admin` | Request intercepted; error 'Admins have View-Only access in dedicated transfer stations' displayed | The system enforces role restrictions, granting Master Admin view-only access to dedicated hubs. | `PASS` | None |

---

### Use Case 16: Dedicated Windows & Doors Material Transfer Station (`Material`, `ProjectMaterialTransfer` Models)

| TEST CASE ID | TEST DESCRIPTION | ACTION | INPUT DATA | ACTUAL OUTCOME | EXPECTED OUTCOME | STATUS | COMMENTS |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-A086** | Windows & Doors Officer dispatches stock to project site | Step 1: Log in as W&D Transfer Officer<br>Step 2: Navigate to `/windows-doors-transfer`<br>Step 3: Select Material 'Aluminum Sliding Window 120x120cm'<br>Step 4: Select Destination Project, enter Qty: 12 sets | Material: `Sliding Window 120x120cm`, Project: `Azure Villas`, Qty: `12 sets` | Warehouse stock deducted by 12; Azure Villas BOM credited by +12; voucher generated | The system dispatches windows and doors stock to target project BOM. | `PASS` | None |
| **TC-A087** | Windows & Doors Officer executes inter-project transfer | Step 1: Select Source Project, Destination Project, Material 'Flush Solid Door'<br>Step 2: Enter Qty: 6 units<br>Step 3: Click 'Transfer Between Sites' | Source: `Horizon Heights`, Dest: `Azure Villas`, Qty: `6` | Stock moved between project BOMs successfully; transfer voucher issued | The system transfers items between project sites accurately. | `PASS` | None |
| **TC-A088** | Master Admin attempts mutation in Windows & Doors Station | Step 1: Log in as Master Admin<br>Step 2: Attempt to submit W&D restock or dispatch form | Role: `admin` | Mutation intercepted; role middleware prevents non-transfer officers from modifying stock | The system restricts modification privileges to authorized Windows & Doors officers. | `PASS` | None |

---

### Use Case 17: Inter-Project Transfer Vouchers & Auditing (`ProjectMaterialTransfer` Model)

| TEST CASE ID | TEST DESCRIPTION | ACTION | INPUT DATA | ACTUAL OUTCOME | EXPECTED OUTCOME | STATUS | COMMENTS |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-A089** | Roofing Officer views and prints Material Transfer Voucher | Step 1: In Transfer History table, click 'View Voucher'<br>Step 2: Verify voucher view | URL: `/roofing-transfer/voucher/{id}` | Voucher loads with official company letterhead, voucher reference ID (`TRF-...`), source, destination, materials, and signature lines | The system renders printable transfer voucher with complete audit information. | `PASS` | None |
| **TC-A090** | Master Admin views Transfer Voucher in auditing mode | Step 1: Log in as Master Admin<br>Step 2: Open voucher link `/windows-doors-transfer/voucher/{id}` | Role: `admin` | Voucher renders successfully in read-only mode for administrative auditing | The system allows Master Admin to inspect transfer vouchers. | `PASS` | None |
| **TC-A091** | Verify voucher timestamp and user attribution integrity | Step 1: Generate voucher for recent transfer<br>Step 2: Inspect `dispatched_by` user name and timestamp | None | Voucher accurately displays full name of officer who processed dispatch and exact server timestamp | The system accurately records and presents officer name and execution timestamp. | `PASS` | None |

---

### Use Case 18: Supplier Portal: Catalog & Quick Stock Management (`SupplierMaterial`, `Supplier` Models)

| TEST CASE ID | TEST DESCRIPTION | ACTION | INPUT DATA | ACTUAL OUTCOME | EXPECTED OUTCOME | STATUS | COMMENTS |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-A092** | Supplier adds new material item to their catalog | Step 1: Log in as Supplier<br>Step 2: Navigate to `/supplier/materials`<br>Step 3: Click 'Add New Product'<br>Step 4: Enter Name, Category, Unit, Unit Price, Stock Qty, MOQ | Name: `Heavy Duty Accordion Security Gate`, Category: `Windows & Doors`, Unit: `sets`, Price: `18500`, Stock: `25`, MOQ: `1` | Product registered in `supplier_materials` table and appears on supplier catalog page | The system creates supplier material record and displays it in the vendor catalog. | `PASS` | None |
| **TC-A093** | Supplier adds product with negative unit price | Step 1: Click 'Add New Product'<br>Step 2: Enter Unit Price: -250<br>Step 3: Click Save | Unit Price: `-250` | Validation blocked creation; 'Unit price must be a positive number' displayed | The system rejects negative product pricing. | `PASS` | None |
| **TC-A094** | Supplier performs Quick Stock level adjustment | Step 1: In catalog table, click 'Quick Stock' on product<br>Step 2: Enter new quantity: 45<br>Step 3: Click Update | Product ID: `2`, New Stock: `45` | `available_quantity` field updated in database and table badge reflects 45 units available | The system updates stock quantity immediately without full page reload. | `PASS` | None |
| **TC-A095** | Supplier soft deletes / removes catalog product | Step 1: Click delete icon on product row<br>Step 2: Confirm deletion | Product ID: `2` | Product marked inactive or removed from catalog; no longer selectable in purchase orders | The system removes product from active catalog. | `PASS` | None |

---

### Use Case 19: Supplier Portal: Order Fulfillment & Inquiries (`SupplierOrder`, `SupplierOrderMessage`, `SupplierInquiry` Models)

| TEST CASE ID | TEST DESCRIPTION | ACTION | INPUT DATA | ACTUAL OUTCOME | EXPECTED OUTCOME | STATUS | COMMENTS |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-A096** | Supplier views incoming purchase orders list | Step 1: Navigate to `/supplier/orders`<br>Step 2: Inspect orders list table | Role: `supplier` | Orders displayed showing Order Code, Target Project, Items, Total Price, and Status badge | The system displays all purchase orders assigned to the logged-in supplier. | `PASS` | None |
| **TC-A097** | Supplier updates order status from 'Pending' to 'Confirmed' | Step 1: Open order details `/supplier/orders/{id}`<br>Step 2: Click 'Accept & Confirm Order'<br>Step 3: Confirm action | Order Code: `ORD-2026-001`, New Status: `confirmed` | Order status updated to confirmed; `supplier_order_logs` audit record created | The system updates order status and creates a status audit log. | `PASS` | None |
| **TC-A098** | Supplier advances order status to 'Ready for Delivery' | Step 1: On order details, click 'Mark Ready for Delivery'<br>Step 2: Confirm update | Status: `ready_for_delivery` | Order status changed; timestamp recorded; notification pushed to admin dashboard | The system advances order status in the fulfillment pipeline. | `PASS` | None |
| **TC-A099** | Send two-way chat message on specific purchase order thread | Step 1: In order conversation box, type 'Delivery truck scheduled to arrive tomorrow at 9:00 AM'<br>Step 2: Click Send | Message: `Delivery truck scheduled to arrive tomorrow at 9:00 AM` | Message stored in `supplier_order_messages` and appears in conversation thread with timestamp | The system saves and displays message in order communication thread. | `PASS` | None |
| **TC-A100** | Supplier responds to technical material inquiry from construction firm | Step 1: Navigate to `/supplier/inquiries`<br>Step 2: Open pending inquiry<br>Step 3: Type response details<br>Step 4: Click 'Submit Response' | Inquiry: `Galvanized coating thickness specs`, Response: `All sheets comply with ASTM A653 G90 standard` | Response saved in `supplier_inquiries` table; inquiry status updated to 'answered' | The system records vendor response and updates inquiry status. | `PASS` | None |

---

### Use Case 20: Engineering Personnel Roster & Project Assignment (`Personnel`, `Project` Models)

| TEST CASE ID | TEST DESCRIPTION | ACTION | INPUT DATA | ACTUAL OUTCOME | EXPECTED OUTCOME | STATUS | COMMENTS |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-A101** | Register a new engineer or architect in company roster | Step 1: Navigate to `/personnel`<br>Step 2: Click 'Add Personnel'<br>Step 3: Enter Name, Professional Title, Email, Phone, License No, Specialization<br>Step 4: Click Save | Name: `Engr. Maria Santos`, Title: `Senior Structural Engineer`, Email: `maria.santos@firm.ph`, License: `PRC-0148921`, Spec: `High-Rise Concrete` | Personnel record created and visible in engineering directory | The system registers professional in roster. | `PASS` | None |
| **TC-A102** | Register personnel with duplicate email address | Step 1: Click 'Add Personnel'<br>Step 2: Enter an existing personnel email<br>Step 3: Click Save | Email: `maria.santos@firm.ph` *(duplicate)* | Validation blocked registration; error 'The email has already been taken' displayed | The system enforces unique email addresses across personnel. | `PASS` | None |
| **TC-A103** | Assign personnel to project lead role | Step 1: On `/projects/{id}`, click 'Assign Personnel'<br>Step 2: Select Engr. Maria Santos, Role: 'Lead Structural Engineer'<br>Step 3: Click Assign | Personnel ID: `5`, Role: `Lead Structural Engineer` | Record created in `project_personnel` pivot table; engineer listed in project header directory | The system associates personnel with project and updates project personnel roster. | `PASS` | None |

---

## 3. Part 2: Blackbox Beta Testing Register (TC-B001 to TC-B013)

### Use Case 21: Pre-Construction Onboarding & Payment-First Gate (Client & Finance UAT)

| TEST CASE ID | TEST DESCRIPTION | ACTION | INPUT DATA | ACTUAL OUTCOME | EXPECTED OUTCOME | STATUS | COMMENTS |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-B001** | End-to-End Client Intake to Project Initialization | Step 1: Client submits request via portal<br>Step 2: Estimator applies Bungalow Template<br>Step 3: Click '1-Click Init Project' | Client: `Arch. Daniel Lim`, Area: `180 sqm`, Est Budget: `₱4,200,000` | New Project created with seeded DUPA breakdown, initial status 'Planning', and code assigned | End-to-end conversion from inquiry to project workspace succeeds without data loss. | `PASS` | None |
| **TC-B002** | Payment-First Policy Site Mobilization Block | Step 1: Enable `payment_first_policy`<br>Step 2: Site Engineer attempts to start task without cleared payment | `payment_first_policy: true`, `status: pending` | Site task execution displays 'Hold Site Works: Pending Mobilization Drawdown' banner | System strictly enforces Payment-First policy by disabling site progress logging until deposit is cleared. | `PASS` | None |
| **TC-B003** | Bank Loan 1st Tranche Clearance & Site Unblock | Step 1: Finance logs Bank Drawdown #1 (₱840,000)<br>Step 2: Attach bank deposit slip<br>Step 3: Set status to `paid` | Payer: `BDO Unibank`, Amount: `₱840,000`, Stage: `Mobilization 20%` | Badge transitions to 'Payment Cleared • Authorized to Construct' (Green); Site tasks unlocked | Verification of bank drawdown automatically authorizes site engineers to begin mobilization. | `PASS` | None |

---

### Use Case 22: Multi-Trade Field Progress & Auto-Handover (Site Engineering UAT)

| TEST CASE ID | TEST DESCRIPTION | ACTION | INPUT DATA | ACTUAL OUTCOME | EXPECTED OUTCOME | STATUS | COMMENTS |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-B004** | Site Engineer logs daily multi-trade progression on mobile | Step 1: Access `/projects/{id}` on tablet<br>Step 2: Update Structural task to 100%, Electrical to 60%, Piping to 40%<br>Step 3: Upload site progress photo | Structural: `100%`, Electrical: `60%`, Piping: `40%`, Photo: `slab_casting.jpg` | Project overall progress dynamically updates to `63%`; photo tagged under milestone gallery | Mobile field updates calculate weighted progress accurately and append photos to timeline. | `PASS` | None |
| **TC-B005** | Final Finishing Trade 100% Completion & Auto-Handover | Step 1: Mark all remaining Finishing tasks 100%<br>Step 2: Trigger trade recalculation | All 4 trades = `100%` | Overall progress hits 100%; Project status changes to `completed` and `actual_completion_date` auto-fills with `now()` | Project automatically completes and records completion timestamp when all trades reach 100%. | `PASS` | None |

---

### Use Case 23: Supply Chain Vendor Fulfillment & Warehouse Ingestion (Supplier & Procurement UAT)

| TEST CASE ID | TEST DESCRIPTION | ACTION | INPUT DATA | ACTUAL OUTCOME | EXPECTED OUTCOME | STATUS | COMMENTS |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-B006** | End-to-End Purchase Order Procurement & In-Transit Chat | Step 1: Admin sends PO for 150 pcs Purlins<br>Step 2: Supplier accepts PO on Supplier Portal<br>Step 3: Supplier posts dispatch message with truck plate | PO Code: `ORD-2026-045`, Supplier: `Titan Steel`, Message: `Truck NBD-4412 dispatched` | Order status moves from 'Pending' to 'In Transit'; message visible in real-time order thread | Supplier and procurement communicate smoothly through dedicated PO thread. | `PASS` | None |
| **TC-B007** | Warehouse Ingestion & Idempotent Stock Crediting | Step 1: Warehouse Custodian clicks 'Receive & Sync to Warehouse'<br>Step 2: Confirm delivery receipt<br>Step 3: Refresh page and re-verify | Order: `ORD-2026-045`, Delivery Date: `Today` | Central warehouse stock increments by 150 pcs; `is_synced_to_inventory` set to true; second sync attempt blocked | Warehouse inventory increments correctly and duplicate ingestion is strictly prevented. | `PASS` | None |

---

### Use Case 24: Inter-Site Trade Logistics & Material Optimization (Transfer Officer UAT)

| TEST CASE ID | TEST DESCRIPTION | ACTION | INPUT DATA | ACTUAL OUTCOME | EXPECTED OUTCOME | STATUS | COMMENTS |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-B008** | Inter-Project Roofing Reallocation between active sites | Step 1: Roofing Officer logs into station<br>Step 2: Select Source Project A, Dest Project B<br>Step 3: Transfer 35 sheets Rib-Type Roofing | Source: `Horizon Heights`, Dest: `Azure Villas`, Qty: `35 sheets` | Project A BOM reduced by 35; Project B BOM credited by 35; Transfer voucher `#TRF-ROOF-09` generated | Inter-site transfers reallocate materials without creating inventory discrepancies. | `PASS` | None |
| **TC-B009** | Windows & Doors Installation & Surplus Stock Return | Step 1: W&D Officer identifies 4 uninstalled sliding windows<br>Step 2: Execute 'Return Excess to Stock' | Project: `Azure Villas`, Material: `Sliding Window 120x120`, Qty: `4 sets` | Site allocated balance deducted by 4; Central Warehouse stock credited by +4 with audit log | Unused fixtures return to central inventory safely after site turnover. | `PASS` | None |

---

### Use Case 25: Financial Cost Engineering, Overrun Control & Audit (Executive UAT)

| TEST CASE ID | TEST DESCRIPTION | ACTION | INPUT DATA | ACTUAL OUTCOME | EXPECTED OUTCOME | STATUS | COMMENTS |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-B010** | Unplanned Site Expense & Automated Overrun Warning | Step 1: Cost Engineer logs emergency equipment repair (₱145,000)<br>Step 2: Total expenses exceed 85% of budget | Category: `Equipment`, Amount: `₱145,000`, Date: `2026-05-18` | Costing Hub activates amber warning badge: '⚠ Budget Alert: 89% Utilized' | System alerts management before budget is completely exhausted. | `PASS` | None |
| **TC-B011** | Official Accomplishment Report Generation & Client Sign-Off | Step 1: Project Manager opens Accomplishment Report<br>Step 2: Verify PCAB License, trade breakdown, and four signature lines<br>Step 3: Print / Save to PDF | Project: `Horizon Heights`, Target: `30% Progress Billing` | Print preview formats cleanly on A4; includes Site Engr, Project Mgr, Managing Director, and Client sign-offs | Printable accomplishment report serves as legally binding billing documentation for bank drawdowns. | `PASS` | None |

---

### Use Case 26: Workforce Licensure Compliance & High Concurrency Field Load (Site Safety & Load UAT)

| TEST CASE ID | TEST DESCRIPTION | ACTION | INPUT DATA | ACTUAL OUTCOME | EXPECTED OUTCOME | STATUS | COMMENTS |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-B012** | Expired PRC License Flagging during Site Assignment | Step 1: Open project team roster<br>Step 2: Review personnel with expired PRC ID | Personnel: `Engr. Roberto Garcia` (PRC Expired: `2025-11-30`) | Roster highlights red badge 'EXPIRED LICENSE'; system blocks assignment as Project In-Charge | Safety compliance rule prevents deploying engineers with invalid credentials to active projects. | `PASS` | None |
| **TC-B013** | Multi-User Concurrent Site Updates under 4G Field Network | Step 1: 5 field users submit daily material usage and task progress simultaneously<br>Step 2: Check database integrity and response times | 5 concurrent requests on LTE network simulation (200ms latency) | All 5 transactions committed successfully without deadlocks or corrupted stock counters | System maintains transaction isolation and performance stability during peak site operational hours. | `PASS` | None |
