@extends('layouts.app')

@section('content')
<div class="container">

    <div class="row">
        <div class="col-md-8">
            {{ $users->links() }}
        </div>
        <div class="col-md-4">
            <form class="form-inline text-right" action="" method="get" style="margin: 20px 0">
                {{ csrf_field() }}

                <div class="form-group">
                    <div class="input-group">
                        <input
                            type="text"
                            name="filter"
                            class="form-control"
                            placeholder="Search for..."
                            value="{{ request('filter') }}">
                        <span class="input-group-btn">
                            <button class="btn btn-default" type="submit">
                                <i class="glyphicon glyphicon-search"></i>
                            </button>
                        </span>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        @foreach ($users as $user)
            <div class="col-md-4">
                <a href="{{ route('users.view', $user) }}">
                    {!! $user !!}
                </a>
            </div>
        @endforeach
    </div>
</div>
@endsection
