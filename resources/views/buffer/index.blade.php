@extends('layouts.app')

@section('content')
    @component('components.modal', ['id' => 'add-buffer-item', 'action' => route('buffer.add')])
        @slot('title')
            New item
        @endslot

        <div class="form-group">
            <label for="input-urls">URL(s)</label>
            <textarea class="form-control" name="urls" id="input-urls" placeholder="..."></textarea>
        </div>
    @endcomponent

    <button data-toggle="modal" data-target="#add-buffer-item" class="btn btn-xlarge btn-primary">
        <i class="fa fa-plus"></i>
    </button>

    <div class="row">
        @foreach (range(0,3) as $i)
            <div class="col-md-3">
                @foreach ($items as $item)
                    @if ($loop->index % 4 == $i)
                        <div id="{{ $item->id }}" data-url="{{ $item->url }}">
                            {!! $item->card !!}
                        </div>
                    @endif
                @endforeach
            </div>
        @endforeach
    </div>
@endsection