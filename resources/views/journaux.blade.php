@extends('layouts.app')

@section('content')
<div class="container">

    <div class="row">
        <div class="col-md-6">
            {{ $journaux->links() }}
        </div>
        <div class="col-md-6">
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


    <table class="table table-log">
        <thead>
            <th>Date</th>
            <th>Level</th>
            <th>Namespace</th>
            <th>Message</th>
        </thead>
        <tbody>
            @foreach ($journaux as $journal)
                <tr class="{{ $journal->css }}">
                    <td style="white-space: nowrap" title="{{ $journal->date }}">{{ $journal->date->diffForHumans() }}</td>
                    <td style="white-space: nowrap">{{ $journal->level }}</td>
                    <td style="white-space: nowrap">{{ $journal->namespace }}</td>
                    <td>{{ $journal->message }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
