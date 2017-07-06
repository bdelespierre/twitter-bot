<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/schedule', function () {
    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    }

    App\Journal::notice("[schedule:run] started");
    Artisan::call('schedule:run');
});

Route::get('/log', ['as' => 'logs.index', function (Request $request) {
    $journaux = App\Journal::where('message', 'like', '%' . $request->input('filter') . '%')
        ->orderBy('date', 'desc')
        ->paginate($request->input('per_page', 15));

    return view('journaux', compact('journaux'));
}]);

Route::get('/users', ['as' => 'users.index', function (Request $request) {
    $users = App\Models\Twitter\User::withScreenName($request->input('filter'))
        ->orderBy('updated_at', 'desc')
        ->paginate($request->input('per_page', 15));

    return view('users.index', compact('users'));
}]);

Route::get('/users/following', function (Request $request) {
    $users = App\Models\Twitter\User::following()
        ->where('screen_name', 'like', '%' . $request->input('filter') . '%')
        ->orderBy('updated_at', 'desc')
        ->paginate($request->input('per_page', 15));

    return view('users.index', compact('users'));
});

Route::get('/users/{id}', ['as' => 'users.view', function (Request $request, $id) {
    $user = App\Models\Twitter\User::findOrFail($id);
    $next = App\Models\Twitter\User::orderBy('id')->where('id', '>', $id)->first();
    $prev = App\Models\Twitter\User::orderBy('id')->where('id', '<', $id)->first();

    return view('users.view', compact('user', 'next', 'prev'));
}]);

Auth::routes();

/*
|--------------------------------------------------------------------------
| Development Routes
|--------------------------------------------------------------------------
|
| Routes below are for development purposes and should NEVER be viewable in
| production without a good security layer.
|
*/

if (config('app.env') == 'production') {
    return;
}

Route::post('/tweet', function(Request $request) {
    if ($request->hasFile('media')) {
        $uploaded = Twitter::uploadMedia([
            'media' => File::get($request->file('media')),
        ]);

        if ($uploaded->media_id_string) {
            $media_ids = $uploaded->media_id_string;
        }
    }

    return Twitter::postTweet([
        'status' => $request->input('content'),
        'format' => 'json',
    ] + compact('media_ids'));
});

Route::get('/timeline', function (Request $request) {
    return Twitter::getHomeTimeline([
        'count' => $request->input('count', 20),
        'format' => 'json'
    ]);
});

Route::get('/mentions', function (Request $request) {
    return Twitter::getMentionsTimeline([
        'count' => $request->input('count', 20),
        'format' => 'json'
    ]);
});

Route::get('/credentials', function () {
    return (array) Twitter::getCredentials([
        'include_email' => 'true',
    ]);
});

Route::get('/{user}/timeline', function ($user, Request $request) {
    return Twitter::getUserTimeline([
        'screen_name' => $user,
        'count' => $request->input('count', 20),
        'format' => 'json',
    ]);
});

Route::get('/followers', function (Request $request) {
    if ($request->has('cursor')) {
        $cursor = $request->input('cursor');
    }

    return Twitter::getFollowersIds([
        'count' => 5000,
        'format' => 'array',
    ] + compact('cursor'));
});

Route::get('/following', function (Request $request) {
    if ($request->has('cursor')) {
        $cursor = $request->input('cursor');
    }

    return Twitter::getFriendsIds([
        'count' => 5000,
        'format' => 'array',
    ] + compact('cursor'));
});

Route::get('/fans', function () {
    $args = ['format' => 'array'];
    $followers = Twitter::getFollowersIds($args)['ids'];
    $following = Twitter::getFriendsIds($args)['ids'];

    return array_values(array_diff($followers, $following));
});
