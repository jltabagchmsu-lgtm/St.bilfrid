<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_code',
        'title',
        'client_name',
        'location',
        'project_type',
        'finish_tier',
        'land_area_sqm',
        'floor_area_sqm',
        'status',
        'contract_budget',
        'client_budget',
        'estimated_cost',
        'spent_budget',
        'financing_type',
        'financing_institution',
        'loan_account_no',
        'approved_loan_amount',
        'client_equity_amount',
        'payment_first_policy',
        'start_date',
        'end_date',
        'actual_completion_date',
        'structural_progress',
        'electrical_progress',
        'piping_progress',
        'finishing_progress',
        'structural_weight',
        'electrical_weight',
        'piping_weight',
        'finishing_weight',
        'overall_progress',
        'current_phase',
        'description',
        'schedule_notes',
        'deployed_workers',
        'deployed_skilled_workers',
        'deployed_engineers',
        'deployed_architects',
        'deployed_foremen',
        'deployed_operators',
        'deployed_safety_officers',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'actual_completion_date' => 'date',
        'land_area_sqm' => 'float',
        'floor_area_sqm' => 'float',
        'contract_budget' => 'float',
        'client_budget' => 'float',
        'estimated_cost' => 'float',
        'spent_budget' => 'float',
        'approved_loan_amount' => 'float',
        'client_equity_amount' => 'float',
        'payment_first_policy' => 'boolean',
        'structural_progress' => 'integer',
        'electrical_progress' => 'integer',
        'piping_progress' => 'integer',
        'finishing_progress' => 'integer',
        'structural_weight' => 'integer',
        'electrical_weight' => 'integer',
        'piping_weight' => 'integer',
        'finishing_weight' => 'integer',
        'overall_progress' => 'integer',
        'deployed_workers' => 'integer',
        'deployed_skilled_workers' => 'integer',
        'deployed_engineers' => 'integer',
        'deployed_architects' => 'integer',
        'deployed_foremen' => 'integer',
        'deployed_operators' => 'integer',
        'deployed_safety_officers' => 'integer',
    ];

    // Relationships
    public function personnel()
    {
        return $this->belongsToMany(Personnel::class, 'project_personnel')
            ->withPivot('assignment_role')
            ->withTimestamps();
    }

    public function projectMaterials()
    {
        return $this->hasMany(ProjectMaterial::class);
    }

    public function tasks()
    {
        return $this->hasMany(ProjectTask::class)->orderBy('sort_order')->orderBy('id');
    }

    public function structuralTasks()
    {
        return $this->hasMany(ProjectTask::class)->where('category', 'Structural')->orderBy('sort_order')->orderBy('id');
    }

    public function electricalTasks()
    {
        return $this->hasMany(ProjectTask::class)->where('category', 'Electrical')->orderBy('sort_order')->orderBy('id');
    }

    public function pipingTasks()
    {
        return $this->hasMany(ProjectTask::class)->whereIn('category', ['Piping', 'Piping & Plumbing', 'Plumbing'])->orderBy('sort_order')->orderBy('id');
    }

    public function finishingTasks()
    {
        return $this->hasMany(ProjectTask::class)->whereIn('category', ['Finishing', 'Design-Build', 'Turnkey Finishing', 'Design-Build / Turnkey Finishing'])->orderBy('sort_order')->orderBy('id');
    }

    public function recalculateTradeProgressFromTasks(): void
    {
        $structCount = $this->structuralTasks()->count();
        $this->structural_progress = $structCount > 0 ? (int) round($this->structuralTasks()->avg('progress') ?? 0) : 0;

        $elecCount = $this->electricalTasks()->count();
        $this->electrical_progress = $elecCount > 0 ? (int) round($this->electricalTasks()->avg('progress') ?? 0) : 0;

        $pipeCount = $this->pipingTasks()->count();
        $this->piping_progress = $pipeCount > 0 ? (int) round($this->pipingTasks()->avg('progress') ?? 0) : 0;

        $finishCount = $this->finishingTasks()->count();
        $this->finishing_progress = $finishCount > 0 ? (int) round($this->finishingTasks()->avg('progress') ?? 0) : 0;

        $this->overall_progress = $this->calculated_overall_progress;

        $totalTasks = $this->tasks()->count();
        if ($totalTasks > 0 && $this->overall_progress >= 100 && $this->status !== 'completed') {
            $this->status = 'completed';
            if (!$this->actual_completion_date) {
                $this->actual_completion_date = now();
            }
        } elseif ($this->overall_progress < 100 && $this->status === 'completed') {
            $this->status = 'in_progress';
            $this->actual_completion_date = null;
        }

        $this->save();
    }

    public function getActiveMaterialsData(): array
    {
        // Active tasks: in_progress or completed
        $activeTasks = $this->tasks()
            ->where(function($q) {
                $q->where('status', 'completed')
                  ->orWhere('status', 'in_progress')
                  ->orWhere('progress', '>', 0);
            })
            ->with('taskMaterials')
            ->get();

        $activeTaskIds = $activeTasks->pluck('id');
        $taskMaterials = ProjectTaskMaterial::whereIn('project_task_id', $activeTaskIds)
            ->with('projectTask')
            ->get();

        $grouped = [];
        foreach ($taskMaterials as $tm) {
            $key = $tm->material_name . '|' . $tm->unit;
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'material_name' => $tm->material_name,
                    'category' => $tm->category,
                    'unit' => $tm->unit,
                    'unit_cost' => (float) $tm->unit_cost,
                    'total_quantity' => 0.0,
                    'total_cost' => 0.0,
                    'task_names' => [],
                    'is_all_completed' => true,
                ];
            }

            $grouped[$key]['total_quantity'] += (float) $tm->quantity;
            $grouped[$key]['total_cost'] += (float) $tm->total_cost;
            if ($tm->projectTask) {
                $taskName = $tm->projectTask->task_name;
                if (!in_array($taskName, $grouped[$key]['task_names'])) {
                    $grouped[$key]['task_names'][] = $taskName;
                }
                if ($tm->projectTask->status !== 'completed' && $tm->projectTask->progress < 100) {
                    $grouped[$key]['is_all_completed'] = false;
                }
            }
        }

        $materialsList = array_values($grouped);
        $totalActiveValue = array_sum(array_column($materialsList, 'total_cost'));
        $totalActiveItems = count($materialsList);
        $totalActiveUnits = array_sum(array_column($materialsList, 'total_quantity'));

        return [
            'materials' => $materialsList,
            'total_active_value' => $totalActiveValue,
            'total_active_items' => $totalActiveItems,
            'total_active_units' => $totalActiveUnits,
            'active_tasks_count' => $activeTasks->count(),
            'completed_tasks_count' => $activeTasks->where('status', 'completed')->count(),
            'in_progress_tasks_count' => $activeTasks->where('status', 'in_progress')->count(),
        ];
    }

    public function initializeDefaultChecklistTasks(bool $force = false): void
    {
        $this->seedDefaultChecklist($force);
    }

    public function seedDefaultChecklist(bool $force = false): void
    {
        if ($force) {
            $this->tasks()->delete();
        }

        $existingCount = $this->tasks()->count();
        if ($existingCount > 0 && !$force) {
            return;
        }

        $defaultChecklists = [
            'Structural' => [
                [
                    'name' => 'Site preparation', 'status' => 'completed', 'progress' => 100, 'phase' => 'Phase 1: Mobilization & Substructure', 'month' => 'Month 1 - 2', 's_day' => 0, 'd_day' => 20,
                    'materials' => [
                        ['name' => 'Stakes & Batter Boards Lumber (2x2x8)', 'unit' => 'pcs', 'unit_cost' => 85, 'qty' => 50],
                        ['name' => 'Heavy Duty Marking String & Marker Paint', 'unit' => 'cans', 'unit_cost' => 180, 'qty' => 10],
                    ]
                ],
                [
                    'name' => 'Excavation', 'status' => 'completed', 'progress' => 100, 'phase' => 'Phase 1: Mobilization & Substructure', 'month' => 'Month 1 - 2', 's_day' => 15, 'd_day' => 40,
                    'materials' => [
                        ['name' => 'Trench Shoring Lumber (2x6x12)', 'unit' => 'pcs', 'unit_cost' => 320, 'qty' => 30],
                        ['name' => 'Sub-base Washed Crushed Gravel', 'unit' => 'cu.m', 'unit_cost' => 1410, 'qty' => 15],
                    ]
                ],
                [
                    'name' => 'Foundation works', 'status' => 'in_progress', 'progress' => 90, 'phase' => 'Phase 1: Mobilization & Substructure', 'month' => 'Month 1 - 2', 's_day' => 30, 'd_day' => 60,
                    'materials' => [
                        ['name' => 'Portland Cement (Type I)', 'unit' => 'bags', 'unit_cost' => 225, 'qty' => 150],
                        ['name' => 'Washed Mixing Sand (Coarse)', 'unit' => 'cu.m', 'unit_cost' => 850, 'qty' => 25],
                        ['name' => '3/4 Crushed Gravel Aggregate', 'unit' => 'cu.m', 'unit_cost' => 1410, 'qty' => 35],
                    ]
                ],
                [
                    'name' => 'Footing reinforcement', 'status' => 'completed', 'progress' => 100, 'phase' => 'Phase 1: Mobilization & Substructure', 'month' => 'Month 1 - 2', 's_day' => 35, 'd_day' => 65,
                    'materials' => [
                        ['name' => '16mm Deformed Bar (Grade 60)', 'unit' => 'pcs', 'unit_cost' => 450, 'qty' => 120],
                        ['name' => '#16 G.I. Tie Wire', 'unit' => 'kg', 'unit_cost' => 95, 'qty' => 25],
                        ['name' => 'Concrete Cover Spacers (50mm)', 'unit' => 'pcs', 'unit_cost' => 15, 'qty' => 200],
                    ]
                ],
                [
                    'name' => 'Column reinforcement', 'status' => 'in_progress', 'progress' => 75, 'phase' => 'Phase 2: Superstructure & Framing', 'month' => 'Month 3 - 5', 's_day' => 60, 'd_day' => 100,
                    'materials' => [
                        ['name' => '20mm Deformed Steel Bar (Grade 60)', 'unit' => 'pcs', 'unit_cost' => 680, 'qty' => 90],
                        ['name' => '10mm Deformed Bar (Lateral Ties)', 'unit' => 'pcs', 'unit_cost' => 220, 'qty' => 110],
                        ['name' => '#16 G.I. Tie Wire', 'unit' => 'kg', 'unit_cost' => 95, 'qty' => 30],
                    ]
                ],
                [
                    'name' => 'Column formwork', 'status' => 'in_progress', 'progress' => 60, 'phase' => 'Phase 2: Superstructure & Framing', 'month' => 'Month 3 - 5', 's_day' => 70, 'd_day' => 110,
                    'materials' => [
                        ['name' => '1/2" Phenolic Plywood Board (4x8)', 'unit' => 'pcs', 'unit_cost' => 1150, 'qty' => 45],
                        ['name' => '2x3 Form Framing Lumber', 'unit' => 'pcs', 'unit_cost' => 185, 'qty' => 120],
                        ['name' => 'Concrete Form Release Oil', 'unit' => 'pails', 'unit_cost' => 1800, 'qty' => 4],
                    ]
                ],
                [
                    'name' => 'Beam reinforcement', 'status' => 'in_progress', 'progress' => 40, 'phase' => 'Phase 2: Superstructure & Framing', 'month' => 'Month 3 - 5', 's_day' => 85, 'd_day' => 130,
                    'materials' => [
                        ['name' => '16mm Deformed Bar (Grade 60)', 'unit' => 'pcs', 'unit_cost' => 450, 'qty' => 140],
                        ['name' => '10mm Deformed Bar (Stirrups)', 'unit' => 'pcs', 'unit_cost' => 220, 'qty' => 95],
                    ]
                ],
                [
                    'name' => 'Beam formwork', 'status' => 'in_progress', 'progress' => 30, 'phase' => 'Phase 2: Superstructure & Framing', 'month' => 'Month 3 - 5', 's_day' => 95, 'd_day' => 140,
                    'materials' => [
                        ['name' => '1/2" Phenolic Plywood Board (4x8)', 'unit' => 'pcs', 'unit_cost' => 1150, 'qty' => 50],
                        ['name' => '2x4 Form Lumber & Bracing', 'unit' => 'pcs', 'unit_cost' => 240, 'qty' => 90],
                        ['name' => 'Heavy Duty Adjustable Steel Shoring Jacks', 'unit' => 'sets', 'unit_cost' => 650, 'qty' => 40],
                    ]
                ],
                [
                    'name' => 'Slab reinforcement', 'status' => 'in_progress', 'progress' => 20, 'phase' => 'Phase 2: Superstructure & Framing', 'month' => 'Month 3 - 5', 's_day' => 110, 'd_day' => 155,
                    'materials' => [
                        ['name' => '12mm Deformed Bar (Temperature Bars)', 'unit' => 'pcs', 'unit_cost' => 310, 'qty' => 160],
                        ['name' => 'Welded Steel Wire Mesh (6x6 gauge 10)', 'unit' => 'rolls', 'unit_cost' => 2450, 'qty' => 20],
                    ]
                ],
                [
                    'name' => 'Concrete pouring', 'status' => 'in_progress', 'progress' => 10, 'phase' => 'Phase 2: Superstructure & Framing', 'month' => 'Month 3 - 5', 's_day' => 125, 'd_day' => 165,
                    'materials' => [
                        ['name' => 'Ready-Mix Concrete (3500 PSI @ 28 Days)', 'unit' => 'cu.m', 'unit_cost' => 4350, 'qty' => 45],
                        ['name' => 'Concrete Curing Compound (Membrane)', 'unit' => 'pails', 'unit_cost' => 2200, 'qty' => 6],
                    ]
                ],
                [
                    'name' => 'Structural walls', 'status' => 'not_started', 'progress' => 0, 'phase' => 'Phase 3: MEP Rough-Ins & Enclosures', 'month' => 'Month 6 - 8', 's_day' => 150, 'd_day' => 200,
                    'materials' => [
                        ['name' => '6" Concrete Hollow Block (CHB Load-Bearing)', 'unit' => 'pcs', 'unit_cost' => 16, 'qty' => 1200],
                        ['name' => 'Portland Cement (Type I)', 'unit' => 'bags', 'unit_cost' => 225, 'qty' => 80],
                        ['name' => '10mm Deformed Bar (CHB Rebar)', 'unit' => 'pcs', 'unit_cost' => 220, 'qty' => 70],
                    ]
                ],
                [
                    'name' => 'Roofing structure', 'status' => 'not_started', 'progress' => 0, 'phase' => 'Phase 3: MEP Rough-Ins & Enclosures', 'month' => 'Month 6 - 8', 's_day' => 180, 'd_day' => 230,
                    'materials' => [
                        ['name' => 'Structural C-Purlins (2x4x1.5mm x 6m)', 'unit' => 'pcs', 'unit_cost' => 520, 'qty' => 85],
                        ['name' => 'Structural Steel Trusses & Angle Bars', 'unit' => 'pcs', 'unit_cost' => 850, 'qty' => 60],
                        ['name' => 'Epoxy Red Oxide Primer Paint', 'unit' => 'gals', 'unit_cost' => 680, 'qty' => 8],
                    ]
                ],
            ],
            'Electrical' => [
                [
                    'name' => 'Electrical layout and planning', 'status' => 'completed', 'progress' => 100, 'phase' => 'Phase 1: Mobilization & Substructure', 'month' => 'Month 1 - 2', 's_day' => 5, 'd_day' => 30,
                    'materials' => [
                        ['name' => 'Architectural Electrical Layout Markers & Tags', 'unit' => 'sets', 'unit_cost' => 2500, 'qty' => 1],
                    ]
                ],
                [
                    'name' => 'Material procurement', 'status' => 'completed', 'progress' => 100, 'phase' => 'Phase 1: Mobilization & Substructure', 'month' => 'Month 1 - 2', 's_day' => 15, 'd_day' => 45,
                    'materials' => [
                        ['name' => 'Electrical Warehouse Consumables & Fasteners', 'unit' => 'lot', 'unit_cost' => 4500, 'qty' => 1],
                    ]
                ],
                [
                    'name' => 'Conduit installation', 'status' => 'in_progress', 'progress' => 85, 'phase' => 'Phase 2: Superstructure & Framing', 'month' => 'Month 3 - 5', 's_day' => 60, 'd_day' => 110,
                    'materials' => [
                        ['name' => '20mm (1/2") uPVC Electrical Conduit Pipe', 'unit' => 'pcs', 'unit_cost' => 115, 'qty' => 120],
                        ['name' => '25mm (3/4") Thick-Wall uPVC Conduit', 'unit' => 'pcs', 'unit_cost' => 145, 'qty' => 60],
                        ['name' => 'PVC Solvent Cement (500cc)', 'unit' => 'cans', 'unit_cost' => 220, 'qty' => 8],
                    ]
                ],
                [
                    'name' => 'Electrical box installation', 'status' => 'in_progress', 'progress' => 80, 'phase' => 'Phase 2: Superstructure & Framing', 'month' => 'Month 3 - 5', 's_day' => 75, 'd_day' => 125,
                    'materials' => [
                        ['name' => '2x4 Deep Galvanized Utility Handy Boxes', 'unit' => 'pcs', 'unit_cost' => 38, 'qty' => 80],
                        ['name' => 'Metal Octagonal Junction Boxes 4x4', 'unit' => 'pcs', 'unit_cost' => 45, 'qty' => 65],
                    ]
                ],
                [
                    'name' => 'Wiring installation', 'status' => 'in_progress', 'progress' => 65, 'phase' => 'Phase 3: MEP Rough-Ins & Enclosures', 'month' => 'Month 6 - 8', 's_day' => 130, 'd_day' => 180,
                    'materials' => [
                        ['name' => '3.5mm² (#12 AWG) THHN/THWN-2 Pure Copper Wire', 'unit' => 'boxes', 'unit_cost' => 5000, 'qty' => 12],
                        ['name' => '5.5mm² (#10 AWG) THHN Stranded Copper Wire', 'unit' => 'boxes', 'unit_cost' => 7800, 'qty' => 6],
                        ['name' => 'Wire Pulling Lubricant (1 Gal)', 'unit' => 'pails', 'unit_cost' => 1250, 'qty' => 3],
                    ]
                ],
                [
                    'name' => 'Main panel installation', 'status' => 'in_progress', 'progress' => 50, 'phase' => 'Phase 3: MEP Rough-Ins & Enclosures', 'month' => 'Month 6 - 8', 's_day' => 150, 'd_day' => 200,
                    'materials' => [
                        ['name' => 'Main Distribution Panelboard (12-Branch Center Mains 100A)', 'unit' => 'sets', 'unit_cost' => 8500, 'qty' => 2],
                    ]
                ],
                [
                    'name' => 'Circuit breaker installation', 'status' => 'in_progress', 'progress' => 40, 'phase' => 'Phase 3: MEP Rough-Ins & Enclosures', 'month' => 'Month 6 - 8', 's_day' => 170, 'd_day' => 220,
                    'materials' => [
                        ['name' => '20A 1-Pole Bolt-On Miniature Circuit Breakers', 'unit' => 'pcs', 'unit_cost' => 280, 'qty' => 18],
                        ['name' => '30A 2-Pole Bolt-On Circuit Breakers', 'unit' => 'pcs', 'unit_cost' => 450, 'qty' => 8],
                        ['name' => '60A 2-Pole Main Breaker', 'unit' => 'pcs', 'unit_cost' => 1150, 'qty' => 2],
                    ]
                ],
                [
                    'name' => 'Lighting fixture installation', 'status' => 'in_progress', 'progress' => 30, 'phase' => 'Phase 4: Architectural Fit-Out & Finishes', 'month' => 'Month 9 - 11', 's_day' => 210, 'd_day' => 260,
                    'materials' => [
                        ['name' => '18W LED Recessed Round Downlights (Tri-Color)', 'unit' => 'pcs', 'unit_cost' => 350, 'qty' => 45],
                        ['name' => '36W Slim LED Linear Batten Fixtures', 'unit' => 'pcs', 'unit_cost' => 620, 'qty' => 16],
                    ]
                ],
                [
                    'name' => 'Power outlet installation', 'status' => 'in_progress', 'progress' => 25, 'phase' => 'Phase 4: Architectural Fit-Out & Finishes', 'month' => 'Month 9 - 11', 's_day' => 225, 'd_day' => 275,
                    'materials' => [
                        ['name' => 'Wide Series 1-Gang / 2-Gang Modern Wall Switches', 'unit' => 'pcs', 'unit_cost' => 160, 'qty' => 35],
                        ['name' => 'Duplex Universal Convenience Outlets with Ground', 'unit' => 'pcs', 'unit_cost' => 210, 'qty' => 40],
                    ]
                ],
                [
                    'name' => 'Grounding system installation', 'status' => 'in_progress', 'progress' => 20, 'phase' => 'Phase 4: Architectural Fit-Out & Finishes', 'month' => 'Month 9 - 11', 's_day' => 240, 'd_day' => 290,
                    'materials' => [
                        ['name' => '5/8" x 10ft Pure Copper Ground Rod', 'unit' => 'pcs', 'unit_cost' => 1250, 'qty' => 4],
                        ['name' => '#6 AWG Bare Copper Ground Wire', 'unit' => 'meters', 'unit_cost' => 110, 'qty' => 50],
                        ['name' => 'Heavy Duty Bronze Ground Rod Clamps', 'unit' => 'pcs', 'unit_cost' => 180, 'qty' => 6],
                    ]
                ],
                [
                    'name' => 'Electrical testing', 'status' => 'not_started', 'progress' => 0, 'phase' => 'Phase 5: Commissioning & Handover', 'month' => 'Month 11 - 12', 's_day' => 290, 'd_day' => 330,
                    'materials' => [
                        ['name' => 'Digital Insulation Megger & Continuity Test Kit', 'unit' => 'sets', 'unit_cost' => 6500, 'qty' => 1],
                    ]
                ],
                [
                    'name' => 'Final inspection', 'status' => 'not_started', 'progress' => 0, 'phase' => 'Phase 5: Commissioning & Handover', 'month' => 'Month 11 - 12', 's_day' => 320, 'd_day' => 350,
                    'materials' => [
                        ['name' => 'Engraved Phenolic Circuit Directory Nameplates', 'unit' => 'lot', 'unit_cost' => 1800, 'qty' => 1],
                    ]
                ],
            ],
            'Piping & Plumbing' => [
                [
                    'name' => 'Plumbing layout and planning', 'status' => 'completed', 'progress' => 100, 'phase' => 'Phase 1: Mobilization & Substructure', 'month' => 'Month 1 - 2', 's_day' => 5, 'd_day' => 30,
                    'materials' => [
                        ['name' => 'Plumbing Blueprint & Trench Layout Materials', 'unit' => 'lot', 'unit_cost' => 2000, 'qty' => 1],
                    ]
                ],
                [
                    'name' => 'Material procurement', 'status' => 'completed', 'progress' => 100, 'phase' => 'Phase 1: Mobilization & Substructure', 'month' => 'Month 1 - 2', 's_day' => 15, 'd_day' => 45,
                    'materials' => [
                        ['name' => 'Plumbing Sealants, Solvents & Gaskets Lot', 'unit' => 'lot', 'unit_cost' => 3500, 'qty' => 1],
                    ]
                ],
                [
                    'name' => 'Underground pipe installation', 'status' => 'in_progress', 'progress' => 90, 'phase' => 'Phase 2: Superstructure & Framing', 'month' => 'Month 3 - 5', 's_day' => 40, 'd_day' => 80,
                    'materials' => [
                        ['name' => '4" (110mm) Series 1000 PVC Sanitary Sewer Pipe', 'unit' => 'pcs', 'unit_cost' => 420, 'qty' => 35],
                        ['name' => '4" Sanitary P-Traps & Cleanout Plugs', 'unit' => 'pcs', 'unit_cost' => 185, 'qty' => 12],
                    ]
                ],
                [
                    'name' => 'Drainage pipe installation', 'status' => 'in_progress', 'progress' => 80, 'phase' => 'Phase 2: Superstructure & Framing', 'month' => 'Month 3 - 5', 's_day' => 60, 'd_day' => 110,
                    'materials' => [
                        ['name' => '3" PVC Drainage Pipe (Series 600)', 'unit' => 'pcs', 'unit_cost' => 310, 'qty' => 28],
                        ['name' => 'Brass Floor Drains with Anti-Odor Trap (4x4)', 'unit' => 'pcs', 'unit_cost' => 340, 'qty' => 14],
                    ]
                ],
                [
                    'name' => 'Water supply pipe installation', 'status' => 'in_progress', 'progress' => 75, 'phase' => 'Phase 3: MEP Rough-Ins & Enclosures', 'month' => 'Month 6 - 8', 's_day' => 120, 'd_day' => 170,
                    'materials' => [
                        ['name' => '25mm (3/4") PN20 PPR Polypropylene Pipe', 'unit' => 'pcs', 'unit_cost' => 260, 'qty' => 30],
                        ['name' => '20mm (1/2") PN20 PPR Water Pipe', 'unit' => 'pcs', 'unit_cost' => 180, 'qty' => 45],
                        ['name' => 'PPR Socket Fusion Fittings (Elbows/Tees)', 'unit' => 'pcs', 'unit_cost' => 35, 'qty' => 120],
                    ]
                ],
                [
                    'name' => 'Sewer pipe installation', 'status' => 'in_progress', 'progress' => 65, 'phase' => 'Phase 3: MEP Rough-Ins & Enclosures', 'month' => 'Month 6 - 8', 's_day' => 135, 'd_day' => 185,
                    'materials' => [
                        ['name' => '4" PVC Soil & Waste Vertical Riser Pipe', 'unit' => 'pcs', 'unit_cost' => 420, 'qty' => 20],
                        ['name' => 'Heavy Duty Galvanized Riser Clamps & Brackets', 'unit' => 'pcs', 'unit_cost' => 140, 'qty' => 25],
                    ]
                ],
                [
                    'name' => 'Plumbing fixture rough-ins', 'status' => 'in_progress', 'progress' => 55, 'phase' => 'Phase 3: MEP Rough-Ins & Enclosures', 'month' => 'Month 6 - 8', 's_day' => 150, 'd_day' => 200,
                    'materials' => [
                        ['name' => '2" PVC Sanitary Vent Pipe', 'unit' => 'pcs', 'unit_cost' => 195, 'qty' => 18],
                        ['name' => 'Mushroom Vent Caps with Flashing Collars', 'unit' => 'sets', 'unit_cost' => 380, 'qty' => 6],
                    ]
                ],
                [
                    'name' => 'Pipe fitting and connections', 'status' => 'in_progress', 'progress' => 50, 'phase' => 'Phase 3: MEP Rough-Ins & Enclosures', 'month' => 'Month 6 - 8', 's_day' => 165, 'd_day' => 215,
                    'materials' => [
                        ['name' => '1/2" Brass Gate Valves & Ball Valves', 'unit' => 'pcs', 'unit_cost' => 450, 'qty' => 12],
                        ['name' => 'Teflon Sealing Tape & Pipe Thread Compound', 'unit' => 'rolls', 'unit_cost' => 35, 'qty' => 40],
                    ]
                ],
                [
                    'name' => 'Water tank installation', 'status' => 'in_progress', 'progress' => 40, 'phase' => 'Phase 4: Architectural Fit-Out & Finishes', 'month' => 'Month 9 - 11', 's_day' => 210, 'd_day' => 255,
                    'materials' => [
                        ['name' => '1,000L SUS304 Stainless Steel Overhead Water Tank', 'unit' => 'units', 'unit_cost' => 24500, 'qty' => 1],
                        ['name' => '1.0 HP Automatic Constant Pressure Booster Pump', 'unit' => 'units', 'unit_cost' => 18500, 'qty' => 1],
                    ]
                ],
                [
                    'name' => 'Plumbing fixture installation', 'status' => 'in_progress', 'progress' => 30, 'phase' => 'Phase 4: Architectural Fit-Out & Finishes', 'month' => 'Month 9 - 11', 's_day' => 230, 'd_day' => 280,
                    'materials' => [
                        ['name' => 'Dual-Flush Rimless Ceramic Water Closet Set', 'unit' => 'sets', 'unit_cost' => 6800, 'qty' => 6],
                        ['name' => 'Undermount Ceramic Lavatory with Pop-up Waste', 'unit' => 'sets', 'unit_cost' => 2900, 'qty' => 6],
                        ['name' => 'SUS304 Double Bowl Kitchen Sink with Strainers', 'unit' => 'sets', 'unit_cost' => 4500, 'qty' => 2],
                    ]
                ],
                [
                    'name' => 'Pressure and leak testing', 'status' => 'in_progress', 'progress' => 20, 'phase' => 'Phase 5: Commissioning & Handover', 'month' => 'Month 11 - 12', 's_day' => 285, 'd_day' => 325,
                    'materials' => [
                        ['name' => 'Pneumatic Pipe Test Plugs & Hydrostatic Pump Kit', 'unit' => 'lot', 'unit_cost' => 4800, 'qty' => 1],
                    ]
                ],
                [
                    'name' => 'Final plumbing inspection', 'status' => 'not_started', 'progress' => 0, 'phase' => 'Phase 5: Commissioning & Handover', 'month' => 'Month 11 - 12', 's_day' => 320, 'd_day' => 350,
                    'materials' => [
                        ['name' => 'Water Pressure Gauge (0-150 PSI) & Chlorine Sanitizer', 'unit' => 'lot', 'unit_cost' => 2400, 'qty' => 1],
                    ]
                ],
            ],
            'Design-Build / Turnkey Finishing' => [
                [
                    'name' => 'Interior layout and finishing plan', 'status' => 'completed', 'progress' => 100, 'phase' => 'Phase 1: Mobilization & Substructure', 'month' => 'Month 1 - 2', 's_day' => 10, 'd_day' => 35,
                    'materials' => [
                        ['name' => 'Architectural Mood Board & Material Samples Lot', 'unit' => 'lot', 'unit_cost' => 3500, 'qty' => 1],
                    ]
                ],
                [
                    'name' => 'Material procurement', 'status' => 'completed', 'progress' => 100, 'phase' => 'Phase 1: Mobilization & Substructure', 'month' => 'Month 1 - 2', 's_day' => 20, 'd_day' => 50,
                    'materials' => [
                        ['name' => 'Architectural Adhesives, Fasteners & Tape Lot', 'unit' => 'lot', 'unit_cost' => 5200, 'qty' => 1],
                    ]
                ],
                [
                    'name' => 'Drywall framing', 'status' => 'in_progress', 'progress' => 90, 'phase' => 'Phase 3: MEP Rough-Ins & Enclosures', 'month' => 'Month 6 - 8', 's_day' => 140, 'd_day' => 185,
                    'materials' => [
                        ['name' => '0.5mm Metal Studs (32x75x3.0m) & Tracks', 'unit' => 'pcs', 'unit_cost' => 165, 'qty' => 110],
                    ]
                ],
                [
                    'name' => 'Drywall installation', 'status' => 'in_progress', 'progress' => 80, 'phase' => 'Phase 3: MEP Rough-Ins & Enclosures', 'month' => 'Month 6 - 8', 's_day' => 160, 'd_day' => 205,
                    'materials' => [
                        ['name' => '9mm Moisture-Resistant Gypsum Board (4x8)', 'unit' => 'pcs', 'unit_cost' => 480, 'qty' => 75],
                    ]
                ],
                [
                    'name' => 'Drywall jointing & finishing', 'status' => 'in_progress', 'progress' => 65, 'phase' => 'Phase 4: Architectural Fit-Out & Finishes', 'month' => 'Month 9 - 11', 's_day' => 195, 'd_day' => 240,
                    'materials' => [
                        ['name' => 'All-Purpose Gypsum Joint Compound (28kg)', 'unit' => 'pails', 'unit_cost' => 850, 'qty' => 8],
                        ['name' => 'Fiberglass Mesh Jointing Tape (90m)', 'unit' => 'rolls', 'unit_cost' => 220, 'qty' => 6],
                    ]
                ],
                [
                    'name' => 'Floor tiling', 'status' => 'in_progress', 'progress' => 60, 'phase' => 'Phase 4: Architectural Fit-Out & Finishes', 'month' => 'Month 9 - 11', 's_day' => 210, 'd_day' => 255,
                    'materials' => [
                        ['name' => '60x60cm Nano Polished Porcelain Floor Tiles', 'unit' => 'pcs', 'unit_cost' => 185, 'qty' => 240],
                        ['name' => 'Heavy Duty Polymer Tile Adhesive (25kg)', 'unit' => 'bags', 'unit_cost' => 340, 'qty' => 25],
                        ['name' => 'Anti-Bacterial Tile Grout (5kg)', 'unit' => 'bags', 'unit_cost' => 180, 'qty' => 12],
                    ]
                ],
                [
                    'name' => 'Wall tiling', 'status' => 'in_progress', 'progress' => 50, 'phase' => 'Phase 4: Architectural Fit-Out & Finishes', 'month' => 'Month 9 - 11', 's_day' => 220, 'd_day' => 265,
                    'materials' => [
                        ['name' => '30x60cm Glazed Ceramic Bathroom Wall Tiles', 'unit' => 'pcs', 'unit_cost' => 95, 'qty' => 180],
                        ['name' => '30x30cm Matte Non-Slip Floor Tiles', 'unit' => 'pcs', 'unit_cost' => 55, 'qty' => 120],
                        ['name' => 'Flexible Cementitious Waterproofing Compound', 'unit' => 'pails', 'unit_cost' => 2800, 'qty' => 4],
                    ]
                ],
                [
                    'name' => 'Ceiling installation', 'status' => 'in_progress', 'progress' => 45, 'phase' => 'Phase 4: Architectural Fit-Out & Finishes', 'month' => 'Month 9 - 11', 's_day' => 230, 'd_day' => 275,
                    'materials' => [
                        ['name' => 'Double Furring Channel (0.4mm x 5m)', 'unit' => 'pcs', 'unit_cost' => 140, 'qty' => 90],
                        ['name' => '4.5mm Fiber Cement Ceiling Boards (4x8)', 'unit' => 'pcs', 'unit_cost' => 380, 'qty' => 80],
                        ['name' => '1" Gypsum Screws (Coarse Thread)', 'unit' => 'boxes', 'unit_cost' => 280, 'qty' => 15],
                    ]
                ],
                [
                    'name' => 'Surface preparation', 'status' => 'in_progress', 'progress' => 40, 'phase' => 'Phase 4: Architectural Fit-Out & Finishes', 'month' => 'Month 9 - 11', 's_day' => 240, 'd_day' => 280,
                    'materials' => [
                        ['name' => 'Interior Skim Coat Powder (20kg)', 'unit' => 'bags', 'unit_cost' => 380, 'qty' => 20],
                        ['name' => 'Concrete Neutralizer & Sanding Paper Lot', 'unit' => 'lot', 'unit_cost' => 2400, 'qty' => 1],
                    ]
                ],
                [
                    'name' => 'Interior painting', 'status' => 'in_progress', 'progress' => 35, 'phase' => 'Phase 4: Architectural Fit-Out & Finishes', 'month' => 'Month 9 - 11', 's_day' => 250, 'd_day' => 290,
                    'materials' => [
                        ['name' => 'Flat Latex Concrete Primer White (16L Pail)', 'unit' => 'pails', 'unit_cost' => 2650, 'qty' => 8],
                        ['name' => 'Semi-Gloss Premium Interior Latex Paint (Greige)', 'unit' => 'pails', 'unit_cost' => 3250, 'qty' => 12],
                        ['name' => 'Professional 9" Paint Roller Sets & Trays', 'unit' => 'sets', 'unit_cost' => 220, 'qty' => 8],
                    ]
                ],
                [
                    'name' => 'Exterior painting', 'status' => 'in_progress', 'progress' => 25, 'phase' => 'Phase 4: Architectural Fit-Out & Finishes', 'month' => 'Month 9 - 11', 's_day' => 255, 'd_day' => 295,
                    'materials' => [
                        ['name' => 'Elastomeric Weather-Proof Waterproofing Paint (16L)', 'unit' => 'pails', 'unit_cost' => 3850, 'qty' => 6],
                    ]
                ],
                [
                    'name' => 'Door installation', 'status' => 'in_progress', 'progress' => 30, 'phase' => 'Phase 4: Architectural Fit-Out & Finishes', 'month' => 'Month 9 - 11', 's_day' => 260, 'd_day' => 300,
                    'materials' => [
                        ['name' => 'Solid Core HDF Engineered Interior Doors', 'unit' => 'sets', 'unit_cost' => 4800, 'qty' => 8],
                        ['name' => 'Kiln-Dried Hardwood Door Jambs', 'unit' => 'sets', 'unit_cost' => 1850, 'qty' => 8],
                        ['name' => 'SUS304 Lever Handle Locksets with Deadbolt', 'unit' => 'sets', 'unit_cost' => 1450, 'qty' => 8],
                    ]
                ],
                [
                    'name' => 'Window installation', 'status' => 'in_progress', 'progress' => 30, 'phase' => 'Phase 4: Architectural Fit-Out & Finishes', 'month' => 'Month 9 - 11', 's_day' => 260, 'd_day' => 300,
                    'materials' => [
                        ['name' => 'Powder-Coated Aluminum Sliding Windows (Black)', 'unit' => 'sets', 'unit_cost' => 5200, 'qty' => 10],
                        ['name' => '6mm Clear Tempered Glass Panes', 'unit' => 'sets', 'unit_cost' => 2800, 'qty' => 10],
                    ]
                ],
                [
                    'name' => 'Cabinetry & built-in fixtures', 'status' => 'in_progress', 'progress' => 20, 'phase' => 'Phase 4: Architectural Fit-Out & Finishes', 'month' => 'Month 9 - 11', 's_day' => 270, 'd_day' => 310,
                    'materials' => [
                        ['name' => '3/4" Marine Plywood & Melamine Boards', 'unit' => 'sheets', 'unit_cost' => 1650, 'qty' => 18],
                        ['name' => 'Soft-Close Concealed Hinges & Slides', 'unit' => 'sets', 'unit_cost' => 350, 'qty' => 24],
                        ['name' => 'Quartz Kitchen Countertop Slab (2.4m)', 'unit' => 'slabs', 'unit_cost' => 14500, 'qty' => 2],
                    ]
                ],
                [
                    'name' => 'Plumbing & electrical fixtures', 'status' => 'in_progress', 'progress' => 15, 'phase' => 'Phase 4: Architectural Fit-Out & Finishes', 'month' => 'Month 9 - 11', 's_day' => 280, 'd_day' => 320,
                    'materials' => [
                        ['name' => 'Magnetic Track Rail & 12W Spotlights', 'unit' => 'sets', 'unit_cost' => 3200, 'qty' => 6],
                        ['name' => '24V COB Warm White LED Strip Lights (5m)', 'unit' => 'rolls', 'unit_cost' => 1100, 'qty' => 6],
                    ]
                ],
                [
                    'name' => 'Final finishing & touch-ups', 'status' => 'not_started', 'progress' => 0, 'phase' => 'Phase 5: Commissioning & Handover', 'month' => 'Month 11 - 12', 's_day' => 310, 'd_day' => 345,
                    'materials' => [
                        ['name' => 'Silicone Acrylic Sealant & Touch-Up Paint Kits', 'unit' => 'lot', 'unit_cost' => 3500, 'qty' => 1],
                    ]
                ],
                [
                    'name' => 'Final inspection', 'status' => 'not_started', 'progress' => 0, 'phase' => 'Phase 5: Commissioning & Handover', 'month' => 'Month 11 - 12', 's_day' => 330, 'd_day' => 355,
                    'materials' => [
                        ['name' => 'Handover Certificate & Protective Film Removal', 'unit' => 'lot', 'unit_cost' => 2000, 'qty' => 1],
                    ]
                ],
            ],
        ];

        $baseDate = $this->start_date ? \Carbon\Carbon::parse($this->start_date) : now()->startOfYear();

        $sort = 1;
        $areaMultiplier = max(0.2, round(($this->floor_area_sqm ?: 100) / 100, 2));

        foreach ($defaultChecklists as $category => $tasks) {
            foreach ($tasks as $t) {
                $startDate = (clone $baseDate)->addDays($t['s_day'] ?? 0);
                $dueDate = (clone $baseDate)->addDays($t['d_day'] ?? 30);

                $createdTask = ProjectTask::create([
                    'project_id' => $this->id,
                    'task_name' => $t['name'],
                    'category' => $category,
                    'start_date' => $startDate,
                    'due_date' => $dueDate,
                    'allocated_budget' => 0,
                    'actual_cost' => 0,
                    'progress' => 0,
                    'sort_order' => $sort++,
                    'status' => 'not_started',
                    'timeline_phase' => $t['phase'] ?? 'Phase 1: Mobilization & Substructure',
                    'timeline_month' => $t['month'] ?? 'Month 1 - 2',
                ]);

                if (!empty($t['materials'])) {
                    $taskAllocatedBudget = 0;
                    foreach ($t['materials'] as $mat) {
                        $scaledQty = max(1, round(($mat['qty'] ?? 1) * $areaMultiplier));
                        $unitCost = (float)($mat['unit_cost'] ?? 0);
                        $matTotal = round($scaledQty * $unitCost, 2);
                        $taskAllocatedBudget += $matTotal;

                        ProjectTaskMaterial::create([
                            'project_task_id' => $createdTask->id,
                            'material_name' => $mat['name'],
                            'category' => $category,
                            'unit' => $mat['unit'] ?? 'pcs',
                            'unit_cost' => $unitCost,
                            'quantity' => $scaledQty,
                            'total_cost' => $matTotal,
                            'status' => 'pending',
                        ]);
                    }
                    $createdTask->update(['allocated_budget' => $taskAllocatedBudget]);
                }
            }
        }

        $this->recalculateTradeProgressFromTasks();
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function costs()
    {
        return $this->hasMany(ProjectCost::class);
    }

    public function photos()
    {
        return $this->hasMany(ProjectPhoto::class)->orderBy('is_primary', 'desc')->orderBy('created_at', 'desc');
    }

    public function primaryPhoto()
    {
        return $this->hasOne(ProjectPhoto::class)->where('is_primary', true);
    }

    public function inventoryLogs()
    {
        return $this->hasMany(InventoryLog::class)->orderBy('created_at', 'desc');
    }

    public function dailyMaterialUsages()
    {
        return $this->hasMany(DailyMaterialUsage::class)->orderBy('usage_date', 'desc')->orderBy('created_at', 'desc');
    }

    public function materialTransfersOut()
    {
        return $this->hasMany(ProjectMaterialTransfer::class, 'source_project_id')->orderBy('transfer_date', 'desc');
    }

    public function materialTransfersIn()
    {
        return $this->hasMany(ProjectMaterialTransfer::class, 'destination_project_id')->orderBy('transfer_date', 'desc');
    }

    public function scopeItems()
    {
        return $this->hasMany(ProjectScopeItem::class)->orderBy('item_number');
    }

    public function getGrandScopeCostAttribute(): float
    {
        return (float) $this->scopeItems()->sum('total_item_cost');
    }

    public function getTotalScopeMaterialsCostAttribute(): float
    {
        return (float) $this->scopeItems()->sum('materials_subtotal');
    }

    public function getTotalScopeLaborCostAttribute(): float
    {
        return (float) $this->scopeItems()->sum('labor_subtotal');
    }

    public function getTotalScopeEquipmentCostAttribute(): float
    {
        return (float) $this->scopeItems()->sum('equipment_subtotal');
    }

    public function getTotalScopeDirectCostAttribute(): float
    {
        return (float) $this->scopeItems()->sum('direct_cost');
    }

    // Progression Bases & Mathematical Calculation from Tasks
    public function getCalculatedOverallProgressAttribute(): int
    {
        $totalTasks = $this->tasks()->count();
        if ($totalTasks === 0) {
            return (int) ($this->attributes['overall_progress'] ?? 0);
        }

        $completedTasks = $this->tasks()->where(function($q) {
            $q->where('progress', '>=', 100)->orWhere('status', 'completed');
        })->count();

        if ($completedTasks === $totalTasks && $totalTasks > 0) {
            return 100;
        }

        $wS = (int) ($this->structural_weight ?? 0);
        $wE = (int) ($this->electrical_weight ?? 0);
        $wP = (int) ($this->piping_weight ?? 0);
        $wF = (int) ($this->finishing_weight ?? 0);

        $structCount = $this->structuralTasks()->count();
        $elecCount = $this->electricalTasks()->count();
        $pipeCount = $this->pipingTasks()->count();
        $finishCount = $this->finishingTasks()->count();

        $activeWeights = 0;
        $weightedSum = 0;

        if ($structCount > 0) {
            $pS = (int) ($this->structural_progress ?? 0);
            $weightedSum += ($pS * $wS);
            $activeWeights += $wS;
        }
        if ($elecCount > 0) {
            $pE = (int) ($this->electrical_progress ?? 0);
            $weightedSum += ($pE * $wE);
            $activeWeights += $wE;
        }
        if ($pipeCount > 0) {
            $pP = (int) ($this->piping_progress ?? 0);
            $weightedSum += ($pP * $wP);
            $activeWeights += $wP;
        }
        if ($finishCount > 0) {
            $pF = (int) ($this->finishing_progress ?? 0);
            $weightedSum += ($pF * $wF);
            $activeWeights += $wF;
        }

        if ($activeWeights > 0) {
            return min(100, (int) round($weightedSum / $activeWeights));
        }

        // Direct average of all task progress
        return min(100, (int) round($this->tasks()->avg('progress') ?? 0));
    }

    // Schedule & Timeline Calculations
    public function getTotalScheduleDaysAttribute(): int
    {
        if (!$this->start_date || !$this->end_date) return 1;
        return max(1, (int) $this->start_date->diffInDays($this->end_date));
    }

    public function getElapsedDaysAttribute(): int
    {
        if (!$this->start_date) return 0;
        if (now()->lt($this->start_date)) return 0;
        $totalDays = $this->total_schedule_days;
        $diff = (int) $this->start_date->diffInDays(now());
        return min($totalDays, $diff);
    }

    public function getRemainingDaysAttribute(): int
    {
        if (!$this->end_date) return 0;
        if ($this->status === 'completed') return 0;
        if (now()->gt($this->end_date)) return 0;
        return max(0, (int) now()->diffInDays($this->end_date));
    }

    public function getScheduleProgressRatioAttribute(): float
    {
        $total = $this->total_schedule_days;
        if ($total <= 0) return 0;
        return min(100, round(($this->elapsed_days / $total) * 100, 1));
    }

    public function getScheduleHealthStatusAttribute(): array
    {
        if ($this->status === 'completed') {
            return [
                'status' => 'completed',
                'label' => 'Project Completed & Turned Over',
                'color' => '#10b981',
                'bg' => 'rgba(16, 185, 129, 0.15)',
                'border' => 'rgba(16, 185, 129, 0.3)',
                'icon' => '✓',
            ];
        }

        if (now()->gt($this->end_date)) {
            $overdueDays = (int) $this->end_date->diffInDays(now());
            return [
                'status' => 'delayed',
                'label' => 'Overdue by ' . $overdueDays . ' Days',
                'color' => '#ef4444',
                'bg' => 'rgba(239, 68, 68, 0.15)',
                'border' => 'rgba(239, 68, 68, 0.3)',
                'icon' => '⚠️',
            ];
        }

        $expectedProg = $this->schedule_progress_ratio;
        $actualProg = $this->overall_progress;
        $variance = $actualProg - $expectedProg;

        if ($variance >= 5) {
            return [
                'status' => 'ahead',
                'label' => 'Ahead of Schedule (+' . round($variance, 1) . '%)',
                'color' => '#10b981',
                'bg' => 'rgba(16, 185, 129, 0.15)',
                'border' => 'rgba(16, 185, 129, 0.3)',
                'icon' => '🚀',
            ];
        } elseif ($variance >= -10) {
            return [
                'status' => 'on_track',
                'label' => 'On Schedule Target',
                'color' => '#38bdf8',
                'bg' => 'rgba(56, 189, 248, 0.15)',
                'border' => 'rgba(56, 189, 248, 0.3)',
                'icon' => '⚡',
            ];
        } else {
            return [
                'status' => 'critical_lag',
                'label' => 'Schedule Lagging (' . round($variance, 1) . '%)',
                'color' => '#f59e0b',
                'bg' => 'rgba(245, 158, 11, 0.15)',
                'border' => 'rgba(245, 158, 11, 0.3)',
                'icon' => '⏳',
            ];
        }
    }

    // Workforce & Manpower Deployment Helpers
    public function getTotalDeployedManpowerAttribute(): int
    {
        return (int) (
            $this->deployed_workers +
            $this->deployed_skilled_workers +
            $this->deployed_engineers +
            $this->deployed_architects +
            $this->deployed_foremen +
            $this->deployed_operators +
            $this->deployed_safety_officers
        );
    }

    public function getManpowerBreakdownAttribute(): array
    {
        $total = max(1, $this->total_deployed_manpower);
        return [
            'workers' => [
                'title' => 'General Construction Workers',
                'role' => 'Laborers & General Hands',
                'count' => (int) $this->deployed_workers,
                'icon' => '👷',
                'color' => '#38bdf8',
                'percent' => round(($this->deployed_workers / $total) * 100, 1),
            ],
            'skilled_workers' => [
                'title' => 'Skilled Tradesmen',
                'role' => 'Masons, Carpenters & Welders',
                'count' => (int) $this->deployed_skilled_workers,
                'icon' => '🔨',
                'color' => '#818cf8',
                'percent' => round(($this->deployed_skilled_workers / $total) * 100, 1),
            ],
            'engineers' => [
                'title' => 'Field & Trade Engineers',
                'role' => 'Site, Structural, Electrical & Piping',
                'count' => (int) $this->deployed_engineers,
                'icon' => '📐',
                'color' => '#f59e0b',
                'percent' => round(($this->deployed_engineers / $total) * 100, 1),
            ],
            'architects' => [
                'title' => 'Architects & Design Leads',
                'role' => 'Architectural & Spatial Planning',
                'count' => (int) $this->deployed_architects,
                'icon' => '🏛️',
                'color' => '#ec4899',
                'percent' => round(($this->deployed_architects / $total) * 100, 1),
            ],
            'operators' => [
                'title' => 'Heavy Equipment Operators',
                'role' => 'Tower Crane, Rig & Excavator',
                'count' => (int) $this->deployed_operators,
                'icon' => '🚜',
                'color' => '#ef4444',
                'percent' => round(($this->deployed_operators / $total) * 100, 1),
            ],
            'foremen' => [
                'title' => 'Site Foremen & Supervisors',
                'role' => 'Crew Directives & Quality Inspection',
                'count' => (int) $this->deployed_foremen,
                'icon' => '📋',
                'color' => '#10b981',
                'percent' => round(($this->deployed_foremen / $total) * 100, 1),
            ],
            'safety_officers' => [
                'title' => 'Safety & QA/QC Officers',
                'role' => 'Site Compliance & Hazard Control',
                'count' => (int) $this->deployed_safety_officers,
                'icon' => '🛡️',
                'color' => '#14b8a6',
                'percent' => round(($this->deployed_safety_officers / $total) * 100, 1),
            ],
        ];
    }

    // Calculated Helpers
    public function getProjectYearAttribute(): int
    {
        return $this->start_date ? (int) $this->start_date->format('Y') : (int) date('Y');
    }

    public function getRemainingBudgetAttribute()
    {
        return max(0, $this->contract_budget - $this->spent_budget);
    }

    public function getBudgetUsagePercentAttribute()
    {
        if ($this->contract_budget <= 0) return 0;
        return min(100, round(($this->spent_budget / $this->contract_budget) * 100, 1));
    }

    public function getTotalPaidSalesAttribute(): float
    {
        return (float) $this->payments->where('status', 'paid')->sum('amount');
    }

    public function getPendingSalesAttribute(): float
    {
        return (float) $this->payments->where('status', 'pending')->sum('amount');
    }

    // Project Costing Calculations
    public function getTotalIncurredCostAttribute(): float
    {
        $costSum = $this->costs->sum('actual_cost');
        return $costSum > 0 ? round($costSum, 2) : (float) $this->spent_budget;
    }

    public function getTotalEstimatedCostAttribute(): float
    {
        $estSum = $this->costs->sum('estimated_cost');
        return $estSum > 0 ? round($estSum, 2) : (float) $this->contract_budget;
    }

    public function getGrossMarginAttribute(): float
    {
        return round($this->contract_budget - $this->total_incurred_cost, 2);
    }

    public function getGrossMarginPercentAttribute(): float
    {
        if ($this->contract_budget <= 0) return 0;
        return round(($this->gross_margin / $this->contract_budget) * 100, 1);
    }

    public function getCostPerFloorSqmAttribute(): float
    {
        if ($this->floor_area_sqm <= 0) return 0;
        return round($this->total_incurred_cost / $this->floor_area_sqm, 2);
    }

    public function getCostPerLandSqmAttribute(): float
    {
        if ($this->land_area_sqm <= 0) return 0;
        return round($this->total_incurred_cost / $this->land_area_sqm, 2);
    }

    public function getCostVarianceAttribute(): float
    {
        return round($this->total_estimated_cost - $this->total_incurred_cost, 2);
    }

    public function getCostHealthStatusAttribute(): string
    {
        if ($this->contract_budget <= 0) return 'normal';
        $ratio = $this->total_incurred_cost / $this->contract_budget;
        if ($ratio > 1.0) return 'overrun';
        if ($ratio >= 0.85) return 'warning';
        return 'healthy';
    }

    // Excess Material Returns Metrics
    public function getTotalReturnedExcessValueAttribute(): float
    {
        return (float) $this->projectMaterials->sum(function ($pm) {
            return $pm->returned_excess_value;
        });
    }

    public function getTotalReturnedExcessUnitsAttribute(): int
    {
        return (int) $this->projectMaterials->sum('excess_returned_qty');
    }

    public function getCategoryCostSummaryAttribute(): array
    {
        $categories = [
            'Materials & Consumables' => ['color' => '#38bdf8', 'icon' => '🧱', 'actual' => 0, 'estimated' => 0, 'count' => 0],
            'Labor & Engineering' => ['color' => '#f59e0b', 'icon' => '👷', 'actual' => 0, 'estimated' => 0, 'count' => 0],
            'Equipment & Heavy Machinery' => ['color' => '#ef4444', 'icon' => '🚜', 'actual' => 0, 'estimated' => 0, 'count' => 0],
            'Subcontractor & Trade' => ['color' => '#8b5cf6', 'icon' => '🤝', 'actual' => 0, 'estimated' => 0, 'count' => 0],
            'Permits & Regulatory' => ['color' => '#06b6d4', 'icon' => '📜', 'actual' => 0, 'estimated' => 0, 'count' => 0],
            'Site Overhead & Utilities' => ['color' => '#10b981', 'icon' => '⚡', 'actual' => 0, 'estimated' => 0, 'count' => 0],
            'Contingency & Testing' => ['color' => '#ec4899', 'icon' => '🛡️', 'actual' => 0, 'estimated' => 0, 'count' => 0],
        ];

        foreach ($this->costs as $cost) {
            $cat = $cost->cost_category;
            if (!isset($categories[$cat])) {
                $categories[$cat] = ['color' => '#94a3b8', 'icon' => '⚙️', 'actual' => 0, 'estimated' => 0, 'count' => 0];
            }
            $categories[$cat]['actual'] += $cost->actual_cost;
            $categories[$cat]['estimated'] += $cost->estimated_cost;
            $categories[$cat]['count'] += 1;
        }

        return $categories;
    }

    public function getTotalLoanDisbursedAttribute(): float
    {
        return (float) $this->payments()->where('status', 'paid')->whereIn('financing_type', ['bank_loan', 'pagibig_loan'])->sum('amount');
    }

    public function getTotalEquityPaidAttribute(): float
    {
        return (float) $this->payments()->where('status', 'paid')->where('financing_type', 'client_equity')->sum('amount');
    }

    public function getPendingLoanDisbursementAttribute(): float
    {
        $approved = $this->approved_loan_amount > 0 ? $this->approved_loan_amount : ($this->contract_budget * 0.80);
        return max(0, $approved - $this->total_loan_disbursed);
    }

    public function getFinancingSummaryAttribute(): array
    {
        $totalPaid = (float) $this->payments()->where('status', 'paid')->sum('amount');
        $contract = $this->contract_budget > 0 ? $this->contract_budget : 1;
        $loanApproved = $this->approved_loan_amount > 0 ? $this->approved_loan_amount : ($contract * 0.80);
        $equityTarget = $this->client_equity_amount > 0 ? $this->client_equity_amount : ($contract - $loanApproved);
        $loanDisbursed = $this->total_loan_disbursed;
        $equityPaid = $this->total_equity_paid;

        return [
            'financing_type' => $this->financing_type ?? 'bank_loan',
            'financing_institution' => $this->financing_institution ?? 'Philippine Banking Partner / Pag-IBIG HDMF',
            'loan_account_no' => $this->loan_account_no ?? 'LOG-PENDING',
            'contract_budget' => $contract,
            'approved_loan_amount' => $loanApproved,
            'client_equity_amount' => $equityTarget,
            'loan_disbursed' => $loanDisbursed,
            'equity_paid' => $equityPaid,
            'total_paid' => $totalPaid,
            'remaining_receivable' => max(0, $contract - $totalPaid),
            'percent_collected' => round(($totalPaid / $contract) * 100, 1),
            'payment_first_cleared' => ($totalPaid > 0),
        ];
    }
}
