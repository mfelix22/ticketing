<x-app-layout>
    <div class="content-wrapper md:ml-[250px] ml-0 mt-14 bg-gray-100 min-h-screen p-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
                <div class="py-6 border-b border-gray-200" style="padding-left: 3rem; padding-right: 2rem;">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">All Notifications</h2>
                            <p class="mt-1 text-gray-600">Stay updated with your ticket
                                activities</p>
                        </div>
                        <div class="flex items-center space-x-3">
                            <form method="GET" class="flex items-center space-x-2">
                                <label for="filter" class="text-sm text-gray-600">Filter</label>
                                <select id="filter" name="filter"
                                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="this.form.submit()">
                                    <option value="all" {{ ($filter ?? 'all') === 'all' ? 'selected' : '' }}>All
                                    </option>
                                    <option value="new-ticket"
                                        {{ ($filter ?? 'all') === 'new-ticket' ? 'selected' : '' }}>New Ticket</option>
                                    <option value="new-comment"
                                        {{ ($filter ?? 'all') === 'new-comment' ? 'selected' : '' }}>New Comment
                                    </option>
                                    <option value="cancel-request"
                                        {{ ($filter ?? 'all') === 'cancel-request' ? 'selected' : '' }}>Request to
                                        Cancel</option>
                                </select>
                            </form>
                            @if (Auth::user()->unreadNotifications->count() > 0)
                                <form action="{{ route('notifications.mark-all-read') }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                        <i class="fas fa-check-double mr-2"></i>
                                        Mark All Read
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Flash Messages -->
            @if (session('success'))
                <div class="mb-6 flex items-center p-4 rounded-lg bg-green-50 border border-green-200 text-green-800">
                    <i class="fas fa-check-circle mr-3"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Notifications List -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                @forelse($notifications as $notification)
                    @php
                        $type = $notification->type;
                        $category = match ($type) {
                            \App\Notifications\NewTicketCreated::class => 'new-ticket',
                            \App\Notifications\NewTicketComment::class => 'new-comment',
                            \App\Notifications\RequestCancellationRequested::class => 'cancel-request',
                            default => 'other',
                        };
                        $badgeLabel =
                            [
                                'new-ticket' => 'New Ticket',
                                'new-comment' => 'New Comment',
                                'cancel-request' => 'Request to Cancel',
                            ][$category] ?? null;
                        $badgeClass = match ($category) {
                            'new-ticket' => 'bg-blue-100 text-blue-800',
                            'new-comment' => 'bg-green-100 text-green-800',
                            'cancel-request' => 'bg-amber-100 text-amber-800',
                            default => 'bg-gray-100 text-gray-600',
                        };
                    @endphp
                    <a href="{{ $notification->data['url'] ?? '#' }}?notification={{ $notification->id }}"
                        class="block px-6 py-4 hover:bg-gray-50 transition-colors border-b border-gray-200 last:border-b-0 {{ $notification->read_at ? '' : 'bg-blue-50' }}">
                        <div class="flex items-start">
                            <!-- Icon -->
                            <div class="flex-shrink-0 mr-4">
                                @if ($category === 'cancel-request')
                                    <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-ban text-amber-600 text-lg"></i>
                                    </div>
                                @elseif(isset($notification->data['hours_overdue']))
                                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-exclamation-triangle text-red-600 text-lg"></i>
                                    </div>
                                @elseif(isset($notification->data['status']))
                                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-sync-alt text-blue-600 text-lg"></i>
                                    </div>
                                @elseif(str_contains($notification->type, 'Comment'))
                                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-comment text-green-600 text-lg"></i>
                                    </div>
                                @else
                                    <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-bell text-gray-600 text-lg"></i>
                                    </div>
                                @endif
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2">
                                            <p class="text-sm font-semibold text-gray-900" style="color: #111827;">
                                                {{ $notification->data['message'] ?? 'New notification' }}
                                            </p>
                                            @if ($badgeLabel)
                                                <span
                                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">
                                                    {{ $badgeLabel }}
                                                </span>
                                            @endif
                                        </div>

                                        @if (isset($notification->data['title']))
                                            <p class="text-sm mt-1" style="color: #374151;">
                                                <strong style="color: #111827;">Ticket:</strong>
                                                {{ $notification->data['title'] }}
                                            </p>
                                        @endif

                                        @if (isset($notification->data['hours_overdue']))
                                            <div class="mt-2 flex items-center space-x-4 text-xs">
                                                <span
                                                    class="inline-flex items-center px-2 py-1 bg-red-100 text-red-800 rounded">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    {{ $notification->data['hours_overdue'] }} hours overdue
                                                </span>
                                                @if (isset($notification->data['priority']))
                                                    <span
                                                        class="inline-flex items-center px-2 py-1 bg-gray-100 text-gray-800 rounded">
                                                        <i class="fas fa-flag mr-1"></i>
                                                        Priority: {{ $notification->data['priority'] }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif

                                        <p class="text-xs mt-2" style="color: #4B5563;">
                                            <i class="fas fa-clock mr-1"></i>
                                            {{ $notification->created_at->diffForHumans() }}
                                            @if ($notification->read_at)
                                                <span class="ml-2 text-green-600">
                                                    <i class="fas fa-check"></i> Read
                                                </span>
                                            @endif
                                        </p>
                                    </div>

                                    @if (!$notification->read_at)
                                        <div class="flex-shrink-0 ml-4">
                                            <div class="w-3 h-3 bg-blue-600 rounded-full"></div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="px-6 py-12 text-center">
                        <i class="fas fa-bell-slash text-6xl text-gray-300 mb-4"></i>
                        <p class="text-lg text-gray-600 font-medium">No notifications yet</p>
                        <p class="text-sm text-gray-500 mt-2">When you have notifications, they'll appear here</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if ($notifications->hasPages())
                <div class="mt-6">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
