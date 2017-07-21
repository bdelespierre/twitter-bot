@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-8">
            <h1>{{ '@' . $user->screen_name }} <small>#{{ $user->id }}</small></h1>
        </div>
        <div class="col-md-4 text-right">
            @if ($prev)
                <a href="{{ route('users.view', $prev->id) }}" class="btn btn-secondary">
                    <i class="fa fa-backward"></i>
                    Previous
                </a>
            @endif

            @if ($next)
                <a href="{{ route('users.view', $next->id) }}" class="btn btn-secondary">
                    Next
                    <i class="fa fa-forward"></i>
                </a>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <pre><code class="json">{{ json_encode($user->data, JSON_PRETTY_PRINT) }}</code></pre>
        </div>
    </div>
@endsection