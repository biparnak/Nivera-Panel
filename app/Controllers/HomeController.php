<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Models\Announcement;
use App\Models\Category;
use App\Models\Product;

final class HomeController extends Controller
{
    public function index(): void
    {
        $this->view('home/index', [
            'products' => Product::allActive(),
            'featuredProducts' => array_filter(Product::allActive(), fn($p) => $p->featured),
            'categories' => Category::allActive(),
            'announcements' => Announcement::allActive(3),
        ]);
    }

    public function pricing(): void
    {
        $this->view('home/pricing', [
            'categories' => Category::allActive(),
            'products' => Product::allActive(),
        ]);
    }

    public function maintenance(): void
    {
        $message = \App\Core\Settings::get('maintenance_message', 'We are performing scheduled maintenance.');
        $this->view('errors/maintenance', ['message' => $message]);
    }

    public function about(): void
    {
        $this->view('home/about');
    }
}
