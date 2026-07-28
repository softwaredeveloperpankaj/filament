<x-filament-panels::page>
    <x-filament::section>

        <x-slot name="heading">
            Exported Forms
        </x-slot>

        <div class="export-files-page">
            @if ($files->isEmpty())
                <x-filament::empty-state
                    heading="No exported files found"
                    description="There are no exported form builder files available right now."
                    icon="heroicon-o-document"
                />
            @else
                <div class="export-files-table-wrap">
                    <table class="export-files-table">
                        <thead>
                            <tr>
                                <th>File Name</th>
                                <th>Size</th>
                                <th>Last Modified</th>
                                <th class="is-right">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($files as $file)
                                <tr>
                                    <td>{{ $file['name'] }}</td>
                                    <td>{{ number_format($file['size'], 2) }} KB</td>
                                    <td>
                                        {{ \Carbon\Carbon::createFromTimestamp($file['last_modified'])->format('d M Y, h:i A') }}
                                    </td>
                                    <td class="is-right">
                                        <x-filament::button
                                            size="sm"
                                            icon="heroicon-o-arrow-down-tray"
                                            wire:click="downloadFile('{{ $file['path'] }}')"
                                        >
                                            Download
                                        </x-filament::button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <style>
            .export-files-page {
                width: 100%;
            }

            .export-files-table-wrap {
                width: 100%;
                overflow-x: auto;
                border: 1px solid #e5e7eb;
                border-radius: 12px;
                background: #ffffff;
            }

            .export-files-table {
                width: 100%;
                border-collapse: collapse;
                min-width: 720px;
            }

            .export-files-table thead tr {
                background: #f9fafb;
            }

            .export-files-table th,
            .export-files-table td {
                padding: 12px 16px;
                font-size: 14px;
                line-height: 1.5;
                text-align: left;
                vertical-align: middle;
            }

            .export-files-table th {
                font-weight: 600;
                color: #111827;
                white-space: nowrap;
            }

            .export-files-table td {
                color: #374151;
            }

            .export-files-table tbody tr {
                border-top: 1px solid #e5e7eb;
                transition: background-color 0.2s ease;
            }

            .export-files-table tbody tr:hover {
                background: #f9fafb;
            }

            .export-files-table .is-right {
                text-align: right;
            }

            .dark .export-files-table-wrap {
                background: #111827;
                border-color: rgba(255, 255, 255, 0.12);
            }

            .dark .export-files-table thead tr {
                background: rgba(255, 255, 255, 0.04);
            }

            .dark .export-files-table th {
                color: #f3f4f6;
            }

            .dark .export-files-table td {
                color: #d1d5db;
            }

            .dark .export-files-table tbody tr {
                border-top: 1px solid rgba(255, 255, 255, 0.10);
            }

            .dark .export-files-table tbody tr:hover {
                background: rgba(255, 255, 255, 0.04);
            }

            @media (max-width: 768px) {
                .export-files-table {
                    min-width: 640px;
                }

                .export-files-table th,
                .export-files-table td {
                    padding: 10px 12px;
                    font-size: 13px;
                }
            }
        </style>
    </x-filament::section>
</x-filament-panels::page>

