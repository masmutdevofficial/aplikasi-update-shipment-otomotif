<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SpecialShipmentTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\SpecialShipmentImport;
use App\Support\SpecialShipmentType;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SpecialShipmentController extends Controller
{
    public function index(string $type)
    {
        $config = SpecialShipmentType::get($type);
        $model = $config['model'];
        $shipments = $model::query()->latest()->get();

        return view('admin.special-shipments.index', compact('type', 'config', 'shipments'));
    }

    public function create(string $type)
    {
        $config = SpecialShipmentType::get($type);

        return view('admin.special-shipments.form', compact('type', 'config'));
    }

    public function store(Request $request, string $type)
    {
        $config = SpecialShipmentType::get($type);
        $model = $config['model'];
        $model::create($request->validate($this->rules($config)));

        return redirect()->route('admin.special-shipments.index', $type)
            ->with('success', "Data {$config['short_label']} berhasil ditambahkan.");
    }

    public function edit(string $type, string $shipment)
    {
        $config = SpecialShipmentType::get($type);
        $model = $config['model'];
        $shipment = $model::query()->findOrFail($shipment);

        return view('admin.special-shipments.form', compact('type', 'config', 'shipment'));
    }

    public function update(Request $request, string $type, string $shipment)
    {
        $config = SpecialShipmentType::get($type);
        $model = $config['model'];
        $shipment = $model::query()->findOrFail($shipment);
        $shipment->update($request->validate($this->rules($config)));

        return redirect()->route('admin.special-shipments.index', $type)
            ->with('success', "Data {$config['short_label']} berhasil diperbarui.");
    }

    public function destroy(string $type, string $shipment)
    {
        $config = SpecialShipmentType::get($type);
        $model = $config['model'];
        $model::query()->findOrFail($shipment)->delete();

        return redirect()->route('admin.special-shipments.index', $type)
            ->with('success', "Data {$config['short_label']} berhasil dihapus.");
    }

    public function bulkDestroy(Request $request, string $type)
    {
        $config = SpecialShipmentType::get($type);
        $model = $config['model'];
        $data = $request->validate([
            'shipment_ids' => ['required', 'array', 'min:1'],
            'shipment_ids.*' => ['required', 'uuid', 'distinct'],
        ]);
        $deleted = $model::query()->whereIn('id', $data['shipment_ids'])->delete();

        return redirect()->route('admin.special-shipments.index', $type)
            ->with('success', "{$deleted} data {$config['short_label']} berhasil dihapus.");
    }

    public function showImport(string $type)
    {
        $config = SpecialShipmentType::get($type);

        return view('admin.special-shipments.import', compact('type', 'config'));
    }

    public function import(Request $request, string $type)
    {
        $config = SpecialShipmentType::get($type);
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ]);

        $import = new SpecialShipmentImport($config);
        Excel::import($import, $request->file('file'));
        $message = "Import {$config['short_label']} selesai: {$import->importedCount} data ditambahkan, {$import->updatedCount} data diperbarui.";

        if ($import->errors !== []) {
            $errors = collect($import->errors)
                ->map(fn (array $error) => "Baris {$error['baris']}: {$error['pesan']}")
                ->implode('<br>');

            return redirect()->route('admin.special-shipments.index', $type)
                ->with('warning', $message . '<br>' . $errors);
        }

        return redirect()->route('admin.special-shipments.index', $type)->with('success', $message);
    }

    public function template(string $type)
    {
        $config = SpecialShipmentType::get($type);
        $filename = 'Format_Upload_' . str_replace('-', '_', strtoupper($type)) . '.xlsx';

        return Excel::download(new SpecialShipmentTemplateExport($config), $filename);
    }

    private function rules(array $config): array
    {
        $rules = [];

        foreach ($config['fields'] as $field => $fieldConfig) {
            $rules[$field] = match ($fieldConfig['type']) {
                'date' => ['nullable', 'date'],
                'integer' => ['nullable', 'integer', 'min:0'],
                default => ['nullable', 'string', 'max:' . ($fieldConfig['max'] ?? 255)],
            };
        }

        return $rules;
    }
}
