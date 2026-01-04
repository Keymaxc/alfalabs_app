<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class SarimaForecaster
{
    public function __construct(private ?string $pythonBinary = null)
    {
        $this->pythonBinary = $pythonBinary ?: (string) env('PYTHON_BINARY', 'python');
    }

    /**
     * Forecast demand using a SARIMA helper script (Python/statsmodels).
     * Falls back to a flat average if the script/dependencies are missing.
     *
     * @return float[]
     */
    public function forecast(array $series, int $periods = 7, int $seasonalPeriod = 7, ?float $fallbackValue = null): array
    {
        $values   = array_values(array_map(static fn ($v) => (float) $v, $series));
        $fallback = $fallbackValue ?? $this->average($values);

        if ($periods <= 0) {
            return [];
        }

        if (count($values) < max(4, $seasonalPeriod * 2)) {
            return array_fill(0, $periods, $fallback);
        }

        $scriptPath = base_path('app/Services/Forecasting/sarima_runner.py');
        if (! is_file($scriptPath)) {
            return array_fill(0, $periods, $fallback);
        }

        $payload = json_encode($values);

        $process = new Process([
            $this->pythonBinary,
            $scriptPath,
            $payload,
            (string) $periods,
            (string) $seasonalPeriod,
        ]);
        $process->setTimeout(10);

        try {
            $process->run();

            if (! $process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $output = json_decode($process->getOutput(), true, 512, JSON_THROW_ON_ERROR);
            $predictions = $output['predictions'] ?? null;

            if (is_array($predictions) && count($predictions) >= $periods) {
                return array_map(
                    static fn ($v) => max(0, (float) $v),
                    array_slice($predictions, 0, $periods)
                );
            }
        } catch (\Throwable $e) {
            Log::warning('SARIMA forecast fallback', [
                'error'  => $e->getMessage(),
                'output' => $process->getErrorOutput(),
            ]);
        }

        return array_fill(0, $periods, $fallback);
    }

    private function average(array $series): float
    {
        return empty($series) ? 0.0 : array_sum($series) / count($series);
    }
}
