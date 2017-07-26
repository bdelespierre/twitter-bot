<!-- /resources/views/buffer/_item.blade.php -->

@component('components.card', [
    'image' => ['src' => $item->metadata->image, 'alt' => $item->metadata->title],
    'links' => [['href' => $item->metadata->url, 'text' => parse_url($item->metadata->url, PHP_URL_HOST)]],
    'class' => $item->trashed() ? 'trashed' : '',
])
    @slot('title')
        {{ $item->metadata->title }}
    @endslot

    @slot('subtitle')
        Last updated {{ $item->updated_at->diffForHumans() }}
    @endslot

    {{ $item->metadata->description }}

    @if ($item->trashed())
        <p class="text-danger"><i class="fa fa-trash-o"></i> Deleted {{ $item->deleted_at->diffForHumans() }}</p>
    @endif

    @if (!$item->trashed())
        @slot('footer')
            <div class="btn-group dropup">
                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Actions
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item text-danger" href="{{ route('buffer.delete', $item) }}"><i class="fa fa-trash"></i> Delete</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('buffer.view', $item) }}"><i class="fa fa-eye"></i> View</a>
                    <a class="dropdown-item" href="{{ route('buffer.refresh', $item) }}"><i class="fa fa-refresh"></i> Refresh</a>
                    <a class="dropdown-item" href="{{ route('buffer.tweet', $item) }}"><i class="fa fa-twitter"></i> Tweet Now</a>
                </div>
            </div>
        @endslot
    @endif
@endcomponent