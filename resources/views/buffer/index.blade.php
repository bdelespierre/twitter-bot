@extends('layouts.app')

@section('content')
    <form action="{{ route('buffer.add') }}" method="post">
        {{ csrf_field() }}

        <div class="form-group">
            <label for="input-urls">URLs</label>
            <textarea class="form-control" name="urls" id="input-urls" placeholder="URLs"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Submit</button>
    </form>

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