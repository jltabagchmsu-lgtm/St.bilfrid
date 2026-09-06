<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\User;
use App\Models\Supplier;
use App\Models\SupplierMaterial;
use App\Models\SupplierOrder;
use App\Models\SupplierOrderItem;
use App\Models\SupplierOrderLog;
use App\Models\SupplierNotification;
use Illuminate\Support\Facades\Hash;

class SupplierManagementSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Windows & Doors Supplier
        $supWndr = Supplier::updateOrCreate(
            ['code' => 'SUP-WNDR-01'],
            [
                'name' => 'Prime Windows & Doors Supply Co.',
                'category' => 'Windows & Doors',
                'contact_person' => 'Roberto M. Santos',
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
                'name' => 'Prime Windows & Doors Portal',
                'role' => 'supplier',
                'supplier_id' => $supWndr->id,
                'password' => Hash::make('supplier123'),
                'email_verified_at' => now(),
            ]
        );

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

        // 2. Roofing Supplier
        $supRoof = Supplier::updateOrCreate(
            ['code' => 'SUP-ROOF-01'],
            [
                'name' => 'Summit Roofing & Metal Works Inc.',
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
                'name' => 'Summit Roofing Portal',
                'role' => 'supplier',
                'supplier_id' => $supRoof->id,
                'password' => Hash::make('supplier123'),
                'email_verified_at' => now(),
            ]
        );

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

        // 3. Structural & Masonry Supplier
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

        // 4. Sample Purchase Orders
        $adminUser = User::where('role', 'admin')->first();
        $samplePrj = Project::first();

        $prjId = $samplePrj ? $samplePrj->id : null;
        $prjLoc = $samplePrj ? ($samplePrj->location ?? 'Villa Rosario Phase 2, Silay City') : 'Villa Rosario Phase 2, Silay City';

        // Order 1: Windows & Doors
        $po1 = SupplierOrder::updateOrCreate(
            ['order_code' => 'ORD-2026-0001'],
            [
                'supplier_id' => $supWndr->id,
                'ordered_by_user_id' => $adminUser ? $adminUser->id : 1,
                'project_id' => $prjId,
                'delivery_location' => $prjLoc,
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

        // Order 2: Roofing
        $po2 = SupplierOrder::updateOrCreate(
            ['order_code' => 'ORD-2026-0002'],
            [
                'supplier_id' => $supRoof->id,
                'ordered_by_user_id' => $adminUser ? $adminUser->id : 1,
                'project_id' => $prjId,
                'delivery_location' => $prjLoc,
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

        // Order 3: Structural & Masonry
        $po3 = SupplierOrder::updateOrCreate(
            ['order_code' => 'ORD-2026-0003'],
            [
                'supplier_id' => $supStrc->id,
                'ordered_by_user_id' => $adminUser ? $adminUser->id : 1,
                'project_id' => $prjId,
                'delivery_location' => $prjLoc,
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
