<?php

declare(strict_types=1);

namespace App\Traits;

trait Maskable
{
    public function getMaskedNikAttribute(): string
    {
        return $this->shouldMask()
            ? $this->maskString($this->nik, 4, 2)
            : $this->nik;
    }

    public function getMaskedNoHpAttribute(): ?string
    {
        return $this->no_hp && $this->shouldMask()
            ? $this->maskString($this->no_hp, 4, 2)
            : $this->no_hp;
    }

    public function getMaskedEmailAttribute(): ?string
    {
        if (!$this->email || !$this->shouldMask()) {
            return $this->email;
        }

        $parts = explode('@', $this->email, 2);
        if (count($parts) !== 2) {
            return $this->maskString($this->email, 2, 2);
        }

        $local = $this->maskString($parts[0], 2, 1);

        return $local . '@' . $parts[1];
    }

    private function shouldMask(): bool
    {
        return auth()->check() && auth()->user()->hasRole('viewer');
    }

    private function maskString(string $value, int $showStart, int $showEnd): string
    {
        $length = strlen($value);

        if ($length <= ($showStart + $showEnd)) {
            return str_repeat('*', $length);
        }

        $maskLength = $length - $showStart - $showEnd;

        return substr($value, 0, $showStart)
            . str_repeat('*', $maskLength)
            . substr($value, -$showEnd);
    }
}
