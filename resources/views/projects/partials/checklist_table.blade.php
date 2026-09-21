<div style="overflow-x: auto; border: 1px solid var(--border-color); border-radius: var(--radius-sm); background: #fafbfc;">
    <table class="data-table custom-table" style="margin-bottom: 0; font-size: 0.85rem; width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #f8fafc; border-bottom: 2px solid {{ $tradeColor ?? 'var(--primary-red)' }};">
                <th style="width: 50px; text-align: center; border-right: 1px solid var(--border-color); padding: 11px 8px; font-weight: 700; color: #1e293b; font-size: 0.75rem; letter-spacing: 0.05em; text-transform: uppercase;">Done</th>
                <th style="min-width: 230px; border-right: 1px solid var(--border-color); padding: 11px 14px; font-weight: 800; color: {{ $tradeColor ?? 'var(--primary-red)' }}; font-size: 0.75rem; letter-spacing: 0.05em; text-transform: uppercase;">{{ $tradeName ?? 'Task Description' }}</th>
                <th style="min-width: 320px; border-right: 1px solid var(--border-color); padding: 11px 14px; font-weight: 700; color: #1e293b; font-size: 0.75rem; letter-spacing: 0.05em; text-transform: uppercase;">Aligned Construction Materials & Cost</th>
                <th style="width: 140px; text-align: center; border-right: 1px solid var(--border-color); padding: 11px 8px; font-weight: 700; color: #1e293b; font-size: 0.75rem; letter-spacing: 0.05em; text-transform: uppercase;">Proof of Work</th>
                <th style="width: 145px; text-align: center; border-right: 1px solid var(--border-color); padding: 11px 8px; font-weight: 700; color: #1e293b; font-size: 0.75rem; letter-spacing: 0.05em; text-transform: uppercase;">Task Status</th>
                <th style="width: 90px; text-align: center; padding: 11px 8px; font-weight: 700; color: #1e293b; font-size: 0.75rem; letter-spacing: 0.05em; text-transform: uppercase;">Action</th>
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
                        <strong class="task-title-text" style="color: {{ $isDone ? '#64748b' : '#0f172a' }}; font-size: 0.925rem; {{ $isDone ? 'text-decoration: line-through;' : '' }}">
                            {{ $task->task_name }}
                        </strong>
                        @if($task->assignedPersonnel)
                            <div style="font-size: 0.75rem; color: #475569; margin-top: 3px;">
                                Lead: <span style="font-weight: 600; color: #0f172a;">{{ $task->assignedPersonnel->name }}</span>
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
                                                <td style="padding: 6px 10px; color: #0f172a; font-weight: 600; border-right: 1px solid #e2e8f0;">
                                                    {{ $mat->material_name }}
                                                </td>
                                                <td style="padding: 6px 10px; text-align: right; width: 170px; font-family: var(--font-mono); font-weight: 700; color: {{ $isDone ? '#047857' : '#dc2626' }}; white-space: nowrap; background: #fafbfc;">
                                                    {{ number_format($mat->quantity) }} {{ $mat->unit }} <span style="font-weight: normal; color: #64748b; font-size: 0.7rem;">(₱{{ number_format($mat->total_cost, 2) }})</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <span style="font-size: 0.75rem; color: #64748b; font-style: italic;">
                                Direct trade labor & inspection activity
                            </span>
                        @endif
                    </td>

                    <!-- 4. Proof of Work (Attach Photo & Thumbnail) -->
                    <td style="text-align: center; vertical-align: middle; border-right: 1px solid #f1f5f9; padding: 8px;" class="cell-proof">
                        @if($task->photo_path)
                            <div style="display: inline-flex; flex-direction: column; align-items: center; gap: 4px;">
                                <div style="position: relative; width: 44px; height: 44px; border-radius: 6px; overflow: hidden; border: 1.5px solid #059669; box-shadow: 0 1px 3px rgba(0,0,0,0.12); cursor: pointer; background: #0f172a;" title="Click to view full-size proof photo" onclick="openTaskPhotoPreviewModal('{{ asset($task->photo_path) }}', '{{ addslashes($task->task_name) }}', '{{ addslashes($task->category) }}', '{{ addslashes($task->photo_caption ?? '') }}', '{{ $task->updated_at ? $task->updated_at->format('M d, Y') : '' }}')">
                                    <img src="{{ asset($task->photo_path) }}" alt="Proof" style="width: 100%; height: 100%; object-fit: cover;">
                                    <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0'">
                                        <span style="color: #fff; font-size: 0.65rem; font-weight: 800;">VIEW</span>
                                    </div>
                                </div>
                                <div style="display: flex; gap: 4px; align-items: center; margin-top: 2px;">
                                    <button type="button" style="font-size: 0.7rem; color: #0284c7; background: none; border: none; padding: 0; cursor: pointer; font-weight: 600; text-decoration: underline;" onclick="openAttachTaskPhotoModal({{ $task->id }}, '{{ addslashes($task->task_name) }}', '{{ addslashes($task->category) }}', '{{ asset($task->photo_path) }}', '{{ addslashes($task->photo_caption ?? '') }}', {{ $isDone ? 1 : 0 }})">
                                        Change
                                    </button>
                                    <span style="color: #cbd5e1; font-size: 0.65rem;">|</span>
                                    <button type="button" style="font-size: 0.7rem; color: #dc2626; background: none; border: none; padding: 0; cursor: pointer; font-weight: 600; text-decoration: underline;" onclick="removeTaskPhotoAjax({{ $task->id }}, '{{ addslashes($task->task_name) }}')">
                                        Remove
                                    </button>
                                </div>
                            </div>
                        @else
                            <button type="button" class="btn-attach-photo" onclick="openAttachTaskPhotoModal({{ $task->id }}, '{{ addslashes($task->task_name) }}', '{{ addslashes($task->category) }}', '', '', {{ $isDone ? 1 : 0 }})" style="display: inline-flex; align-items: center; justify-content: center; gap: 5px; font-size: 0.75rem; font-weight: 700; padding: 6px 10px; border-radius: 6px; border: 1.5px dashed #0284c7; background: #f0f9ff; color: #0284c7; cursor: pointer; transition: all 0.2s; white-space: nowrap;" onmouseover="this.style.background='#e0f2fe'; this.style.borderColor='#0369a1';" onmouseout="this.style.background='#f0f9ff'; this.style.borderColor='#0284c7';" title="Attach photo proof of completion">
                                <svg style="width: 14px; height: 14px; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                Attach Photo
                            </button>
                        @endif
                    </td>

                    <!-- 5. Task Status Dropdown (Forward-Only Monotonic) -->
                    <td style="text-align: center; vertical-align: middle; border-right: 1px solid #f1f5f9; padding: 8px;" class="cell-status">
                        @if($isDone)
                            <span class="badge badge-completed" style="font-size: 0.8rem; padding: 5px 12px; display: inline-flex; align-items: center; gap: 4px;" title="Permanent milestone: Completed and materials mobilized">
                                Completed
                            </span>
                        @else
                            <select class="form-select task-status-select" onchange="updateTaskStatusAjax({{ $task->id }}, this.value, this)" style="padding: 4px 8px; font-size: 0.775rem; font-weight: 700; border-radius: 4px; height: 32px; width: 100%; max-width: 130px; background: #fafbfc; color: {{ $task->status === 'in_progress' ? 'var(--primary-red)' : '#0f172a' }}; border-color: {{ $task->status === 'in_progress' ? 'var(--primary-red-border)' : 'var(--border-color)' }};">
                                <option value="not_started" {{ $task->status === 'not_started' ? 'selected' : '' }}>Not Started</option>
                                <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ $task->status === 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                        @endif
                    </td>

                    <!-- 6. Actions (Edit / Delete) -->
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
                    <td colspan="6" style="text-align: center; color: #64748b; padding: 24px;">
                        No tasks in this discipline checklist yet. Click "+ Add Task" or use "Reset Standard Checklist".
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
