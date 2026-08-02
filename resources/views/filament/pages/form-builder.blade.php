<x-filament-panels::page>

    @if ($this->template->status === 'published')
        <x-filament::callout icon="heroicon-o-check-circle" color="success">
            <x-slot name="heading"> {{ __('Published') }} </x-slot>
            <x-slot name="description"> {{ $this->template->name }} </x-slot>
        </x-filament::callout>
    @else
        <x-filament::callout icon="heroicon-o-exclamation-circle" color="warning">
            <x-slot name="heading"> {{ __('Draft') }} </x-slot>
            <x-slot name="description"> {{ $this->template->name }} </x-slot>
        </x-filament::callout>
    @endif  

    <div class="grid grid-cols-1 lg:grid-cols-[320px_minmax(0,1fr)] gap-5 items-start">
        <div class="lg:sticky lg:top-4">
            <x-filament::section>
                <x-slot name="heading">{{ __('Field Palette') }}</x-slot>

                <x-slot name="description">
                    {{ __('Drag a field into any section.') }}
                </x-slot>

                <div class="space-y-3">
                    <x-filament::button
                        icon="heroicon-o-plus"
                        class="w-full"
                        x-on:click="$dispatch('open-modal', { id: 'add-section-modal' })"
                    >
                        {{ __('Add Section') }}
                    </x-filament::button>

                    <x-filament::modal id="add-section-modal" width="md">
                        <x-slot name="heading">{{ __('Add Section') }}</x-slot>

                        <div class="space-y-4">
                            <x-filament::input.wrapper>
                                <x-filament::input
                                    wire:model.live="newSectionTitle"
                                    type="text"
                                    placeholder="{{ __('e.g. Student Information') }}"
                                />
                            </x-filament::input.wrapper>
                        </div>

                        <x-slot name="footerActions">
                            <x-filament::button
                                color="gray"
                                x-on:click="$dispatch('close-modal', { id: 'add-section-modal' })"
                            >
                                {{ __('Cancel') }}
                            </x-filament::button>

                            <x-filament::button wire:click="createSection">
                                {{ __('Create Section') }}
                            </x-filament::button>
                        </x-slot>
                    </x-filament::modal>
                </div>

                <div class="my-4 h-px bg-gray-200 dark:bg-gray-700"></div>

                <div id="field-palette" class="flex flex-col gap-3">
                    @foreach ([
                        'text' => __('Text'),
                        'email' => __('Email'),
                        'number' => __('Number'),
                        'date' => __('Date'),
                        'textarea' => __('Textarea'),
                        'select' => __('Select'),
                        'radio' => __('Radio'),
                        'checkbox' => __('Checkbox'),
                        'file' => __('File Upload'),
                    ] as $type => $label)
                        <div
                            class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white/80 dark:bg-gray-900/70 p-4 cursor-grab hover:border-primary-400 hover:-translate-y-0.5 transition-all duration-200"
                            data-type="{{ $type }}"
                            data-label="{{ $label }}"
                        >
                            <div class="font-semibold text-sm text-gray-900 dark:text-white">{{ $label }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $type }}</div>
                        </div>
                    @endforeach
                </div>
            </x-filament::section>
        </div>

        <div class="fb-canvas">
            <x-filament::section>
                <x-slot name="heading">
                    {{ __('Builder Canvas') }} — {{ $this->template->branch->name ?? '' }}
                </x-slot>

                <x-slot name="description">
                    {{ __('Drag sections to reorder. Drag fields from palette or between sections.') }}
                </x-slot>

                <div id="sections-wrapper" class="flex flex-col gap-4">
                    @forelse ($this->getBuilderSections() as $section)
                        <div
                            wire:key="section-{{ $section->id }}"
                            class="section-card rounded-2xl border border-gray-200 dark:border-gray-700 bg-white/80 dark:bg-gray-900/70 overflow-hidden"
                            data-section-id="{{ $section->id }}"
                            x-data="{ open: {{ $loop->first ? 'true' : 'false' }} }"
                        >
                            <div class="flex items-center justify-between gap-4 px-5 py-4 border-b border-gray-200 dark:border-gray-700 cursor-pointer" @click="open = !open">
                                <div class="flex items-center gap-3 min-w-0">
                                    <button
                                        type="button"
                                        class="fb-handle text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 cursor-grab touch-none" @click.stop>
                                        <x-filament::icon icon="heroicon-o-bars-3" class="h-5 w-5" />
                                    </button>

                                    <div>
                                        <div class="font-semibold text-sm text-gray-900 dark:text-white truncate">{{ $section->title }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                            {{ trans_choice('{1} :count field|[2,*] :count fields', $section->fields->count(), ['count' => $section->fields->count()]) }}
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 flex-wrap" @click.stop>
                                    <x-filament::icon-button
                                        icon="heroicon-o-pencil-square"
                                        color="warning"
                                        size="sm"
                                        wire:click="openEditSection({{ $section->id }})"
                                        tooltip="{{ __('Edit Section') }}"
                                    />

                                    <x-filament::icon-button
                                        icon="heroicon-o-trash"
                                        color="danger"
                                        size="sm"
                                        wire:click="confirmDeleteSection({{ $section->id }})"
                                        tooltip="{{ __('Delete Section') }}"
                                    />

                                    <x-filament::icon
                                        icon="heroicon-m-chevron-down"
                                        class="h-4 w-4 transition-transform duration-200 pointer-events-none"
                                        {{-- x-bind:style="`transform: rotate(${open ? 180 : 0}deg); transition: transform .2s ease;`" --}}
                                        x-bind:class="open ? 'rotate-180' : ''"
                                    />
                                </div>
                            </div>

                            <div x-show="open" x-collapse>
                                <div
                                    class="field-dropzone min-h-24 p-4 flex flex-col gap-3"
                                    data-section-id="{{ $section->id }}"
                                >
                                    @forelse ($section->fields as $field)
                                        <div
                                            wire:key="field-{{ $field->id }}"
                                            class="field-card flex flex-col md:flex-row md:items-center md:justify-between gap-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 select-none"
                                            data-field-id="{{ $field->id }}"
                                        >
                                            <button type="button" class="fb-field-handle text-gray-500 cursor-grab mt-0.5">
                                                <x-filament::icon icon="heroicon-o-bars-3" class="h-5 w-5" />
                                            </button>

                                            <div class="flex-1 min-w-0">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <div class="font-medium text-sm text-gray-900 dark:text-white">{{ $field->label }}</div>

                                                    <x-filament::badge color="gray" size="sm">
                                                        {{ $field->type }}
                                                    </x-filament::badge>

                                                    @if ($field->is_required)
                                                        <x-filament::badge color="danger" size="sm">
                                                            {{ __('Required') }}
                                                        </x-filament::badge>
                                                    @endif
                                                </div>

                                                @if ($field->help_text)
                                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                        {{ $field->help_text }}
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="flex items-center gap-2 flex-wrap">
                                                @if (in_array($field->type, ['select', 'radio', 'checkbox']))
                                                    <x-filament::icon-button
                                                        icon="heroicon-o-list-bullet"
                                                        color="gray"
                                                        size="sm"
                                                        tooltip="{{ __('Manage Options') }}"
                                                        wire:click="openOptionsModal({{ $field->id }})"
                                                    />
                                                @endif

                                                <x-filament::icon-button
                                                    icon="heroicon-o-pencil-square"
                                                    color="warning"
                                                    size="sm"
                                                    wire:click="openEditField({{ $field->id }})"
                                                    tooltip="{{ __('Edit Field') }}"
                                                />

                                                <x-filament::icon-button
                                                    icon="heroicon-o-trash"
                                                    color="danger"
                                                    size="sm"
                                                    wire:click="confirmDeleteField({{ $field->id }})"
                                                    tooltip="{{ __('Delete Field') }}"
                                                />
                                            </div>
                                        </div>
                                    @empty
                                        <div class="rounded-xl border border-dashed border-gray-300 dark:border-gray-600 p-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                            {{ __('No fields yet. Drag from the palette.') }}
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @empty
                        <x-filament::empty-state
                            icon="heroicon-o-clock"
                            icon-color="info"
                        >
                            <x-slot name="heading">
                                {{ __('No sections created yet.') }}
                            </x-slot>

                            <x-slot name="description">
                                {{ __('Add a section from the left panel to start building the form.') }}
                            </x-slot>
                        </x-filament::empty-state>
                    @endforelse
                </div>
            </x-filament::section>
        </div>
    </div>

    <x-filament::modal
        id="delete-field-modal"
        icon="heroicon-o-exclamation-triangle"
        icon-color="danger"
    >
        <x-slot name="heading">
            {{ __('Delete Field') }}
        </x-slot>

        <x-slot name="description">
            {{ __('Are you sure you want to delete this field?') }}
        </x-slot>

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

    <x-filament::modal id="delete-section-modal">
        <x-slot name="heading">
            {{ __('Delete Section') }}
        </x-slot>

        {{ __('Are you sure you want to delete this section and all its fields?') }}

        <x-slot name="footerActions">
            <x-filament::button
                color="gray"
                x-on:click="$dispatch('close-modal', { id: 'delete-section-modal' })"
            >
                {{ __('Cancel') }}
            </x-filament::button>

            <x-filament::button
                color="danger"
                wire:click="deleteSection"
            >
                {{ __('Delete') }}
            </x-filament::button>
        </x-slot>
    </x-filament::modal>

    <x-filament::modal id="edit-section-modal" width="md">
        <x-slot name="heading">
            {{ __('Edit Section') }}
        </x-slot>

        <div class="space-y-4">
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">{{ __('Section Title') }}</label>

                <x-filament::input.wrapper>
                    <x-filament::input
                        wire:model="editingSectionTitle"
                        type="text"
                        placeholder="{{ __('e.g. Student Information') }}"
                    />
                </x-filament::input.wrapper>
            </div>
        </div>

        <x-slot name="footerActions">
            <x-filament::button
                color="gray"
                x-on:click="$dispatch('close-modal', { id: 'edit-section-modal' })"
            >
                {{ __('Cancel') }}
            </x-filament::button>

            <x-filament::button wire:click="saveEditSection">
                {{ __('Save Changes') }}
            </x-filament::button>
        </x-slot>
    </x-filament::modal>

    <x-filament::modal id="edit-field-modal" width="3xl">
        <x-slot name="heading">
            {{ __('Edit Field') }}
        </x-slot>

        <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-filament::fieldset>
                    <x-slot name="label"> {{ __('Label') }} </x-slot>
                    <x-filament::input.wrapper>
                        <x-filament::input wire:model="editingFieldData.label" type="text" />
                    </x-filament::input.wrapper>

                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('Appears above the field.') }}</p>
                </x-filament::fieldset>                

                <x-filament::fieldset>
                    <x-slot name="label">{{ __('Field Key (Input Name)') }}</x-slot>

                    <x-filament::input.wrapper>
                        <x-filament::input wire:model="editingFieldData.field_key" type="text" />
                    </x-filament::input.wrapper>

                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('Unique identifier for this field.') }}</p>
                </x-filament::fieldset>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-filament::fieldset>
                    <x-slot name="label">{{ __('Required') }}</x-slot>
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model="editingFieldData.is_required">
                            <option value="0">{{ __('No') }}</option>
                            <option value="1">{{ __('Yes') }}</option>
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </x-filament::fieldset>

                @if (!empty($editingFieldData['option_layout']))
                <x-filament::fieldset>
                    <x-slot name="label">{{ __('Option Layout') }}</x-slot>
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model="editingFieldData.option_layout">
                            <option value="horizontal">{{ __('Horizontal') }}</option>
                            <option value="vertical">{{ __('Vertical') }}</option>
                        </x-filament::input.select>
                    </x-filament::input.wrapper>

                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('For radio/checkbox fields only.') }}</p>
                </x-filament::fieldset>                
                @endif
            </div>
            <x-filament::fieldset>
                <x-slot name="label">{{ __('Placeholder') }}</x-slot>

                <x-filament::input.wrapper>
                    <x-filament::input
                        wire:model="editingFieldData.placeholder"
                        type="text"
                        placeholder="{{ __('e.g. Enter your full name') }}"
                    />
                </x-filament::input.wrapper>
            </x-filament::fieldset>

            <x-filament::fieldset>
                <x-slot name="label">{{ __('Validation Rules') }}</x-slot>
                <x-filament::input.wrapper>
                    <x-filament::input
                        wire:model="editingFieldData.validation_rules_input"
                        type="text"
                        placeholder="required|string|max:255"
                    />
                </x-filament::input.wrapper>

                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('Pipe-delimited rules, e.g. required|string|max:255') }}</p>
            </x-filament::fieldset>

            <x-filament::fieldset>
                <x-slot name="label">{{ __('Help Text') }}</x-slot>

                <x-filament::input.wrapper>
                    <x-filament::input wire:model="editingFieldData.help_text" type="text" />
                </x-filament::input.wrapper>

                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('Appears below the field.') }}</p>
            </x-filament::fieldset>            

            <x-filament::fieldset>
                <x-slot name="label">
                    {{ __('Visibility JSON') }}
                </x-slot>

                <x-filament::input.wrapper>
                    <textarea
                        wire:model="editingFieldData.visibility_conditions_input"
                        rows="3"
                        class="w-full min-h-[90px] resize-y border-0 bg-transparent px-3 py-2 text-sm font-mono focus:ring-0"
                        placeholder='{"field":"transport_required","equals":"yes"}'
                    ></textarea>
                </x-filament::input.wrapper>

            </x-filament::fieldset>

            <x-filament::fieldset>
                <x-slot name="label">
                    {{ __('Settings JSON') }}
                </x-slot>

                <x-filament::input.wrapper>
                    <textarea
                        wire:model="editingFieldData.settings_input"
                        rows="5"
                        class="w-full min-h-[90px] resize-y border-0 bg-transparent px-3 py-2 text-sm font-mono focus:ring-0"
                        placeholder='{"column_span": "full","accept": ["pdf", "jpg"]}'
                    >
                    </textarea>
                </x-filament::input.wrapper>

            </x-filament::fieldset>            

            <x-filament::fieldset>
                <x-slot name="label">
                    {{ __('Available Settings JSON') }}
                </x-slot>

                <x-filament::section compact collapsible collapsed>
                    <x-slot name="heading">
                        {{ __('Settings JSON') }}
                    </x-slot>

                        <pre>
                        //Common settings for all field types
                        {
                        "default": "mixed $state",
                        "column_span": "array|int|string"
                        }

                        //For file upload
                        {
                        "accept": ["pdf", "jpg"],
                        "directory": "string",
                        "disk": "string",
                        "visibility": "string",
                        "max_size": "int",
                        "multiple": "bool"
                        },

                        //Text & Email field
                        {
                        "min_length": "int",
                        "max_length": "int"
                        }

                        //Number
                        {
                        "min_length": "",
                        "max_length": "",
                        "step": "int|float|string"
                        }

                        //Textarea
                        {
                        "rows": "int"
                        }

                        //Select
                        {
                        "searchable": "bool|array",
                        "multiple":"bool"
                        }

                        //Checkbox
                        {
                        "accepted": "bool"
                        }
                        </pre>
                </x-filament::section>

            </x-filament::fieldset>

        </div>

        <x-slot name="footerActions">
            <x-filament::button
                color="gray"
                x-on:click="$dispatch('close-modal', { id: 'edit-field-modal' })"
            >
                {{ __('Cancel') }}
            </x-filament::button>

            <x-filament::button wire:click="saveEditField">
                {{ __('Save Changes') }}
            </x-filament::button>
        </x-slot>
    </x-filament::modal>

    <x-filament::modal id="field-options-modal" width="4xl">
        <x-slot name="heading">
            {{ __('Manage Options') }}
        </x-slot>

        <x-slot name="description">
            {{ __('Add, edit and drag to reorder options. Changes are saved when you click Save.') }}
        </x-slot>

        <div class="space-y-3">
            <div class="grid grid-cols-[2rem_1fr_1fr_4rem_2.5rem] items-center gap-3 px-2 pb-2 border-b border-gray-200 dark:border-gray-700">
                <span></span>
                <span class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Label') }}</span>
                <span class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Value') }}</span>
                <span class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 text-center">{{ __('Default') }}</span>
                <span></span>
            </div>

            <div id="options-sortable" class="space-y-2">
                @forelse($fieldOptions as $index => $option)
                    <div
                        class="opt-row grid grid-cols-[2rem_1fr_1fr_4rem_2.5rem] items-center gap-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-3 transition-all duration-150 hover:border-gray-300 dark:hover:border-gray-600"
                        wire:key="option-{{ $index }}"
                        data-index="{{ $index }}"
                    >
                        <button
                            type="button"
                            class="option-handle inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-600 dark:hover:text-gray-300 cursor-grab"
                            title="{{ __('Drag to reorder') }}"
                        >
                            <x-filament::icon icon="heroicon-o-bars-3" class="h-4 w-4" />
                        </button>

                        <div>
                            <x-filament::input.wrapper>
                                <x-filament::input
                                    wire:model="fieldOptions.{{ $index }}.label"
                                    placeholder="{{ __('e.g. Option') }}"
                                />
                            </x-filament::input.wrapper>
                        </div>

                        <div>
                            <x-filament::input.wrapper>
                                <x-filament::input
                                    wire:model="fieldOptions.{{ $index }}.value"
                                    placeholder="{{ __('e.g. option_value') }}"
                                />
                            </x-filament::input.wrapper>
                        </div>

                        <div class="flex items-center justify-center">
                            <x-filament::input.checkbox
                                wire:model="fieldOptions.{{ $index }}.is_default"
                            />
                        </div>

                        <div class="flex items-center justify-center">
                            <x-filament::icon-button
                                icon="heroicon-o-trash"
                                color="danger"
                                size="sm"
                                wire:click="removeOption({{ $index }})"
                                tooltip="{{ __('Remove') }}"
                            />
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-gray-300 dark:border-gray-600 p-8 text-center text-sm text-gray-500 dark:text-gray-400">
                        <x-filament::icon icon="heroicon-o-list-bullet" class="h-8 w-8 mx-auto mb-2 opacity-30" />
                        <p>{!! __('No options yet. Click <strong>Add Option</strong> to get started.') !!}</p>
                    </div>
                @endforelse
            </div>

            <div class="pt-1">
                <x-filament::button
                    icon="heroicon-o-plus"
                    color="gray"
                    wire:click="addOption"
                    class="w-full"
                >
                    {{ __('Add Option') }}
                </x-filament::button>
            </div>
        </div>

        <x-slot name="footerActions">
            <x-filament::button
                color="gray"
                x-on:click="$dispatch('close-modal',{id:'field-options-modal'})"
            >
                {{ __('Cancel') }}
            </x-filament::button>

            <x-filament::button wire:click="saveOptions" color="primary">
                {{ __('Save Options') }}
            </x-filament::button>
        </x-slot>
    </x-filament::modal>

    <x-filament::modal id="versions-modal" width="4xl">
        <x-slot name="heading">
            {{ __('Template Versions') }}
        </x-slot>

        <x-slot name="description">
            {{ __('Only versions for') }} <strong>{{ $this->template->name }}</strong> {{ __('are shown.') }}
            {{ __('Toggle') }} <em>{{ __('Active') }}</em> {{ __('to set which version is currently live.') }}
        </x-slot>

        <div>
            @if (count($templateVersions) === 0)
                <x-filament::empty-state icon="heroicon-o-clock">
                    <x-slot name="heading">
                        {{ __('No versions published yet.') }}
                    </x-slot>

                    <x-slot name="description">
                        {{ __('Click') }} <strong>{{ __('Publish Version') }}</strong> {{ __('in the header to create one.') }}
                    </x-slot>
                </x-filament::empty-state>
            @else
                <div class="grid grid-cols-[2.5rem_1fr_1fr_5rem] items-center gap-3 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                    <span>#</span>
                    <span>{{ __('Version') }}</span>
                    <span>{{ __('Published At') }}</span>
                    <span class="text-center">{{ __('Active') }}</span>
                </div>

                <div class="mt-2 space-y-2">
                    @foreach($templateVersions as $i => $ver)
                        <div class="grid grid-cols-[2.5rem_1fr_1fr_5rem] items-center gap-3 rounded-xl border p-3 transition-all duration-150 {{ $ver['is_active'] ? 'border-success-300 bg-success-50 dark:border-success-700 dark:bg-success-950/20' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800' }}">
                            <span class="text-center text-xs font-semibold text-gray-400 dark:text-gray-500">{{ $i + 1 }}</span>

                            <span>
                                <x-filament::badge color="{{ $ver['is_active'] ? 'success' : 'gray' }}">
                                    v{{ $ver['version'] }}
                                </x-filament::badge>
                            </span>

                            <span class="text-sm text-gray-600 dark:text-gray-300">
                                {{ $ver['published_at'] }}
                            </span>

                            <div class="flex justify-center">
                                <button
                                    type="button"
                                    wire:click="toggleVersionActive({{ $ver['id'] }})"
                                    wire:loading.attr="disabled"
                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 {{ $ver['is_active'] ? 'bg-success-500' : 'bg-gray-300 dark:bg-gray-600' }}"
                                    title="{{ $ver['is_active'] ? __('Deactivate') : __('Activate') }}"
                                >
                                    <span class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition duration-200 {{ $ver['is_active'] ? 'translate-x-5' : 'translate-x-1' }}"></span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <x-slot name="footerActions">
            <x-filament::button
                color="gray"
                x-on:click="$dispatch('close-modal', { id: 'versions-modal' })"
            >
                {{ __('Close') }}
            </x-filament::button>
        </x-slot>
    </x-filament::modal>

    @push('styles')
    <style>
        .sortable-ghost {
            opacity: 0.5;
        }
    </style>
    @endpush

    @push('scripts')
        @assets
            <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js" defer></script>
        @endassets

        @script
        <script>
            let paletteSortable = null
            let sectionsSortable = null
            let optionSortable = null
            let dropzoneSortables = new Map()

            const getSectionPayload = (sectionsWrapper) => {
                return [...sectionsWrapper.querySelectorAll('.section-card')].map((el, index) => ({
                    id: Number(el.dataset.sectionId),
                    sort_order: index + 1,
                }))
            }

            const getFieldPayload = () => {
                const fields = []

                document.querySelectorAll('.field-dropzone').forEach((dropzone) => {
                    ;[...dropzone.querySelectorAll('.field-card')].forEach((row, index) => {
                        if (!row.dataset.fieldId) return

                        fields.push({
                            id: Number(row.dataset.fieldId),
                            form_section_id: Number(dropzone.dataset.sectionId),
                            sort_order: index + 1,
                        })
                    })
                })

                return fields
            }

            const syncFieldsOrder = () => {
                const fields = getFieldPayload()

                if (fields.length) {
                    $wire.reorderFields(fields)
                }
            }

            const destroyDropzones = () => {
                dropzoneSortables.forEach(instance => instance.destroy())
                dropzoneSortables.clear()
            }

            const initPalette = () => {
                const palette = document.getElementById('field-palette')

                if (!palette) return

                if (paletteSortable) {
                    paletteSortable.destroy()
                    paletteSortable = null
                }

                paletteSortable = new Sortable(palette, {
                    group: { name: 'form-fields', pull: 'clone', put: false },
                    sort: false,
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    fallbackOnBody: true,
                })
            }

            const initSections = () => {
                const sectionsWrapper = document.getElementById('sections-wrapper')

                if (!sectionsWrapper) return

                if (sectionsSortable) {
                    sectionsSortable.destroy()
                    sectionsSortable = null
                }

                sectionsSortable = new Sortable(sectionsWrapper, {
                    animation: 150,
                    handle: '.fb-handle',
                    draggable: '.section-card',
                    ghostClass: 'sortable-ghost',
                    fallbackOnBody: true,
                    onEnd() {
                        $wire.reorderSections(getSectionPayload(sectionsWrapper))
                    },
                })
            }

            const initDropzones = () => {
                destroyDropzones()

                document.querySelectorAll('.field-dropzone').forEach((zone) => {
                    const sectionId = Number(zone.dataset.sectionId)

                    const sortable = new Sortable(zone, {
                        group: {
                            name: 'form-fields',
                            pull: true,
                            put: true,
                        },
                        animation: 150,
                        draggable: '.field-card, .fb-palette-item',
                        handle: '.fb-field-handle',
                        ghostClass: 'sortable-ghost',
                        fallbackOnBody: true,

                        onAdd(evt) {
                            const targetSectionId = Number(evt.to.dataset.sectionId)
                            const isPaletteClone = evt.item.classList.contains('fb-palette-item')

                            if (!targetSectionId) {
                                evt.item.remove()
                                return
                            }

                            if (isPaletteClone) {
                                const type = evt.item.dataset.type
                                const label = evt.item.dataset.label || '{{ __('New Field') }}'
                                evt.item.remove()

                                if (!type) return
                                if(type === 'file') {
                                    const settings = {
                                        accept : ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
                                    }
                                    $wire.call('addField', targetSectionId, type, label, settings)
                                } else {
                                    $wire.call('addField', targetSectionId, type, label)
                                }
                                return
                            }

                            syncFieldsOrder()
                        },

                        onEnd(evt) {
                            if (evt.item.classList.contains('fb-palette-item')) return
                            syncFieldsOrder()
                        },
                    })

                    dropzoneSortables.set(sectionId, sortable)
                })
            }

            const initOptionsSortable = () => {
                const el = document.getElementById('options-sortable')

                if (!el) return

                if (optionSortable) {
                    optionSortable.destroy()
                    optionSortable = null
                }

                optionSortable = new Sortable(el, {
                    animation: 150,
                    handle: '.option-handle',
                    ghostClass: 'sortable-ghost',
                    onEnd() {
                        $wire.reorderOptions(
                            [...el.querySelectorAll('.opt-row')].map(row => Number(row.dataset.index))
                        )
                    },
                })
            }

            const initBuilder = () => {
                initPalette()
                initSections()
                initDropzones()
            }

            initBuilder()

            $wire.interceptMessage(({ onSuccess }) => {
                onSuccess(({ onRender }) => {
                    onRender(() => {
                        initBuilder()
                    })
                })
            })

            window.addEventListener('open-modal', (e) => {
                if (e.detail?.id === 'field-options-modal') {
                    requestAnimationFrame(() => {
                        initOptionsSortable()
                    })
                }
            })
        </script>
        @endscript
    @endpush
</x-filament-panels::page>