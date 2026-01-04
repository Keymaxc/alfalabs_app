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
        $user = auth()->user();

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
                'label' => 'Produk & Stok',
                'route' => '#',
                'is_active' => request()->routeIs('master-data.*'),
                'icon' => 'fas fa-boxes',
                'is_dropdown' => true,
                'roles' => ['admin', 'superadmin'], // admin manages product data
                'item' => [
                    [
                        'label' => 'Kelola Produk',
                        'route' => 'master-data.kategori-produk.index',
                        'roles' => ['admin', 'superadmin'],
                    ],
                ],
            ],
            // 🔹 Penjualan
            [
                'label' => 'Penjualan',
                'route' => '#',
                'is_active' => request()->routeIs('transaksi.masuk') || request()->routeIs('transaksi.index') || request()->routeIs('transaksi.export.pdf') || request()->routeIs('transaksi.struk.*'),
                'icon' => 'fas fa-cart-plus', 
                'is_dropdown' => true,
                'roles' => ['admin', 'staff', 'superadmin'],
                'item' => [
                    [
                        'label' => 'Input Penjualan',
                        'route' => 'transaksi.masuk',
                        'roles' => ['admin', 'superadmin'], // admin adds new orders/products
                        'icon' => null,
                    ],
                    [
                        'label' => 'Daftar Penjualan',
                        'route' => 'transaksi.index',
                        'roles' => ['staff', 'admin', 'superadmin'], // staff & admin view transactions
                        'icon' => null,
                    ],
                ],
            ],
            // 🔹 Stok Masuk
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
                        'roles' => ['admin', 'superadmin'], // admin adds stock
                        'icon'  => 'fas fa-arrow-circle-down',
                    ],
                    [
                        'label' => 'Laporan Stok Masuk',
                        'route' => 'transaksi.stok-masuk.laporan',
                        'roles' => ['staff', 'admin', 'superadmin'], // staff & admin view stock
                        'icon'  => 'fas fa-clipboard-list',
                    ],
                ],
            ],
            [
                'label'      => 'Pengerjaan Order',
                'route'      => '#',
                'is_active'  => request()->routeIs('pengerjaan.*'),
                'icon'       => 'fas fa-tasks',
                'is_dropdown'=> true,
                'roles' => ['admin', 'staff', 'superadmin'],
                'item'       => [
                    [
                        'label' => 'Order Berjalan',
                        'route' => 'pengerjaan.berjalan',
                        'roles' => ['admin', 'staff', 'superadmin'], // admin & staff produksi pantau pekerjaan aktif
                    ],
                    [
                        'label' => 'Order Selesai',
                        'route' => 'pengerjaan.selesai',
                        'roles' => ['admin', 'superadmin'], // admin: tabel pesanan selesai
                    ],
                ],
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

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.sidebar');
    }
}
