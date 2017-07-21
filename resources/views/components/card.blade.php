<!-- /resources/components/card.blade.php -->

<div class="card">
    @if (isset($header))
        <div class="card-header">{{ $header }}</div>
    @endif

    @if (isset($image))
        <img class="card-img-top" src="{{ $image['src'] }}" alt="{{ $image['alt'] }}" style="max-width: 100%">
    @endif

    <div class="card-block">
        @if (isset($title))
            <h4 class="card-title">{{ $title }}</h4>
        @endif

        @if (isset($subtitle))
            <h6 class="card-subtitle mb-2 text-muted">{{ $subtitle }}</h6>
        @endif

        <p class="card-text">{{ $slot }}</p>

        @foreach ($links ?? [] as $link)
            <a href="{{ $link['href'] }}" class="card-link">{{ $link['text'] }}</a>
        @endforeach
    </div>

    @if (!empty($list))
        <ul class="list-group list-group-flush">
            @foreach ($list as $item)
                <li class="list-group-item">{{ $item }}</li>
            @endforeach
        </ul>
    @endif

    @if (isset($footer))
        <div class="card-footer">
            <small class="text-muted">{{ $footer }}</small>
        </div>
    @endif
</div>