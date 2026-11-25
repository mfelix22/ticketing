<x-app-layout>
    <!-- Use layout's content wrapper; avoid extra left margins/gaps next to sidebar -->
    <div class="w-full bg-gray-100 min-h-screen p-8">
        <div class="w-full">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
                <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-8 py-6">
                    <div class="flex items-center">
                        <a href="{{ route('admin.priorities.index') }}" class="mr-4 text-white hover:text-purple-100">
                            <i class="fas fa-arrow-left text-xl"></i>
                        </a>
                        <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-edit text-white text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-white">Edit Priority: {{ $priority->name }}</h2>
                            <p class="text-purple-100 mt-1">Modify SLA times and settings</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <form action="{{ route('admin.priorities.update', $priority) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="p-8 space-y-6">
                        <!-- Flash Messages -->
                        @if ($errors->any())
                            <div class="p-4 rounded-lg bg-red-50 border border-red-200 text-red-800">
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-exclamation-circle mr-2"></i>
                                    <strong>There were some problems with your input:</strong>
                                </div>
                                <ul class="list-disc ml-6 text-sm">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Priority Name -->
                        <div>
                            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                                Priority Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="name"
                                value="{{ old('name', $priority->name) }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                required>
                            @error('name')
                                <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                                Description <span class="text-red-500">*</span>
                            </label>
                            <textarea name="description" id="description" rows="3"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                required>{{ old('description', $priority->description) }}</textarea>
                            @error('description')
                                <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- SLA Times -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Response Time -->
                            <div>
                                <label for="sla_response_hours" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-clock text-blue-500 mr-1"></i>
                                    Response Time (hours) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="sla_response_hours" id="sla_response_hours"
                                    value="{{ old('sla_response_hours', $priority->sla_response_hours) }}"
                                    min="1" max="168"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                    required>
                                <p class="text-xs text-gray-500 mt-1">How quickly to respond (Max: 168 hours / 1 week)
                                </p>
                                @error('sla_response_hours')
                                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Resolution Time -->
                            <div>
                                <label for="sla_resolution_hours"
                                    class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-hourglass-end text-purple-500 mr-1"></i>
                                    Resolution Time (hours) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="sla_resolution_hours" id="sla_resolution_hours"
                                    value="{{ old('sla_resolution_hours', $priority->sla_resolution_hours) }}"
                                    min="1" max="720"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                    required>
                                <p class="text-xs text-gray-500 mt-1">How quickly to resolve (Max: 720 hours / 30 days)
                                </p>
                                @error('sla_resolution_hours')
                                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Visual Settings -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Color -->
                            <div>
                                <label for="color" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Badge Color <span class="text-red-500">*</span>
                                </label>
                                <select name="color" id="color"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                    required>
                                    <option value="danger"
                                        {{ old('color', $priority->color) == 'danger' ? 'selected' : '' }}>Red (Danger)
                                    </option>
                                    <option value="warning"
                                        {{ old('color', $priority->color) == 'warning' ? 'selected' : '' }}>Orange
                                        (Warning)</option>
                                    <option value="info"
                                        {{ old('color', $priority->color) == 'info' ? 'selected' : '' }}>Blue (Info)
                                    </option>
                                    <option value="success"
                                        {{ old('color', $priority->color) == 'success' ? 'selected' : '' }}>Green
                                        (Success)</option>
                                    <option value="primary"
                                        {{ old('color', $priority->color) == 'primary' ? 'selected' : '' }}>Purple
                                        (Primary)</option>
                                    <option value="secondary"
                                        {{ old('color', $priority->color) == 'secondary' ? 'selected' : '' }}>Gray
                                        (Secondary)</option>
                                </select>
                                @error('color')
                                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Icon -->
                            <div>
                                <label for="icon" class="block text-sm font-semibold text-gray-700 mb-2">
                                    FontAwesome Icon <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="icon" id="icon"
                                    value="{{ old('icon', $priority->icon) }}" placeholder="fa-exclamation-circle"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                    required>
                                <p class="text-xs text-gray-500 mt-1">Example: fa-arrow-up, fa-exclamation-circle</p>
                                @error('icon')
                                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Preview -->
                        <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3">Preview:</h4>
                            <div id="preview"
                                class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold">
                                <i id="preview-icon" class="fas {{ $priority->icon }} mr-2"></i>
                                <span id="preview-text">{{ $priority->name }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="bg-gray-50 px-8 py-6 border-t border-gray-200 flex justify-between">
                        <a href="{{ route('admin.priorities.index') }}"
                            class="px-6 py-3 bg-gray-600 text-white font-semibold rounded-lg hover:bg-gray-700 transition-colors">
                            <i class="fas fa-times mr-2"></i>
                            Cancel
                        </a>
                        <button type="submit"
                            class="px-8 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white font-semibold rounded-lg hover:from-purple-700 hover:to-purple-800 transition-colors">
                            <i class="fas fa-save mr-2"></i>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Live preview
        document.getElementById('name').addEventListener('input', function() {
            document.getElementById('preview-text').textContent = this.value;
        });

        document.getElementById('icon').addEventListener('input', function() {
            const iconElement = document.getElementById('preview-icon');
            iconElement.className = 'fas ' + this.value + ' mr-2';
        });

        document.getElementById('color').addEventListener('change', function() {
            const preview = document.getElementById('preview');
            preview.className = 'inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold badge-' +
                this.value;
        });
    </script>

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
    </style>
</x-app-layout>
