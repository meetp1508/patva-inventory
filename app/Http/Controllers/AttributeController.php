<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttributeRequest;
use App\Http\Requests\UpdateAttributeRequest;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Support\Facades\DB;

class AttributeController extends Controller
{
    public function index()
    {
        $attributes = Attribute::with('values')->orderBy('name')->get();

        return view('attributes.index', compact('attributes'));
    }

    public function create()
    {
        return view('attributes.create');
    }

    public function store(StoreAttributeRequest $request)
    {
        DB::transaction(function () use ($request) {
            $attribute = Attribute::create(['name' => $request->validated('name')]);

            foreach (array_values($request->validated('values')) as $i => $row) {
                $attribute->values()->create(['value' => $row['value'], 'sort_order' => $i]);
            }
        });

        return redirect()->route('attributes.index')->with('success', 'Attribute created successfully.');
    }

    public function edit(Attribute $attribute)
    {
        $attribute->load('values');

        return view('attributes.edit', compact('attribute'));
    }

    public function update(UpdateAttributeRequest $request, Attribute $attribute)
    {
        $rows = collect($request->validated('values'));
        $keepIds = $rows->pluck('id')->filter()->map(fn ($id) => (int) $id)->all();

        // Block removing a value that is still attached to product variants.
        $removable = $attribute->values()->whereNotIn('id', $keepIds)->get();
        foreach ($removable as $value) {
            if ($value->variants()->exists()) {
                return redirect()->route('attributes.edit', $attribute)
                    ->with('error', "Cannot remove value \"{$value->value}\" — it is used by existing product variants.");
            }
        }

        DB::transaction(function () use ($request, $attribute, $rows, $keepIds) {
            $attribute->update(['name' => $request->validated('name')]);

            $attribute->values()->whereNotIn('id', $keepIds)->delete();

            $rows->values()->each(function ($row, $i) use ($attribute) {
                if (! empty($row['id'])) {
                    AttributeValue::where('id', $row['id'])->where('attribute_id', $attribute->id)
                        ->update(['value' => $row['value'], 'sort_order' => $i]);
                } else {
                    $attribute->values()->create(['value' => $row['value'], 'sort_order' => $i]);
                }
            });
        });

        return redirect()->route('attributes.index')->with('success', 'Attribute updated successfully.');
    }

    public function destroy(Attribute $attribute)
    {
        $inUse = $attribute->values()->whereHas('variants')->exists();

        if ($inUse) {
            return redirect()->route('attributes.index')
                ->with('error', 'Cannot delete an attribute whose values are used by product variants.');
        }

        $attribute->delete(); // cascades to its values

        return redirect()->route('attributes.index')->with('success', 'Attribute deleted successfully.');
    }
}
