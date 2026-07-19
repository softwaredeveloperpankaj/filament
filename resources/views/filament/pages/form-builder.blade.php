<x-filament-panels::page>
    <x-filament::section compact>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500">Status:</span>

                @if ($this->template->status === 'published')
                    <x-filament::badge color="success">Published</x-filament::badge>
                @else
                    <x-filament::badge color="warning">Draft</x-filament::badge>
                @endif
            </div>

            <div class="text-sm text-gray-500">
                {{ $this->template->name }}
            </div>
        </div>
    </x-filament::section>

    <div class="fb-layout">
        {{-- Left panel --}}
        <div class="fb-sidebar">
            <div class="fb-sticky">
                <x-filament::section>
                    <x-slot name="heading">Field Palette</x-slot>
                    <x-slot name="description">
                        Drag a field into any section.
                    </x-slot>

                    <div x-data="{ open: false }" class="space-y-3">
                        <x-filament::button
                            icon="heroicon-o-plus"
                            class="w-full"
                            x-on:click="$dispatch('open-modal', { id: 'add-section-modal' })"
                        >
                            Add Section
                        </x-filament::button>

                        <x-filament::modal id="add-section-modal" width="md">
                            <x-slot name="heading">
                                Add Section
                            </x-slot>

                            <div class="space-y-4">
                                <x-filament::input.wrapper>
                                    <x-filament::input
                                        wire:model.live="newSectionTitle"
                                        type="text"
                                        placeholder="e.g. Student Information"
                                    />
                                </x-filament::input.wrapper>
                            </div>

                            <x-slot name="footerActions">
                                <x-filament::button
                                    color="gray"
                                    x-on:click="$dispatch('close-modal', { id: 'add-section-modal' })"
                                >
                                    Cancel
                                </x-filament::button>

                                <x-filament::button
                                    wire:click="createSection"
                                >
                                    Create Section
                                </x-filament::button>
                            </x-slot>
                        </x-filament::modal>

                    </div>
                    
                    <div class="fb-divider"></div>

                    <div id="field-palette" class="fb-palette">
                        @foreach ([
                            'text' => 'Text',
                            'email' => 'Email',
                            'number' => 'Number',
                            'date' => 'Date',
                            'textarea' => 'Textarea',
                            'select' => 'Select',
                            'radio' => 'Radio',
                            'checkbox' => 'Checkbox',
                            'file' => 'File Upload',
                        ] as $type => $label)
                            <div
                                class="fb-palette-item"
                                data-type="{{ $type }}"
                                data-label="{{ $label }}"
                            >
                                <div class="fb-palette-title">{{ $label }}</div>
                                <div class="fb-palette-meta">{{ $type }}</div>
                            </div>
                        @endforeach
                    </div>
                </x-filament::section>
            </div>
        </div>

        {{-- Right panel --}}
        <div class="fb-canvas">
            <x-filament::section>
                <x-slot name="heading">
                    Builder Canvas — {{ $this->template->branch->name ?? '' }}
                </x-slot>

                <x-slot name="description">
                    Drag sections to reorder. Drag fields from palette or between sections.
                </x-slot>

                <div id="sections-wrapper" class="fb-sections">
                    @forelse ($this->template->sections as $section)
                        <div
                            class="section-card fb-section"
                            data-section-id="{{ $section->id }}"
                            x-data="{ open: {{ $loop->first ? 'true' : 'false' }} }"
                        >
                            <div class="fb-section-header" @click="open = !open">
                                <div class="fb-section-left">
                                    <button type="button" class="handle fb-handle" @click.stop>
                                        <x-filament::icon name="heroicon-o-bars-3" class="h-5 w-5" />
                                    </button>

                                    <div>
                                        <div class="fb-section-title">{{ $section->title }}</div>
                                        <div class="fb-section-meta">
                                            {{ $section->fields->count() }} fields
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2" @click.stop>
                                    <x-filament::badge color="gray" size="sm">
                                        <x-filament::icon
                                            x-bind:icon="open ? 'heroicon-m-chevron-up' : 'heroicon-m-chevron-down'"
                                            icon="heroicon-m-chevron-down"
                                            class="h-4 w-4"
                                        />
                                    </x-filament::badge>

                                    <x-filament::icon-button
                                        icon="heroicon-o-trash"
                                        color="danger"
                                        size="sm"
                                        wire:click="deleteSection({{ $section->id }})"
                                        wire:confirm="Delete this section and all its fields?"
                                        tooltip="Delete Section"
                                    />
                                </div>
                            </div>

                            <div x-show="open" x-collapse>
                                <div
                                    class="field-dropzone fb-dropzone"
                                    data-section-id="{{ $section->id }}"
                                >
                                    @forelse ($section->fields as $field)
                                        <div
                                            class="field-card fb-field"
                                            data-field-id="{{ $field->id }}"
                                        >
                                            <div class="fb-field-main">
                                                <div class="fb-field-top">
                                                    <div class="fb-field-title">{{ $field->label }}</div>

                                                    <x-filament::badge color="gray" size="sm">
                                                        {{ $field->type }}
                                                    </x-filament::badge>

                                                    @if ($field->is_required)
                                                        <x-filament::badge color="danger" size="sm">
                                                            Required
                                                        </x-filament::badge>
                                                    @endif
                                                </div>

                                                @if ($field->help_text)
                                                    <div class="fb-field-help">
                                                        {{ $field->help_text }}
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="fb-field-actions">
                                                @if (in_array($field->type, ['select', 'radio', 'checkbox']))
                                                    <x-filament::icon-button
                                                        icon="heroicon-o-list-bullet"
                                                        color="gray"
                                                        size="sm"
                                                        tooltip="Manage Options"
                                                        wire:click="openOptionsModal({{ $field->id }})"
                                                    />
                                                @endif

                                                <x-filament::icon-button
                                                    icon="heroicon-o-pencil-square"
                                                    color="warning"
                                                    size="sm"
                                                    wire:click="openEditField({{ $field->id }})"
                                                    tooltip="Edit Field"
                                                />

                                                <x-filament::icon-button
                                                    icon="heroicon-o-trash"
                                                    color="danger"
                                                    size="sm"
                                                    wire:click="confirmDeleteField({{ $field->id }})"
                                                />

                                                <button type="button" class="fb-field-handle">
                                                    <x-filament::icon name="heroicon-o-bars-3" class="h-5 w-5" />
                                                </button>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="fb-empty">
                                            No fields yet. Drag from the palette.
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="fb-empty-state">
                            <div class="fb-empty-title">No sections created yet.</div>
                            <div class="fb-empty-text">
                                Add a section from the left panel to start building the form.
                            </div>
                        </div>
                    @endforelse
                </div>
            </x-filament::section>
        </div>
    </div>

    <x-filament::modal id="delete-field-modal">
        <x-slot name="heading">
            {{ __('Delete Field') }}
        </x-slot>

        {{ __('Are you sure you want to delete this field?') }}

        <x-slot name="footerActions">
            <x-filament::button
                color="gray"
                x-on:click="$dispatch('close-modal',{id:'delete-field-modal'})"
            >
                {{ __('Cancel') }}
            </x-filament::button>

            <x-filament::button
                color="danger"
                wire:click="deleteField"
            >
                {{ __('Delete') }}
            </x-filament::button>
        </x-slot>
    </x-filament::modal>

    {{-- Edit Field Modal --}}
    <x-filament::modal id="edit-field-modal" width="3xl">
        <x-slot name="heading">
            Edit Field
        </x-slot>

        <div class="space-y-4">
            <div class="fb-form-grid">
                <div class="space-y-2">
                    <label class="fb-label">Label</label>
                    <x-filament::input.wrapper>
                        <x-filament::input wire:model="editingFieldData.label" type="text" />
                    </x-filament::input.wrapper>
                    <p class="fb-help">Appears above the field.</p>
                </div>

                <div class="space-y-2">
                    <label class="fb-label">Field Key (Input Name)</label>
                    <x-filament::input.wrapper>
                        <x-filament::input wire:model="editingFieldData.field_key" type="text" />
                    </x-filament::input.wrapper>
                    <p class="fb-help">Unique identifier for this field.</p>
                </div>
            </div>

            <div class="fb-form-grid">
                <div class="space-y-2">
                    <label class="fb-label">Required</label>
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model="editingFieldData.is_required">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>

                <div class="space-y-2">
                    <label class="fb-label">Option Layout</label>
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model="editingFieldData.option_layout">
                            <option value="horizontal">Horizontal</option>
                            <option value="vertical">Vertical</option>
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                    <p class="fb-help">For radio/checkbox fields only.</p>
                </div>
            </div>

            <div class="space-y-2">
                <label class="fb-label">Placeholder</label>
                <x-filament::input.wrapper>
                    <x-filament::input
                        wire:model="editingFieldData.placeholder"
                        type="text"
                        placeholder="e.g. Enter your full name"
                    />
                </x-filament::input.wrapper>
            </div>

            <div class="space-y-2">
                <label class="fb-label">Validation Rules</label>
                <x-filament::input.wrapper>
                    <x-filament::input
                        wire:model="editingFieldData.validation_rules_input"
                        type="text"
                        placeholder="required|string|max:255"
                    />
                </x-filament::input.wrapper>
                <p class="fb-help">Pipe-delimited rules, e.g. required|string|max:255</p>
            </div>

            <div class="space-y-2">
                <label class="fb-label">Help Text</label>
                <x-filament::input.wrapper>
                    <x-filament::input wire:model="editingFieldData.help_text" type="text" />
                </x-filament::input.wrapper>
                <p class="fb-help">Appears below the field.</p>
            </div>

            <div class="space-y-2">
                <label class="fb-label">Visibility JSON</label>
                <x-filament::input.wrapper>
                    <textarea
                        wire:model="editingFieldData.visibility_conditions_input"
                        rows="3"
                        class="fb-textarea"
                        placeholder='{"field":"transport_required","equals":"yes"}'
                    ></textarea>
                </x-filament::input.wrapper>
            </div>

            <div class="space-y-2">
                <label class="fb-label">Settings JSON</label>
                <x-filament::input.wrapper>
                    <textarea
                        wire:model="editingFieldData.settings_input"
                        rows="4"
                        class="fb-textarea"
                        placeholder='{"width":"full","accept":["pdf","jpg"]}'
                    ></textarea>
                </x-filament::input.wrapper>
            </div>
        </div>

        <x-slot name="footerActions">
            <x-filament::button
                color="gray"
                x-on:click="$dispatch('close-modal', { id: 'edit-field-modal' })"
            >
                Cancel
            </x-filament::button>

            <x-filament::button wire:click="saveEditField">
                Save Changes
            </x-filament::button>
        </x-slot>
    </x-filament::modal>

    {{-- Field Option Modal --}}
    <x-filament::modal id="field-options-modal" width="4xl">

        <x-slot name="heading">
            Manage Options
        </x-slot>

        <x-slot name="description">
            Add, edit and drag to reorder options. Changes are saved when you click Save.
        </x-slot>

        <div class="space-y-3">

            {{-- Header row --}}
            <div class="opt-header-row">
                <span></span>{{-- drag handle spacer --}}
                <span class="opt-col-label">Label</span>
                <span class="opt-col-label">Value</span>
                <span class="opt-col-label text-center">Default</span>
                <span></span>{{-- delete spacer --}}
            </div>

            {{-- Options list --}}
            <div id="options-sortable" class="space-y-2">

                @forelse($fieldOptions as $index => $option)
                    <div
                        class="opt-row"
                        wire:key="option-{{ $index }}"
                        data-index="{{ $index }}"
                    >
                        {{-- Drag Handle --}}
                        <button
                            type="button"
                            class="option-handle opt-handle"
                            title="Drag to reorder"
                        >
                            <x-filament::icon name="heroicon-o-bars-3" class="h-4 w-4" />
                        </button>

                        {{-- Label --}}
                        <div>
                            <x-filament::input.wrapper>
                                <x-filament::input
                                    wire:model.live="fieldOptions.{{ $index }}.label"
                                    placeholder="e.g. Male"
                                />
                            </x-filament::input.wrapper>
                        </div>

                        {{-- Value --}}
                        <div>
                            <x-filament::input.wrapper>
                                <x-filament::input
                                    wire:model.live="fieldOptions.{{ $index }}.value"
                                    placeholder="e.g. male"
                                />
                            </x-filament::input.wrapper>
                        </div>

                        {{-- Default --}}
                        <div class="flex items-center justify-center">
                            <x-filament::input.checkbox
                                wire:model.live="fieldOptions.{{ $index }}.is_default"
                            />
                        </div>

                        {{-- Delete --}}
                        <div class="flex items-center justify-center">
                            <x-filament::icon-button
                                icon="heroicon-o-trash"
                                color="danger"
                                size="sm"
                                wire:click="removeOption({{ $index }})"
                                tooltip="Remove"
                            />
                        </div>
                    </div>
                @empty
                    <div class="opt-empty">
                        <x-filament::icon name="heroicon-o-list-bullet" class="h-8 w-8 mx-auto mb-2 opacity-30" />
                        <p>No options yet. Click <strong>Add Option</strong> to get started.</p>
                    </div>
                @endforelse

            </div>

            {{-- Add Option button — bottom, full width --}}
            <div class="pt-1">
                <x-filament::button
                    icon="heroicon-o-plus"
                    color="gray"
                    wire:click="addOption"
                    class="w-full"
                >
                    Add Option
                </x-filament::button>
            </div>

        </div>

        <x-slot name="footerActions">
            <x-filament::button
                color="gray"
                x-on:click="$dispatch('close-modal',{id:'field-options-modal'})"
            >
                Cancel
            </x-filament::button>

            <x-filament::button wire:click="saveOptions" color="primary">
                Save Options
            </x-filament::button>
        </x-slot>

    </x-filament::modal>

    @push('styles')
        <style>
            .sortable-ghost {
                opacity: .45;
            }

            /* ── Options Modal ─────────────────────────────────────── */
            .opt-header-row,
            .opt-row {
                display: grid;
                grid-template-columns: 2rem 1fr 1fr 3.5rem 2.5rem;
                align-items: center;
                gap: 0.625rem;
            }

            .opt-header-row {
                padding: 0 0.375rem 0.25rem;
                border-bottom: 1px solid rgba(148, 163, 184, 0.18);
            }

            .opt-col-label {
                font-size: 0.75rem;
                font-weight: 600;
                color: rgb(107 114 128);
                text-transform: uppercase;
                letter-spacing: 0.04em;
            }

            .opt-row {
                background: rgba(255, 255, 255, 0.7);
                border: 1px solid rgba(148, 163, 184, 0.20);
                border-radius: 0.75rem;
                padding: 0.5rem 0.5rem 0.5rem 0.375rem;
                transition: box-shadow 0.15s ease, border-color 0.15s ease;
            }

            .dark .opt-row {
                background: rgba(31, 41, 55, 0.75);
                border-color: rgba(75, 85, 99, 0.35);
            }

            .opt-row:hover {
                border-color: rgba(148, 163, 184, 0.40);
                box-shadow: 0 1px 4px rgba(0,0,0,0.06);
            }

            .opt-handle {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 2rem;
                height: 2rem;
                border-radius: 0.5rem;
                color: rgb(156 163 175);
                cursor: grab;
                transition: background 0.15s, color 0.15s;
                background: transparent;
                border: none;
            }

            .opt-handle:hover {
                background: rgba(148, 163, 184, 0.15);
                color: rgb(75 85 99);
            }

            .opt-handle:active {
                cursor: grabbing;
            }

            .opt-empty {
                text-align: center;
                padding: 2rem 1rem;
                color: rgb(156 163 175);
                font-size: 0.875rem;
                border: 1px dashed rgba(148, 163, 184, 0.30);
                border-radius: 0.75rem;
            }

            /* Ghost row while dragging */
            .sortable-ghost.opt-row {
                opacity: 0.45;
                background: rgba(99, 102, 241, 0.07) !important;
                border: 1px dashed rgba(99, 102, 241, 0.40) !important;
            }            

            .fb-layout {
                display: grid;
                grid-template-columns: 320px minmax(0, 1fr);
                gap: 1.25rem;
                align-items: start;
            }

            /* .fb-sticky {
                position: sticky;
                top: 1.25rem;
            } */

            .fb-palette,
            .fb-sections {
                display: flex;
                flex-direction: column;
                gap: 0.875rem;
            }

            .fb-palette-item {
                border: 1px solid rgba(148, 163, 184, 0.22);
                border-radius: 0.875rem;
                padding: 0.875rem 1rem;
                cursor: grab;
                background: rgba(255, 255, 255, 0.7);
                transition: all .2s ease;
            }

            .dark .fb-palette-item {
                background: rgba(17, 24, 39, 0.72);
            }

            .fb-palette-item:hover {
                border-color: rgba(59, 130, 246, 0.35);
                transform: translateY(-1px);
            }

            .fb-palette-title,
            .fb-section-title,
            .fb-field-title,
            .fb-empty-title {
                font-weight: 600;
            }

            .fb-palette-meta,
            .fb-section-meta,
            .fb-field-help,
            .fb-help,
            .fb-empty-text {
                font-size: .8125rem;
                color: rgb(107 114 128);
            }

            .fb-divider {
                height: 1px;
                background: rgba(148, 163, 184, 0.18);
                margin: 1rem 0;
            }

            .fb-section {
                border: 1px solid rgba(148, 163, 184, 0.18);
                border-radius: 1rem;
                overflow: hidden;
                background: rgba(255, 255, 255, 0.72);
            }

            .dark .fb-section {
                background: rgba(17, 24, 39, 0.62);
            }

            .fb-section-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 1rem;
                padding: 1rem 1.125rem;
                border-bottom: 1px solid rgba(148, 163, 184, 0.12);
                cursor: pointer;
            }

            .fb-section-left,
            .fb-field {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
            }

            .fb-handle,
            .fb-field-handle {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                color: rgb(107 114 128);
                cursor: move;
            }

            .fb-dropzone {
                min-height: 5rem;
                padding: 1rem;
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
            }

            .fb-field {
                border: 1px solid rgba(148, 163, 184, 0.16);
                border-radius: 0.875rem;
                padding: 0.875rem 1rem;
                background: rgba(255, 255, 255, 0.82);
            }

            .dark .fb-field {
                background: rgba(31, 41, 55, 0.8);
            }

            .fb-field-main {
                min-width: 0;
                flex: 1;
            }

            .fb-field-top,
            .fb-field-actions {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                flex-wrap: wrap;
            }

            .fb-empty,
            .fb-empty-state {
                border: 1px dashed rgba(148, 163, 184, 0.3);
                border-radius: 0.875rem;
                padding: 1rem;
                text-align: center;
                color: rgb(107 114 128);
            }

            .fb-empty-state {
                padding: 2rem 1rem;
            }

            .fb-form-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 1rem;
            }

            .fb-label {
                display: block;
                font-size: .875rem;
                font-weight: 600;
            }

            .fb-textarea {
                width: 100%;
                min-height: 90px;
                border: none;
                outline: none;
                resize: vertical;
                background: transparent;
                padding: 0.75rem 0.875rem;
                font-size: .875rem;
                font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            }

            .sortable-ghost {
                opacity: .45;
            }

            @media (max-width: 1024px) {
                .fb-layout {
                    grid-template-columns: 1fr;
                }

                .fb-sticky {
                    position: static;
                }
            }

            @media (max-width: 768px) {
                .fb-form-grid {
                    grid-template-columns: 1fr;
                }

                .fb-section-header,
                .fb-field {
                    flex-direction: column;
                    align-items: flex-start;
                }

                .fb-field-actions {
                    width: 100%;
                    justify-content: flex-end;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
        <script>
            function initFormBuilder() {
                const palette = document.getElementById('field-palette');

                if (palette && !palette.dataset.sortableLoaded) {
                    new Sortable(palette, {
                        group: { name: 'form-fields', pull: 'clone', put: false },
                        sort: false,
                        animation: 150,
                        ghostClass: 'sortable-ghost',
                    });

                    palette.dataset.sortableLoaded = 'true';
                }

                const sectionsWrapper = document.getElementById('sections-wrapper');

                if (sectionsWrapper && !sectionsWrapper.dataset.sortableLoaded) {
                    new Sortable(sectionsWrapper, {
                        animation: 150,
                        handle: '.handle',
                        ghostClass: 'sortable-ghost',
                        onEnd() {
                            const sections = [...document.querySelectorAll('.section-card')].map((el, i) => ({
                                id: el.dataset.sectionId,
                                sort_order: i + 1,
                            }));

                            @this.reorderSections(sections);
                        },
                    });

                    sectionsWrapper.dataset.sortableLoaded = 'true';
                }

                document.querySelectorAll('.field-dropzone').forEach((zone) => {
                    if (zone.dataset.sortableLoaded) return;

                    new Sortable(zone, {
                        group: { name: 'form-fields', pull: true, put: true },
                        animation: 150,
                        handle: '.fb-field-handle',
                        ghostClass: 'sortable-ghost',
                        onAdd(evt) {
                            const type = evt.item.dataset.type;
                            const label = evt.item.dataset.label || 'New Field';
                            const sectionId = evt.to.dataset.sectionId;

                            if (!type) return;

                            @this.call('addField', parseInt(sectionId), type, label);
                            evt.item.remove();
                        },
                        onEnd() {
                            const fields = [];

                            document.querySelectorAll('.field-dropzone').forEach((dz) => {
                                [...dz.querySelectorAll('.field-card')].forEach((row, i) => {
                                    if (!row.dataset.fieldId) return;

                                    fields.push({
                                        id: row.dataset.fieldId,
                                        form_section_id: dz.dataset.sectionId,
                                        sort_order: i + 1,
                                    });
                                });
                            });

                            if (fields.length) {
                                @this.reorderFields(fields);
                            }
                        },
                    });

                    zone.dataset.sortableLoaded = 'true';
                });
            }

            document.addEventListener('DOMContentLoaded', initFormBuilder);
            document.addEventListener('livewire:navigated', initFormBuilder);
            document.addEventListener('livewire:initialized', initFormBuilder);

            // ── Options Sortable ────────────────────────────────────────────
            function initOptionSortable() {
                const el = document.getElementById('options-sortable');
                if (!el) return;

                // Destroy existing instance before re-attaching
                if (el._sortableInstance) {
                    el._sortableInstance.destroy();
                    el._sortableInstance = null;
                }

                el._sortableInstance = new Sortable(el, {
                    animation: 150,
                    handle: '.option-handle',
                    ghostClass: 'sortable-ghost',
                    onEnd() {
                        // Send current order of data-index values to Livewire
                        @this.reorderOptions(
                            [...el.querySelectorAll('.opt-row')].map(row => Number(row.dataset.index))
                        );
                    },
                });
            }

            // Re-init on page navigation / Livewire boot
            document.addEventListener('livewire:initialized', initOptionSortable);
            document.addEventListener('livewire:navigated', initOptionSortable);

            // KEY FIX: init every time the options modal is opened (DOM exists only then)
            window.addEventListener('open-modal', (e) => {
                if (e.detail?.id === 'field-options-modal') {
                    setTimeout(initOptionSortable, 60);
                }
            });
        </script>
    @endpush
</x-filament-panels::page>