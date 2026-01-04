<?php

namespace App\Http\Controllers;

use App\Services\ForecastService;
use Illuminate\Http\Request;

class ForecastController extends Controller
{
    public function __construct(private ForecastService $forecastService)
    {
    }

    public function index(Request $request)
    {
        [$recommendations, $generatedAt] = $this->forecastService->run();

        $pageTitle = 'Forecast & Rekomendasi Stok';

        return view('forecast.index', compact('pageTitle', 'recommendations', 'generatedAt'));
    }
}
