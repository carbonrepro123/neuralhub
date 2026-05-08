<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Support\Portal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        if ($request->user()) {
            return redirect()->route(Portal::roleDashboardRoute($request->user()));
        }

        return view('portal.home');
    }
}
