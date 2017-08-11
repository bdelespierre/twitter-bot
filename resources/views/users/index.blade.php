@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-6">
            {{ $users->links('vendor.pagination.bootstrap-4') }}
        </div>
        <div class="col-md-6">
            <form class="form-inline pull-right" action="" method="get">
                <div class="form-group mr-2">
                    <div class="input-group">
                        <span class="input-group-addon">{{ $users->total() }} users</span>
                        <select class="custom-select" style="border-top-left-radius: 0; border-bottom-left-radius: 0" name="show">
                            <option value>Show...</option>
                            @foreach (['VIP', 'Muted', 'Blocked', 'Friends', 'Followers', 'Fans'] as $attr)
                                @if (Request::input('show') == strtolower($attr))
                                    <option value="{{ strtolower($attr) }}" selected>{{ $attr }}</option>
                                @else
                                    <option value="{{ strtolower($attr) }}">{{ $attr }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Search for..." value="{{ request('search') }}">
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
