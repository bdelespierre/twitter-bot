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

    <div class="row mb-3">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-addon">{{ count($items) }} items</span>
                <span class="input-group-btn">
                    <button data-toggle="modal" data-target="#add-buffer-item" class="btn btn-xlarge btn-primary">
                        <i class="fa fa-plus"></i>
                    </button>
                </span>
            </div>
        </div>
        <div class="col-md-6">
            <form action="{{ route('buffer.index') }}" method="get" class="form-inline pull-right">
                <div class="form-group mr-2">
                    <select class="custom-select" name="show">
                        <option value>Show...</option>
                        @foreach (['trashed' => 'Trashed', 'only-trashed' => 'Only trashed'] as $val => $attr)
                            @if (Request::input('show') == $val)
                                <option value="{{ $val }}" selected>{{ $attr }}</option>
                            @else
                                <option value="{{ $val }}">{{ $attr }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="input-group">
                    <input class="form-control"
                        type="text"
                        name="search"
                        placeholder="Search"
                        value="{{ Request::input('search') }}">
                    <span class="input-group-btn">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                    </span>
                </div>
            </form>
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