<?php

namespace Core\Console\Commands;

use Domain\Business\Models\Category;
use Domain\Business\Models\Filter;
use Illuminate\Console\Command;

class AddFiltersToCategory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'filters:add-to-category';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create filters from array of titles and attach them to a specific category';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $categoryId = 20;
        $titles = [
            'ایرانی',
            'ترکی',
            'فست‌فود',
            'آسیایی',
            'عربی',
            'مدیترانه‌ای',
            'شیرینی/نوشیدنی',
            'برندهای زنجیره‌ای',
            'گزینه‌های سالم',
            'اقتصادی/دانشجویی',
        ];
        // $interactive = $this->option('interactive');

        // Validate category exists
        $category = Category::find($categoryId);
        if (!$category) {
            $this->error("Category with ID {$categoryId} not found.");
            return 1;
        }

        // Get titles from different sources
        // if ($interactive) {
        //     $titles = $this->getTitlesInteractively();
        // } elseif ($filePath) {
        //     if (!file_exists($filePath)) {
        //         $this->error("File {$filePath} not found.");
        //         return 1;
        //     }
        //     $titles = array_filter(array_map('trim', file($filePath, FILE_IGNORE_NEW_LINES)));
        // }

        if (empty($titles)) {
            $this->error("No titles provided. Use --titles option, --file option, or --interactive mode.");
            return 1;
        }

        $this->info("Adding " . count($titles) . " filters to category: {$category->title} (ID: {$categoryId})");

        $createdCount = 0;
        $attachedCount = 0;

        foreach ($titles as $title) {
            if (empty(trim($title))) {
                continue;
            }

            // Create or find existing filter
            $filter = Filter::firstOrCreate(
                ['title' => trim($title)],
                [
                    'title' => trim($title),
                    'status' => 1, // Active by default
                ]
            );

            if ($filter->wasRecentlyCreated) {
                $createdCount++;
                $this->line("Created filter: {$filter->title}");
            } else {
                $this->line("Found existing filter: {$filter->title}");
            }

            // Attach filter to category if not already attached
            if (!$category->filters()->where('filter_id', $filter->id)->exists()) {
                $category->filters()->attach($filter->id, [
                    'priority' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $attachedCount++;
                $this->line("Attached filter '{$filter->title}' to category '{$category->title}'");
            } else {
                $this->line("Filter '{$filter->title}' already attached to category '{$category->title}'");
            }
        }

        $this->info("Summary:");
        $this->info("- Created {$createdCount} new filters");
        $this->info("- Attached {$attachedCount} filters to category");
        $this->info("- Total filters in category: " . $category->filters()->count());

        return 0;
    }

    /**
     * Get titles interactively from user input
     */
    private function getTitlesInteractively(): array
    {
        $titles = [];

        $this->info("Enter filter titles (press Enter with empty input to finish):");

        while (true) {
            $title = $this->ask("Enter filter title");

            if (empty(trim($title))) {
                break;
            }

            $titles[] = trim($title);
        }

        return $titles;
    }
}
