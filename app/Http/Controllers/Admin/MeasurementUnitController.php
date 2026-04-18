<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MeasurementUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class MeasurementUnitController extends Controller
{
    public function index()
    {
        $measurementUnits = MeasurementUnit::query()->latest()->paginate(15);

        return view('admin.measurement-units.index', compact('measurementUnits'));
    }

    public function create()
    {
        return view('admin.measurement-units.create', [
            'measurementUnit' => null,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:100', Rule::unique('measurement_units', 'name')->whereNull('deleted_at')],
                'symbol' => ['required', 'string', 'max:20', Rule::unique('measurement_units', 'symbol')->whereNull('deleted_at')],
                'description' => ['nullable', 'string', 'max:255'],
                'active' => ['sometimes', 'boolean'],
            ]);

            MeasurementUnit::create([
                ...$validated,
                'active' => $request->boolean('active', true),
            ]);

            return redirect()->route('measurement-units.index')->with('success', 'Unidad de medida creada exitosamente.');
        } catch (\Throwable $exception) {
            Log::error('Error al crear unidad de medida', ['message' => $exception->getMessage()]);
            return back()->withInput()->withErrors(['error' => 'No fue posible crear la unidad de medida.']);
        }
    }

    public function edit(MeasurementUnit $measurementUnit)
    {
        return view('admin.measurement-units.edit', compact('measurementUnit'));
    }

    public function update(Request $request, MeasurementUnit $measurementUnit)
    {
        try {
            $validated = $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('measurement_units', 'name')->ignore($measurementUnit->id)->whereNull('deleted_at'),
                ],
                'symbol' => [
                    'required',
                    'string',
                    'max:20',
                    Rule::unique('measurement_units', 'symbol')->ignore($measurementUnit->id)->whereNull('deleted_at'),
                ],
                'description' => ['nullable', 'string', 'max:255'],
                'active' => ['sometimes', 'boolean'],
            ]);

            $measurementUnit->update([
                ...$validated,
                'active' => $request->boolean('active', false),
            ]);

            return redirect()->route('measurement-units.index')->with('success', 'Unidad de medida actualizada exitosamente.');
        } catch (\Throwable $exception) {
            Log::error('Error al actualizar unidad de medida', [
                'measurement_unit_id' => $measurementUnit->id,
                'message' => $exception->getMessage(),
            ]);
            return back()->withInput()->withErrors(['error' => 'No fue posible actualizar la unidad de medida.']);
        }
    }

    public function destroy(MeasurementUnit $measurementUnit)
    {
        if ($measurementUnit->products()->exists()) {
            return back()->withErrors(['error' => 'No puedes eliminar una unidad con productos asociados.']);
        }

        $measurementUnit->delete();

        return redirect()->route('measurement-units.index')->with('success', 'Unidad de medida eliminada exitosamente.');
    }
}
