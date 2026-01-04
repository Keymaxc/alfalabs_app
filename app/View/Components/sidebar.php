<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class sidebar extends Component
{
    public $links;

    public function __construct()
    {
        $user = auth()->user();

        // Urutan: Dashboard -> Penjualan -> Pengerjaan -> Stok Masuk -> Produk & Stok -> Forecast -> Laporan
        $this->links = [
            [
                'label' => 'Dashboard',
                'route' => 'home',
                'is_active' => request()->routeIs('home'),
                'icon' => 'fas fa-chart-line',
                'is_dropdown' => false,
                'roles' => ['admin', 'staff', 'superadmin'],
            ],
            [
                'label' => 'Penjualan',
                'route' => '#',
                'is_active' => request()->routeIs('transaksi.masuk')
                    || request()->routeIs('transaksi.index')
                    || request()->routeIs('transaksi.export.pdf')
                    || request()->routeIs('transaksi.struk.*'),
                'icon' => 'fas fa-cart-plus',
                'is_dropdown' => true,
                'roles' => ['admin', 'staff', 'superadmin'],
                'item' => [
                    [
                        'label' => 'Input Penjualan',
                        'route' => 'transaksi.masuk',
                        'roles' => ['admin', 'superadmin'],
                    ],
                    [
                        'label' => 'Daftar Penjualan',
                        'route' => 'transaksi.index',
                        'roles' => ['staff', 'admin', 'superadmin'],
                    ],
                ],
            ],
            [
                'label'      => 'Pengerjaan Order',
                'route'      => '#',
                'is_active'  => request()->routeIs('pengerjaan.*'),
                'icon'       => 'fas fa-tasks',
                'is_dropdown'=> true,
                'roles'      => ['admin', 'staff', 'superadmin'],
                'item'       => [
                    [
                        'label' => 'Order Berjalan',
                        'route' => 'pengerjaan.berjalan',
                        'roles' => ['admin', 'staff', 'superadmin'],
                    ],
                    [
                        'label' => 'Order Selesai',
                        'route' => 'pengerjaan.selesai',
                        'roles' => ['admin', 'superadmin'],
                    ],
                ],
            ],
            [
                'label' => 'Stok Masuk',
                'route' => '#',
                'is_active' => request()->routeIs('transaksi.stok-masuk') || request()->routeIs('transaksi.stok-masuk.*'),
                'icon' => 'fas fa-exchange-alt',
                'is_dropdown' => true,
                'roles' => ['admin', 'staff', 'superadmin'],
                'item' => [
                    [
                        'label' => 'Input Stok',
                        'route' => 'transaksi.stok-masuk',
                        'roles' => ['admin', 'superadmin'],
                    ],
                    [
                        'label' => 'Laporan Stok Masuk',
                        'route' => 'transaksi.stok-masuk.laporan',
                        'roles' => ['staff', 'admin', 'superadmin'],
                    ],
                ],
            ],
            [
                'label' => 'Produk & Stok',
                'route' => '#',
                'is_active' => request()->routeIs('master-data.*'),
                'icon' => 'fas fa-boxes',
                'is_dropdown' => true,
                'roles' => ['admin', 'superadmin'],
                'item' => [
                    [
                        'label' => 'Kelola Produk',
                        'route' => 'master-data.kategori-produk.index',
                        'roles' => ['admin', 'superadmin'],
                    ],
                ],
            ],
            [
                'label' => 'Forecast Stok',
                'route' => 'forecast.index',
                'is_active' => request()->routeIs('forecast.index'),
                'icon' => 'fas fa-chart-bar',
                'is_dropdown' => false,
                'roles' => ['admin', 'staff', 'superadmin'],
            ],
            [
                'label' => 'Laporan Keuangan (PDF)',
                'route' => 'laporan.keuangan.pdf',
                'is_active' => request()->routeIs('laporan.keuangan.pdf'),
                'icon' => 'fas fa-file-invoice-dollar',
                'is_dropdown' => false,
                'roles' => ['superadmin'],
            ],
        ];

        if ($user) {
            $this->links = $this->filterLinksByRole($this->links, $user->role);
        }
    }

    private function filterLinksByRole(array $links, string $role): array
    {
        $filtered = [];

        foreach ($links as $link) {
            $roles = $link['roles'] ?? [];
            if ($roles && ! in_array($role, $roles, true) && $role !== 'superadmin') {
                continue;
            }

            if (! empty($link['item'])) {
                $link['item'] = $this->filterLinksByRole($link['item'], $role);
            }

            if (($link['is_dropdown'] ?? false) && empty($link['item'])) {
                continue;
            }

            $filtered[] = $link;
        }

        return $filtered;
    }

    public function render(): View|Closure|string
    {
        return view('components.sidebar');
    }
}
