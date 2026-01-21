<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function viewForRole(string $view, array $data = [])
    {
        $user = auth()->user();
        if ($user && $user->role === 'team') {
            $teamView = 'team.' . $view;
            if (view()->exists($teamView)) {
                return view($teamView, $data);
            }
        }

        return view($view, $data);
    }
}
