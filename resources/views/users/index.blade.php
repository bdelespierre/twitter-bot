@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-8">
            {{ $users->links('vendor.pagination.bootstrap-4') }}
        </div>
        <div class="col-md-4">
            <form class="form-inline pull-right" action="" method="get">
                {{ csrf_field() }}

                <div class="form-group">
                    <div class="input-group">
                        <input type="text" name="filter" class="form-control" placeholder="Search for..." value="{{ request('filter') }}">
                        <span class="input-group-btn">
                            <button class="btn btn-primary" type="submit">
                                <i class="fa fa-search"></i>
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
@endsection
