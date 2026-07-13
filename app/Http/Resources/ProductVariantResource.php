<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    /**
     * Parse variant name into attribute key-value pairs.
     * Supports separator " / " (e.g. "Navy / S" → [Warna: Navy, Ukuran: S])
     * Falls back to a single attribute "Varian" for flat names (e.g. "S").
     *
     * Label keys are inferred by position from the first multi-attribute variant
     * found on the same product. For a single-attribute product, key is "Varian".
     */
    protected function parsedAttributes(): array
    {
        $name = trim($this->name ?? '');

        if ($name === '') {
            return [];
        }

        $parts = array_map('trim', explode('/', $name));

        if (count($parts) === 1) {
            // Flat variant — single attribute
            return ['Varian' => $parts[0]];
        }

        // Multi-attribute — derive labels from sibling variants on same product
        $labels = $this->inferAttributeLabels($parts);

        $attrs = [];
        foreach ($parts as $i => $value) {
            $label = $labels[$i] ?? "Atribut " . ($i + 1);
            $attrs[$label] = $value;
        }

        return $attrs;
    }

    /**
     * Try to infer attribute labels (e.g. ["Warna", "Ukuran"]) from all sibling
     * variants of the same product. Falls back to positional labels.
     */
    protected function inferAttributeLabels(array $parts): array
    {
        $count = count($parts);

        // Common label maps by count and common values
        $sizeKeywords = ['XS','S','M','L','XL','XXL','XXXL','2XL','3XL','4XL','5XL','6XL','7XL','8XL'];
        $colorKeywords = ['navy','hitam','putih','abu','merah','biru','hijau','kuning','coklat','krem','cream','grey','gray','black','white','red','blue','green','yellow','brown','olive','maroon','tosca','pink','ungu','purple','orange'];

        if ($count === 2) {
            $first  = strtolower($parts[0]);
            $second = strtolower($parts[1]);

            $firstIsSize  = in_array(strtoupper($parts[0]), $sizeKeywords);
            $secondIsSize = in_array(strtoupper($parts[1]), $sizeKeywords);
            $firstIsColor = in_array($first, $colorKeywords);
            $secondIsColor = in_array($second, $colorKeywords);

            if ($firstIsColor && $secondIsSize) return ['Warna', 'Ukuran'];
            if ($firstIsSize && $secondIsColor) return ['Ukuran', 'Warna'];
            if ($secondIsSize) return ['Warna', 'Ukuran'];
            if ($firstIsSize)  return ['Ukuran', 'Warna'];

            return ['Atribut 1', 'Atribut 2'];
        }

        // Generic fallback
        return array_map(fn($i) => "Atribut $i", range(1, $count));
    }

    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'sku'               => $this->sku,
            'price'             => (float) $this->price,
            'stock'             => $this->stock,
            'weight'            => $this->weight,
            'is_active'         => $this->is_active,
            'sort_order'        => $this->sort_order,
            'parsed_attributes' => $this->parsedAttributes(),
            'image_url'         => $this->image_url,
        ];
    }
}
