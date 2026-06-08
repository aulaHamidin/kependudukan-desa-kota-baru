<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to normalize request data types.
 * 
 * This middleware automatically converts string numeric values to proper integers
 * for common ID and numeric fields. This prevents TypeError exceptions when
 * strict_types=1 is enabled and services expect integer parameters.
 */
class NormalizeRequestTypes
{
    /**
     * Fields that should be converted to integers if numeric.
     * 
     * @var array<int, string>
     */
    private const INT_FIELDS = [
        // Primary keys and foreign keys
        'id',
        'penduduk_id',
        'kartu_keluarga_id',
        'kk_id',
        'rt_id',
        'rw_id',
        'desa_id',
        'event_id',
        'user_id',
        'created_by',
        'updated_by',
        'cancelled_by',
        'verified_by',
        'approved_by',

        // Date components
        'tahun',
        'bulan',
        'year',
        'month',
        'day',
        'hari',

        // Pagination
        'page',
        'per_page',
        'limit',
        'offset',

        // Numeric data
        'masa_berlaku_hari',
        'sequence_number',
        'urutan',
    ];

    /**
     * Fields that should be converted to floats if numeric.
     * 
     * @var array<int, string>
     */
    private const FLOAT_FIELDS = [
        'berat_badan_kg',
        'tinggi_badan_cm',
        'panjang_badan_cm',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->normalizeIntegers($request);
        $this->normalizeFloats($request);

        return $next($request);
    }

    /**
     * Convert string numeric values to integers for specified fields.
     */
    private function normalizeIntegers(Request $request): void
    {
        foreach (self::INT_FIELDS as $field) {
            $this->normalizeField($request, $field, 'int');
        }
    }

    /**
     * Convert string numeric values to floats for specified fields.
     */
    private function normalizeFloats(Request $request): void
    {
        foreach (self::FLOAT_FIELDS as $field) {
            $this->normalizeField($request, $field, 'float');
        }
    }

    /**
     * Normalize a single field to the specified type.
     */
    private function normalizeField(Request $request, string $field, string $type): void
    {
        // Check direct field
        if ($request->has($field) && $request->filled($field)) {
            $value = $request->input($field);

            if (is_numeric($value)) {
                $request->merge([
                    $field => $type === 'float' ? (float) $value : (int) $value,
                ]);
            }
        }

        // Check nested fields (e.g., data.penduduk_id)
        $all = $request->all();
        $this->normalizeNestedField($all, $field, $type);
        $request->replace($all);
    }

    /**
     * Recursively normalize nested array fields.
     * 
     * @param array<string, mixed> $data
     */
    private function normalizeNestedField(array &$data, string $field, string $type): void
    {
        foreach ($data as $key => &$value) {
            if ($key === $field && is_numeric($value)) {
                $value = $type === 'float' ? (float) $value : (int) $value;
            } elseif (is_array($value)) {
                $this->normalizeNestedField($value, $field, $type);
            }
        }
    }
}
