<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Recipe;

class RemoveDuplicateRecipes extends Command
{
    protected $signature = 'recipes:remove-duplicates';
    protected $description = 'Remove duplicate recipes based on title';

    public function handle()
    {
        $this->info('Checking duplicate recipes...');

        // Ambil title yang duplikat
        $duplicates = Recipe::select('title')
            ->groupBy('title')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('title');

        $deletedCount = 0;

        foreach ($duplicates as $title) {

            $recipes = Recipe::where('title', $title)
                ->orderBy('id') // keep the first one
                ->get();

            // Simpan satu, hapus sisanya
            $recipes->shift(); // remove first (keep it)

            foreach ($recipes as $recipe) {
                $recipe->delete();
                $deletedCount++;
            }
        }

        $this->info("Deleted {$deletedCount} duplicate records.");
    }
}
