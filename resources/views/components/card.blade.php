<!-- /resources/components/card.blade.php -->

<div class="card">
    @if (isset($image))
        <img class="card-img-top" src="{{ $image }}">
    @endif
    <div class="card-block">
        @if (isset($title))
            <h4 class="card-title mt-3">{{ $title }}</h4>
        @endif
        <div class="card-text">
            @if (isset($description))
                {{ $description }}
            @else
                {{ $slot }}
            @endif
        </div>
    </div>
    @if (isset($last_update) || isset($url))
        <div class="card-footer">
            @if (isset($last_update))
                <small>Last updated {{ $last_update }}</small>
            @endif

            @if (isset($url))
                <a href="{{ $url }}" class="btn btn-default btn-sm" target="_blank">show</a>
            @endif
        </div>
    @endif
</div>