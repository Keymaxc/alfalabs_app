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
                'label' => 'Dashboard Analitik',
                'route' => 'home',
                'is_active' => request()->routeIs('home'),
                'icon' => 'fas fa-chart-line',
                'is_dropdown' => false,
                'roles' => ['admin', 'staff', 'superadmin'],
            ],
            [
                'label' => 'Master Data',
                'route' => '#',
                'is_active' => request()->routeIs('master-data.*'),
                'icon' => 'fas fa-cloud',
                'is_dropdown' => true,
                'roles' => ['admin', 'superadmin'],
                'item' => [
                    [
                        'label' => 'Kategori Produk',
                        'route' => 'master-data.kategori-produk.index',
                        'roles' => ['admin', 'superadmin'],
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
                'roles' => ['admin', 'staff', 'superadmin'],
                'item' => [
                    [
                        'label' => 'Input Transaksi',
                        'route' => 'transaksi.masuk',
                        'roles' => ['admin', 'staff', 'superadmin'],
                    ],
                    [
                        'label' => 'Stok Masuk',
                        'route' => 'transaksi.stok-masuk',
                        'roles' => ['admin', 'staff', 'superadmin'],
                    ],
                    [
                        'label' => 'Laporan Transaksi',
                        'route' => 'transaksi.index',
                        'roles' => ['admin', 'staff', 'superadmin'],
                    ],
                ],
            ],
            [
                'label'      => 'Pengerjaan',
                'route'      => '#',
                'is_active'  => request()->routeIs('pengerjaan.*'),
                'icon'       => 'fas fa-tasks',
                'is_dropdown'=> true,
                'roles' => ['admin', 'staff', 'superadmin'],
                'item'       => [
                    [
                        'label' => 'Pengerjaan Berjalan',
                        'route' => 'pengerjaan.berjalan',
                        'roles' => ['admin', 'staff', 'superadmin'],
                    ],
                    [
                        'label' => 'Pengerjaan Selesai',
                        'route' => 'pengerjaan.selesai',
                        'roles' => ['admin', 'staff', 'superadmin'],
                    ],
                ],
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
