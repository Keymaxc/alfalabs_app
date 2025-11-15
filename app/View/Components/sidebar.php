<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class sidebar extends Component
{
    /**
     * Create a new component instance.
     */
    public $links;

    public function __construct()
    {
        $this->links = [
            [
                'label' => 'Dashboard Analitik',
                'route' => 'home',
                'is_active' => request()->routeIs('home'),
                'icon' => 'fas fa-chart-line',
                'is_dropdown' => false,
            ],
            [
                'label' => 'Master Data',
                'route' => '#',
                'is_active' => request()->routeIs('master-data.*'),
                'icon' => 'fas fa-cloud',
                'is_dropdown' => true,
                'item' => [
                    [
                        'label' => 'Kategori Produk',
                        'route' => 'master-data.kategori-produk.index',
                    ],
                ],
            ],
            // 🔹 Dropdown Transaksi
            [
                'label' => 'Transaksi',
                'route' => '#',
                'is_active' => request()->routeIs('transaksi.*'),
                'icon' => 'fas fa-shopping-cart', 
                'is_dropdown' => true,
                'item' => [
                    [
                        'label' => 'Input Transaksi',
                        'route' => 'transaksi.masuk',
                    ],
                    [
                        'label' => 'Laporan Transaksi',
                        'route' => 'transaksi.index',
                    ],
                ],
            ],
        ];
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.sidebar');
    }
}
