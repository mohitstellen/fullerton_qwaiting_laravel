<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PlatoAPIService;
use App\Models\Company; // Adjust to your model name
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportCorporateFromPlato extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plato:import-corporate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import corporate data from Plato API';

    protected $platoAPIService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(PlatoAPIService $platoAPIService)
    {
        parent::__construct();
        $this->platoAPIService = $platoAPIService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $this->info('Starting corporate data import from Plato API...');
        $startTime = now();

        $currentPage = 1;
        $skip = 0;
        $totalProcessed = 0;
        $totalAdded = 0;
        $totalUpdated = 0;
        $totalSkipped = 0;
        $totalErrors = 0;
        $maxConsecutiveErrors = 3; // Stop after 3 consecutive errors
        $consecutiveErrors = 0;

        while (true) {
            try {
                $this->line("Fetching page {$currentPage}...");

                $response = $this->platoAPIService->fetchCorporateFromPlato($skip, $currentPage);

                if ($response->successful()) {
                    $data = $response->json();

                    // Check if response has data - API returns empty array [] when no more pages
                    if (!is_array($data) || empty($data)) {
                        $this->info("✓ Page {$currentPage}: Empty response received - no more data available");
                        $this->info("✓ Stopping: Reached end of data");
                        break;
                    }

                    $records = $data;
                    $recordCount = count($records);

                    $this->info("Page {$currentPage}: Processing {$recordCount} records...");

                    // Process records
                    foreach ($records as $corporate) {
                        try {
                            $result = $this->processCorporateRecord($corporate);

                            if ($result === 'added') {
                                $totalAdded++;
                            } elseif ($result === 'updated') {
                                $totalUpdated++;
                            } else {
                                $totalSkipped++;
                            }

                            $totalProcessed++;
                        } catch (\Exception $e) {
                            $totalErrors++;
                            $this->error("Error processing record: " . $e->getMessage());
                            Log::error("Error processing corporate record", [
                                'corporate_id' => $corporate['_id'] ?? 'unknown',
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString()
                            ]);
                        }
                    }

                    $this->info("✓ Page {$currentPage} completed: Added: {$totalAdded}, Updated: {$totalUpdated}, Skipped: {$totalSkipped}, Errors: {$totalErrors}");

                    
                    $currentPage++;
                    $consecutiveErrors = 0; // Reset consecutive errors on success
                } else {
                    $consecutiveErrors++;
                    $totalErrors++;
                    $this->error("API Error on page {$currentPage}: Status " . $response->status());
                    $this->error("Response: " . $response->body());
                    Log::error("API Error on page {$currentPage}", [
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);

                    if ($consecutiveErrors >= $maxConsecutiveErrors) {
                        $this->error("✗ Stopping: {$maxConsecutiveErrors} consecutive API errors");
                        break;
                    }

                    // Skip to next page on error
                    $currentPage++;
                    $this->warn("Continuing to next page after error...");
                }

                // Small delay to avoid rate limiting
                usleep(2000000); // 2 seconds delay

            } catch (\Exception $e) {
                $consecutiveErrors++;
                $totalErrors++;
                $this->error("Exception on page {$currentPage}: " . $e->getMessage());
                Log::error("Exception on page {$currentPage}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                if ($consecutiveErrors >= $maxConsecutiveErrors) {
                    $this->error("✗ Stopping: {$maxConsecutiveErrors} consecutive exceptions");
                    break;
                }

                // Skip to next page on exception
                $currentPage++;
                $this->warn("Continuing to next page after exception...");
            }
        }

        $endTime = now();
        $duration = $endTime->diffInSeconds($startTime);

        // Final summary
        $this->newLine();
        $this->info('═══════════════════════════════════════════');
        $this->info('Import Summary:');
        $this->info('═══════════════════════════════════════════');
        $this->info("Total Pages Processed: {$currentPage}");
        $this->info("Total Records Processed: {$totalProcessed}");
        $this->info("Records Added: {$totalAdded}");
        $this->info("Records Updated: {$totalUpdated}");
        $this->info("Records Skipped: {$totalSkipped}");
        $this->info("Records with Errors: {$totalErrors}");
        $this->info("Duration: {$duration} seconds");
        $this->info('═══════════════════════════════════════════');

        Log::info('Corporate import completed', [
            'total_pages' => $currentPage,
            'total_processed' => $totalProcessed,
            'added' => $totalAdded,
            'updated' => $totalUpdated,
            'skipped' => $totalSkipped,
            'errors' => $totalErrors,
            'duration_seconds' => $duration
        ]);

        return 0;
    }

    /**
     * Process a single corporate record
     *
     * @param array $corporate
     * @return string 'added', 'updated', or 'skipped'
     */
    protected function processCorporateRecord($corporate)
    {
        // Extract the corporate ID
        $corporateId = $corporate['_id'] ?? null;

        if (!$corporateId) {
            Log::warning('Corporate record missing _id', ['corporate' => $corporate]);
            return 'skipped';
        }

        // Check if record exists
        $existingCompany = Company::where('company_name', $corporate['name'])->first();

        // Prepare company data - adjust these fields according to your database schema and API response
        $companyData = [
            'team_id' => 3,
            'location_id' => 101,
            'company_name' => $corporate['name'] ?? null,
            'email' => $corporate['email'] ?? null,
            'phone' => $corporate['phone'] ?? null,
            'address' => $corporate['address'] ?? null,
            'postal_code' => $corporate['postal'] ?? null,
            'contact_person1_name' => $corporate['person'] ?? null,
            'contact_person1_email' => $corporate['email'] ?? null,
            'contact_person1_phone' => $corporate['telephone'] ?? null,
            'status' => $corporate['status'] ?? 'active',
            'notes' => $corporate['remarks'] ?? null,
        ];

        if ($existingCompany) {
            $existingCompany->update($companyData);
            return 'updated';
        } else {
            Company::create($companyData);
            return 'added';
        }
    }
}
