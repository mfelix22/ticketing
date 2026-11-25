<x-app-layout>
    <!-- Normalize page like Priority SLA: subtle outer gap with rounded + shadow content box -->
    <div class="w-full min-h-screen bg-gray-100 p-8">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Header Section -->
            <div class="px-8 py-6" style="background: linear-gradient(90deg, #2563eb 0%, #1d4ed8 100%);">
                <div class="flex items-center">
                    <div style="margin-left: 1rem;">
                        <h2 class="text-2xl font-bold" style="color: #ffffff;">Edit Ticket</h2>
                        <p class="mt-1" style="color: #ffffff; opacity: 0.9;">Update ticket details below
                        </p>
                    </div>
                </div>
            </div>

            <form action="{{ route('tickets.update', $ticket) }}" method="POST" enctype="multipart/form-data"
                id="ticketForm">
                @csrf
                @method('PUT')
                @if (config('app.debug'))
                    <input type="hidden" name="_debug" value="1">
                @endif
                <div class="p-8 space-y-8" style="background-color: #f6f4f4;">
                    <!-- Flash messages -->
                    @if (session('success'))
                        <div
                            class="flex items-center p-4 rounded-lg bg-green-50 border border-green-200 text-green-800">
                            <i class="fas fa-check-circle mr-3"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="flex items-center p-4 rounded-lg bg-red-50 border border-red-200 text-red-800">
                            <i class="fas fa-triangle-exclamation mr-3"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="p-4 rounded-lg bg-red-50 border border-red-200 text-red-800">
                            <div class="flex items-center mb-2"><i
                                    class="fas fa-circle-exclamation mr-2"></i><strong>There were some
                                    problems with
                                    your input:</strong></div>
                            <ul class="list-disc ml-6 text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Request Type -->
                    <div class="space-y-3">
                        <label for="request_type_id" class="flex items-center text-sm font-semibold mb-4"
                            style="color: #111827;">
                            <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center mr-4">
                                <i class="fas fa-list text-green-600 text-base"></i>
                            </div>
                            <span>Request Type</span>
                            <span class="text-red-500 ml-2">*</span>
                        </label>
                        <select
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error('request_type_id') border-red-500 ring-2 ring-red-200 @enderror"
                            id="request_type_id" name="request_type_id" required>
                            <option value="">Select Request Type</option>
                            @foreach ($requestTypes as $type)
                                <option value="{{ $type->id }}"
                                    {{ old('request_type_id', $ticket->request_type_id) == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('request_type_id')
                            <p class="text-red-600 text-sm mt-2 flex items-center">
                                <i class="fas fa-exclamation-circle mr-2"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Department (Moved here for better UX - before Template) -->
                    <div class="space-y-3">
                        <label for="department_id" class="flex items-center text-sm font-semibold mb-4"
                            style="color: #111827;">
                            <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center mr-4">
                                <i class="fas fa-building text-purple-600 text-base"></i>
                            </div>
                            <span>Department</span>
                            <span class="text-red-500 ml-2">*</span>
                        </label>
                        <select
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error('department_id') border-red-500 ring-2 ring-red-200 @enderror"
                            id="department_id" name="department_id" required onchange="updatePICOptions()">
                            <option value="">Select Department</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}" data-code="{{ $department->code ?? '' }}"
                                    {{ old('department_id', $ticket->department_id) == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('department_id')
                            <p class="text-red-600 text-sm mt-2 flex items-center">
                                <i class="fas fa-exclamation-circle mr-2"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Template Selector (Filtered by Department) -->
                    <div class="space-y-3" id="template-selector-container" style="display: none;">
                        <label for="template_id" class="flex items-center text-sm font-semibold mb-4"
                            style="color: #111827;">
                            <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center mr-4">
                                <i class="fas fa-layer-group text-purple-600 text-base"></i>
                            </div>
                            <span>Use Template</span>
                            <span class="text-gray-500 text-xs ml-2">(Optional - Quick start)</span>
                        </label>
                        <select
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all"
                            id="template_id">
                            <option value="">-- Start from scratch --</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-magic mr-1"></i>
                            Select a template to auto-fill common fields
                        </p>
                    </div>

                    <!-- Title Section -->
                    <div class="space-y-3">
                        <label for="title" class="flex items-center text-sm font-semibold mb-4"
                            style="color: #111827;">
                            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center mr-4">
                                <i class="fas fa-heading text-blue-600 text-base"></i>
                            </div>
                            <span>Title</span>
                            <span class="text-red-500 ml-2">*</span>
                        </label>
                        <input type="text"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error('title') border-red-500 ring-2 ring-red-200 @enderror"
                            id="title" name="title" value="{{ old('title', $ticket->title) }}"
                            placeholder="Enter a descriptive title for your ticket" required>
                        @error('title')
                            <p class="text-red-600 text-sm mt-2 flex items-center">
                                <i class="fas fa-exclamation-circle mr-2"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Dynamic Fields Container (Customer dropdown will appear here for BC + Delivery + Invoice) -->
                    <div id="dynamic-fields-container"></div>

                    <!-- Schedule Date -->
                    <div class="space-y-3">
                        <label for="schedule_date" class="flex items-center text-sm font-semibold mb-4"
                            style="color: #111827;">
                            <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center mr-4">
                                <i class="fas fa-calendar-day text-indigo-600 text-base"></i>
                            </div>
                            <span>Schedule Date</span>
                            <span class="text-gray-500 text-xs ml-2">(Optional - defaults to today)</span>
                        </label>
                        <input type="datetime-local"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error('schedule_date') border-red-500 ring-2 ring-red-200 @enderror"
                            id="schedule_date" name="schedule_date"
                            value="{{ old('schedule_date', $ticket->schedule_date ? $ticket->schedule_date->format('Y-m-d\TH:i') : '') }}"
                            min="{{ now()->format('Y-m-d\TH:i') }}">
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>
                            Set when you want this task to be scheduled. Due date will be calculated from this date +
                            priority SLA.
                        </p>
                        @error('schedule_date')
                            <p class="text-red-600 text-sm mt-2 flex items-center">
                                <i class="fas fa-exclamation-circle mr-2"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Assign to PIC (Person in Charge) -->
                    <div class="space-y-3">
                        <label for="assigned_to" class="flex items-center text-sm font-semibold mb-4"
                            style="color: #111827;">
                            <div class="w-10 h-10 bg-pink-100 rounded-xl flex items-center justify-center mr-4">
                                <i class="fas fa-user-tie text-pink-600 text-base"></i>
                            </div>
                            <span>Assign to PIC (Person in Charge)</span>
                            <span class="text-gray-500 text-xs ml-2">(Optional - Leave blank for
                                auto-assignment)</span>
                        </label>
                        <select
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error('assigned_to') border-red-500 ring-2 ring-red-200 @enderror"
                            id="assigned_to" name="assigned_to">
                            <option value="">Auto-assign or select PIC...</option>
                            @foreach ($pics as $pic)
                                <option value="{{ $pic->id }}" data-department="{{ $pic->department_id }}"
                                    {{ old('assigned_to', $ticket->assigned_to) == $pic->id ? 'selected' : '' }}>
                                    {{ $pic->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>
                            Select a department first to see available PICs for that department
                        </p>
                        @error('assigned_to')
                            <p class="text-red-600 text-sm mt-2 flex items-center">
                                <i class="fas fa-exclamation-circle mr-2"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Priority -->
                    <div class="space-y-3">
                        <label for="priority_id" class="flex items-center text-sm font-semibold mb-4"
                            style="color: #111827;">
                            <div class="w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center mr-4">
                                <i class="fas fa-exclamation-triangle text-orange-600 text-base"></i>
                            </div>
                            <span>Priority</span>
                            <span class="text-red-500 ml-2">*</span>
                        </label>
                        <select
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error('priority_id') border-red-500 ring-2 ring-red-200 @enderror"
                            id="priority_id" name="priority_id" required>
                            <option value="">Select Priority</option>
                            @foreach ($priorities as $priority)
                                <option value="{{ $priority->id }}"
                                    data-sla-hours="{{ $priority->sla_resolution_hours }}"
                                    {{ old('priority_id', $ticket->priority_id) == $priority->id ? 'selected' : '' }}>
                                    {{ $priority->name }}
                                </option>
                            @endforeach
                        </select>
                        <div id="sla-info" class="text-sm text-gray-600 mt-2 hidden">
                            <i class="fas fa-info-circle mr-1"></i>
                            <span id="sla-text"></span>
                        </div>
                        @error('priority_id')
                            <p class="text-red-600 text-sm mt-2 flex items-center">
                                <i class="fas fa-exclamation-circle mr-2"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Attachments -->
                    <div class="space-y-3">
                        <label for="attachments" class="flex items-center text-sm font-semibold mb-4"
                            style="color: #111827;">
                            <div class="w-10 h-10 bg-teal-100 rounded-xl flex items-center justify-center mr-4">
                                <i class="fas fa-paperclip text-teal-600 text-base"></i>
                            </div>
                            <span>Attachments</span>
                        </label>
                        <div
                            class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-400 transition-colors">
                            <input type="file" class="hidden @error('attachments.*') border-red-500 @enderror"
                                id="attachments" name="attachments[]" multiple accept=".png,.jpg,.jpeg,.pdf">
                            <label for="attachments" class="cursor-pointer">
                                <div
                                    class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-cloud-upload-alt text-gray-400 text-2xl"></i>
                                </div>
                                <p class="text-sm text-gray-600 mb-2">
                                    <span class="font-semibold text-blue-600">Click to upload</span> or
                                    drag
                                    and drop
                                </p>
                                <p class="text-xs text-gray-500">PNG, JPG, PDF up to 10MB each</p>
                            </label>
                            <div id="file-list" class="mt-4 space-y-2 hidden"></div>
                        </div>
                        @error('attachments.*')
                            <p class="text-red-600 text-sm mt-2 flex items-center">
                                <i class="fas fa-exclamation-circle mr-2"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="bg-gray-50 px-8 py-6 border-t border-gray-200">
                    <!-- Update Button - Full Width -->
                    <button type="submit"
                        class="w-full inline-flex items-center justify-center px-8 py-4 font-semibold rounded-lg shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transform transition-all duration-200 hover:scale-105"
                        style="background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%); color: #ffffff;">
                        <i class="fas fa-save mr-3 text-sm"></i>
                        <span>Update Ticket</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

<script>
    // Base URL aware of subdirectory deployments (e.g., /ticketing)
    const APP_BASE_URL = "{{ rtrim(url('/'), '/') }}";

    // Filter PICs based on selected department
    function updatePICOptions() {
        const departmentId = document.getElementById('department_id').value;
        const picSelect = document.getElementById('assigned_to');
        const allOptions = picSelect.querySelectorAll('option[data-department]');

        // Save current selection before filtering
        const currentValue = picSelect.value;

        // Show/hide options based on department
        allOptions.forEach(option => {
            const optionDepartment = option.getAttribute('data-department');
            if (!departmentId || optionDepartment === departmentId) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        });

        // Restore previously selected value if the option is still visible
        if (currentValue) {
            const selectedOption = picSelect.querySelector(`option[value="${currentValue}"]`);
            if (selectedOption && selectedOption.style.display !== 'none') {
                picSelect.value = currentValue;
            } else {
                picSelect.value = '';
            }
        }
    }

    // Call on page load if department is already selected (for validation errors)
    document.addEventListener('DOMContentLoaded', function() {
        updatePICOptions();
        updateSLAInfo();

        // Add event listener for priority change
        document.getElementById('priority_id').addEventListener('change', updateSLAInfo);

        // Add event listener for schedule_date change
        document.getElementById('schedule_date').addEventListener('change', updateSLAInfo);
    });

    // Filter departments based on request type
    function filterDepartmentsForRequestType(requestTypeName) {
        const departmentSelect = document.getElementById('department_id');
        const options = departmentSelect.querySelectorAll('option');

        // For Delivery request type, only show BC and PCH departments
        if (requestTypeName === 'Delivery' || requestTypeName === 'Courier') {
            options.forEach(option => {
                if (option.value === '') {
                    option.style.display = '';
                    return;
                }
                const deptName = option.text.trim().toUpperCase();
                const deptCode = option.getAttribute('data-code')?.toUpperCase() || '';

                // Show only if department name or code contains BC or PCH
                if (deptName.includes('BC') || deptName.includes('BILLING') || deptName.includes(
                        'COLLECTION') ||
                    deptName.includes('PCH') || deptName.includes('PURCHASING') ||
                    deptCode === 'BC' || deptCode === 'PCH') {
                    option.style.display = '';
                } else {
                    option.style.display = 'none';
                }
            });

            // Reset selection if current selection is now hidden
            const selectedOption = departmentSelect.options[departmentSelect.selectedIndex];
            if (selectedOption && selectedOption.style.display === 'none') {
                departmentSelect.value = '';
            }
        } else {
            // Show all departments for other request types
            options.forEach(option => {
                option.style.display = '';
            });
        }
    }

    // Show SLA info when priority is selected (info only, no input field)
    function updateSLAInfo() {
        const prioritySelect = document.getElementById('priority_id');
        const selectedOption = prioritySelect.options[prioritySelect.selectedIndex];
        const slaHours = selectedOption.getAttribute('data-sla-hours');
        const slaInfo = document.getElementById('sla-info');
        const slaText = document.getElementById('sla-text');

        if (slaHours && slaHours > 0) {
            // Calculate due date for display only
            const scheduleDate = document.getElementById('schedule_date').value;
            let baseDate;

            if (scheduleDate) {
                baseDate = new Date(scheduleDate);
            } else {
                baseDate = new Date();
            }

            const dueDate = new Date(baseDate.getTime() + (slaHours * 60 * 60 * 1000));

            // Format for display
            const options = {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            const displayDate = dueDate.toLocaleString('en-US', options);

            // Show info
            slaText.innerHTML =
                `Due date will be set to: <strong>${displayDate}</strong> (${slaHours} hours from ${scheduleDate ? 'schedule date' : 'now'})`;
            slaInfo.classList.remove('hidden');
        } else {
            slaInfo.classList.add('hidden');
        }
    }

    // Debug form submission - ONLY for ticket form
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('#ticketForm'); // Target specific form by ID
        if (form) {
            form.addEventListener('submit', function(e) {
                console.log('Form submitting...');
                console.log('Form action:', form.action);
                console.log('Form method:', form.method);

                // Validate required fields
                const title = document.getElementById('title').value;
                const requestType = document.getElementById('request_type_id').value;
                const department = document.getElementById('department_id').value;
                const priority = document.getElementById('priority_id').value;
                const detailRequest = document.getElementById('detail_request').value;

                console.log('Title:', title);
                console.log('Request Type:', requestType);
                console.log('Department:', department);
                console.log('Priority:', priority);
                console.log('Detail Request:', detailRequest);

                if (!title || !requestType || !department || !priority || !detailRequest) {
                    e.preventDefault();
                    alert('Please fill in all required fields!');
                    return false;
                }
            });
        }
    });

    // Enhanced file input with preview
    document.getElementById('attachments').addEventListener('change', function(e) {
        const files = e.target.files;
        const fileList = document.getElementById('file-list');

        if (files.length > 0) {
            fileList.classList.remove('hidden');
            fileList.innerHTML = '';

            Array.from(files).forEach((file, index) => {
                const fileItem = document.createElement('div');
                fileItem.className =
                    'flex items-center justify-between bg-blue-50 border border-blue-200 rounded-lg p-3';

                const fileInfo = document.createElement('div');
                fileInfo.className = 'flex items-center';

                const fileIcon = document.createElement('i');
                fileIcon.className = getFileIcon(file.name) + ' text-blue-600 mr-3';

                const fileName = document.createElement('span');
                fileName.className = 'text-sm font-medium text-gray-700';
                fileName.textContent = file.name;

                const fileSize = document.createElement('span');
                fileSize.className = 'text-xs text-gray-500 ml-2';
                fileSize.textContent = `(${formatFileSize(file.size)})`;

                fileInfo.appendChild(fileIcon);
                fileInfo.appendChild(fileName);
                fileInfo.appendChild(fileSize);

                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'text-red-500 hover:text-red-700 transition-colors';
                removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                removeBtn.onclick = () => removeFile(index);

                fileItem.appendChild(fileInfo);
                fileItem.appendChild(removeBtn);
                fileList.appendChild(fileItem);
            });
        } else {
            fileList.classList.add('hidden');
        }
    });

    function getFileIcon(filename) {
        const ext = filename.split('.').pop().toLowerCase();
        switch (ext) {
            case 'pdf':
                return 'fas fa-file-pdf';
            case 'jpg':
            case 'jpeg':
            case 'png':
                return 'fas fa-file-image';
            default:
                return 'fas fa-file';
        }
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function removeFile(index) {
        // Reset the file input (simple approach)
        document.getElementById('attachments').value = '';
        document.getElementById('file-list').classList.add('hidden');
    }

    // Initialize Select2 safely if jQuery is present
    document.addEventListener('DOMContentLoaded', function() {
        if (window.$ && $.fn && $.fn.select2) {
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%'
            });
        }

        // Template functionality
        const templateSelector = document.getElementById('template_id');
        const templateContainer = document.getElementById('template-selector-container');
        let currentTemplates = [];

        // Load templates when request type changes (always show, but hide Invoice unless BC selected)
        async function loadTemplates(requestTypeId, departmentId) {
            if (!requestTypeId) {
                templateContainer.style.display = 'none';
                templateSelector.innerHTML = '<option value="">-- Start from scratch --</option>';
                currentTemplates = [];
                return;
            }

            try {
                const response = await fetch(
                    `${APP_BASE_URL}/api/templates/by-request-type?request_type_id=${requestTypeId}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    }
                );

                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    console.error('Response is not JSON:', contentType);
                    templateContainer.style.display = 'none';
                    return;
                }

                const templates = await response.json();

                // Check if requester (logged in user) is from BC department
                const requesterDeptName = '{{ Auth::user()->department->name ?? '' }}';
                const requesterDeptCode = '{{ Auth::user()->department->code ?? '' }}';
                const isRequesterBC = requesterDeptName.toUpperCase().includes('BC') ||
                    requesterDeptName.toUpperCase().includes('BILLING') ||
                    requesterDeptName.toUpperCase().includes('COLLECTION') ||
                    requesterDeptCode.toUpperCase() === 'BC';

                // Check if selected department is BC
                const departmentSelect = document.getElementById('department_id');
                const selectedDept = departmentSelect ? departmentSelect.options[departmentSelect
                    .selectedIndex] : null;
                const selectedDeptName = selectedDept ? selectedDept.text.trim().toUpperCase() : '';
                const selectedDeptCode = selectedDept ? (selectedDept.getAttribute('data-code') || '')
                    .toUpperCase() : '';
                const isSelectedDeptBC = selectedDeptName.includes('BC') ||
                    selectedDeptName.includes('BILLING') ||
                    selectedDeptName.includes('COLLECTION') ||
                    selectedDeptCode === 'BC';

                // Show ALL templates, but hide "Invoice" template unless BOTH requester AND selected department are BC
                let filteredTemplates = templates.filter(template => {
                    const templateName = template.name.toUpperCase();
                    // If template name contains "INVOICE", only show if BOTH requester and selected dept are BC
                    if (templateName.includes('INVOICE') && !(isRequesterBC && isSelectedDeptBC)) {
                        return false;
                    }
                    return true;
                });

                currentTemplates = filteredTemplates;

                if (filteredTemplates.length > 0) {
                    templateContainer.style.display = 'block';
                    let options = '<option value="">-- Start from scratch --</option>';
                    filteredTemplates.forEach(template => {
                        options +=
                            `<option value="${template.id}" data-name="${template.name}">${template.name}</option>`;
                    });
                    templateSelector.innerHTML = options;
                } else {
                    // Still show container with "Start from scratch" option
                    templateContainer.style.display = 'block';
                    templateSelector.innerHTML = '<option value="">-- Start from scratch --</option>';
                    currentTemplates = [];
                }
            } catch (error) {
                console.error('Error loading templates:', error);
                templateContainer.style.display = 'none';
            }
        }

        // Apply template when selected
        templateSelector.addEventListener('change', function() {
            const templateId = this.value;

            // Handle Invoice template - disable customer fields
            const selectedOption = this.options[this.selectedIndex];
            const templateName = selectedOption ? selectedOption.getAttribute('data-name') : '';
            const isInvoiceTemplate = templateName && templateName.toUpperCase().includes('INVOICE');

            // Make customer address and phone fields readonly (not disabled) for Invoice template
            const addressField = document.getElementById('dynamic_delivery_address');
            const phoneField = document.getElementById('dynamic_phone_number');

            if (isInvoiceTemplate) {
                if (addressField) {
                    addressField.readOnly = true;
                    addressField.classList.add('bg-gray-100', 'cursor-not-allowed');
                    addressField.title =
                        'This field is auto-filled from customer data and cannot be edited for Invoice template';
                }
                if (phoneField) {
                    phoneField.readOnly = true;
                    phoneField.classList.add('bg-gray-100', 'cursor-not-allowed');
                    phoneField.title =
                        'This field is auto-filled from customer data and cannot be edited for Invoice template';
                }
            } else {
                if (addressField) {
                    addressField.readOnly = false;
                    addressField.classList.remove('bg-gray-100', 'cursor-not-allowed');
                    addressField.title = '';
                }
                if (phoneField) {
                    phoneField.readOnly = false;
                    phoneField.classList.remove('bg-gray-100', 'cursor-not-allowed');
                    phoneField.title = '';
                }
            }

            if (!templateId) return;

            const template = currentTemplates.find(t => t.id == templateId);
            if (!template) return;

            // Fill in template values
            document.getElementById('title').value = template.title_template;

            if (template.default_priority_id) {
                document.getElementById('priority_id').value = template.default_priority_id;
            }

            // Fill dynamic fields if they exist
            if (template.field_defaults) {
                Object.keys(template.field_defaults).forEach(fieldKey => {
                    const input = document.querySelector(
                        `[name="detail_request[${fieldKey}]"]`);
                    if (input && template.field_defaults[fieldKey]) {
                        input.value = template.field_defaults[fieldKey];
                    }
                });
            }

            // Show success message
            const msg = document.createElement('div');
            msg.className = 'alert alert-success alert-dismissible fade show';
            msg.innerHTML = `
                <i class="fas fa-check-circle mr-2"></i>
                Template "${template.name}" applied! You can still modify the fields.
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            `;
            document.querySelector('form').insertBefore(msg, document.querySelector('form').firstChild);
            setTimeout(() => msg.remove(), 5000);
        });

        // Get existing ticket data for auto-filling dynamic fields
        const TICKET_DATA = @json($ticket->detail_request ?? []);

        // Load dynamic fields when request type changes
        const requestTypeSelect = document.getElementById('request_type_id');
        const dynamicFieldsContainer = document.getElementById('dynamic-fields-container');

        if (requestTypeSelect && dynamicFieldsContainer) {
            requestTypeSelect.addEventListener('change', async function() {
                const requestTypeId = this.value;
                const selectedOption = this.options[this.selectedIndex];
                const requestTypeName = selectedOption ? selectedOption.text.trim() : '';

                // Filter departments for Delivery request type
                filterDepartmentsForRequestType(requestTypeName);

                // Load templates for this request type AND department
                const departmentId = document.getElementById('department_id').value;
                await loadTemplates(requestTypeId, departmentId);

                if (!requestTypeId) {
                    dynamicFieldsContainer.innerHTML = '';
                    return;
                }

                try {
                    const response = await fetch(
                        `${APP_BASE_URL}/api/request-types/${requestTypeId}/fields`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin'
                        }
                    );

                    // Check if response is JSON
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        console.error('Fields response is not JSON:', contentType);
                        dynamicFieldsContainer.innerHTML = '';
                        return;
                    }

                    const fields = await response.json();

                    if (fields.length === 0) {
                        dynamicFieldsContainer.innerHTML = '';
                        return;
                    }

                    let html =
                        '<br><div class="mb-4"><h3 class="text-lg font-semibold mb-4" style="color: #111827;"><i class="fas fa-list-check mr-2"></i>Additional Information</h3></div>';

                    fields.forEach((field, index) => {
                        html += `<div class="space-y-3 mb-4">`;
                        html +=
                            `<label for="dynamic_${field.field_key}" class="flex items-center text-sm font-semibold mb-4" style="color: #111827;">`;
                        html +=
                            `<div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center mr-4">`;
                        html += `<i class="fas fa-pen text-purple-600 text-base"></i>`;
                        html += `</div>`;
                        html += `<span>${field.field_name}</span>`;
                        if (field.is_required) {
                            html += `<span class="text-red-500 ml-2">*</span>`;
                        }
                        html += `</label>`;

                        // Render different input types
                        if (field.field_type === 'textarea') {
                            html += `<textarea class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all resize-none"
                                id="dynamic_${field.field_key}" name="detail_request[${field.field_key}]" rows="4" 
                                ${field.is_required ? 'required' : ''} 
                                placeholder="${field.placeholder || ''}"></textarea>`;
                        } else if (field.field_type === 'select') {
                            html += `<select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                id="dynamic_${field.field_key}" name="detail_request[${field.field_key}]" 
                                ${field.is_required ? 'required' : ''}>`;
                            html += `<option value="">Select an option</option>`;
                            if (field.field_options && Array.isArray(field.field_options)) {
                                field.field_options.forEach(option => {
                                    html +=
                                        `<option value="${option}">${option}</option>`;
                                });
                            }
                            html += `</select>`;
                        } else {
                            html += `<input type="${field.field_type}" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                id="dynamic_${field.field_key}" name="detail_request[${field.field_key}]" 
                                ${field.is_required ? 'required' : ''} 
                                placeholder="${field.placeholder || ''}">`;
                        }

                        html += `</div>`;
                    });

                    dynamicFieldsContainer.innerHTML = html;

                    // Auto-fill dynamic fields from existing ticket data
                    if (TICKET_DATA && Object.keys(TICKET_DATA).length > 0) {
                        fields.forEach(field => {
                            const element = document.getElementById(
                                `dynamic_${field.field_key}`);
                            if (element && TICKET_DATA[field.field_key]) {
                                if (field.field_type === 'select') {
                                    // For select fields, set the selected option
                                    element.value = TICKET_DATA[field.field_key];
                                } else {
                                    // For input and textarea fields
                                    element.value = TICKET_DATA[field.field_key];
                                }
                            }
                        });
                    }

                    // Check if we should show customer dropdown (Delivery + BC department)
                    await injectCustomerDropdownIfNeeded(requestTypeName);
                } catch (error) {
                    console.error('Error loading fields:', error);
                    dynamicFieldsContainer.innerHTML = '';
                }
            });

            // Trigger load if there's a pre-selected value (for validation errors)
            if (requestTypeSelect.value) {
                requestTypeSelect.dispatchEvent(new Event('change'));
            }
        }

        // Also check when department changes
        const departmentSelect = document.getElementById('department_id');
        if (departmentSelect) {
            departmentSelect.addEventListener('change', async function() {
                const requestTypeSelect = document.getElementById('request_type_id');
                if (requestTypeSelect && requestTypeSelect.value) {
                    const selectedOption = requestTypeSelect.options[requestTypeSelect
                        .selectedIndex];
                    const requestTypeName = selectedOption ? selectedOption.text.trim() : '';

                    // Reload templates when department changes
                    await loadTemplates(requestTypeSelect.value, this.value);

                    // Check customer dropdown
                    await injectCustomerDropdownIfNeeded(requestTypeName);
                }
            });
        }

        // Also check when template changes
        templateSelector.addEventListener('change', async function() {
            const requestTypeSelect = document.getElementById('request_type_id');
            if (requestTypeSelect && requestTypeSelect.value) {
                const selectedOption = requestTypeSelect.options[requestTypeSelect
                    .selectedIndex];
                const requestTypeName = selectedOption ? selectedOption.text.trim() : '';
                await injectCustomerDropdownIfNeeded(requestTypeName);
            }
        });

        // Function to inject customer dropdown for BC + Delivery + Invoice template combination
        async function injectCustomerDropdownIfNeeded(requestTypeName) {
            const departmentSelect = document.getElementById('department_id');
            const templateSelect = document.getElementById('template_id');
            const dynamicFieldsContainer = document.getElementById('dynamic-fields-container');

            if (!departmentSelect || !dynamicFieldsContainer) return;

            const selectedDept = departmentSelect.options[departmentSelect.selectedIndex];
            const deptName = selectedDept ? selectedDept.text.trim().toUpperCase() : '';
            const deptCode = selectedDept ? (selectedDept.getAttribute('data-code') || '').toUpperCase() :
                '';

            const selectedTemplate = templateSelect.options[templateSelect.selectedIndex];
            const templateName = selectedTemplate ? selectedTemplate.getAttribute('data-name') : '';

            // Check if it's Delivery request type AND BC department AND Invoice template
            const isDelivery = requestTypeName === 'Delivery' || requestTypeName === 'Courier';
            const isBC = deptName.includes('BC') || deptName.includes('BILLING') || deptName.includes(
                'COLLECTION') || deptCode === 'BC';
            const isInvoiceTemplate = templateName && templateName.toUpperCase().includes('INVOICE');

            // Remove existing customer dropdown if it exists
            const existingCustomerField = document.getElementById('customer-dropdown-container');
            if (existingCustomerField) {
                existingCustomerField.remove();
            }

            // ONLY show customer dropdown if ALL THREE conditions are met:
            // 1. Delivery request type
            // 2. BC department
            // 3. Invoice template selected
            if (isDelivery && isBC && isInvoiceTemplate) {
                // Fetch customers and inject dropdown
                try {
                    const response = await fetch(`${APP_BASE_URL}/api/customers`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    });

                    const customers = await response.json();

                    if (customers && customers.length > 0) {
                        // Create customer dropdown HTML with search
                        let customerHtml =
                            '<div id="customer-dropdown-container" class="space-y-3 mb-4" style="border: 2px solid #10b981; padding: 15px; border-radius: 8px; background-color: #f0fdf4;">';
                        customerHtml +=
                            '<label for="customer_selector" class="flex items-center text-sm font-semibold mb-4" style="color: #111827;">';
                        customerHtml +=
                            '<div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center mr-4">';
                        customerHtml += '<i class="fas fa-user-tie text-green-600 text-base"></i>';
                        customerHtml += '</div>';
                        customerHtml += '<span>Select Customer (Auto-fill Info)</span>';
                        customerHtml +=
                            '<span class="text-gray-500 text-xs ml-2">(For BC Department)</span>';
                        customerHtml += '</label>';

                        // Add search input
                        customerHtml +=
                            '<div class="mb-2">';
                        customerHtml +=
                            '<input type="text" id="customer_search" placeholder="Search customer by name or code..." class="w-full px-4 py-2 border border-green-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all" autocomplete="off">';
                        customerHtml += '</div>';

                        // Select dropdown
                        customerHtml +=
                            '<select class="w-full px-4 py-3 border border-green-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all" id="customer_selector" size="8" style="height: auto;">';
                        customerHtml += '<option value="">-- Select a Customer --</option>';

                        customers.forEach(customer => {
                            customerHtml +=
                                `<option value="${customer.id}" data-name="${customer.name}" data-pic="${customer.pic_name || ''}" data-phone="${customer.phone}" data-address="${customer.address}" data-search-text="${customer.name.toLowerCase()} ${customer.code ? customer.code.toLowerCase() : ''}">${customer.name} ${customer.code ? '(' + customer.code + ')' : ''}</option>`;
                        });

                        customerHtml += '</select>';
                        customerHtml += '<small class="text-xs text-green-700 mt-1 block">';
                        customerHtml +=
                            '<i class="fas fa-magic mr-1"></i>Selecting a customer will auto-fill Recipient Name, Phone Number, and Delivery Address';
                        customerHtml += '</small>';
                        customerHtml += '</div>';

                        // Insert at the beginning of dynamic fields
                        dynamicFieldsContainer.insertAdjacentHTML('afterbegin', customerHtml);

                        // Add search functionality
                        const customerSearch = document.getElementById('customer_search');
                        const customerSelector = document.getElementById('customer_selector');

                        if (customerSearch && customerSelector) {
                            // Search input event
                            customerSearch.addEventListener('input', function() {
                                const searchTerm = this.value.toLowerCase().trim();
                                const options = customerSelector.options;

                                for (let i = 0; i < options.length; i++) {
                                    const option = options[i];
                                    if (i === 0) continue; // Skip the placeholder option

                                    const searchText = option.getAttribute('data-search-text') ||
                                        '';
                                    if (searchText.includes(searchTerm)) {
                                        option.style.display = '';
                                    } else {
                                        option.style.display = 'none';
                                    }
                                }

                                // Reset selection if current selection is hidden
                                if (customerSelector.selectedIndex > 0 && options[customerSelector
                                        .selectedIndex].style.display === 'none') {
                                    customerSelector.selectedIndex = 0;
                                }
                            });

                            // Clear search when customer is selected
                            customerSelector.addEventListener('change', function() {
                                if (this.value && customerSearch) {
                                    customerSearch.value = '';
                                    // Show all options again
                                    const options = this.options;
                                    for (let i = 0; i < options.length; i++) {
                                        options[i].style.display = '';
                                    }
                                }
                            });
                        }

                        // Add event listener for customer selection
                        if (customerSelector) {
                            customerSelector.addEventListener('change', function() {
                                const selectedOption = this.options[this.selectedIndex];
                                if (selectedOption && selectedOption.value) {
                                    const customerName = selectedOption.getAttribute('data-name');
                                    const picName = selectedOption.getAttribute('data-pic');
                                    const customerPhone = selectedOption.getAttribute('data-phone');
                                    const customerAddress = selectedOption.getAttribute(
                                        'data-address');

                                    // Auto-fill the dynamic fields
                                    const recipientField = document.getElementById(
                                        'dynamic_recipient_name');
                                    const phoneField = document.getElementById(
                                        'dynamic_phone_number');
                                    const addressField = document.getElementById(
                                        'dynamic_delivery_address');

                                    // Use PIC name if available, otherwise use company name
                                    if (recipientField) {
                                        recipientField.value = picName || customerName;
                                        recipientField.readOnly = true;
                                        recipientField.classList.add('bg-gray-100',
                                            'cursor-not-allowed');
                                    }
                                    if (phoneField) {
                                        phoneField.value = customerPhone;
                                        phoneField.readOnly = true;
                                        phoneField.classList.add('bg-gray-100',
                                            'cursor-not-allowed');
                                    }
                                    if (addressField) {
                                        addressField.value = customerAddress;
                                        addressField.readOnly = true;
                                        addressField.classList.add('bg-gray-100',
                                            'cursor-not-allowed');
                                    }
                                }
                            });
                        }
                    }
                } catch (error) {
                    console.error('Error loading customers:', error);
                }
            }
        }
    });
</script>
