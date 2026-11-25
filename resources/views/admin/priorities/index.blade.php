<x-app-layout>
    <!-- Use layout's content wrapper; avoid extra left margins/gaps next to sidebar -->
    <div class="w-full min-h-screen bg-gray-100 p-8">
        <div class="w-full">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
                <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-8 py-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-sliders-h text-gray-200 text-xl"></i>
                            </div>
                            <div style="margin-left: 1rem;">
                                <h2 class="text-2xl font-bold text-gray-900">Priority SLA Management</h2>
                                <p class="text-purple-100 mt-1">Configure response and resolution times for each
                                    priority
                                    level</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Flash Messages -->
            @if (session('success'))
                <div class="mb-6 flex items-center p-4 rounded-lg bg-green-50 border border-green-200 text-green-800">
                    <i class="fas fa-check-circle mr-3 text-lg"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 flex items-center p-4 rounded-lg bg-red-50 border border-red-200 text-red-800">
                    <i class="fas fa-exclamation-circle mr-3 text-lg"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- Priorities List -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Priority Levels & SLA Times</h3>
                    <p class="text-sm text-gray-600 mt-1">Click "Edit" to modify SLA response and resolution times</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Priority</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Description</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Response Time</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Resolution Time</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Visual</th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($priorities as $priority)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold badge-{{ $priority->color }}">
                                            <i class="fas {{ $priority->icon }} mr-2"></i>
                                            {{ $priority->name }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $priority->description }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <i class="fas fa-clock text-blue-500 mr-2"></i>
                                            <span
                                                class="font-semibold text-gray-900">{{ $priority->sla_response_hours }}</span>
                                            <span
                                                class="text-gray-600 ml-1">{{ $priority->sla_response_hours == 1 ? 'hour' : 'hours' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <i class="fas fa-hourglass-end text-purple-500 mr-2"></i>
                                            <span
                                                class="font-semibold text-gray-900">{{ $priority->sla_resolution_hours }}</span>
                                            <span
                                                class="text-gray-600 ml-1">{{ $priority->sla_resolution_hours == 1 ? 'hour' : 'hours' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-2">
                                            <div class="w-6 h-6 rounded"
                                                style="background-color: var(--{{ $priority->color }})"></div>
                                            <code
                                                class="text-xs bg-gray-100 px-2 py-1 rounded">{{ $priority->icon }}</code>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('admin.priorities.edit', $priority) }}"
                                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                            <i class="fas fa-edit mr-2"></i>
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Info Box -->
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-semibold text-blue-900">About SLA Times</h3>
                        <div class="mt-2 text-sm text-blue-800 space-y-1">
                            <p><strong>Response Time:</strong> How quickly the ticket should receive initial attention
                                from support team</p>
                            <p><strong>Resolution Time:</strong> How quickly the ticket issue should be completely
                                resolved (automatically sets the due date)</p>
                            <p class="mt-3 text-blue-700"><i class="fas fa-lightbulb mr-1"></i> When a ticket is
                                created, the due date is automatically calculated based on the priority's resolution
                                time</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .badge-danger {
            background-color: #dc2626;
            color: white;
        }

        .badge-warning {
            background-color: #f59e0b;
            color: white;
        }

        .badge-info {
            background-color: #3b82f6;
            color: white;
        }

        .badge-success {
            background-color: #10b981;
            color: white;
        }

        .badge-primary {
            background-color: #6366f1;
            color: white;
        }

        .badge-secondary {
            background-color: #6b7280;
            color: white;
        }

        :root {
            --danger: #dc2626;
            --warning: #f59e0b;
            --info: #3b82f6;
            --success: #10b981;
            --primary: #6366f1;
            --secondary: #6b7280;
        }
    </style>
</x-app-layout>
