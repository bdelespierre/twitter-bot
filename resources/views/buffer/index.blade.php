@extends('layouts.app')

@section('content')
    @component('components.modal', ['id' => 'add-buffer-item', 'action' => route('buffer.add')])
        @slot('title')
            New item(s)
        @endslot

        <div class="form-group">
            <label for="input-urls">URL(s)</label>
            <textarea class="form-control" name="urls" id="input-urls"></textarea>
        </div>
    @endcomponent

    <div class="btn-toolbar my-3">
        <div class="btn-group mr-2">
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                    <i class="fa fa-trash-o"></i> Trashed
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="{{ route('buffer.index', ['trashed' => 1] + Request::query()) }}">
                        Show
                    </a>
                    <a class="dropdown-item" href="{{ route('buffer.index', ['trashed' => 1, 'only' => 1] + Request::query()) }}">
                        Only
                    </a>
                    <a class="dropdown-item" href="{{ route('buffer.index', array_except(Request::query(), ['trashed', 'only'])) }}">
                        Hide
                    </a>
                </div>
            </div>
        </div>
        <div class="btn-group mr-2">
            <a href="#" class="btn btn-secondary active"><i class="fa fa-th-large"></i></a>
            <a href="#" class="btn btn-secondary"><i class="fa fa-th-list"></i></a>
        </div>
        <form action="{{ route('buffer.index', Request::query()) }}" method="get" class="form-inline mr-2">
            <div class="input-group">
                <input class="form-control"
                    type="text"
                    name="search"
                    placeholder="Search"
                    value="{{ Request::input('search') }}">
                <button type="submit" class="input-group-addon"><i class="fa fa-search"></i></button>
            </div>
        </form>
        <div class="btn-group">
            <button data-toggle="modal" data-target="#add-buffer-item" class="btn btn-xlarge btn-success btn-block">
                Add <i class="fa fa-plus"></i>
            </button>
        </div>
    </div>

    <div class="card-columns">
        @each('buffer._item', $items, 'item')

        <div class="card">
            <button data-toggle="modal" data-target="#add-buffer-item" class="btn btn-xlarge btn-primary btn-block" style="height: 250px; font-size: 100px">
                <i class="fa fa-plus"></i>
            </button>
        </div>
    </div>
@endsection