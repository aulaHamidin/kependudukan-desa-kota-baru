<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class AuditLogService
{
    /**
     * @var array<int, string>
     */
    private array $hiddenKeys = [
        'password',
        'remember_token',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * @var array<int, string>
     */
    private array $maskedKeys = [
        'nik',
        'no_hp',
        'email',
        'no_kk',    // 16-digit KK number, setara sensitivitas dengan NIK
        'tgl_lahir',    // kombinasi identifikasi — berlaku di Penduduk
        'nama_ibu',     // kombinasi identifikasi — berlaku di Penduduk & EventKelahiran
    ];

    public function logModelAction(Model $model, string $action): void
    {
        if ($model instanceof AuditLog) {
            return;
        }

        $actor = auth()->user();

        $oldValues = null;
        $newValues = null;

        if ($action === AuditLog::ACTION_CREATE) {
            $attributes = $model->getAttributes();
            $fillable = $model->getFillable();

            if (!empty($fillable)) {
                $attributes = Arr::only($attributes, $fillable);
            }

            $newValues = $this->filterValues($model, $attributes);
        }

        if ($action === AuditLog::ACTION_UPDATE) {
            $changes = $this->filterValues($model, $model->getChanges());
            $original = $this->filterValues($model, $model->getOriginal());

            $oldValues = Arr::only($original, array_keys($changes));
            $newValues = $changes;

            if (empty($oldValues) && empty($newValues)) {
                return;
            }
        }

        if ($action === AuditLog::ACTION_DELETE) {
            $oldValues = $this->filterValues($model, $model->getAttributes());
        }

        $this->writeLog($actor, $action, $model, $oldValues, $newValues);
    }

    public function logAuthAction(User $user, string $action): void
    {
        $this->writeLog($user, $action, $user, null, null);
    }

    /**
     * @param class-string $modelClass
     */
    public function logImportAction(string $modelClass, int $count, string $action): void
    {
        $actor = auth()->user();
        $model = new $modelClass();

        $this->writeLog($actor, $action, $model, null, [
            'count' => $count,
        ]);
    }

    /**
     * @param array<string, mixed>|null $oldValues
     * @param array<string, mixed>|null $newValues
     */
    private function writeLog(?User $actor, string $action, Model $model, ?array $oldValues, ?array $newValues): void
    {
        $actorType = $actor ? 'user' : 'system';

        AuditLog::create([
            'user_id' => $actor?->id,
            'actor_type' => $actorType,
            'actor_id' => $actor?->id,
            'aksi' => $action,
            'model' => class_basename($model),
            'model_id' => $model->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'role_snapshot' => $actor?->role,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * @param array<string, mixed> $values
     * @return array<string, mixed>
     */
    private function filterValues(Model $model, array $values): array
    {
        $hidden = array_unique(array_merge($model->getHidden(), $this->hiddenKeys));

        $filtered = Arr::except($values, $hidden);

        return $this->maskSensitiveValues($filtered);
    }

    /**
     * @param array<string, mixed> $values
     * @return array<string, mixed>
     */
    private function maskSensitiveValues(array $values): array
    {
        foreach ($values as $key => $value) {
            if (!in_array($key, $this->maskedKeys, true)) {
                continue;
            }

            $values[$key] = $this->maskValue($key, $value);
        }

        return $values;
    }

    /**
     * @param mixed $value
     */
    private function maskValue(string $key, $value): string
    {
        $stringValue = is_scalar($value) ? (string) $value : '';

        if ($stringValue === '') {
            return '-';
        }

        if ($key === 'email') {
            return $this->maskEmail($stringValue);
        }

        return $this->maskPartial($stringValue, 4, 2);
    }

    private function maskEmail(string $value): string
    {
        $parts = explode('@', $value, 2);
        if (count($parts) !== 2) {
            return $this->maskPartial($value, 2, 2);
        }

        $local = $this->maskPartial($parts[0], 2, 1);

        return $local . '@' . $parts[1];
    }

    private function maskPartial(string $value, int $prefix, int $suffix): string
    {
        $length = strlen($value);
        if ($length <= ($prefix + $suffix)) {
            return str_repeat('*', $length);
        }

        return substr($value, 0, $prefix)
            . str_repeat('*', $length - $prefix - $suffix)
            . substr($value, -$suffix);
    }
}
