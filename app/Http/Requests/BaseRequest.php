<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Base FormRequest class with type casting support.
 * 
 * Provides `validatedTyped()` method that returns validated data
 * with proper PHP types instead of strings from form data.
 * 
 * Usage:
 * 1. Extend this class instead of FormRequest
 * 2. Override `casts()` to define field types
 * 3. Use `$request->validatedTyped()` in controllers
 * 
 * Example:
 * ```php
 * class StoreSuratRequest extends BaseRequest
 * {
 *     protected function casts(): array
 *     {
 *         return [
 *             'penduduk_id' => 'int',
 *             'rt_id' => 'int',
 *             'tahun' => 'int',
 *         ];
 *     }
 * }
 * 
 * // In controller:
 * $data = $request->validatedTyped();
 * // ['penduduk_id' => 123, 'rt_id' => 5] - actual integers!
 * ```
 */
abstract class BaseRequest extends FormRequest
{
    /**
     * Get validated data with proper PHP types.
     * 
     * @return array<string, mixed>
     */
    public function validatedTyped(): array
    {
        $validated = $this->validated();

        return $this->castTypes($validated);
    }

    /**
     * Get a specific validated field with proper type.
     */
    public function validatedInt(string $key, int $default = 0): int
    {
        $value = $this->validated($key);

        if ($value === null) {
            return $default;
        }

        return (int) $value;
    }

    /**
     * Get a specific validated field as float.
     */
    public function validatedFloat(string $key, float $default = 0.0): float
    {
        $value = $this->validated($key);

        if ($value === null) {
            return $default;
        }

        return (float) $value;
    }

    /**
     * Get a specific validated field as boolean.
     */
    public function validatedBool(string $key, bool $default = false): bool
    {
        $value = $this->validated($key);

        if ($value === null) {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Get a specific validated field as string.
     */
    public function validatedString(string $key, string $default = ''): string
    {
        $value = $this->validated($key);

        if ($value === null) {
            return $default;
        }

        return (string) $value;
    }

    /**
     * Cast validated data to proper types based on casts() definition.
     * 
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function castTypes(array $data): array
    {
        $casts = $this->casts();

        foreach ($casts as $key => $type) {
            if (!array_key_exists($key, $data)) {
                continue;
            }

            // Null atau empty string → tetap null, jangan di-cast
            if ($data[$key] === null || $data[$key] === '') {
                $data[$key] = null; // normalisasi "" jadi null
                continue;
            }

            $data[$key] = match ($type) {
                'int', 'integer'            => (int) $data[$key],
                'float', 'double', 'decimal' => (float) $data[$key],
                'bool', 'boolean'           => filter_var($data[$key], FILTER_VALIDATE_BOOLEAN),
                'string'                    => (string) $data[$key],
                'array'                     => is_array($data[$key]) ? $data[$key] : [$data[$key]],
                default                     => $data[$key],
            };
        }

        return $data;
    }

    /**
     * Define type casts for validated fields.
     * 
     * Override this method in child classes to specify field types.
     * 
     * Supported types: 'int', 'integer', 'float', 'double', 'decimal',
     * 'bool', 'boolean', 'string', 'array'
     * 
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [];
    }
}
