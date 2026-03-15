<?php

namespace App\Console\Commands;

use App\Models\Recipe;
use App\Services\NutritionService;
use Illuminate\Console\Command;

class SeedRecipeNutrition extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'recipes:seed-nutrition
                            {--force : Re-generate nutrition even for recipes that already have it}
                            {--dry-run : Preview what would be seeded without saving}
                            {--chunk=100 : Number of recipes to process per batch}';

    /**
     * The console command description.
     */
    protected $description = 'Generate and seed estimated nutrition data for recipes that currently have none.';

    public function __construct(
        private NutritionService $nutritionService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $force   = $this->option('force');
        $dryRun  = $this->option('dry-run');
        $chunk   = (int) $this->option('chunk');

        // ----------------------------------------------------------------
        // Stats before
        // ----------------------------------------------------------------
        $totalRecipes   = Recipe::count();
        $withNutrition  = Recipe::has('nutrition')->count();
        $withoutNutrition = $totalRecipes - $withNutrition;

        $this->info("=================================================");
        $this->info(" Rillbite – Recipe Nutrition Seeder");
        $this->info("=================================================");
        $this->line("  Total recipes       : {$totalRecipes}");
        $this->line("  With nutrition      : {$withNutrition}");
        $this->line("  Without nutrition   : {$withoutNutrition}");
        $this->newLine();

        if ($force) {
            $this->warn("  --force flag set: ALL recipes will be re-processed.");
        }

        if ($dryRun) {
            $this->warn("  --dry-run flag set: No data will be saved.");
        }

        $this->newLine();

        // ----------------------------------------------------------------
        // Confirm before mass-update
        // ----------------------------------------------------------------
        $toProcess = $force ? $totalRecipes : $withoutNutrition;

        if ($toProcess === 0) {
            $this->info("✓ All recipes already have nutrition data. Nothing to do.");
            return self::SUCCESS;
        }

        if (!$dryRun && !$this->confirm("Proceed to seed nutrition for {$toProcess} recipe(s)?", true)) {
            $this->line("Aborted.");
            return self::SUCCESS;
        }

        // ----------------------------------------------------------------
        // Process in chunks
        // ----------------------------------------------------------------
        $query = $force
            ? Recipe::query()
            : Recipe::doesntHave('nutrition');

        $processed = 0;
        $failed    = 0;
        $bar       = $this->output->createProgressBar($toProcess);
        $bar->start();

        $query->chunkById($chunk, function ($recipes) use ($dryRun, &$processed, &$failed, $bar, $force) {
            foreach ($recipes as $recipe) {
                try {
                    $estimate = $this->nutritionService->generateEstimate($recipe);

                    if ($dryRun) {
                        $this->newLine();
                        $this->line(sprintf(
                            "  [DRY-RUN] Recipe #%d \"%s\" → cal:%.1f, prot:%.1f, fat:%.1f, carbs:%.1f",
                            $recipe->id,
                            str($recipe->title)->limit(40),
                            $estimate['calories'],
                            $estimate['protein'],
                            $estimate['fat'],
                            $estimate['carbohydrates'],
                        ));
                    } else {
                        if ($force) {
                            // Upsert even if exists
                            $this->nutritionService->upsert($recipe->id, $estimate);
                        } else {
                            \App\Models\Nutrition::create(
                                array_merge(['recipe_id' => $recipe->id], $estimate)
                            );
                        }
                    }

                    $processed++;
                } catch (\Throwable $e) {
                    $failed++;
                    $this->newLine();
                    $this->error("  Failed for recipe #{$recipe->id}: " . $e->getMessage());
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);

        // ----------------------------------------------------------------
        // Summary
        // ----------------------------------------------------------------
        $this->info("=================================================");
        $this->info($dryRun ? " DRY-RUN complete" : " Seeding complete");
        $this->line("  Processed : {$processed}");
        $this->line("  Failed    : {$failed}");
        $this->info("=================================================");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
