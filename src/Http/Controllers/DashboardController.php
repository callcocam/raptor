<?php
/**
 * @author:  callcocam <callcocam@gmail.com>
 * @date:    2025-06-05
 * @version: 1.0.0
 */

namespace Callcocam\Raptor\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('admin/dashboard');
    }
}