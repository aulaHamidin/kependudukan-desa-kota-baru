<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SeedMasterData::class,
            SeedWilayah::class,
            SeedUsers::class,
            JenisSuratSeeder::class,
            DummyDataSeeder::class,
        ]);
    }
}
