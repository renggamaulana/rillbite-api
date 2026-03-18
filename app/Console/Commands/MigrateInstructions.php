<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateInstructions extends Command
{
    protected $signature   = 'recipes:migrate-instructions {--dry-run : Preview changes without writing to database}';
    protected $description = 'Convert HTML instructions (<ol><li>...</li></ol>) to numbered plain text format';

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        $this->info('🔍 Fetching recipes with instructions...');
        $this->newLine();

        $recipes = DB::table('recipes')
            ->whereNotNull('instructions')
            ->where('instructions', '!=', '')
            ->select('id', 'title', 'instructions')
            ->orderBy('id')
            ->get();

        $toMigrate = $recipes->filter(fn($r) => $this->isHtml($r->instructions));

        $this->line("📋 Total recipes         : <fg=white>{$recipes->count()}</>");
        $this->line("🔄 Need migration (HTML) : <fg=yellow>{$toMigrate->count()}</>");
        $this->line("✅ Already plain text    : <fg=green>" . ($recipes->count() - $toMigrate->count()) . "</>");
        $this->newLine();

        if ($toMigrate->isEmpty()) {
            $this->info('Nothing to migrate. All instructions are already plain text.');
            return self::SUCCESS;
        }

        // ── Preview table ──────────────────────────────────────────────────
        $this->line('─── Preview (up to 5 recipes) ───────────────────────────────');

        $toMigrate->take(5)->each(function ($recipe) {
            $converted = $this->convertToNumberedSteps($recipe->instructions);
            $before    = substr(strip_tags($recipe->instructions), 0, 80);
            $after     = substr($converted, 0, 120);

            $this->newLine();
            $this->line("  <fg=cyan>ID {$recipe->id}:</> {$recipe->title}");
            $this->line("  <fg=red>BEFORE:</> {$before}...");
            $this->line("  <fg=green>AFTER :</>");
            collect(explode("\n", $after))->each(fn($l) => $this->line("    $l"));
        });

        $this->newLine();
        $this->line('─────────────────────────────────────────────────────────────');
        $this->newLine();

        if ($isDryRun) {
            $this->warn('🚫 --dry-run: No changes written to the database.');
            return self::SUCCESS;
        }

        // ── Confirm ────────────────────────────────────────────────────────
        if (! $this->confirm("Migrate {$toMigrate->count()} recipe(s)?", true)) {
            $this->line('Aborted.');
            return self::SUCCESS;
        }

        // ── Migrate ────────────────────────────────────────────────────────
        $ok   = 0;
        $fail = 0;

        DB::beginTransaction();

        try {
            foreach ($toMigrate as $recipe) {
                try {
                    $converted = $this->convertToNumberedSteps($recipe->instructions);

                    DB::table('recipes')
                        ->where('id', $recipe->id)
                        ->update(['instructions' => $converted]);

                    $this->line("  ✅ ID " . str_pad($recipe->id, 5) . " {$recipe->title}");
                    $ok++;
                } catch (\Throwable $e) {
                    $this->error("  ❌ ID {$recipe->id} failed: {$e->getMessage()}");
                    $fail++;
                }
            }

            DB::commit();

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("💥 Fatal error, rolled back: {$e->getMessage()}");
            return self::FAILURE;
        }

        $this->newLine();
        $this->info("🎉 Done!  ✅ {$ok} migrated   ❌ {$fail} errors");

        return self::SUCCESS;
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    /** Returns true if the string contains HTML tags */
    private function isHtml(string $text): bool
    {
        return (bool) preg_match('/<(ol|ul|li|p|br|div|h[1-6])[^>]*>/i', $text);
    }

    /**
     * Convert HTML instructions to numbered plain-text steps.
     * Priority: <li> items → <p> items → raw strip fallback.
     */
    private function convertToNumberedSteps(string $html): string
    {
        // 1. Extract <li> contents  (covers <ol> and <ul>)
        if (preg_match_all('/<li[^>]*>(.*?)<\/li>/is', $html, $liMatches)) {
            $steps = array_values(array_filter(
                array_map(fn($s) => trim($this->stripTags($s)), $liMatches[1])
            ));

            if (! empty($steps)) {
                return implode("\n", array_map(
                    fn($text, $i) => ($i + 1) . ". $text",
                    $steps,
                    array_keys($steps)
                ));
            }
        }

        // 2. Extract <p> contents
        if (preg_match_all('/<p[^>]*>(.*?)<\/p>/is', $html, $pMatches)) {
            $steps = array_values(array_filter(
                array_map(fn($s) => trim($this->stripTags($s)), $pMatches[1])
            ));

            if (! empty($steps)) {
                return implode("\n", array_map(
                    fn($text, $i) => ($i + 1) . ". $text",
                    $steps,
                    array_keys($steps)
                ));
            }
        }

        // 3. Fallback: just strip tags
        return trim($this->stripTags($html));
    }

    private function stripTags(string $html): string
    {
        $html = preg_replace('/<br\s*\/?>/i', "\n", $html);
        $html = strip_tags($html);
        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim($html);
    }
}
