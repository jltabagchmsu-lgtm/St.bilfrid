<div style="overflow-x: auto; border: 1px solid var(--border-color); border-radius: var(--radius-sm); background: #fafbfc;">
    <table class="data-table custom-table" style="margin-bottom: 0; font-size: 0.85rem; width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #f8fafc; border-bottom: 2px solid {{ $tradeColor ?? 'var(--primary-red)' }};">
                <th style="width: 60px; text-align: center; border-right: 1px solid var(--border-color); padding: 11px 8px; font-weight: 700; color: var(--text-secondary); font-size: 0.75rem; letter-spacing: 0.05em; text-transform: uppercase;">Done</th>
                <th style="min-width: 250px; border-right: 1px solid var(--border-color); padding: 11px 14px; font-weight: 800; color: {{ $tradeColor ?? 'var(--primary-red)' }}; font-size: 0.75rem; letter-spacing: 0.05em; text-transform: uppercase;">{{ $tradeName ?? 'Task Description' }}</th>
                <th style="min-width: 380px; border-right: 1px solid var(--border-color); padding: 11px 14px; font-weight: 700; color: var(--text-secondary); font-size: 0.75rem; letter-spacing: 0.05em; text-transform: uppercase;">Aligned Construction Materials & Cost Breakdown</th>
                <th style="width: 160px; text-align: center; border-right: 1px solid var(--border-color); padding: 11px 8px; font-weight: 700; color: var(--text-secondary); font-size: 0.75rem; letter-spacing: 0.05em; text-transform: uppercase;">Task Status</th>
                <th style="width: 95px; text-align: center; padding: 11px 8px; font-weight: 700; color: var(--text-secondary); font-size: 0.75rem; letter-spacing: 0.05em; text-transform: uppercase;">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tasks as $task)
                @php
                    $isDone = $task->status === 'completed' || $task->progress >= 100;
                    $taskMats = $task->taskMaterials;
                @endphp
                <tr id="task-row-{{ $task->id }}" class="checklist-task-row" data-task-id="{{ $task->id }}" data-status="{{ $task->status }}" style="{{ $isDone ? 'background: #f0fdf4;' : '' }}; border-bottom: 1px solid #f1f5f9; transition: background 0.3s ease;">
                    
                    <!-- 1. Done Checkbox (Irreversible Completion Rule) -->
                    <td style="text-align: center; vertical-align: middle; border-right: 1px solid #f1f5f9; padding: 10px 8px;" class="cell-done">
                        @if($isDone)
                            <span class="locked-done-badge" title="Irreversible Completion: This task is completed and permanently locked." style="display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 6px; background: rgba(4, 120, 87, 0.15); border: 1.5px solid #047857; color: #047857; font-weight: 900; font-size: 0.75rem; cursor: not-allowed;">
                                OK
                            </span>
                        @else
                            <input type="checkbox" onchange="toggleChecklistAjax({{ $task->id }}, this)" title="Click to mark Completed & activate materials (Irreversible)" style="width: 18px; height: 18px; cursor: pointer; accent-color: var(--primary-red);">
                        @endif
                    </td>

                    <!-- 2. Task Description & Lead -->
                    <td class="cell-description" style="vertical-align: middle; border-right: 1px solid #f1f5f9; padding: 12px 14px;">
                        <strong class="task-title-text" style="color: {{ $isDone ? '#94a3b8' : 'var(--text-primary)' }}; font-size: 0.925rem; {{ $isDone ? 'text-decoration: line-through;' : '' }}">
                            {{ $task->task_name }}
                        </strong>
                        @if($task->assignedPersonnel)
                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 3px;">
                                Lead: <span style="font-weight: 600; color: var(--text-secondary);">{{ $task->assignedPersonnel->name }}</span>
                            </div>
                        @endif
                    </td>

                    <!-- 3. Strictly Aligned Construction Materials Table with Guiding Lines -->
                    <td class="cell-materials" style="vertical-align: middle; border-right: 1px solid #f1f5f9; padding: 8px 12px;">
                        @if($taskMats->count() > 0)
                            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden;">
                                <table style="width: 100%; border-collapse: collapse; font-size: 0.75rem;">
                                    <tbody>
                                        @foreach($taskMats as $mIndex => $mat)
                                            <tr style="{{ !$loop->last ? 'border-bottom: 1px solid #e2e8f0;' : '' }}">
                                                <td style="padding: 6px 10px; color: var(--text-primary); font-weight: 600; border-right: 1px solid #e2e8f0;">
                                                    {{ $mat->material_name }}
                                                </td>
                                                <td style="padding: 6px 10px; text-align: right; width: 180px; font-family: var(--font-mono); font-weight: 700; color: {{ $isDone ? '#047857' : '#dc2626' }}; white-space: nowrap; background: #fafbfc;">
                                                    {{ number_format($mat->quantity) }} {{ $mat->unit }} <span style="font-weight: normal; color: var(--text-muted); font-size: 0.7rem;">(₱{{ number_format($mat->total_cost, 2) }})</span>
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
                    <td style="text-align: center; vertical-align: middle; border-right: 1px solid #f1f5f9; padding: 8px;" class="cell-status">
                        @if($isDone)
                            <span class="badge badge-completed" style="font-size: 0.8rem; padding: 5px 12px; display: inline-flex; align-items: center; gap: 4px;" title="Permanent milestone: Completed and materials mobilized">
                                Completed
                            </span>
                        @else
                            <select class="form-select task-status-select" onchange="updateTaskStatusAjax({{ $task->id }}, this.value, this)" style="padding: 4px 8px; font-size: 0.775rem; font-weight: 700; border-radius: 4px; height: 32px; width: 100%; max-width: 140px; background: #fafbfc; color: {{ $task->status === 'in_progress' ? 'var(--primary-red)' : 'var(--text-secondary)' }}; border-color: {{ $task->status === 'in_progress' ? 'var(--primary-red-border)' : 'var(--border-color)' }};">
                                <option value="not_started" {{ $task->status === 'not_started' ? 'selected' : '' }}>Not Started</option>
                                <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ $task->status === 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                        @endif
                    </td>

                    <!-- 5. Actions (Edit / Delete) -->
                    <td style="text-align: center; vertical-align: middle; padding: 8px;" class="cell-actions">
                        <div style="display: inline-flex; gap: 4px; justify-content: center;">
                            <button type="button" class="btn-secondary" style="font-size: 0.75rem; padding: 4px 8px;" title="Edit Task" onclick="openEditTaskModal({{ $task->id }}, '{{ addslashes($task->task_name) }}', '{{ addslashes($task->category) }}', {{ $task->progress }}, '{{ $task->status }}', '{{ $task->assigned_personnel_id ?? '' }}', '{{ $task->start_date ? $task->start_date->format('Y-m-d') : '' }}', '{{ $task->due_date ? $task->due_date->format('Y-m-d') : '' }}', {{ $task->allocated_budget ?? 0 }})">
                                Edit
                            </button>
                            @if(!$isDone)
                                <form action="{{ route('projects.tasks.destroy', $task->id) }}" method="POST" onsubmit="return confirm('Remove task {{ addslashes($task->task_name) }} from checklist?');" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-secondary" style="font-size: 0.75rem; padding: 4px 8px; color: var(--primary-red); border-color: var(--primary-red-border);" title="Delete Task">
                                        &times;
                                    </button>
                                </form>
                            @else
                                <button type="button" class="btn-secondary" style="font-size: 0.75rem; padding: 4px 8px; opacity: 0.4; cursor: not-allowed;" title="Completed milestones cannot be deleted" disabled>
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
