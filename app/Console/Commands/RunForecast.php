<?php

namespace App\Console\Commands;

use App\Services\ForecastService;
use Illuminate\Console\Command;

class RunForecast extends Command
{
    protected $signature = 'forecast:run';
    protected $description = 'Hitung forecast & rekomendasi stok';

    public function __construct(private ForecastService $forecastService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        [$recs, $generatedAt] = $this->forecastService->run();
        $this->info('Forecast & rekomendasi dihitung: ' . $generatedAt->toDateString());
        $this->info('Total rekomendasi: ' . $recs->count());
        return Command::SUCCESS;
    }
}
