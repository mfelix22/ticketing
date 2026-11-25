<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RequestType;
use App\Models\RequestTypeField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RequestTypeFieldController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, RequestType $requestType)
    {
        $validated = $request->validate([
            'field_name' => 'required|string|max:255',
            'field_key' => 'required|string|max:255',
            'field_type' => 'required|in:text,textarea,number,select,date,email,tel',
            'is_required' => 'nullable|boolean',
            'field_options' => 'nullable|string',
            'placeholder' => 'nullable|string|max:255',
            'validation_rules' => 'nullable|string',
            'order' => 'required|integer|min:0',
        ]);

        // Convert field_options from comma-separated string to array if provided
        if (!empty($validated['field_options'])) {
            $validated['field_options'] = array_map('trim', explode(',', $validated['field_options']));
        } else {
            $validated['field_options'] = null;
        }

        $validated['is_required'] = $request->has('is_required');

        Log::info('RequestTypeField store', [
            'request_type_id' => $requestType->id,
            'validated' => $validated,
        ]);
        $requestType->fields()->create($validated);

        return redirect()
            ->route('request-types.edit', $requestType)
            ->with('success', 'Field added successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RequestType $requestType, RequestTypeField $field)
    {
        $validated = $request->validate([
            'field_name' => 'required|string|max:255',
            'field_key' => 'required|string|max:255',
            'field_type' => 'required|in:text,textarea,number,select,date,email,tel',
            'is_required' => 'nullable|boolean',
            'field_options' => 'nullable|string',
            'placeholder' => 'nullable|string|max:255',
            'validation_rules' => 'nullable|string',
            'order' => 'required|integer|min:0',
        ]);

        // Convert field_options from comma-separated string to array if provided
        if (!empty($validated['field_options'])) {
            $validated['field_options'] = array_map('trim', explode(',', $validated['field_options']));
        } else {
            $validated['field_options'] = null;
        }

        $validated['is_required'] = $request->has('is_required');

        Log::info('RequestTypeField update', [
            'request_type_id' => $requestType->id,
            'field_id' => $field->id,
            'validated' => $validated,
        ]);
        $field->update($validated);

        return redirect()
            ->route('request-types.edit', $requestType)
            ->with('success', 'Field updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RequestType $requestType, RequestTypeField $field)
    {
        $field->delete();

        return redirect()
            ->route('request-types.edit', $requestType)
            ->with('success', 'Field deleted successfully');
    }
}
