@extends('layouts.app')

@section('content')
    @if (!Request::has('display') || Request::input('display') == 'table')
        <table class="table" rel="container">
            <thead>
                <th>#</th>
                <th>Item</th>
                <th class="text-right">Action</th>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td class="pl-0">
                            @if ($item->metadata->image)
                                <div class="img" style="width: 7em; height: 4em; background-image: url('{{ $item->metadata->image }}')"></div>
                            @endif
                        </td>
                        <td>
                            <a href="{{ $item->url }}">{{ $item->title}}</a>
                            <small class="text-muted font-italic">{{ parse_url($item->url, PHP_URL_HOST) }}</small>
                            <br>
                            <span class="text-muted" title="{{ $item->created_at }}">{{ $item->created_at->diffForHumans() }}</span>
                            <p>{{ $item->metadata->description }}</p>
                        </td>
                        <td class="text-right pr-0 text-nowrap">
                            <a href="{{ route('pool.accept', $item) }}" class="btn btn-outline-success" rel="ajaxify"><i class="fa fa-check"></i></a>
                            <a href="{{ route('pool.reject', $item) }}" class="btn btn-outline-danger" rel="ajaxify"><i class="fa fa-ban"></i></a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3">
                        {{ $items->links('vendor.pagination.bootstrap-4', ['display' => 'table']) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    @elseif (Request::input('display') == 'columns')
        <div class="card-columns" rel="infinite">
            @each('shared.item', $items, 'item')

            @if ($items->hasMorePages())
                <div class="card">
                    <a href="{{ $items->nextPageUrl() }}" rel="next" class="btn btn-xlarge btn-success btn-block" style="height: 250px; font-size: 100px; line-height: 250px" title="Load more">
                        <i class="fa fa-arrow-down"></i>
                    </a>
                </div>
            @endif
        </div>
    @endif
@endsection