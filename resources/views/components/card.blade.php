<!-- /resources/components/card.blade.php -->

<div class="card">
    @if (!empty($image) && !empty($url))
        <a href="{{ $url }}">
            <img class="card-img-top" src="{{ $image }}">
        </a>
    @elseif (!empty($image))
        <img class="card-img-top" src="{{ $image }}">
    @endif

    <div class="card-block">
        @if (!empty($profile))
            <figure class="profile">
                <img src="{{ $profile }}" class="profile-avatar" alt="">
            </figure>
        @endif

        @if (!empty($title))
            <h4 class="card-title mt-3">
                @if (!empty($url))
                    <a href="{{ $url }}">{{ $title }}</a>
                @else
                    {{ $title }}
                @endif
            </h4>
        @endif

        @if (!empty($author))
            <div class="meta">
                <a>{{ $author }}</a>
            </div>
        @endif

        <div class="card-text">
            @if (!empty($description))
                {{ $description }}
            @elseif (isset($slot))
                {{ $slot }}
            @endif
        </div>
    </div>

    @if (!empty($footer) || !empty($url))
        <div class="card-footer">
            @if (!empty($footer))
                <small>{{ $footer }}</small>
            @endif

            @if (!empty($url))
                <a href="{{ $url }}" class="btn btn-default btn-sm" target="_blank">show</a>
            @endif
        </div>
    @endif
</div>