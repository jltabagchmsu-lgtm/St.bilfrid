<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\Personnel;
use App\Models\Material;
use App\Models\ProjectMaterial;
use App\Models\ProjectTask;
use App\Models\ServiceRequest;
use App\Models\Payment;
use App\Models\ProjectCost;
use App\Models\ProjectPhoto;
use App\Models\InventoryLog;
use App\Models\DailyMaterialUsage;
use App\Models\ProjectMaterialTransfer;
use App\Models\User;
use App\Models\Supplier;
use App\Models\SupplierMaterial;
use App\Models\SupplierOrder;
use App\Models\SupplierOrderItem;
use App\Models\SupplierOrderLog;
use App\Models\SupplierNotification;
use Illuminate\Support\Facades\Hash;

class ConstructionSystemSeeder extends Seeder
{
    public function run(): void
    {
        // 0. System User Accounts
        // 0.1 Master Administrator
        User::updateOrCreate(
            ['email' => 'admin@newconstuc.firm'],
            [
                'name' => 'Master Administrator',
                'role' => 'admin',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
            ]
        );

        // 0.2 Roofing Materials Transfer Account
        User::updateOrCreate(
            ['email' => 'roofing@newconstuc.firm'],
            [
                'name' => 'Roofing Materials Transfer Officer',
                'role' => 'roofing_transfer',
                'password' => Hash::make('roofing123'),
                'email_verified_at' => now(),
            ]
        );

        // 0.3 Windows & Doors Materials Transfer Account
        User::updateOrCreate(
            ['email' => 'windows.doors@newconstuc.firm'],
            [
                'name' => 'Windows & Doors Materials Transfer Officer',
                'role' => 'windows_doors_transfer',
                'password' => Hash::make('windows123'),
                'email_verified_at' => now(),
            ]
        );

        // 1. Personnel (Licensed Engineers, Architects & Developers)
        $pLonzaga = Personnel::create([
            'name' => 'Engr. Ignacio S. Lonzaga',
            'title' => 'Registered Civil Engineer',
            'email' => 'i.lonzaga@newconstuc.firm',
            'phone' => '+63 (34) 495-2019',
            'license_no' => '0042019',
            'specialization' => 'Residential Build, Structural Design & Subdivision Development (PTR: 2901354, Silay City)',
        ]);

        $pMitra = Personnel::create([
            'name' => 'Engr. Esabyl B. Mitra',
            'title' => 'Registered Civil Engineer',
            'email' => 'e.mitra@newconstuc.firm',
            'phone' => '+63 (34) 495-3199',
            'license_no' => '0180490',
            'specialization' => 'Civil Engineering, Bill of Materials & Cost Estimation (PTR: 4531999, Silay City)',
        ]);

        $p1 = Personnel::create([
            'name' => 'Arch. Marcus Vance',
            'title' => 'Lead Principal Architect',
            'email' => 'm.vance@newconstuc.firm',
            'phone' => '+63 (2) 8892-1090',
            'license_no' => 'ARC-991204',
            'specialization' => 'Commercial High-Rise & Modern Glassmorphic Structures',
        ]);

        $p2 = Personnel::create([
            'name' => 'Engr. Elena Rostova',
            'title' => 'Chief Structural Engineer',
            'email' => 'e.rostova@newconstuc.firm',
            'phone' => '+63 (2) 8892-3412',
            'license_no' => 'PE-330412',
            'specialization' => 'Seismic & Heavy Steel Foundation Design',
        ]);

        $p3 = Personnel::create([
            'name' => 'Engr. Carlos Rodriguez',
            'title' => 'Lead Electrical Engineer',
            'email' => 'c.rodriguez@newconstuc.firm',
            'phone' => '+63 (2) 8892-5501',
            'license_no' => 'EE-771029',
            'specialization' => 'HVAC, Transformers & High-Voltage Grid Systems',
        ]);

        $p4 = Personnel::create([
            'name' => 'Engr. David Kim',
            'title' => 'Senior Plumbing & Piping Engineer',
            'email' => 'd.kim@newconstuc.firm',
            'phone' => '+63 (2) 8892-8877',
            'license_no' => 'ME-445109',
            'specialization' => 'Hydraulic Risers & Wastewater Treatment',
        ]);

        $p5 = Personnel::create([
            'name' => 'Engr. Sophia Martinez',
            'title' => 'Project Director & Financial Controller',
            'email' => 's.martinez@newconstuc.firm',
            'phone' => '+63 (2) 8892-9900',
            'license_no' => 'PMP-882190',
            'specialization' => 'Project Operations & Financial Auditing',
        ]);

        // 2. Master Materials Catalog in Warehouse
        $m1 = Material::create(['material_code' => 'MAT-CEM-01', 'name' => 'Portland Cement (Type I)', 'category' => 'Structural', 'unit' => 'bags', 'unit_cost' => 225.00, 'stock_quantity' => 15000]);
        $m2 = Material::create(['material_code' => 'MAT-STEEL-16', 'name' => '16mm Deformed Bar (Grade 60)', 'category' => 'Structural', 'unit' => 'pcs', 'unit_cost' => 450.00, 'stock_quantity' => 8500]);
        $m3 = Material::create(['material_code' => 'MAT-STEEL-12', 'name' => '12mm Deformed Bar', 'category' => 'Structural', 'unit' => 'pcs', 'unit_cost' => 310.00, 'stock_quantity' => 12000]);
        $m4 = Material::create(['material_code' => 'MAT-STEEL-10', 'name' => '10mm Deformed Bar', 'category' => 'Structural', 'unit' => 'pcs', 'unit_cost' => 220.00, 'stock_quantity' => 18000]);
        $m5 = Material::create(['material_code' => 'MAT-STEEL-08', 'name' => '8mm Deformed Bar', 'category' => 'Structural', 'unit' => 'pcs', 'unit_cost' => 120.00, 'stock_quantity' => 14000]);
        $m6 = Material::create(['material_code' => 'MAT-AGG-SAND', 'name' => 'Mixing Sand (Coarse / Fine)', 'category' => 'Structural', 'unit' => 'cu.m', 'unit_cost' => 850.00, 'stock_quantity' => 2500]);
        $m7 = Material::create(['material_code' => 'MAT-AGG-GRAV', 'name' => '3/4 Crushed Gravel', 'category' => 'Structural', 'unit' => 'cu.m', 'unit_cost' => 1410.00, 'stock_quantity' => 2000]);
        $m8 = Material::create(['material_code' => 'MAT-CHB-04', 'name' => '4" Concrete Hollow Block (CHB)', 'category' => 'Structural', 'unit' => 'pcs', 'unit_cost' => 13.00, 'stock_quantity' => 35000]);
        $m9 = Material::create(['material_code' => 'MAT-CHB-06', 'name' => '6" Concrete Hollow Block (CHB)', 'category' => 'Structural', 'unit' => 'pcs', 'unit_cost' => 16.00, 'stock_quantity' => 15000]);
        
        // Roofing Specific Catalog Items
        $m10 = Material::create(['material_code' => 'MAT-ROOF-RIB', 'name' => 'Rib-Type Pre-Painted Long Span Roofing (0.40mm)', 'category' => 'Roofing & Metal Sheets', 'unit' => 'ln.m.', 'unit_cost' => 410.00, 'stock_quantity' => 5000]);
        $mRoofPurlin = Material::create(['material_code' => 'MAT-ROOF-PUR2X4', 'name' => 'C-Purlins 2" x 4" x 1.20mm Heavy Gauge', 'category' => 'Roofing & Metal Sheets', 'unit' => 'pcs', 'unit_cost' => 485.00, 'stock_quantity' => 1200]);
        $mRoofPurlin2 = Material::create(['material_code' => 'MAT-ROOF-PUR2X3', 'name' => 'C-Purlins 2" x 3" x 1.00mm Structural', 'category' => 'Roofing & Metal Sheets', 'unit' => 'pcs', 'unit_cost' => 390.00, 'stock_quantity' => 1500]);
        $mRoofFlash = Material::create(['material_code' => 'MAT-ROOF-FLASH', 'name' => 'Pre-Painted Ridge Cap & Wall Flashing 8ft', 'category' => 'Roofing & Metal Sheets', 'unit' => 'pcs', 'unit_cost' => 340.00, 'stock_quantity' => 800]);
        $mRoofGutter = Material::create(['material_code' => 'MAT-ROOF-GUTTER', 'name' => 'Stainless Steel Spanish Box Gutter 8ft', 'category' => 'Roofing & Metal Sheets', 'unit' => 'pcs', 'unit_cost' => 580.00, 'stock_quantity' => 600]);
        $mRoofScrew = Material::create(['material_code' => 'MAT-ROOF-TEK', 'name' => '2-1/2" Tekscrew Self-Drilling for Metal Roofing', 'category' => 'Roofing & Metal Sheets', 'unit' => 'boxes', 'unit_cost' => 650.00, 'stock_quantity' => 300]);
        $mRoofSeal = Material::create(['material_code' => 'MAT-ROOF-SEAL', 'name' => 'Elastomeric Weatherproof Roof Sealant (1-Gallon)', 'category' => 'Roofing & Metal Sheets', 'unit' => 'cans', 'unit_cost' => 920.00, 'stock_quantity' => 250]);
        $mRoofInsu = Material::create(['material_code' => 'MAT-ROOF-INSU', 'name' => 'Double-Sided Aluminum Thermal Roof Insulation (50m)', 'category' => 'Roofing & Metal Sheets', 'unit' => 'rolls', 'unit_cost' => 2850.00, 'stock_quantity' => 120]);

        // Windows & Doors Specific Catalog Items
        $mDoorPanel = Material::create(['material_code' => 'MAT-DOOR-PNL90', 'name' => 'Main Solid Mahogany Panel Door 0.90m x 2.10m', 'category' => 'Windows & Doors', 'unit' => 'sets', 'unit_cost' => 4500.00, 'stock_quantity' => 80]);
        $mDoorFlush80 = Material::create(['material_code' => 'MAT-DOOR-FLSH80', 'name' => 'Bedroom Solid Core Flush Door 0.80m x 2.10m', 'category' => 'Windows & Doors', 'unit' => 'sets', 'unit_cost' => 4200.00, 'stock_quantity' => 120]);
        $mDoorFlush70 = Material::create(['material_code' => 'MAT-DOOR-FLSH70', 'name' => 'Balcony/Service Flush Door 0.70m x 2.10m', 'category' => 'Windows & Doors', 'unit' => 'sets', 'unit_cost' => 3800.00, 'stock_quantity' => 110]);
        $mDoorPvc = Material::create(['material_code' => 'MAT-DOOR-PVC60', 'name' => 'Heavy-Duty PVC Door w/ Louver & Jamb 0.60m x 2.10m', 'category' => 'Windows & Doors', 'unit' => 'sets', 'unit_cost' => 1700.00, 'stock_quantity' => 150]);
        $mDoorSlide150 = Material::create(['material_code' => 'MAT-DOOR-SLD150', 'name' => '1.50m x 2.10m Sliding Patio Glass Door on Aluminum Frame', 'category' => 'Windows & Doors', 'unit' => 'sets', 'unit_cost' => 20000.00, 'stock_quantity' => 40]);
        $mDoorJamb = Material::create(['material_code' => 'MAT-DOOR-JAMB2X4', 'name' => 'Treated Solid Wood Door Jamb 2" x 4"', 'category' => 'Windows & Doors', 'unit' => 'sets', 'unit_cost' => 1300.00, 'stock_quantity' => 200]);
        $mDoorLockMain = Material::create(['material_code' => 'MAT-DOOR-LCKMAIN', 'name' => 'Heavy-Duty Lever Entrance Lockset (Main Door)', 'category' => 'Windows & Doors', 'unit' => 'sets', 'unit_cost' => 3000.00, 'stock_quantity' => 140]);
        $mDoorLockBed = Material::create(['material_code' => 'MAT-DOOR-LCKBED', 'name' => 'Cylindrical Bedroom Door Knob Lockset', 'category' => 'Windows & Doors', 'unit' => 'sets', 'unit_cost' => 1500.00, 'stock_quantity' => 250]);
        $mDoorHinges = Material::create(['material_code' => 'MAT-DOOR-HNGE', 'name' => 'Stainless Steel Ball Bearing Loosepin Hinges 3.5"x3.5"', 'category' => 'Windows & Doors', 'unit' => 'pairs', 'unit_cost' => 220.00, 'stock_quantity' => 600]);

        $mWinSlide120 = Material::create(['material_code' => 'MAT-WIN-SLD120', 'name' => '1.20m x 1.20m Sliding Window 1/4" Glass on Aluminum Frame', 'category' => 'Windows & Doors', 'unit' => 'units', 'unit_cost' => 6300.00, 'stock_quantity' => 90]);
        $mWinSlide200 = Material::create(['material_code' => 'MAT-WIN-SLD200', 'name' => '1.20m x 2.00m Sliding Window 1/4" Glass on Aluminum Frame', 'category' => 'Windows & Doors', 'unit' => 'units', 'unit_cost' => 10500.00, 'stock_quantity' => 45]);
        $mWinSlide60 = Material::create(['material_code' => 'MAT-WIN-SLD60', 'name' => '0.60m x 0.90m Bathroom Frosted Sliding Window Aluminum Frame', 'category' => 'Windows & Doors', 'unit' => 'units', 'unit_cost' => 2360.00, 'stock_quantity' => 100]);
        $mWinAwn180 = Material::create(['material_code' => 'MAT-WIN-AWN180', 'name' => '1.80m x 0.45m Awning Casement Window Aluminum Frame', 'category' => 'Windows & Doors', 'unit' => 'units', 'unit_cost' => 3540.00, 'stock_quantity' => 65]);

        $m11 = Material::create(['material_code' => 'MAT-ELE-THW12', 'name' => 'THW Copper Wire #12 (3.5mm²)', 'category' => 'Electrical', 'unit' => 'boxes', 'unit_cost' => 5000.00, 'stock_quantity' => 250]);
        $m12 = Material::create(['material_code' => 'MAT-PIP-PVC04', 'name' => '4" Sanitary PVC Pipe', 'category' => 'Piping/Plumbing', 'unit' => 'pcs', 'unit_cost' => 420.00, 'stock_quantity' => 3500]);

        // ==========================================
        // 3. Active Project: Apex Horizon Commercial Tower
        // ==========================================
        $prj1 = Project::create([
            'project_code' => 'PRJ-2026-001',
            'title' => 'Apex Horizon Commercial Tower',
            'client_name' => 'Apex Global Holdings',
            'location' => 'Financial District, Block 4',
            'project_type' => 'Commercial Construction',
            'land_area_sqm' => 1450.00,
            'floor_area_sqm' => 8200.00,
            'status' => 'in_progress',
            'contract_budget' => 45000000.00,
            'spent_budget' => 26500000.00,
            'financing_type' => 'bank_loan',
            'financing_institution' => 'BDO Unibank - Commercial Loan Division',
            'loan_account_no' => 'BDO-LOG-2026-8812',
            'approved_loan_amount' => 36000000.00, // 80%
            'client_equity_amount' => 9000000.00,  // 20%
            'payment_first_policy' => true,
            'start_date' => '2026-01-15',
            'end_date' => '2026-11-30',
            'structural_progress' => 85,
            'electrical_progress' => 60,
            'piping_progress' => 55,
            'finishing_progress' => 30,
            'structural_weight' => 40,
            'electrical_weight' => 25,
            'piping_weight' => 20,
            'finishing_weight' => 15,
            'overall_progress' => 65,
            'current_phase' => 'Phase 3: MEP Rough-in & Conduits',
            'description' => '15-story modern commercial tower with underground parking, HVAC chillers, and energy-efficient glass facade.',
            'schedule_notes' => 'Financed via BDO Letter of Guaranty. Payment First policy active: Tranche 2 released. Tranche 3 pending bank inspection.',
            'deployed_workers' => 45,
            'deployed_skilled_workers' => 22,
            'deployed_engineers' => 6,
            'deployed_architects' => 2,
            'deployed_foremen' => 3,
            'deployed_operators' => 4,
            'deployed_safety_officers' => 2,
        ]);

        $prj1->personnel()->attach([
            $p1->id => ['assignment_role' => 'Lead Architect'],
            $p2->id => ['assignment_role' => 'Project Lead & Structural Engineer'],
            $p3->id => ['assignment_role' => 'Electrical Specialist'],
            $p4->id => ['assignment_role' => 'Plumbing Engineer'],
        ]);

        ProjectPhoto::create([
            'project_id' => $prj1->id,
            'photo_type' => 'blueprint',
            'title' => 'Structural Framing & Column Grid CAD Drawing',
            'description' => 'Approved Level 1-15 structural framing plan showing shear wall core and 56m grid layout.',
            'file_path' => '/uploads/projects/blueprint_tower_cad.svg',
            'is_primary' => false,
            'taken_at' => '2026-01-10',
        ]);

        ProjectPhoto::create([
            'project_id' => $prj1->id,
            'photo_type' => '3d_render',
            'title' => '3D Architectural Exterior Glass Facade Concept Render',
            'description' => 'Client target design with glassmorphic curtain wall, rooftop crown spire, and pedestrian entrance.',
            'file_path' => '/uploads/projects/render_tower_3d.svg',
            'is_primary' => true,
            'taken_at' => '2026-01-12',
        ]);

        ProjectPhoto::create([
            'project_id' => $prj1->id,
            'photo_type' => 'actual_site',
            'title' => 'Level 8 Concrete Slab Pouring & Tower Crane In-Progress',
            'description' => 'Active site photograph showing 55m jib tower crane, rebar formwork, and green safety netting.',
            'file_path' => '/uploads/projects/site_progress_concrete.svg',
            'is_primary' => false,
            'taken_at' => '2026-05-18',
        ]);

        $pm1 = ProjectMaterial::create(['project_id' => $prj1->id, 'material_id' => $m1->id, 'allocated_qty' => 5000, 'used_qty' => 4200, 'excess_returned_qty' => 500, 'unit_price' => 225.00]);
        $pm2 = ProjectMaterial::create(['project_id' => $prj1->id, 'material_id' => $m2->id, 'allocated_qty' => 3000, 'used_qty' => 2500, 'excess_returned_qty' => 200, 'unit_price' => 450.00]);

        InventoryLog::create([
            'material_id' => $m1->id,
            'project_id' => $prj1->id,
            'transaction_type' => 'excess_return',
            'quantity' => 500,
            'unit_cost' => 225.00,
            'reference_no' => 'RET-202604-001',
            'notes' => 'Returned 500 bags of unused cement surplus from foundation slab phase.',
        ]);

        DailyMaterialUsage::create([
            'project_id' => $prj1->id,
            'project_material_id' => $pm1->id,
            'material_id' => $m1->id,
            'usage_date' => date('Y-m-d'),
            'quantity_used' => 50,
            'activity_description' => 'Level 9 Shear Core Wall Grouting & Pouring',
            'logged_by' => 'Engr. Elena Rostova',
            'notes' => '50 bags used today. Remaining on-site stock is ready for tomorrow morning floor screeding.',
        ]);

        ProjectTask::create(['project_id' => $prj1->id, 'task_name' => 'Foundation & Basement Structure', 'category' => 'Structural', 'assigned_personnel_id' => $p2->id, 'start_date' => '2026-01-15', 'due_date' => '2026-04-30', 'allocated_budget' => 9500000, 'actual_cost' => 9400000, 'progress' => 100, 'status' => 'completed']);
        ProjectTask::create(['project_id' => $prj1->id, 'task_name' => 'Main Concrete Frame (Floors 1-15)', 'category' => 'Structural', 'assigned_personnel_id' => $p2->id, 'start_date' => '2026-05-01', 'due_date' => '2026-08-31', 'allocated_budget' => 12000000, 'actual_cost' => 9800000, 'progress' => 85, 'status' => 'in_progress']);
        ProjectTask::create(['project_id' => $prj1->id, 'task_name' => 'Electrical Conduit & High-Voltage Transformers', 'category' => 'Electrical', 'assigned_personnel_id' => $p3->id, 'start_date' => '2026-06-15', 'due_date' => '2026-09-30', 'allocated_budget' => 6000000, 'actual_cost' => 3800000, 'progress' => 60, 'status' => 'in_progress']);
        ProjectTask::create(['project_id' => $prj1->id, 'task_name' => 'Water Riser & Fire Sprinkler Piping Lines', 'category' => 'Piping', 'assigned_personnel_id' => $p4->id, 'start_date' => '2026-06-20', 'due_date' => '2026-10-15', 'allocated_budget' => 4500000, 'actual_cost' => 2400000, 'progress' => 55, 'status' => 'in_progress']);

        Payment::create([
            'project_id' => $prj1->id,
            'invoice_no' => 'INV-2026-001',
            'official_receipt_no' => 'OR-202601-8812',
            'payer_name' => 'BDO Unibank (fbo Apex Global Holdings)',
            'amount' => 13500000.00,
            'payment_date' => '2026-01-20',
            'payment_stage' => 'Initial Mobilization & Substructure (Tranche 1)',
            'payment_method' => 'Bank Wire (RTGS)',
            'financing_type' => 'bank_loan',
            'financing_institution' => 'BDO Unibank',
            'loan_reference_no' => 'BDO-LOG-2026-8812',
            'disbursing_entity' => 'BDO Commercial Loan Disbursement Unit',
            'drawdown_tranche' => 'Tranche 1: 30% Foundation & Substructure Release',
            'payment_first_cleared' => true,
            'construction_clearance_status' => 'cleared_to_construct',
            'bank_reference' => 'MB-TXN-2026-99182301',
            'received_by' => 'Engr. Sophia Martinez, PMP',
            'receipt_file' => 'proof_payment_deposit.svg',
            'status' => 'paid',
            'notes' => 'Cleared downpayment per BDO Letter of Guaranty clause 4.1. Construction authorized.',
        ]);

        Payment::create([
            'project_id' => $prj1->id,
            'invoice_no' => 'INV-2026-002',
            'official_receipt_no' => 'OR-202605-9014',
            'payer_name' => 'BDO Unibank (fbo Apex Global Holdings)',
            'amount' => 13500000.00,
            'payment_date' => '2026-05-10',
            'payment_stage' => 'Superstructure & Level 10 Concrete Frame (Tranche 2)',
            'payment_method' => 'Bank Wire (RTGS)',
            'financing_type' => 'bank_loan',
            'financing_institution' => 'BDO Unibank',
            'loan_reference_no' => 'BDO-LOG-2026-8812',
            'disbursing_entity' => 'BDO Commercial Loan Disbursement Unit',
            'drawdown_tranche' => 'Tranche 2: 30% Superstructure Framing Release',
            'payment_first_cleared' => true,
            'construction_clearance_status' => 'cleared_to_construct',
            'bank_reference' => 'BDO-WIRE-2026-8840192',
            'received_by' => 'Engr. Sophia Martinez, PMP',
            'receipt_file' => 'proof_wire_transfer.svg',
            'status' => 'paid',
            'notes' => 'Substructure inspection passed by BDO Appraiser. Funds cleared. Construction authorized.',
        ]);

        ProjectCost::create(['project_id' => $prj1->id, 'cost_code' => 'CST-2026-001', 'cost_category' => 'Materials & Consumables', 'item_name' => 'High-Strength Ready-Mix Concrete & Deformed Rebar Package', 'cost_type' => 'Direct', 'quantity' => 1, 'unit' => 'lot', 'unit_rate' => 11000000.00, 'estimated_cost' => 11000000.00, 'actual_cost' => 10500000.00, 'status' => 'incurred', 'cost_date' => '2026-02-10', 'vendor_payee' => 'Holcim & SteelAsia Corp', 'reference_no' => 'PO-ST-8821', 'notes' => 'Basement to 10th floor structural pour requirements.']);
        ProjectCost::create(['project_id' => $prj1->id, 'cost_code' => 'CST-2026-002', 'cost_category' => 'Labor & Engineering', 'item_name' => 'Master Formwork, Rebar & Carpentry Crew (120 Days)', 'cost_type' => 'Direct', 'quantity' => 120, 'unit' => 'days', 'unit_rate' => 52000.00, 'estimated_cost' => 6500000.00, 'actual_cost' => 6240000.00, 'status' => 'incurred', 'cost_date' => '2026-04-15', 'vendor_payee' => 'Summit Manpower Services', 'reference_no' => 'VOUCH-2026-104', 'notes' => 'Certified structural formwork and tie-wire crew.']);
        ProjectCost::create(['project_id' => $prj1->id, 'cost_code' => 'CST-2026-003', 'cost_category' => 'Equipment & Heavy Machinery', 'item_name' => 'Tower Crane 55m Jib & Concrete Boom Pump Hire', 'cost_type' => 'Direct', 'quantity' => 6, 'unit' => 'months', 'unit_rate' => 650000.00, 'estimated_cost' => 4000000.00, 'actual_cost' => 3900000.00, 'status' => 'incurred', 'cost_date' => '2026-03-01', 'vendor_payee' => 'Pacific Heavy Rigging Ltd.', 'reference_no' => 'LEAS-CRN-991', 'notes' => 'Includes certified operator and regular OSHA maintenance.']);

        $prj1->spent_budget = $prj1->costs()->sum('actual_cost');
        $prj1->save();

        // =========================================================================
        // 4. COMPLETED PROJECT 1 (PDF 1): 3 Bedroom Bungalow Single Detached
        // Location: Block 15 Lot 1, Villa Romeo Subd., Brgy. 5, Silay City, Neg. Occ.
        // Total Cost: ₱1,778,062.08 | Completed: Jan 7, 2025
        // =========================================================================
        $prjVR15 = Project::create([
            'project_code' => 'PRJ-2025-VR15',
            'title' => '3 Bedroom Bungalow Single Detached Residential Unit',
            'client_name' => 'MC HIRO Realty Corp.',
            'location' => 'Block 15 Lot 1, Villa Romeo Subd., Brgy. 5, Silay City, Neg. Occ.',
            'project_type' => 'Residential Build',
            'land_area_sqm' => 150.00,
            'floor_area_sqm' => 75.00,
            'status' => 'completed',
            'contract_budget' => 1778062.08,
            'spent_budget' => 1546819.20,
            'financing_type' => 'bank_loan',
            'financing_institution' => 'BDO Unibank / Residential Financing Division',
            'loan_account_no' => 'BDO-VR-2024-1501',
            'approved_loan_amount' => 1422449.66, // 80%
            'client_equity_amount' => 355612.42,  // 20%
            'payment_first_policy' => true,
            'start_date' => '2024-06-01',
            'end_date' => '2025-01-15',
            'actual_completion_date' => '2025-01-07',
            'structural_progress' => 100,
            'electrical_progress' => 100,
            'piping_progress' => 100,
            'finishing_progress' => 100,
            'structural_weight' => 40,
            'electrical_weight' => 25,
            'piping_weight' => 20,
            'finishing_weight' => 15,
            'overall_progress' => 100,
            'current_phase' => 'Phase 5: Completed & Turned Over',
            'description' => '3-Bedroom single detached bungalow residential unit located at Block 15 Lot 1, Villa Romeo Subdivision, Brgy. 5, Silay City, Negros Occidental. Developer: MC HIRO Realty Corp., Certified by Engr. Esabyl B. Mitra (PRC License No. 0180490, PTR No. 4531999) and approved by Engr. Ignacio S. Lonzaga (Owner / MC HIRO Realty Corp.).',
            'schedule_notes' => 'Construction successfully completed, inspected, and turned over to owner on January 7, 2025. All warranties, as-built plans, and certificates issued.',
            'deployed_workers' => 10,
            'deployed_skilled_workers' => 5,
            'deployed_engineers' => 2,
            'deployed_architects' => 1,
            'deployed_foremen' => 1,
            'deployed_operators' => 1,
            'deployed_safety_officers' => 1,
        ]);

        $prjVR15->personnel()->attach([
            $pMitra->id => ['assignment_role' => 'Certified Civil Engineer & Cost Estimator'],
            $pLonzaga->id => ['assignment_role' => 'Project Owner & Executive Civil Engineer'],
        ]);

        ProjectPhoto::create([
            'project_id' => $prjVR15->id,
            'photo_type' => 'blueprint',
            'title' => 'Approved 3BR Bungalow Architectural & Structural Plan',
            'description' => 'Official structural layout, foundation footing plan, and 3-bedroom interior partitions blueprint.',
            'file_path' => '/uploads/projects/blueprint_villa_floorplan.svg',
            'is_primary' => false,
            'taken_at' => '2024-05-25',
        ]);

        ProjectPhoto::create([
            'project_id' => $prjVR15->id,
            'photo_type' => '3d_render',
            'title' => '3D Architectural Exterior Concept Render',
            'description' => '3D render of 3-bedroom single detached bungalow with perimeter fencing, porch, and long span roofing.',
            'file_path' => '/uploads/projects/render_villa_luxury.svg',
            'is_primary' => false,
            'taken_at' => '2024-05-28',
        ]);

        ProjectPhoto::create([
            'project_id' => $prjVR15->id,
            'photo_type' => 'actual_site',
            'title' => 'Turned Over 3BR Bungalow As-Built Residence',
            'description' => 'Completed and turned over single detached residential unit at Block 15 Lot 1, Villa Romeo Subd., Silay City.',
            'file_path' => '/uploads/projects/site_progress_concrete.svg',
            'is_primary' => true,
            'taken_at' => '2025-01-07',
        ]);

        // Load 20-Item Official Bill of Materials & Cost Estimates (₱1,778,062.08)
        app(\App\Http\Controllers\ProjectScopeController::class)->load3BrBungalowTemplate($prjVR15->id);

        // Payments for 3BR Bungalow (Fully Settled ₱1,778,062.08)
        Payment::create([
            'project_id' => $prjVR15->id,
            'invoice_no' => 'INV-VR15-001',
            'official_receipt_no' => 'OR-202406-1501',
            'payer_name' => 'MC HIRO Realty Corp. (Client Equity Downpayment)',
            'amount' => 533418.62,
            'payment_date' => '2024-06-10',
            'payment_stage' => '30% Mobilization & Substructure Downpayment',
            'payment_method' => 'Bank Wire (RTGS)',
            'financing_type' => 'client_equity',
            'financing_institution' => 'BDO Unibank',
            'loan_reference_no' => 'BDO-VR-2024-1501',
            'disbursing_entity' => 'MC HIRO Realty Corp.',
            'drawdown_tranche' => 'Tranche 1: 30% Substructure & Foundation Release',
            'payment_first_cleared' => true,
            'construction_clearance_status' => 'cleared_to_construct',
            'bank_reference' => 'BDO-TXN-VR15-101',
            'received_by' => 'Engr. Esabyl B. Mitra',
            'status' => 'paid',
            'notes' => 'Equity downpayment cleared. Foundation excavation and footing construction authorized.',
        ]);

        Payment::create([
            'project_id' => $prjVR15->id,
            'invoice_no' => 'INV-VR15-002',
            'official_receipt_no' => 'OR-202410-1502',
            'payer_name' => 'BDO Unibank (fbo MC HIRO Realty Corp.)',
            'amount' => 711224.83,
            'payment_date' => '2024-10-15',
            'payment_stage' => '40% Superstructure Framing, Masonry & Roofing Milestone',
            'payment_method' => 'Bank Wire (RTGS)',
            'financing_type' => 'bank_loan',
            'financing_institution' => 'BDO Unibank',
            'loan_reference_no' => 'BDO-VR-2024-1501',
            'disbursing_entity' => 'BDO Commercial Loan Disbursement Unit',
            'drawdown_tranche' => 'Tranche 2: 40% Superstructure & Enclosure Release',
            'payment_first_cleared' => true,
            'construction_clearance_status' => 'cleared_to_construct',
            'bank_reference' => 'BDO-TXN-VR15-202',
            'received_by' => 'Engr. Esabyl B. Mitra',
            'status' => 'paid',
            'notes' => 'Bank progress inspection passed. Beams, columns, masonry and roofing installation verified.',
        ]);

        Payment::create([
            'project_id' => $prjVR15->id,
            'invoice_no' => 'INV-VR15-003',
            'official_receipt_no' => 'OR-202501-1503',
            'payer_name' => 'BDO Unibank (fbo MC HIRO Realty Corp.)',
            'amount' => 533418.63,
            'payment_date' => '2025-01-07',
            'payment_stage' => '30% Final Finishes, Turnover & Retention Settlement',
            'payment_method' => 'Bank Wire (RTGS)',
            'financing_type' => 'bank_loan',
            'financing_institution' => 'BDO Unibank',
            'loan_reference_no' => 'BDO-VR-2024-1501',
            'disbursing_entity' => 'BDO Commercial Loan Disbursement Unit',
            'drawdown_tranche' => 'Tranche 3: 30% Final Turnover & Retention Release',
            'payment_first_cleared' => true,
            'construction_clearance_status' => 'cleared_to_construct',
            'bank_reference' => 'BDO-TXN-VR15-303',
            'received_by' => 'Engr. Esabyl B. Mitra',
            'status' => 'paid',
            'notes' => 'Final client turnover and acceptance signed. Certificate of occupancy issued.',
        ]);

        // Cost Records for 3BR Bungalow (Total Actual Spend: ₱1,546,819.20 -> Realized Profit Margin: ₱231,242.88 / 13.0%)
        ProjectCost::create(['project_id' => $prjVR15->id, 'cost_code' => 'CST-VR15-001', 'cost_category' => 'Materials & Consumables', 'item_name' => 'Structural Cement, Deformed Bars, Gravel, Sand & CHB Package', 'cost_type' => 'Direct', 'quantity' => 1, 'unit' => 'lot', 'unit_rate' => 520000.00, 'estimated_cost' => 525000.00, 'actual_cost' => 520000.00, 'status' => 'settled', 'cost_date' => '2024-06-20', 'vendor_payee' => 'Silay Hardware & Building Supply', 'reference_no' => 'PO-VR15-01', 'notes' => 'Footings, columns, beams and slab materials.']);
        ProjectCost::create(['project_id' => $prjVR15->id, 'cost_code' => 'CST-VR15-002', 'cost_category' => 'Materials & Consumables', 'item_name' => 'Pre-painted Long Span Roofing, C-Purlins, Doors, Windows & Tile Finishes', 'cost_type' => 'Direct', 'quantity' => 1, 'unit' => 'lot', 'unit_rate' => 443512.00, 'estimated_cost' => 445000.00, 'actual_cost' => 443512.00, 'status' => 'settled', 'cost_date' => '2024-09-15', 'vendor_payee' => 'Negros Architectural Supplies', 'reference_no' => 'PO-VR15-02', 'notes' => 'Roofing rib sheets, ficem board ceiling and ceramic tiles.']);
        ProjectCost::create(['project_id' => $prjVR15->id, 'cost_code' => 'CST-VR15-003', 'cost_category' => 'Labor & Engineering', 'item_name' => 'Masonry, Rebar, Carpentry, Plumbing, Electrical & Painting Crew', 'cost_type' => 'Direct', 'quantity' => 1, 'unit' => 'lot', 'unit_rate' => 433580.40, 'estimated_cost' => 435000.00, 'actual_cost' => 433580.40, 'status' => 'settled', 'cost_date' => '2024-12-20', 'vendor_payee' => 'Villa Romeo Construction Craftsmen', 'reference_no' => 'VOUCH-VR15-03', 'notes' => '45% standard labor allocation for items 1-20.1.']);
        ProjectCost::create(['project_id' => $prjVR15->id, 'cost_code' => 'CST-VR15-004', 'cost_category' => 'Contingency & Testing', 'item_name' => 'Price Escalation, Site Testing & 2-Year Force Majeure Insurance', 'cost_type' => 'Contingency', 'quantity' => 1, 'unit' => 'lot', 'unit_rate' => 149726.80, 'estimated_cost' => 150000.00, 'actual_cost' => 149726.80, 'status' => 'settled', 'cost_date' => '2025-01-05', 'vendor_payee' => 'Engineering QA & Insurance Group', 'reference_no' => 'INS-VR15-04', 'notes' => 'Contingencies and 2-year force majeure insurance coverage.']);

        $prjVR15->spent_budget = $prjVR15->costs()->sum('actual_cost');
        $prjVR15->save();

        // =========================================================================
        // 5. COMPLETED PROJECT 2 (PDF 2): 2 Bedroom Bungalow Single Detached
        // Location: Block 16 Lot 8, Villa Romeo Subd., Brgy. V, Silay City, Neg. Occ.
        // Total Cost: ₱1,831,613.80 | Completed: Jan 7, 2025
        // =========================================================================
        $prjVR16 = Project::create([
            'project_code' => 'PRJ-2025-VR16',
            'title' => '2 Bedroom Bungalow, Single Detached Residential Building',
            'client_name' => 'Engr. Ignacio S. Lonzaga / Villa Romeo Subdivision',
            'location' => 'Block 16 Lot 8, Villa Romeo Subdivision, Brgy. V, Silay City, Negros Occidental',
            'project_type' => 'Residential Build',
            'land_area_sqm' => 135.00,
            'floor_area_sqm' => 50.00,
            'status' => 'completed',
            'contract_budget' => 1831613.80,
            'spent_budget' => 1617478.80,
            'financing_type' => 'pagibig_loan',
            'financing_institution' => 'Pag-IBIG Fund (HDMF) - Housing Loan Division',
            'loan_account_no' => 'HDMF-VR-2024-1608',
            'approved_loan_amount' => 1465291.04, // 80%
            'client_equity_amount' => 366322.76,  // 20%
            'payment_first_policy' => true,
            'start_date' => '2024-07-01',
            'end_date' => '2025-01-20',
            'actual_completion_date' => '2025-01-07',
            'structural_progress' => 100,
            'electrical_progress' => 100,
            'piping_progress' => 100,
            'finishing_progress' => 100,
            'structural_weight' => 40,
            'electrical_weight' => 25,
            'piping_weight' => 20,
            'finishing_weight' => 15,
            'overall_progress' => 100,
            'current_phase' => 'Phase 5: Completed & Turned Over',
            'description' => '2-Bedroom single-detached residential bungalow (50 sq.m floor area on 135 sq.m lot) at Block 16 Lot 8, Villa Romeo Subdivision, Brgy. V, Silay City, Negros Occidental. Certified by Engr. Esabyl B. Mitra (PRC No. 0180490, PTR No. 4531999) and approved by Engr. Ignacio S. Lonzaga (Owner / Developer).',
            'schedule_notes' => 'All 18 scope milestones completed. HDMF loan take-out finalized. As-built plans and occupancy permit released January 7, 2025.',
            'deployed_workers' => 8,
            'deployed_skilled_workers' => 5,
            'deployed_engineers' => 2,
            'deployed_architects' => 1,
            'deployed_foremen' => 1,
            'deployed_operators' => 1,
            'deployed_safety_officers' => 1,
        ]);

        $prjVR16->personnel()->attach([
            $pMitra->id => ['assignment_role' => 'Certified Civil Engineer & Cost Estimator'],
            $pLonzaga->id => ['assignment_role' => 'Project Owner & Executive Civil Engineer'],
        ]);

        ProjectPhoto::create([
            'project_id' => $prjVR16->id,
            'photo_type' => 'blueprint',
            'title' => 'Architectural Ground Floor Plan & Elevation Blueprint',
            'description' => 'Approved 2-bedroom floor plan showing master bedroom, bedroom 2, 2 CRs, kitchen counter, and porch.',
            'file_path' => '/uploads/projects/blueprint_villa_floorplan.svg',
            'is_primary' => false,
            'taken_at' => '2024-06-25',
        ]);

        ProjectPhoto::create([
            'project_id' => $prjVR16->id,
            'photo_type' => '3d_render',
            'title' => '3D Architectural Exterior Concept Render',
            'description' => 'Photorealistic render showing travertine stone accent wall, front canopy molding, and sliding aluminum windows.',
            'file_path' => '/uploads/projects/render_villa_luxury.svg',
            'is_primary' => false,
            'taken_at' => '2024-06-28',
        ]);

        ProjectPhoto::create([
            'project_id' => $prjVR16->id,
            'photo_type' => 'actual_site',
            'title' => 'Turned Over 2BR Bungalow As-Built Residence',
            'description' => 'Completed and turned over single-detached 2-bedroom residence at Block 16 Lot 8, Villa Romeo Subdivision.',
            'file_path' => '/uploads/projects/site_progress_concrete.svg',
            'is_primary' => true,
            'taken_at' => '2025-01-07',
        ]);

        // Load 18-Item Official Bill of Materials & Cost Estimates (₱1,831,613.80)
        app(\App\Http\Controllers\ProjectScopeController::class)->load2BrBungalowTemplate($prjVR16->id);

        // Payments for 2BR Bungalow (Fully Settled ₱1,831,613.80)
        Payment::create([
            'project_id' => $prjVR16->id,
            'invoice_no' => 'INV-VR16-001',
            'official_receipt_no' => 'OR-202407-1601',
            'payer_name' => 'Engr. Ignacio S. Lonzaga (Client Personal Equity)',
            'amount' => 549484.14,
            'payment_date' => '2024-07-12',
            'payment_stage' => '30% Equity & Mobilization Drawdown',
            'payment_method' => 'Bank Wire (RTGS)',
            'financing_type' => 'client_equity',
            'financing_institution' => 'Client Direct Equity',
            'loan_reference_no' => 'HDMF-VR-2024-1608',
            'disbursing_entity' => 'Owner Personal Account',
            'drawdown_tranche' => 'Tranche 1: 30% Substructure Release',
            'payment_first_cleared' => true,
            'construction_clearance_status' => 'cleared_to_construct',
            'bank_reference' => 'BPI-VR16-01',
            'received_by' => 'Engr. Esabyl B. Mitra',
            'status' => 'paid',
            'notes' => 'Initial equity cleared. Foundation footing and columns construction authorized.',
        ]);

        Payment::create([
            'project_id' => $prjVR16->id,
            'invoice_no' => 'INV-VR16-002',
            'official_receipt_no' => 'OR-202411-1602',
            'payer_name' => 'Pag-IBIG Fund / HDMF (fbo Engr. Ignacio S. Lonzaga)',
            'amount' => 732645.52,
            'payment_date' => '2024-11-18',
            'payment_stage' => '40% Superstructure Framing, MEP & Roofing Release',
            'payment_method' => 'Manager Check (HDMF Disbursed)',
            'financing_type' => 'pagibig_loan',
            'financing_institution' => 'Pag-IBIG Fund (HDMF)',
            'loan_reference_no' => 'HDMF-VR-2024-1608',
            'disbursing_entity' => 'Pag-IBIG Fund Loan Release Division',
            'drawdown_tranche' => 'HDMF Tranche 1: 40% Framing & Enclosure Release',
            'payment_first_cleared' => true,
            'construction_clearance_status' => 'cleared_to_construct',
            'bank_reference' => 'HDMF-CHK-VR16-02',
            'received_by' => 'Engr. Esabyl B. Mitra',
            'status' => 'paid',
            'notes' => 'HDMF site inspection passed. Funds cleared for architectural finishes and painting.',
        ]);

        Payment::create([
            'project_id' => $prjVR16->id,
            'invoice_no' => 'INV-VR16-003',
            'official_receipt_no' => 'OR-202501-1603',
            'payer_name' => 'Pag-IBIG Fund / HDMF (fbo Engr. Ignacio S. Lonzaga)',
            'amount' => 549484.14,
            'payment_date' => '2025-01-07',
            'payment_stage' => '30% Final Turnover & HDMF Final Release',
            'payment_method' => 'Manager Check (HDMF Disbursed)',
            'financing_type' => 'pagibig_loan',
            'financing_institution' => 'Pag-IBIG Fund (HDMF)',
            'loan_reference_no' => 'HDMF-VR-2024-1608',
            'disbursing_entity' => 'Pag-IBIG Fund Loan Release Division',
            'drawdown_tranche' => 'HDMF Tranche 2: 30% Final Turnover Release',
            'payment_first_cleared' => true,
            'construction_clearance_status' => 'cleared_to_construct',
            'bank_reference' => 'HDMF-CHK-VR16-03',
            'received_by' => 'Engr. Esabyl B. Mitra',
            'status' => 'paid',
            'notes' => 'Final turnover certified and accepted. Occupancy permit verified.',
        ]);

        // Cost Records for 2BR Bungalow (Total Actual Spend: ₱1,617,478.80 -> Realized Profit Margin: ₱214,135.00 / 11.7%)
        ProjectCost::create(['project_id' => $prjVR16->id, 'cost_code' => 'CST-VR16-001', 'cost_category' => 'Materials & Consumables', 'item_name' => 'Structural Premix Concrete, Deformed Rebars & CHB Masonry', 'cost_type' => 'Direct', 'quantity' => 1, 'unit' => 'lot', 'unit_rate' => 650000.00, 'estimated_cost' => 660000.00, 'actual_cost' => 650000.00, 'status' => 'settled', 'cost_date' => '2024-07-25', 'vendor_payee' => 'Silay Builders Center', 'reference_no' => 'PO-VR16-01', 'notes' => 'Foundation, column, beam premix concrete and rebar cages.']);
        ProjectCost::create(['project_id' => $prjVR16->id, 'cost_code' => 'CST-VR16-002', 'cost_category' => 'Materials & Consumables', 'item_name' => 'Roofing Sheets, C-Purlins, Tiles, Doors, Windows, Plumbing & Electrical', 'cost_type' => 'Direct', 'quantity' => 1, 'unit' => 'lot', 'unit_rate' => 550000.00, 'estimated_cost' => 555000.00, 'actual_cost' => 550000.00, 'status' => 'settled', 'cost_date' => '2024-10-20', 'vendor_payee' => 'Negros Architectural Supplies', 'reference_no' => 'PO-VR16-02', 'notes' => 'Architectural finishes and MEP fixtures.']);
        ProjectCost::create(['project_id' => $prjVR16->id, 'cost_code' => 'CST-VR16-003', 'cost_category' => 'Labor & Engineering', 'item_name' => 'Licensed Trade Craftsmen, Electricians, Plumbers & Carpenters', 'cost_type' => 'Direct', 'quantity' => 1, 'unit' => 'lot', 'unit_rate' => 313478.80, 'estimated_cost' => 315000.00, 'actual_cost' => 313478.80, 'status' => 'settled', 'cost_date' => '2024-12-18', 'vendor_payee' => 'Master Trade Guild', 'reference_no' => 'VOUCH-VR16-03', 'notes' => 'Itemized labor workforce across all 17 direct scope items.']);
        ProjectCost::create(['project_id' => $prjVR16->id, 'cost_code' => 'CST-VR16-004', 'cost_category' => 'Permits & Regulatory', 'item_name' => 'Professional Engineering Sign-off & Municipal Permit Fees', 'cost_type' => 'Indirect', 'quantity' => 1, 'unit' => 'lot', 'unit_rate' => 64000.00, 'estimated_cost' => 64000.00, 'actual_cost' => 64000.00, 'status' => 'settled', 'cost_date' => '2024-07-05', 'vendor_payee' => 'Silay City Engineering Office', 'reference_no' => 'PERM-VR16-04', 'notes' => 'Civil, electrical, sanitary, geodetic engineering sign-off and building permit.']);
        ProjectCost::create(['project_id' => $prjVR16->id, 'cost_code' => 'CST-VR16-005', 'cost_category' => 'Site Overhead & Utilities', 'item_name' => 'Site Equipment, Testing Admixtures & Quality Verification', 'cost_type' => 'Overhead', 'quantity' => 1, 'unit' => 'lot', 'unit_rate' => 40000.00, 'estimated_cost' => 45000.00, 'actual_cost' => 40000.00, 'status' => 'settled', 'cost_date' => '2024-11-05', 'vendor_payee' => 'Site Operations', 'reference_no' => 'OVH-VR16-05', 'notes' => 'Concrete cylinder testing and site power.']);

        $prjVR16->spent_budget = $prjVR16->costs()->sum('actual_cost');
        $prjVR16->save();

        // =========================================================================
        // 6. COMPLETED PROJECT 3 (PDF 3): 31 m² Housing Unit Duplex
        // Location: Block 12 Lot 2, Villa Romeo Subd., Brgy. 5, Silay City, Neg. Occ.
        // Total Cost: ₱742,800.74 | Completed: Jan 10, 2024
        // =========================================================================
        $prjVR12 = Project::create([
            'project_code' => 'PRJ-2024-VR12',
            'title' => '31 m² Housing Unit (1 Side of Residential Duplex)',
            'client_name' => 'MC HIRO Realty Corp.',
            'location' => 'Block 12 Lot 2, Villa Romeo Subd., Brgy. 5, Silay City, Neg. Occ.',
            'project_type' => 'Residential Build',
            'land_area_sqm' => 90.00,
            'floor_area_sqm' => 31.00,
            'status' => 'completed',
            'contract_budget' => 742800.74,
            'spent_budget' => 650000.00,
            'financing_type' => 'client_equity',
            'financing_institution' => 'MC HIRO Realty Corp. Developer Financing',
            'loan_account_no' => 'MCH-VR-2023-1202',
            'approved_loan_amount' => 594240.59, // 80%
            'client_equity_amount' => 148560.15,  // 20%
            'payment_first_policy' => true,
            'start_date' => '2023-08-01',
            'end_date' => '2024-01-20',
            'actual_completion_date' => '2024-01-10',
            'structural_progress' => 100,
            'electrical_progress' => 100,
            'piping_progress' => 100,
            'finishing_progress' => 100,
            'structural_weight' => 40,
            'electrical_weight' => 25,
            'piping_weight' => 20,
            'finishing_weight' => 15,
            'overall_progress' => 100,
            'current_phase' => 'Phase 5: Completed & Turned Over',
            'description' => '31 sq.m 1-side residential duplex housing unit constructed at Block 12 Lot 2, Villa Romeo Subd., Brgy. 5, Silay City, Neg. Occ. Developer: MC HIRO Realty Corp., Certified and Approved by Engr. Ignacio S. Lonzaga (PRC License No. 0042019, PTR No. 2901354).',
            'schedule_notes' => 'Duplex unit delivered 10 days ahead of schedule on January 10, 2024. Full settlement cleared and client accepted.',
            'deployed_workers' => 7,
            'deployed_skilled_workers' => 3,
            'deployed_engineers' => 1,
            'deployed_architects' => 0,
            'deployed_foremen' => 1,
            'deployed_operators' => 0,
            'deployed_safety_officers' => 0,
        ]);

        $prjVR12->personnel()->attach([
            $pLonzaga->id => ['assignment_role' => 'Certified Civil Engineer & Developer Lead'],
        ]);

        ProjectPhoto::create([
            'project_id' => $prjVR12->id,
            'photo_type' => 'blueprint',
            'title' => '31m² Residential Duplex Floor Plan & Elevation CAD Drawing',
            'description' => 'Approved 31 sq.m duplex blueprint showing living/dining area, bedroom, toilet & bath, kitchen counter and setback perimeter fencing.',
            'file_path' => '/uploads/projects/blueprint_villa_floorplan.svg',
            'is_primary' => false,
            'taken_at' => '2023-07-20',
        ]);

        ProjectPhoto::create([
            'project_id' => $prjVR12->id,
            'photo_type' => '3d_render',
            'title' => '3D Architectural Duplex Housing Concept Render',
            'description' => '3D exterior perspective showing contemporary modern duplex housing with perimeter fence and entry gate.',
            'file_path' => '/uploads/projects/render_villa_luxury.svg',
            'is_primary' => false,
            'taken_at' => '2023-07-25',
        ]);

        ProjectPhoto::create([
            'project_id' => $prjVR12->id,
            'photo_type' => 'actual_site',
            'title' => 'Turned Over 31m² Duplex Unit As-Built Residence',
            'description' => 'Completed and delivered duplex housing unit at Block 12 Lot 2, Villa Romeo Subdivision.',
            'file_path' => '/uploads/projects/site_progress_concrete.svg',
            'is_primary' => true,
            'taken_at' => '2024-01-10',
        ]);

        // Load 20-Item Official Bill of Materials & Cost Estimates (₱742,800.74)
        app(\App\Http\Controllers\ProjectScopeController::class)->loadDuplexHousingTemplate($prjVR12->id);

        // Payments for Duplex Unit (Fully Settled ₱742,800.74)
        Payment::create([
            'project_id' => $prjVR12->id,
            'invoice_no' => 'INV-VR12-001',
            'official_receipt_no' => 'OR-202308-1201',
            'payer_name' => 'MC HIRO Realty Corp.',
            'amount' => 371400.37,
            'payment_date' => '2023-08-15',
            'payment_stage' => '50% Initial Downpayment & Mobilization Release',
            'payment_method' => 'Bank Wire (RTGS)',
            'financing_type' => 'client_equity',
            'financing_institution' => 'MC HIRO Realty Corp.',
            'loan_reference_no' => 'MCH-VR-2023-1202',
            'disbursing_entity' => 'Corporate Treasury',
            'drawdown_tranche' => 'Tranche 1: 50% Substructure Release',
            'payment_first_cleared' => true,
            'construction_clearance_status' => 'cleared_to_construct',
            'bank_reference' => 'MCH-TXN-2023-01',
            'received_by' => 'Engr. Ignacio S. Lonzaga',
            'status' => 'paid',
            'notes' => 'Initial 50% mobilization cleared. Earthworks and structural framing authorized.',
        ]);

        Payment::create([
            'project_id' => $prjVR12->id,
            'invoice_no' => 'INV-VR12-002',
            'official_receipt_no' => 'OR-202401-1202',
            'payer_name' => 'MC HIRO Realty Corp.',
            'amount' => 371400.37,
            'payment_date' => '2024-01-10',
            'payment_stage' => '50% Final Settlement, Turnover & Acceptance',
            'payment_method' => 'Bank Wire (RTGS)',
            'financing_type' => 'client_equity',
            'financing_institution' => 'MC HIRO Realty Corp.',
            'loan_reference_no' => 'MCH-VR-2023-1202',
            'disbursing_entity' => 'Corporate Treasury',
            'drawdown_tranche' => 'Tranche 2: 50% Final Turnover Release',
            'payment_first_cleared' => true,
            'construction_clearance_status' => 'cleared_to_construct',
            'bank_reference' => 'MCH-TXN-2024-02',
            'received_by' => 'Engr. Ignacio S. Lonzaga',
            'status' => 'paid',
            'notes' => 'Final turnover certified and accepted. Project successfully completed and turned over.',
        ]);

        // Cost Records for Duplex Unit (Total Actual Spend: ₱650,000.00 -> Realized Profit Margin: ₱92,800.74 / 12.5%)
        ProjectCost::create(['project_id' => $prjVR12->id, 'cost_code' => 'CST-VR12-001', 'cost_category' => 'Materials & Consumables', 'item_name' => 'Cement, Deformed Rebars, CHB, Sand, Gravel & Fencing Materials', 'cost_type' => 'Direct', 'quantity' => 1, 'unit' => 'lot', 'unit_rate' => 320000.00, 'estimated_cost' => 325000.00, 'actual_cost' => 320000.00, 'status' => 'settled', 'cost_date' => '2023-08-28', 'vendor_payee' => 'Silay Builders Supply', 'reference_no' => 'PO-VR12-01', 'notes' => 'Structural framing and perimeter fencing materials.']);
        ProjectCost::create(['project_id' => $prjVR12->id, 'cost_code' => 'CST-VR12-002', 'cost_category' => 'Materials & Consumables', 'item_name' => 'Roofing Sheets, Doors, Windows, Plumbing & Electrical Package', 'cost_type' => 'Direct', 'quantity' => 1, 'unit' => 'lot', 'unit_rate' => 124126.00, 'estimated_cost' => 125000.00, 'actual_cost' => 124126.00, 'status' => 'settled', 'cost_date' => '2023-11-10', 'vendor_payee' => 'Negros Architectural Center', 'reference_no' => 'PO-VR12-02', 'notes' => 'Long span roofing, electrical wires, and sanitary accessories.']);
        ProjectCost::create(['project_id' => $prjVR12->id, 'cost_code' => 'CST-VR12-003', 'cost_category' => 'Labor & Engineering', 'item_name' => 'Direct Construction Workforce & Skilled Tradesmen', 'cost_type' => 'Direct', 'quantity' => 1, 'unit' => 'lot', 'unit_rate' => 199856.70, 'estimated_cost' => 200000.00, 'actual_cost' => 199856.70, 'status' => 'settled', 'cost_date' => '2023-12-28', 'vendor_payee' => 'Villa Romeo Construction Team', 'reference_no' => 'VOUCH-VR12-03', 'notes' => 'Direct masonry, roofing, tiling and fencing labor.']);
        ProjectCost::create(['project_id' => $prjVR12->id, 'cost_code' => 'CST-VR12-004', 'cost_category' => 'Permits & Regulatory', 'item_name' => 'Municipal Building Clearance, Tax Assessments & Verification', 'cost_type' => 'Indirect', 'quantity' => 1, 'unit' => 'lot', 'unit_rate' => 6017.30, 'estimated_cost' => 7000.00, 'actual_cost' => 6017.30, 'status' => 'settled', 'cost_date' => '2023-08-05', 'vendor_payee' => 'Silay City Treasury', 'reference_no' => 'TAX-VR12-04', 'notes' => 'Municipal construction tax and permits.']);

        $prjVR12->spent_budget = $prjVR12->costs()->sum('actual_cost');
        $prjVR12->save();

        // --- Service Requests & Estimations ---
        ServiceRequest::create([
            'request_code' => 'EST-2026-001',
            'client_name' => 'St. Jude Healthcare Foundation',
            'client_email' => 'contact@stjude-health.org',
            'client_phone' => '+63 (2) 8900-1122',
            'service_type' => 'Commercial Construction',
            'land_area_sqm' => 3200.00,
            'floor_area_sqm' => 9500.00,
            'estimated_cost' => 52250000.00,
            'requested_start_date' => '2026-10-01',
            'status' => 'pending',
            'notes' => 'Proposed 8-story specialty hospital wing. Financed via Bank Construction Loan (80% Loanable: ₱41.8M, 20% Equity: ₱10.45M).',
        ]);

        ServiceRequest::create([
            'request_code' => 'EST-2026-002',
            'client_name' => 'Vanguard Logistics Group',
            'client_email' => 'projects@vanguard-logistics.com',
            'client_phone' => '+63 (2) 8900-3344',
            'service_type' => 'Industrial Complex',
            'land_area_sqm' => 8000.00,
            'floor_area_sqm' => 5500.00,
            'estimated_cost' => 38500000.00,
            'requested_start_date' => '2026-11-15',
            'status' => 'approved',
            'notes' => 'Cavite Cold Storage Hub. Bank Letter of Guaranty issued by BDO. Initialized into project tracker.',
        ]);

        // =========================================================================
        // 8. SUPPLIER MANAGEMENT ECOSYSTEM (3 PRIMARY SUPPLIER CATEGORIES)
        // =========================================================================

        // 8.1 Windows & Doors Supplier (Mils Glass and Aluminum Works)
        $supWndr = Supplier::updateOrCreate(
            ['code' => 'SUP-WNDR-01'],
            [
                'name' => 'Mils Glass and Aluminum Works',
                'category' => 'Windows & Doors',
                'contact_person' => 'Engr. Roberto M. Santos',
                'email' => 'windows.doors.supplier@stbilfrid.com',
                'phone' => '+63 (34) 495-8821',
                'address' => 'Zone 4 Industrial Park, Silay City, Negros Occidental',
                'rating' => 4.95,
                'status' => 'active',
            ]
        );

        User::updateOrCreate(
            ['email' => 'windows.doors.supplier@stbilfrid.com'],
            [
                'name' => 'Mils Glass and Aluminum Works Portal',
                'role' => 'supplier',
                'supplier_id' => $supWndr->id,
                'password' => Hash::make('supplier123'),
                'email_verified_at' => now(),
            ]
        );

        // Windows & Doors Materials Catalog
        $wndrItems = [
            [
                'material_code' => 'MAT-WNDR-001-SLD120',
                'name' => '1.20m x 1.20m Sliding Window 1/4" Clear Glass Heavy Aluminum Frame',
                'category' => 'Windows & Doors',
                'subcategory' => 'Windows',
                'description' => 'Two-panel horizontal sliding glass window with heavy duty extruded aluminum framing and weatherstripping.',
                'specifications' => '1/4" (6mm) Clear Tempered Glass, Powder-Coated Aluminum 38mm Section, Heavy-Duty Stainless Bearing Rollers',
                'unit' => 'units',
                'available_quantity' => 85,
                'unit_price' => 6100.00,
                'min_order_qty' => 1,
                'availability_status' => 'available',
            ],
            [
                'material_code' => 'MAT-WNDR-002-SLD200',
                'name' => '1.20m x 2.00m Sliding Window Heavy Aluminum Frame',
                'category' => 'Windows & Doors',
                'subcategory' => 'Windows',
                'description' => 'Large format picture sliding window designed for living rooms and premium residential facades.',
                'specifications' => '6mm Clear Tempered Glass, Heavy-Duty Analok/Powder-Coated Aluminum, Integrated Flyscreen Mesh',
                'unit' => 'units',
                'available_quantity' => 42,
                'unit_price' => 10200.00,
                'min_order_qty' => 1,
                'availability_status' => 'available',
            ],
            [
                'material_code' => 'MAT-WNDR-003-AWN60',
                'name' => '0.60m x 0.90m Bathroom Frosted Awning Window',
                'category' => 'Windows & Doors',
                'subcategory' => 'Windows',
                'description' => 'Top-hinged awning casement window with obscured frosted glass for bathrooms and powder rooms.',
                'specifications' => 'Frosted Privacy Tempered Glass 6mm, Stainless Steel Friction Hinges, Aluminum Frame',
                'unit' => 'units',
                'available_quantity' => 95,
                'unit_price' => 2250.00,
                'min_order_qty' => 1,
                'availability_status' => 'available',
            ],
            [
                'material_code' => 'MAT-WNDR-004-AWN180',
                'name' => '1.80m x 0.45m Transom Awning Casement Window',
                'category' => 'Windows & Doors',
                'subcategory' => 'Windows',
                'description' => 'High-level clerestory transom awning window providing natural daylighting and cross ventilation.',
                'specifications' => 'Clear Float Glass 6mm, Multi-Point Locking Casement Handle, Extruded Aluminum',
                'unit' => 'units',
                'available_quantity' => 60,
                'unit_price' => 3400.00,
                'min_order_qty' => 1,
                'availability_status' => 'available',
            ],
            [
                'material_code' => 'MAT-WNDR-005-PNL90',
                'name' => 'Main Solid Kiln-Dried Mahogany Panel Door 0.90m x 2.10m',
                'category' => 'Windows & Doors',
                'subcategory' => 'Doors',
                'description' => 'Premium solid hardwood front entrance door featuring raised decorative panels.',
                'specifications' => '100% Solid Kiln-Dried Mahogany Hardwood, 44mm Door Thickness, Precision Sanded Finish',
                'unit' => 'sets',
                'available_quantity' => 75,
                'unit_price' => 4350.00,
                'min_order_qty' => 1,
                'availability_status' => 'available',
            ],
            [
                'material_code' => 'MAT-WNDR-006-FLSH80',
                'name' => 'Solid Core Interior Flush Door 0.80m x 2.10m',
                'category' => 'Windows & Doors',
                'subcategory' => 'Doors',
                'description' => 'High-durability acoustic flush door engineered for bedrooms and private interior offices.',
                'specifications' => 'Solid Particleboard Core, Premium Marine Plywood Facing, Factory Sanded 40mm Thick',
                'unit' => 'sets',
                'available_quantity' => 110,
                'unit_price' => 3950.00,
                'min_order_qty' => 1,
                'availability_status' => 'available',
            ],
            [
                'material_code' => 'MAT-WNDR-007-FLSH70',
                'name' => 'Service Flush Door 0.70m x 2.10m Moisture Resistant',
                'category' => 'Windows & Doors',
                'subcategory' => 'Doors',
                'description' => 'Moisture resistant flush door designed for kitchen exits, utility rooms, and balcony access.',
                'specifications' => 'Waterproof Marine Core, Anti-Warp Solid Wood Stiles, 38mm Thickness',
                'unit' => 'sets',
                'available_quantity' => 90,
                'unit_price' => 3600.00,
                'min_order_qty' => 1,
                'availability_status' => 'available',
            ],
            [
                'material_code' => 'MAT-WNDR-008-PVC60',
                'name' => 'Heavy-Duty Waterproof PVC Door w/ Louver & Jamb 0.60m x 2.10m',
                'category' => 'Windows & Doors',
                'subcategory' => 'Doors',
                'description' => 'Complete PVC door unit with bottom louver and matching PVC jamb for toilet & bath.',
                'specifications' => 'High-Impact Resistant Virgin PVC, Reinforced Core, Includes Complete PVC Jamb & Hinges',
                'unit' => 'sets',
                'available_quantity' => 140,
                'unit_price' => 1650.00,
                'min_order_qty' => 1,
                'availability_status' => 'available',
            ],
            [
                'material_code' => 'MAT-WNDR-009-SLD150',
                'name' => '1.50m x 2.10m Sliding Patio Double Glass Door Aluminum Frame',
                'category' => 'Windows & Doors',
                'subcategory' => 'Doors',
                'description' => 'Heavy duty sliding patio door offering wide garden views and smooth floor-level threshold.',
                'specifications' => '6mm Tempered Safety Glass, Heavy Extruded Powder-Coated Aluminum Track, Stainless Mortise Lock',
                'unit' => 'sets',
                'available_quantity' => 35,
                'unit_price' => 19500.00,
                'min_order_qty' => 1,
                'availability_status' => 'available',
            ],
            [
                'material_code' => 'MAT-WNDR-010-JAMB2X4',
                'name' => 'Treated Solid Hardwood Door Jamb 2" x 4" Double Rabbeted',
                'category' => 'Windows & Doors',
                'subcategory' => 'Frames',
                'description' => 'Precision milled hardwood door frames with pressure treated anti-termite protection.',
                'specifications' => 'Kiln-Dried Philippine Hardwood, 2" x 4" Cross-Section, Double Rabbet Profile',
                'unit' => 'sets',
                'available_quantity' => 180,
                'unit_price' => 1250.00,
                'min_order_qty' => 2,
                'availability_status' => 'available',
            ],
            [
                'material_code' => 'MAT-WNDR-011-LCKMAIN',
                'name' => 'Heavy-Duty Stainless Steel Lever Entrance Lockset (Main Door)',
                'category' => 'Windows & Doors',
                'subcategory' => 'Locks',
                'description' => 'Commercial grade lever entrance lockset with solid brass mortise cylinder and anti-drill pins.',
                'specifications' => 'SUS304 Stainless Steel Construction, Grade 2 Commercial Standard, 3 Computer-Cut Keys',
                'unit' => 'sets',
                'available_quantity' => 130,
                'unit_price' => 2850.00,
                'min_order_qty' => 1,
                'availability_status' => 'available',
            ],
            [
                'material_code' => 'MAT-WNDR-012-LCKBED',
                'name' => 'Cylindrical Stainless Steel Bedroom Door Knob Lockset',
                'category' => 'Windows & Doors',
                'subcategory' => 'Handles',
                'description' => 'Residential tubular cylindrical door knob lockset for interior privacy and passage doors.',
                'specifications' => 'SUS304 Stainless Steel Finish, Heavy Brass Core Latch Mechanism, Push-Button Lock',
                'unit' => 'sets',
                'available_quantity' => 220,
                'unit_price' => 1350.00,
                'min_order_qty' => 2,
                'availability_status' => 'available',
            ],
            [
                'material_code' => 'MAT-WNDR-013-HNGE',
                'name' => 'Stainless Steel Ball Bearing Loosepin Hinges 3.5" x 3.5" (Pair)',
                'category' => 'Windows & Doors',
                'subcategory' => 'Hardware',
                'description' => 'Non-corrosive door hinges with four internal ball bearing rings for silent and smooth swing.',
                'specifications' => 'SUS304 Stainless Steel 2.5mm Thick, 4 Ball Bearing Rings per Leaf, Matching Screws',
                'unit' => 'pairs',
                'available_quantity' => 550,
                'unit_price' => 195.00,
                'min_order_qty' => 6,
                'availability_status' => 'available',
            ],
        ];

        foreach ($wndrItems as $item) {
            SupplierMaterial::updateOrCreate(
                ['material_code' => $item['material_code']],
                array_merge($item, ['supplier_id' => $supWndr->id, 'is_active' => true])
            );
        }

        // 8.2 Roofing Supplier (Colorsteel)
        $supRoof = Supplier::updateOrCreate(
            ['code' => 'SUP-ROOF-01'],
            [
                'name' => 'Colorsteel',
                'category' => 'Roofing',
                'contact_person' => 'Engr. Danilo V. Tan',
                'email' => 'roofing.supplier@stbilfrid.com',
                'phone' => '+63 (34) 495-7744',
                'address' => 'Km. 14 National Highway, Talisay - Silay Coastal Rd, Negros Occidental',
                'rating' => 4.90,
                'status' => 'active',
            ]
        );

        User::updateOrCreate(
            ['email' => 'roofing.supplier@stbilfrid.com'],
            [
                'name' => 'Colorsteel Supplier Portal',
                'role' => 'supplier',
                'supplier_id' => $supRoof->id,
                'password' => Hash::make('supplier123'),
                'email_verified_at' => now(),
            ]
        );

        // Roofing Materials Catalog
        $roofItems = [
            [
                'material_code' => 'MAT-ROOF-001-RIB40',
                'name' => 'Rib-Type Pre-Painted Long Span Roofing Sheet (0.40mm TCT)',
                'category' => 'Roofing',
                'subcategory' => 'Roofing sheets',
                'description' => 'High-profile rib-type roofing engineered with deep water channels for maximum rainfall discharge.',
                'specifications' => '0.40mm Total Coated Thickness (TCT), AZ150 Zinc-Aluminum Anti-Rust Coating, Spanish Red / Evergreen',
                'unit' => 'ln.m.',
                'available_quantity' => 4800,
                'unit_price' => 395.00,
                'min_order_qty' => 10,
                'availability_status' => 'available',
            ],
            [
                'material_code' => 'MAT-ROOF-002-CORR40',
                'name' => 'Corrugated Pre-Painted Galvanized Iron (PGI) Sheet 0.40mm',
                'category' => 'Roofing',
                'subcategory' => 'Roofing sheets',
                'description' => 'Traditional wave corrugated metal sheet with multi-layer baked polyester color finish.',
                'specifications' => 'Standard Wave Profile, UV-Resistant Polyester Top Coat, 0.40mm Base Metal Thickness',
                'unit' => 'ln.m.',
                'available_quantity' => 3200,
                'unit_price' => 380.00,
                'min_order_qty' => 10,
                'availability_status' => 'available',
            ],
            [
                'material_code' => 'MAT-ROOF-003-PUPNL50',
                'name' => 'High-Density Polyurethane (PU) Sandwich Roof Panel 50mm',
                'category' => 'Roofing',
                'subcategory' => 'Roof panels',
                'description' => 'Insulated composite roof panel with injected high-density rigid polyurethane core.',
                'specifications' => '50mm Injected Rigid PU Foam (40kg/m³), Dual 0.40mm Pre-Painted Steel Skin, K-Value 0.024 W/mK',
                'unit' => 'sq.m.',
                'available_quantity' => 650,
                'unit_price' => 1150.00,
                'min_order_qty' => 20,
                'availability_status' => 'available',
            ],
            [
                'material_code' => 'MAT-ROOF-004-PUR2X4',
                'name' => 'Structural C-Purlins 2" x 4" x 1.20mm Heavy Gauge Galvanized',
                'category' => 'Roofing',
                'subcategory' => 'Roofing accessories',
                'description' => 'Cold-formed structural roof purlins with high tensile strength and anti-corrosion galvanized zinc coating.',
                'specifications' => 'Galvanized High-Yield Steel Grade 275, 6.00m Standard Length, 1.20mm Actual Thickness',
                'unit' => 'pcs',
                'available_quantity' => 1100,
                'unit_price' => 465.00,
                'min_order_qty' => 5,
                'availability_status' => 'available',
            ],
            [
                'material_code' => 'MAT-ROOF-005-PUR2X3',
                'name' => 'Structural C-Purlins 2" x 3" x 1.00mm Commercial Grade',
                'category' => 'Roofing',
                'subcategory' => 'Roofing accessories',
                'description' => 'Standard roof purlin sections suitable for residential rafters and secondary roof bracing.',
                'specifications' => '6.00m Standard Length, High Tensile Cold-Formed Section, 1.00mm Nominal Thickness',
                'unit' => 'pcs',
                'available_quantity' => 1400,
                'unit_price' => 375.00,
                'min_order_qty' => 5,
                'availability_status' => 'available',
            ],
            [
                'material_code' => 'MAT-ROOF-006-RDGCAP',
                'name' => 'Pre-Painted Ridge Cap Roll-Top 8-Foot Section',
                'category' => 'Roofing',
                'subcategory' => 'Flashing',
                'description' => 'Crest flashing cover that seals the roof apex against wind-driven torrential rain.',
                'specifications' => '0.40mm Pre-Painted Steel, 2.44m (8ft) Girth 18", Color-Matched with Rib-Type Sheets',
                'unit' => 'pcs',
                'available_quantity' => 750,
                'unit_price' => 320.00,
                'min_order_qty' => 2,
                'availability_status' => 'available',
            ],
            [
                'material_code' => 'MAT-ROOF-007-WALLFLSH',
                'name' => 'Wall Flashing & Counter Flashing Sheet 8-Foot (0.40mm)',
                'category' => 'Roofing',
                'subcategory' => 'Flashing',
                'description' => 'Precision bent flashing with hemmed drip edge to seal roof-to-masonry wall junctions.',
                'specifications' => '0.40mm Anti-Rust Coated Sheet, 2.44m Length, Hemmed Water Drip Edge',
                'unit' => 'pcs',
                'available_quantity' => 820,
                'unit_price' => 290.00,
                'min_order_qty' => 2,
                'availability_status' => 'available',
            ],
            [
                'material_code' => 'MAT-ROOF-008-GUTTERSS',
                'name' => 'Stainless Steel 304 Spanish Box Gutter 8-Foot Section',
                'category' => 'Roofing',
                'subcategory' => 'Gutters',
                'description' => 'Heavy-duty commercial rain gutter made from architectural grade stainless steel.',
                'specifications' => 'Grade 304 Stainless Steel 0.50mm Thick, Box Profile with Stiffened Lip, 2.44m Length',
                'unit' => 'pcs',
                'available_quantity' => 580,
                'unit_price' => 560.00,
                'min_order_qty' => 2,
                'availability_status' => 'available',
            ],
            [
                'material_code' => 'MAT-ROOF-009-TEKSCREW',
                'name' => 'Self-Drilling 2-1/2" Tekscrew for Metal Roofing (Box of 500)',
                'category' => 'Roofing',
                'subcategory' => 'Roofing accessories',
                'description' => 'Hex-head self-drilling fastener with weatherproofing EPDM bonded sealing washer.',
                'specifications' => 'Ruspert Multi-Layer Anti-Corrosion Coating (1,000 hrs Salt Spray), High-Grade EPDM Washer',
                'unit' => 'boxes',
                'available_quantity' => 280,
                'unit_price' => 620.00,
                'min_order_qty' => 1,
                'availability_status' => 'available',
            ],
            [
                'material_code' => 'MAT-ROOF-010-SEAL1GAL',
                'name' => 'Elastomeric Weatherproof Polyurethane Roof Sealant (1 Gallon)',
                'category' => 'Roofing',
                'subcategory' => 'Roofing accessories',
                'description' => 'Flexible rubberized waterproofing compound for sealing lap joints, tek fasteners, and flashing.',
                'specifications' => '100% Elastomeric Waterproof Polyurethane, UV & Thermal Shock Resistant, Non-Sag',
                'unit' => 'cans',
                'available_quantity' => 230,
                'unit_price' => 890.00,
                'min_order_qty' => 1,
                'availability_status' => 'available',
            ],
            [
                'material_code' => 'MAT-ROOF-011-INSU50M',
                'name' => 'Double-Sided Aluminum Foil Thermal Roof Insulation (1.2m x 50m)',
                'category' => 'Roofing',
                'subcategory' => 'Roofing accessories',
                'description' => 'High-efficiency radiant heat barrier with fiberglass reinforced scrim.',
                'specifications' => '97% Radiant Heat Reflectance, High Tear Resistance Reinforced Scrim, Roll Coverage 60 sq.m',
                'unit' => 'rolls',
                'available_quantity' => 115,
                'unit_price' => 2750.00,
                'min_order_qty' => 1,
                'availability_status' => 'available',
            ],
        ];

        foreach ($roofItems as $item) {
            SupplierMaterial::updateOrCreate(
                ['material_code' => $item['material_code']],
                array_merge($item, ['supplier_id' => $supRoof->id, 'is_active' => true])
            );
        }

        // 8.3 Structural & Masonry Supplier
        $supStrc = Supplier::updateOrCreate(
            ['code' => 'SUP-STRC-01'],
            [
                'name' => 'Titan Structural & Steel Supplies Corp.',
                'category' => 'Structural & Masonry',
                'contact_person' => 'Engr. Ferdinand G. Tan',
                'email' => 'structural.supplier@stbilfrid.com',
                'phone' => '+63 (34) 495-9910',
                'address' => 'Bacolod Port Area Logistics Hub, Reclamation District, Bacolod City',
                'rating' => 4.98,
                'status' => 'active',
            ]
        );

        User::updateOrCreate(
            ['email' => 'structural.supplier@stbilfrid.com'],
            [
                'name' => 'Titan Structural Portal',
                'role' => 'supplier',
                'supplier_id' => $supStrc->id,
                'password' => Hash::make('supplier123'),
                'email_verified_at' => now(),
            ]
        );

        // Structural & Masonry Materials Catalog
        $strcItems = [
            [
                'material_code' => 'MAT-STRC-001-CEM40',
                'name' => 'Portland Cement (Type I) 40kg Premium High-Strength',
                'category' => 'Structural & Masonry',
                'subcategory' => 'Cement',
                'description' => 'General purpose hydraulic cement formulated for structural concrete columns, beams, and suspended slabs.',
                'specifications' => 'ASTM C150 Type I Standard, 28-day Compressive Strength >= 40.0 MPa (5,800 psi)',
                'unit' => 'bags',
                'available_quantity' => 14500,
                'unit_price' => 220.00,
                'min_order_qty' => 50,
                'availability_status' => 'available',
            ],
            [
                'material_code' => 'MAT-STRC-002-POZZ40',
                'name' => 'Pozzolan Cement 40kg (Type IP) Blended Masonry Cement',
                'category' => 'Structural & Masonry',
                'subcategory' => 'Cement',
                'description' => 'Blended hydraulic pozzolan cement engineered for masonry block laying, plastering, and floor screeds.',
                'specifications' => 'PNS 63 / ASTM C595 Blended Hydraulic Cement, High Sulfate Resistance, Reduced Heat of Hydration',
                'unit' => 'bags',
                'available_quantity' => 8000,
                'unit_price' => 205.00,
                'min_order_qty' => 50,
                'availability_status' => 'available',
            ],
            [
                'material_code' => 'MAT-STRC-003-ST16G60',
                'name' => '16mm Deformed Steel Rebar (Grade 60) High Tensile (6.0m)',
                'category' => 'Structural & Masonry',
                'subcategory' => 'Structural Steel',
                'description' => 'High-yield deformed steel reinforcing bars for heavy structural column main bars and foundation footings.',
                'specifications' => 'PNS 49 / ASTM A615 Grade 60 (Yield Strength >= 415 MPa), 6.00m Standard Length, Micro-Alloyed',
                'unit' => 'pcs',
                'available_quantity' => 8200,
                'unit_price' => 440.00,
                'min_order_qty' => 20,
                'availability_status' => 'available',
            ],
            [
                'material_code' => 'MAT-STRC-004-ST12G40',
                'name' => '12mm Deformed Steel Rebar (Grade 40) (6.0m)',
                'category' => 'Structural & Masonry',
                'subcategory' => 'Structural Steel',
                'description' => 'Standard structural grade rebar for floor slab reinforcement grids and retaining wall cages.',
                'specifications' => 'PNS 49 / ASTM A615 Grade 40 (Yield Strength >= 275 MPa), 6.00m Length, Hot-Rolled High Ductility',
                'unit' => 'pcs',
                'available_quantity' => 11500,
                'unit_price' => 300.00,
                'min_order_qty' => 20,
                'availability_status' => 'available',
            ],
            [
                'material_code' => 'MAT-STRC-005-ST10G40',
                'name' => '10mm Deformed Steel Rebar (Grade 40) (6.0m)',
                'category' => 'Structural & Masonry',
                'subcategory' => 'Structural Steel',
                'description' => 'Structural rebar widely used for beam lateral ties, column stirrup rings, and CHB wall dowels.',
                'specifications' => 'Standard 6.00m Length, PNS 49 Certified, Grade 40',
                'unit' => 'pcs',
                'available_quantity' => 17500,
                'unit_price' => 215.00,
                'min_order_qty' => 25,
                'availability_status' => 'available',
            ],
            [
                'material_code' => 'MAT-STRC-006-ST08',
                'name' => '8mm Plain Round Steel Bar / Rebar (6.0m)',
                'category' => 'Structural & Masonry',
                'subcategory' => 'Structural Steel',
                'description' => 'Round structural carbon steel used for temperature reinforcement and concrete crack control mesh.',
                'specifications' => 'Structural Mild Steel, 6.00m Length, Smooth Round Profile',
                'unit' => 'pcs',
                'available_quantity' => 13500,
                'unit_price' => 115.00,
                'min_order_qty' => 30,
                'availability_status' => 'available',
            ],
            [
                'material_code' => 'MAT-STRC-007-SNDRIV',
                'name' => 'Mixing Sand (Coarse / Fine River Sand)',
                'category' => 'Structural & Masonry',
                'subcategory' => 'Aggregates',
                'description' => 'Clean washed river aggregate sand free of silt and organic contaminants for concrete and mortar.',
                'specifications' => 'Washed River Sand, Fineness Modulus 2.6 - 2.9, Specific Gravity >= 2.60',
                'unit' => 'cu.m',
                'available_quantity' => 2400,
                'unit_price' => 820.00,
                'min_order_qty' => 5,
                'availability_status' => 'available',
            ],
            [
                'material_code' => 'MAT-STRC-008-GRV34',
                'name' => '3/4" Crushed Basalt Gravel Aggregate',
                'category' => 'Structural & Masonry',
                'subcategory' => 'Aggregates',
                'description' => '100% crushed hard basalt quarry rock for high-strength structural concrete ready-mix.',
                'specifications' => '19mm (3/4") Nominal Sieve Size, 100% Angular Crushed Basalt, Abrasion Loss < 25%',
                'unit' => 'cu.m',
                'available_quantity' => 1900,
                'unit_price' => 1380.00,
                'min_order_qty' => 5,
                'availability_status' => 'available',
            ],
            [
                'material_code' => 'MAT-STRC-009-CHB04',
                'name' => '4" Concrete Hollow Block (CHB) Load-Bearing Machine-Pressed',
                'category' => 'Structural & Masonry',
                'subcategory' => 'Masonry Blocks',
                'description' => 'Machine vibrated concrete blocks for exterior non-loadbearing partitions and interior divider walls.',
                'specifications' => '100mm x 200mm x 400mm (4" x 8" x 16"), Compressive Strength >= 4.5 MPa (650 psi)',
                'unit' => 'pcs',
                'available_quantity' => 34000,
                'unit_price' => 12.50,
                'min_order_qty' => 500,
                'availability_status' => 'available',
            ],
            [
                'material_code' => 'MAT-STRC-010-CHB06',
                'name' => '6" Concrete Hollow Block (CHB) Heavy-Duty Load-Bearing',
                'category' => 'Structural & Masonry',
                'subcategory' => 'Masonry Blocks',
                'description' => 'High-strength structural hollow blocks designed for perimeter firewall enclosures and structural core walls.',
                'specifications' => '150mm x 200mm x 400mm (6" x 8" x 16"), Compressive Strength >= 5.5 MPa (800 psi)',
                'unit' => 'pcs',
                'available_quantity' => 14500,
                'unit_price' => 15.50,
                'min_order_qty' => 300,
                'availability_status' => 'available',
            ],
            [
                'material_code' => 'MAT-STRC-011-PHEN12',
                'name' => 'Phenolic Plywood 1/2" x 4\' x 8\' Film-Faced Formworks',
                'category' => 'Structural & Masonry',
                'subcategory' => 'Formworks',
                'description' => 'Waterproof film faced plywood providing fair-faced smooth architectural concrete finishes.',
                'specifications' => '12mm WBP Phenolic Glue, Double-Sided Dynea Film, Reusable 8-10 Concrete Pours',
                'unit' => 'sheets',
                'available_quantity' => 450,
                'unit_price' => 1150.00,
                'min_order_qty' => 10,
                'availability_status' => 'available',
            ],
            [
                'material_code' => 'MAT-STRC-012-TIEW16',
                'name' => '#16 G.I. Tie Wire (35kg Roll)',
                'category' => 'Structural & Masonry',
                'subcategory' => 'Structural Steel',
                'description' => 'Soft annealed galvanized iron wire for securing steel rebar intersections and stirrups.',
                'specifications' => '16-Gauge Annealed Galvanized Iron, High Pliability & Tensile Strength, 35kg Gross Weight',
                'unit' => 'rolls',
                'available_quantity' => 120,
                'unit_price' => 2100.00,
                'min_order_qty' => 2,
                'availability_status' => 'available',
            ],
        ];

        foreach ($strcItems as $item) {
            SupplierMaterial::updateOrCreate(
                ['material_code' => $item['material_code']],
                array_merge($item, ['supplier_id' => $supStrc->id, 'is_active' => true])
            );
        }

        // 8.4 Seed Starter Purchase Orders & Logs
        $adminUser = User::where('role', 'admin')->first();

        // Sample Order 1: Windows & Doors
        $po1 = SupplierOrder::updateOrCreate(
            ['order_code' => 'ORD-2026-0001'],
            [
                'supplier_id' => $supWndr->id,
                'ordered_by_user_id' => $adminUser ? $adminUser->id : 1,
                'project_id' => $prjVR12->id,
                'delivery_location' => $prjVR12->location ?? 'Villa Rosario Phase 2, Silay City',
                'requested_delivery_date' => now()->addDays(5)->toDateString(),
                'total_amount' => 152500.00,
                'status' => 'processing',
                'notes' => 'Batch 1 window installations for Blocks 1 to 4. Ensure protective shrink wrapping on aluminum profiles.',
            ]
        );

        $matWndr1 = SupplierMaterial::where('material_code', 'MAT-WNDR-001-SLD120')->first();
        $matWndr5 = SupplierMaterial::where('material_code', 'MAT-WNDR-005-PNL90')->first();
        if ($matWndr1 && $matWndr5) {
            SupplierOrderItem::updateOrCreate(
                ['supplier_order_id' => $po1->id, 'supplier_material_id' => $matWndr1->id],
                [
                    'material_name' => $matWndr1->name,
                    'quantity' => 15,
                    'unit' => $matWndr1->unit,
                    'unit_price' => $matWndr1->unit_price,
                    'total_price' => 15 * $matWndr1->unit_price,
                ]
            );
            SupplierOrderItem::updateOrCreate(
                ['supplier_order_id' => $po1->id, 'supplier_material_id' => $matWndr5->id],
                [
                    'material_name' => $matWndr5->name,
                    'quantity' => 14,
                    'unit' => $matWndr5->unit,
                    'unit_price' => $matWndr5->unit_price,
                    'total_price' => 14 * $matWndr5->unit_price,
                ]
            );
        }

        SupplierOrderLog::updateOrCreate(
            ['supplier_order_id' => $po1->id, 'to_status' => 'processing'],
            [
                'user_id' => $adminUser ? $adminUser->id : 1,
                'from_status' => 'confirmed',
                'comment' => 'Supplier started fabrication and glazing assembly in factory.',
            ]
        );

        // Sample Order 2: Roofing
        $po2 = SupplierOrder::updateOrCreate(
            ['order_code' => 'ORD-2026-0002'],
            [
                'supplier_id' => $supRoof->id,
                'ordered_by_user_id' => $adminUser ? $adminUser->id : 1,
                'project_id' => $prjVR12->id,
                'delivery_location' => $prjVR12->location ?? 'Villa Rosario Phase 2, Silay City',
                'requested_delivery_date' => now()->addDays(3)->toDateString(),
                'total_amount' => 248000.00,
                'status' => 'ready_for_delivery',
                'notes' => 'Roofing sheets and purlins for Roof Truss installation phase. Delivery via 10-wheeler boom truck.',
            ]
        );

        $matRoof1 = SupplierMaterial::where('material_code', 'MAT-ROOF-001-RIB40')->first();
        $matRoof4 = SupplierMaterial::where('material_code', 'MAT-ROOF-004-PUR2X4')->first();
        if ($matRoof1 && $matRoof4) {
            SupplierOrderItem::updateOrCreate(
                ['supplier_order_id' => $po2->id, 'supplier_material_id' => $matRoof1->id],
                [
                    'material_name' => $matRoof1->name,
                    'quantity' => 400,
                    'unit' => $matRoof1->unit,
                    'unit_price' => $matRoof1->unit_price,
                    'total_price' => 400 * $matRoof1->unit_price,
                ]
            );
            SupplierOrderItem::updateOrCreate(
                ['supplier_order_id' => $po2->id, 'supplier_material_id' => $matRoof4->id],
                [
                    'material_name' => $matRoof4->name,
                    'quantity' => 193,
                    'unit' => $matRoof4->unit,
                    'unit_price' => $matRoof4->unit_price,
                    'total_price' => 193 * $matRoof4->unit_price,
                ]
            );
        }

        SupplierOrderLog::updateOrCreate(
            ['supplier_order_id' => $po2->id, 'to_status' => 'ready_for_delivery'],
            [
                'user_id' => $adminUser ? $adminUser->id : 1,
                'from_status' => 'processing',
                'comment' => 'Materials bundled, banded, and staged at logistics dock ready for dispatch.',
            ]
        );

        // Sample Order 3: Structural & Masonry
        $po3 = SupplierOrder::updateOrCreate(
            ['order_code' => 'ORD-2026-0003'],
            [
                'supplier_id' => $supStrc->id,
                'ordered_by_user_id' => $adminUser ? $adminUser->id : 1,
                'project_id' => $prjVR12->id,
                'delivery_location' => $prjVR12->location ?? 'Villa Rosario Phase 2, Silay City',
                'requested_delivery_date' => now()->subDays(2)->toDateString(),
                'actual_delivery_date' => now()->subDays(1)->toDateString(),
                'total_amount' => 485000.00,
                'status' => 'completed',
                'notes' => 'Bulk Portland cement & rebar delivery for foundation structural pour.',
            ]
        );

        $matStrc1 = SupplierMaterial::where('material_code', 'MAT-STRC-001-CEM40')->first();
        $matStrc3 = SupplierMaterial::where('material_code', 'MAT-STRC-003-ST16G60')->first();
        if ($matStrc1 && $matStrc3) {
            SupplierOrderItem::updateOrCreate(
                ['supplier_order_id' => $po3->id, 'supplier_material_id' => $matStrc1->id],
                [
                    'material_name' => $matStrc1->name,
                    'quantity' => 1000,
                    'unit' => $matStrc1->unit,
                    'unit_price' => $matStrc1->unit_price,
                    'total_price' => 1000 * $matStrc1->unit_price,
                ]
            );
            SupplierOrderItem::updateOrCreate(
                ['supplier_order_id' => $po3->id, 'supplier_material_id' => $matStrc3->id],
                [
                    'material_name' => $matStrc3->name,
                    'quantity' => 600,
                    'unit' => $matStrc3->unit,
                    'unit_price' => $matStrc3->unit_price,
                    'total_price' => 600 * $matStrc3->unit_price,
                ]
            );
        }

        SupplierOrderLog::updateOrCreate(
            ['supplier_order_id' => $po3->id, 'to_status' => 'completed'],
            [
                'user_id' => $adminUser ? $adminUser->id : 1,
                'from_status' => 'delivered',
                'comment' => 'Delivery verified by site engineer and accepted into project materials ledger.',
            ]
        );
    }
}
