<div style="overflow-x: auto; border: 1px solid rgba(255, 255, 255, 0.12); border-radius: var(--radius-sm); background: rgba(15, 23, 42, 0.6);">
    <table class="data-table" style="margin-bottom: 0; font-size: 0.85rem; width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: rgba(15, 23, 42, 0.95); border-bottom: 2px solid {{ $tradeColor ?? 'rgba(56, 189, 248, 0.4)' }};">
                <th style="width: 60px; text-align: center; border-right: 1px solid rgba(255, 255, 255, 0.08); padding: 11px 8px; font-weight: 700; color: #94a3b8; font-size: 0.75rem; letter-spacing: 0.05em; text-transform: uppercase;">Done</th>
                <th style="min-width: 250px; border-right: 1px solid rgba(255, 255, 255, 0.08); padding: 11px 14px; font-weight: 700; color: {{ $tradeColor ?? '#f8fafc' }}; font-size: 0.75rem; letter-spacing: 0.05em; text-transform: uppercase;">{{ $tradeName ?? 'Task Description' }}</th>
                <th style="min-width: 380px; border-right: 1px solid rgba(255, 255, 255, 0.08); padding: 11px 14px; font-weight: 700; color: #94a3b8; font-size: 0.75rem; letter-spacing: 0.05em; text-transform: uppercase;">Aligned Construction Materials & Cost Breakdown</th>
                <th style="width: 160px; text-align: center; border-right: 1px solid rgba(255, 255, 255, 0.08); padding: 11px 8px; font-weight: 700; color: #94a3b8; font-size: 0.75rem; letter-spacing: 0.05em; text-transform: uppercase;">Task Status</th>
                <th style="width: 95px; text-align: center; padding: 11px 8px; font-weight: 700; color: #94a3b8; font-size: 0.75rem; letter-spacing: 0.05em; text-transform: uppercase;">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tasks as $task)
                @php
                    $isDone = $task->status === 'completed' || $task->progress >= 100;
                    $taskMats = $task->taskMaterials;
                @endphp
                <tr id="task-row-{{ $task->id }}" class="checklist-task-row" data-task-id="{{ $task->id }}" data-status="{{ $task->status }}" style="{{ $isDone ? 'background: rgba(16, 185, 129, 0.05);' : '' }}; border-bottom: 1px solid rgba(255, 255, 255, 0.08); transition: background 0.3s ease;">
                    
                    <!-- 1. Done Checkbox (Irreversible Completion Rule) -->
                    <td style="text-align: center; vertical-align: middle; border-right: 1px solid rgba(255, 255, 255, 0.08); padding: 10px 8px;" class="cell-done">
                        @if($isDone)
                            <span class="locked-done-badge" title="Irreversible Completion: This task is completed and permanently locked." style="display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 6px; background: rgba(16, 185, 129, 0.2); border: 1.5px solid #10b981; color: #10b981; font-weight: 900; font-size: 0.75rem; cursor: not-allowed;">
                                OK
                            </span>
                        @else
                            <input type="checkbox" onchange="toggleChecklistAjax({{ $task->id }}, this)" title="Click to mark Completed & activate materials (Irreversible)" style="width: 20px; height: 20px; cursor: pointer; accent-color: #10b981;">
                        @endif
                    </td>

                    <!-- 2. Task Description & Lead -->
                    <td class="cell-description" style="vertical-align: middle; border-right: 1px solid rgba(255, 255, 255, 0.08); padding: 12px 14px;">
                        <strong class="task-title-text" style="color: {{ $isDone ? '#94a3b8' : '#f8fafc' }}; font-size: 0.925rem; {{ $isDone ? 'text-decoration: line-through;' : '' }}">
                            {{ $task->task_name }}
                        </strong>
                        @if($task->assignedPersonnel)
                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 3px;">
                                Lead: {{ $task->assignedPersonnel->name }}
                            </div>
                        @endif
                    </td>

                    <!-- 3. Strictly Aligned Construction Materials Table with Guiding Lines -->
                    <td class="cell-materials" style="vertical-align: middle; border-right: 1px solid rgba(255, 255, 255, 0.08); padding: 8px 12px;">
                        @if($taskMats->count() > 0)
                            <div style="background: rgba(10, 16, 30, 0.75); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 6px; overflow: hidden;">
                                <table style="width: 100%; border-collapse: collapse; font-size: 0.75rem;">
                                    <tbody>
                                        @foreach($taskMats as $mIndex => $mat)
                                            <tr style="{{ !$loop->last ? 'border-bottom: 1px solid rgba(255, 255, 255, 0.07);' : '' }}">
                                                <td style="padding: 6px 10px; color: #cbd5e1; font-weight: 500; border-right: 1px solid rgba(255, 255, 255, 0.07);">
                                                    {{ $mat->material_name }}
                                                </td>
                                                <td style="padding: 6px 10px; text-align: right; width: 175px; font-family: var(--font-mono); font-weight: 700; color: {{ $isDone ? '#10b981' : '#38bdf8' }}; white-space: nowrap; background: rgba(0, 0, 0, 0.15);">
                                                    {{ number_format($mat->quantity) }} {{ $mat->unit }} <span style="opacity: 0.75; font-weight: normal; color: {{ $isDone ? '#6ee7b7' : '#93c5fd' }};">(₱{{ number_format($mat->total_cost, 2) }})</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <span style="font-size: 0.75rem; color: var(--text-muted); font-style: italic;">
                                Direct trade labor & inspection activity
                            </span>
                        @endif
                    </td>

                    <!-- 4. Task Status Dropdown (Forward-Only Monotonic) -->
                    <td style="text-align: center; vertical-align: middle; border-right: 1px solid rgba(255, 255, 255, 0.08); padding: 8px;" class="cell-status">
                        @if($isDone)
                            <span class="badge badge-completed" style="font-size: 0.8rem; padding: 5px 12px; display: inline-flex; align-items: center; gap: 4px;" title="Permanent milestone: Completed and materials mobilized">
                                Completed
                            </span>
                        @else
                            <select class="form-select task-status-select" onchange="updateTaskStatusAjax({{ $task->id }}, this.value, this)" style="padding: 4px 8px; font-size: 0.775rem; font-weight: 700; border-radius: 4px; height: 30px; width: 100%; max-width: 140px; background: rgba(15, 23, 42, 0.85); color: {{ $task->status === 'in_progress' ? '#38bdf8' : '#94a3b8' }}; border-color: {{ $task->status === 'in_progress' ? 'rgba(56, 189, 248, 0.5)' : 'rgba(255,255,255,0.15)' }};">
                                <option value="not_started" {{ $task->status === 'not_started' ? 'selected' : '' }}>Not Started</option>
                                <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ $task->status === 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                        @endif
                    </td>

                    <!-- 5. Actions (Edit / Delete) -->
                    <td style="text-align: center; vertical-align: middle; padding: 8px;" class="cell-actions">
                        <div style="display: inline-flex; gap: 4px; justify-content: center;">
                            <button type="button" class="btn-secondary" style="font-size: 0.75rem; padding: 4px 7px;" title="Edit Task" onclick="openEditTaskModal({{ $task->id }}, '{{ addslashes($task->task_name) }}', '{{ addslashes($task->category) }}', {{ $task->progress }}, '{{ $task->status }}', '{{ $task->assigned_personnel_id ?? '' }}', '{{ $task->start_date ? $task->start_date->format('Y-m-d') : '' }}', '{{ $task->due_date ? $task->due_date->format('Y-m-d') : '' }}', {{ $task->allocated_budget ?? 0 }})">
                                Edit
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
                                <button type="button" class="btn-secondary" style="font-size: 0.75rem; padding: 4px 7px; opacity: 0.4; cursor: not-allowed;" title="Completed milestones cannot be deleted" disabled>
                                    Done
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 24px;">
                        No tasks in this discipline checklist yet. Click "+ Add Task" or use "Reset Standard Checklist".
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
