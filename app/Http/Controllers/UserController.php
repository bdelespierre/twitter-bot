<?php

namespace App\Http\Controllers;

use App\Models\Twitter\User as TwitterUser;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = TwitterUser::withScreenName($request->input('search'))
            ->orderBy('updated_at', 'desc')
            ->when($request->has('show'), function($query) use ($request) {
                return $query->only($request->get('show'));
            })
            ->when($request->has('hide'), function($query) use ($request) {
                return $query->except($request->get('hide'));
            })
            ->paginate($request->input('per_page', 15));

        return view('users.index', compact('users'));
    }

    public function view(TwitterUser $user)
    {
        $next = TwitterUser::orderBy('id')->where('id', '>', $user->id)->first();
        $prev = TwitterUser::orderBy('id')->where('id', '<', $user->id)->first();

        return view('users.view', compact('user', 'next', 'prev'));
    }
}
