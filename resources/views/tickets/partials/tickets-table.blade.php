@if ($tickets->count() > 0)
    <div class="overflow-x-auto">
        <table class="w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Customer /
                        Recipient</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Department
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Priority
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Due Date
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Created
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($tickets as $ticket)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $ticket->request_number }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <a href="{{ route('tickets.show', $ticket) }}"
                                    class="text-blue-600 hover:text-blue-900 font-medium">
                                    {{ $ticket->title }}
                                </a>
                                @if ($ticket->attachments_count > 0)
                                    <svg class="w-4 h-4 ml-2 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                    </svg>
                                @endif
                                @if (
                                    $ticket->due_date &&
                                        $ticket->schedule_date &&
                                        $ticket->schedule_date > $ticket->due_date &&
                                        !in_array($ticket->status->name, ['Completed', 'Closed', 'Cancelled']))
                                    <span
                                        class="ml-2 inline-flex items-center px-2 py-0.5 text-xs font-bold rounded bg-red-100 text-red-800 animate-pulse">
                                        🔥 OVERDUE
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            @if (is_array($ticket->detail_request) && isset($ticket->detail_request['recipient_name']))
                                <div class="flex items-center">
                                    <i class="fas fa-user text-gray-400 mr-2"></i>
                                    <span>{{ $ticket->detail_request['recipient_name'] }}</span>
                                </div>
                            @else
                                <span class="text-gray-400 text-xs">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $ticket->department->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                {{ $ticket->priority->name === 'Urgent' ? 'bg-red-100 text-red-800' : ($ticket->priority->name === 'High' ? 'bg-orange-100 text-orange-800' : ($ticket->priority->name === 'Medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')) }}">
                                {{ $ticket->priority->name }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                {{ $ticket->status->name === 'Open' ? 'bg-blue-100 text-blue-800' : ($ticket->status->name === 'In Progress' ? 'bg-yellow-100 text-yellow-800' : ($ticket->status->name === 'Completed' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800')) }}">
                                {{ $ticket->status->name }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if (in_array($ticket->status->name, ['Completed', 'Closed', 'Cancelled']))
                                <span class="text-success text-xs d-flex align-items-center">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <span>Ticket finished</span>
                                </span>
                            @elseif ($ticket->due_date)
                                @php
                                    $isOverdue = $ticket->schedule_date && $ticket->schedule_date > $ticket->due_date;
                                    $hoursUntilDue = now()->diffInHours($ticket->due_date, false);
                                    $isDueSoon = !$isOverdue && $hoursUntilDue > 0 && $hoursUntilDue <= 24;
                                @endphp
                                <div class="flex flex-col">
                                    <span
                                        class="font-medium {{ $isOverdue ? 'text-red-600' : ($isDueSoon ? 'text-orange-600' : 'text-gray-900') }}">
                                        {{ $ticket->due_date->format('M d, Y') }}
                                    </span>
                                    <span
                                        class="text-xs {{ $isOverdue ? 'text-red-500 font-semibold' : ($isDueSoon ? 'text-orange-500' : 'text-gray-500') }}">
                                        @if ($isOverdue)
                                            Scheduled beyond Deadline! 🔥
                                        @elseif ($isDueSoon)
                                            @php
                                                $hours = floor($hoursUntilDue);
                                                $minutes = round(($hoursUntilDue - $hours) * 60);
                                            @endphp
                                            Due in {{ $hours }}h {{ $minutes }}m ⏰
                                        @else
                                            {{ $ticket->due_date->diffForHumans() }}
                                        @endif
                                    </span>
                                </div>
                            @else
                                <span class="text-gray-400 text-xs">Not set</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $ticket->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('tickets.show', $ticket) }}"
                                    class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-md transition-colors duration-200">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    View
                                </a>
                                @php
                                    $statusName = $ticket->status->name ?? 'Open';
                                    $isEditable = $statusName === 'Open';
                                @endphp
                                @if ($isEditable)
                                    <a href="{{ route('tickets.edit', $ticket) }}"
                                        class="inline-flex items-center px-3 py-1.5 text-white text-xs font-medium rounded-md transition-colors duration-200"
                                        style="background-color: #f97316;">
                                        <i class="fas fa-edit mr-1"></i>
                                        Edit
                                    </a>
                                @endif
                                <form action="{{ route('tickets.duplicate', $ticket) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                        class="inline-flex items-center px-3 py-1.5 text-white text-xs font-medium rounded-md transition-colors duration-200"
                                        style="background-color: #6366f1;">
                                        <i class="fas fa-copy mr-1"></i>
                                        Duplicate
                                    </button>
                                </form>
                                @if (
                                    !in_array($statusName, [
                                        'Completed',
                                        'Closed',
                                        'Cancelled by PIC',
                                        'Cancelled by User',
                                        'Pending Cancellation',
                                        'Pending Approval',
                                    ]))
                                    <button type="button" onclick="openCancelModal({{ $ticket->id }})"
                                        class="inline-flex items-center px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-medium rounded-md transition-colors duration-200">
                                        <i class="fas fa-ban mr-1"></i>
                                        Cancel
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $tickets->withQueryString()->links() }}
    </div>
@else
    <div class="px-6 py-12 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No tickets found</h3>
        <p class="mt-1 text-sm text-gray-600">Try adjusting your search or filters.</p>
    </div>
@endif
