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

    <table class="table table-users">
        <thead>
            <th>#</th>
            <th>Name</th>
            <th>Created</th>
            <th>Updated</th>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr>
                    <td><a href="{{ route('users.view', $user->id) }}">{{ $user->id }}</a></td>
                    <td>{{ $user->screen_name }}</td>
                    <td>{{ $user->created_at->diffForHumans() }}</td>
                    <td>{{ $user->updated_at->diffForHumans() }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
