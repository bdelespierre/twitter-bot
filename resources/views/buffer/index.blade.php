@extends('layouts.app')

@section('content')
<div class="container">
    <form class="form" action="{{ route('buffer.add') }}" method="post">
        {{ csrf_field() }}

        <div class="form-group">
            <label for="input1">URLs</label>
            <textarea class="form-control" name="urls" id="input1" placeholder="URLs"></textarea>
        </div>

        <button type="submit" class="btn btn-default">Submit</button>
    </form>

    <div class="row">
        @foreach ($items as $item)
            <div class="col-md-3">
                {!! $item->card !!}
            </div>
        @endforeach
    </div>
</div>
@endsection