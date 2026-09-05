<div style="overflow-x: auto;">
    <table class="data-table" style="margin-bottom: 0; font-size: 0.85rem; width: 100%; border-collapse: separate; border-spacing: 0;">
        <thead>
            <tr>
                <th style="width: 60px; text-align: center;">Done</th>
                <th style="min-width: 250px;">{{ $tradeName ?? 'Task Description' }}</th>
                <th style="width: 230px;">Timeline Phase</th>
                <th style="min-width: 260px;">Aligned Construction Materials</th>
                <th style="width: 160px; text-align: center;">Task Status</th>
                <th style="width: 85px; text-align: right;">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tasks as $task)
                @php
                    $isDone = $task->status === 'completed' || $task->progress >= 100;
                    $phaseColor = $task->timeline_phase_badge_color;
                    $taskMats = $task->taskMaterials;
                @endphp
                <tr id="task-row-{{ $task->id }}" class="checklist-task-row" data-task-id="{{ $task->id }}" data-phase="{{ $task->timeline_phase_key }}" data-status="{{ $task->status }}" style="{{ $isDone ? 'background: rgba(16, 185, 129, 0.05);' : '' }}; transition: background 0.3s ease;">
                    
                    <!-- 1. Done Checkbox (Irreversible Completion Rule) -->
                    <td style="text-align: center; vertical-align: middle;" class="cell-done">
                        @if($isDone)
                            <span class="locked-done-badge" title="🔒 Irreversible Completion: This task is completed and permanently locked." style="display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; border-radius: 6px; background: rgba(16, 185, 129, 0.2); border: 1.5px solid #10b981; color: #10b981; font-weight: 900; font-size: 0.9rem; cursor: not-allowed;">
                                ✓
                            </span>
                        @else
                            <input type="checkbox" onchange="toggleChecklistAjax({{ $task->id }}, this)" title="Click to mark Completed & activate materials (Irreversible)" style="width: 20px; height: 20px; cursor: pointer; accent-color: #10b981;">
                        @endif
                    </td>

                    <!-- 2. Task Description & Lead -->
                    <td class="cell-description" style="vertical-align: middle;">
                        <strong class="task-title-text" style="color: {{ $isDone ? '#94a3b8' : '#f8fafc' }}; font-size: 0.925rem; {{ $isDone ? 'text-decoration: line-through;' : '' }}">
                            {{ $task->task_name }}
                        </strong>
                        @if($task->assignedPersonnel)
                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 3px;">
                                👤 Lead: {{ $task->assignedPersonnel->name }}
                            </div>
                        @endif
                    </td>

                    <!-- 3. Timeline Schedule Phase Dropdown Choice (Spacious Layout) -->
                    <td class="cell-timeline" style="vertical-align: middle;">
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <select name="timeline_phase" class="form-select task-timeline-select" onchange="updateQuickTimelineAjax({{ $task->id }}, this.value, this)" style="padding: 4px 8px; font-size: 0.75rem; font-weight: 700; height: 30px; border-radius: 4px; background: rgba(15, 23, 42, 0.85); color: {{ $phaseColor }}; border-color: {{ $phaseColor }}55; width: 100%; max-width: 215px;">
                                <option value="Phase 1: Mobilization & Substructure" {{ $task->timeline_phase_key === 'phase1' ? 'selected' : '' }}>Phase 1 (M1-2): Mobilization</option>
                                <option value="Phase 2: Superstructure & Framing" {{ $task->timeline_phase_key === 'phase2' ? 'selected' : '' }}>Phase 2 (M3-5): Structure</option>
                                <option value="Phase 3: MEP Rough-Ins & Enclosures" {{ $task->timeline_phase_key === 'phase3' ? 'selected' : '' }}>Phase 3 (M6-8): MEP Rough-in</option>
                                <option value="Phase 4: Architectural Fit-Out & Finishes" {{ $task->timeline_phase_key === 'phase4' ? 'selected' : '' }}>Phase 4 (M9-11): Finishes</option>
                                <option value="Phase 5: Commissioning & Handover" {{ $task->timeline_phase_key === 'phase5' ? 'selected' : '' }}>Phase 5 (M11-12): Handover</option>
                            </select>
                            <span style="font-family: var(--font-mono); font-size: 0.725rem; color: var(--text-muted);">
                                📅 {{ $task->timeline_window_label }}
                            </span>
                        </div>
                    </td>

                    <!-- 4. Strictly Aligned Construction Materials -->
                    <td class="cell-materials" style="vertical-align: middle;">
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            @forelse($taskMats as $mat)
                                <div style="display: inline-flex; align-items: center; justify-content: space-between; gap: 8px; background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.08); padding: 3px 8px; border-radius: 4px; font-size: 0.75rem;">
                                    <span style="color: #cbd5e1; font-weight: 500;">
                                        🧱 {{ $mat->material_name }}
                                    </span>
                                    <span style="font-family: var(--font-mono); font-weight: 700; color: {{ $isDone ? '#10b981' : '#38bdf8' }}; white-space: nowrap;">
                                        {{ number_format($mat->quantity) }} {{ $mat->unit }} (₱{{ number_format($mat->total_cost, 2) }})
                                    </span>
                                </div>
                            @empty
                                <span style="font-size: 0.75rem; color: var(--text-muted); font-style: italic;">
                                    Direct trade labor & inspection activity
                                </span>
                            @endforelse
                        </div>
                    </td>

                    <!-- 5. Task Status Dropdown (Forward-Only Monotonic) -->
                    <td style="text-align: center; vertical-align: middle;" class="cell-status">
                        @if($isDone)
                            <span class="badge badge-completed" style="font-size: 0.8rem; padding: 5px 12px; display: inline-flex; align-items: center; gap: 4px;" title="🔒 Permanent milestone: Completed and materials mobilized">
                                🔒 Completed
                            </span>
                        @else
                            <select class="form-select task-status-select" onchange="updateTaskStatusAjax({{ $task->id }}, this.value, this)" style="padding: 4px 8px; font-size: 0.775rem; font-weight: 700; border-radius: 4px; height: 30px; width: 100%; max-width: 140px; background: rgba(15, 23, 42, 0.85); color: {{ $task->status === 'in_progress' ? '#38bdf8' : '#94a3b8' }}; border-color: {{ $task->status === 'in_progress' ? 'rgba(56, 189, 248, 0.5)' : 'rgba(255,255,255,0.15)' }};">
                                <option value="not_started" {{ $task->status === 'not_started' ? 'selected' : '' }}>⏳ Not Started</option>
                                <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>⚡ In Progress</option>
                                <option value="completed" {{ $task->status === 'completed' ? 'selected' : '' }}>🔒 Completed</option>
                            </select>
                        @endif
                    </td>

                    <!-- 6. Actions (Edit / Delete) -->
                    <td style="text-align: right; vertical-align: middle;" class="cell-actions">
                        <div style="display: inline-flex; gap: 4px; justify-content: flex-end;">
                            <button type="button" class="btn-secondary" style="font-size: 0.75rem; padding: 4px 7px;" title="Edit Task" onclick="openEditTaskModal({{ $task->id }}, '{{ addslashes($task->task_name) }}', '{{ addslashes($task->category) }}', {{ $task->progress }}, '{{ $task->status }}', '{{ $task->assigned_personnel_id ?? '' }}', '{{ $task->start_date ? $task->start_date->format('Y-m-d') : '' }}', '{{ $task->due_date ? $task->due_date->format('Y-m-d') : '' }}', {{ $task->allocated_budget ?? 0 }}, '{{ addslashes($task->timeline_phase ?? '') }}', '{{ addslashes($task->timeline_month ?? '') }}')">
                                ✏️
                            </button>
                            @if(!$isDone)
                                <form action="{{ route('projects.tasks.destroy', $task->id) }}" method="POST" onsubmit="return confirm('Remove task {{ addslashes($task->task_name) }} from checklist?');" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-secondary" style="font-size: 0.75rem; padding: 4px 7px; color: #ef4444;" title="Delete Task">
                                        &times;
                                    </button>
                                </form>
                            @else
                                <button type="button" class="btn-secondary" style="font-size: 0.75rem; padding: 4px 7px; opacity: 0.4; cursor: not-allowed;" title="🔒 Completed milestones cannot be deleted" disabled>
                                    🔒
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 24px;">
                        No tasks in this discipline checklist yet. Click "+ Add Task" or use "⚡ Reset Standard Checklist".
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
