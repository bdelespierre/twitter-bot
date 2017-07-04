@extends('layouts.app')

@section('content')
<div class="container" role="users.view" id="user:{{ $user->id }}">
    <div class="row">
        <div class="col-md-8">
            <h1 style="margin: 0">{{ '@' . $user->screen_name }} <small>#{{ $user->id }}</small></h1>
        </div>
        <div class="col-md-4 text-right">
            @if ($prev)
                <a href="{{ route('users.view', $prev->id) }}" class="btn btn-default">prev</a>
            @endif

            @if ($next)
                <a href="{{ route('users.view', $next->id) }}" class="btn btn-default">next</a>
            @endif
        </div>
    </div>

    <hr>

    <div class="row">
        <div class="col-md-12">
            <pre><code class="json">{{ json_encode($user->data, JSON_PRETTY_PRINT) }}</code></pre>
        </div>
    </div>
</div>
@endsection