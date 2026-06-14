<?php

namespace Database\Seeders;

use App\Models\Attribute;
use Illuminate\Database\Seeder;

class AttributeSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'Color' => ['Red', 'Blue', 'Green', 'Black'],
            'Size'  => ['S', 'M', 'L', 'XL'],
        ];

        foreach ($defaults as $name => $values) {
            $attribute = Attribute::firstOrCreate(['name' => $name]);

            foreach (array_values($values) as $i => $value) {
                $attribute->values()->firstOrCreate(['value' => $value], ['sort_order' => $i]);
            }
        }
    }
}
