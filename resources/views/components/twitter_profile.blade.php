<!-- resources/views/components/twitter_profile.blade.php -->

<div class="twitter-profile">
    <a class="background"
        @if (!empty($profile_banner_url))
            style="background-image: url({{ $profile_banner_url }})"
        @endif
    ></a>

    <div>
        <a title="{{ $name }}" href="https://twitter.com/{{ $screen_name }}" class="avatar">
            <img alt="{{ $name }}" src="{{ $profile_image_url }}">
        </a>

        <div class="user">
            <div class="name">
                <a href="{{ route('users.view', $id) }}">{{ $name }}</a>
            </div>
            <span>
                <a href="https://twitter.com/{{ $screen_name }}">@<span>{{ $screen_name }}</span></a>
            </span>
        </div>

        <div class="stats">
            <ul class="arrange">
                <li>
                    <a href="https://twitter.com/{{ $screen_name }}" title="{{ $statuses_count }} Tweet">
                        <span class="stat-label">Tweets</span>
                        <span class="stat-value">{{ $statuses_count }}</span>
                    </a>
                </li>
                <li>
                    <a href="https://twitter.com/{{ $screen_name }}/following" title="{{ $friends_count }} Following">
                        <span class="stat-label">Following</span>
                        <span class="stat-value">{{ $friends_count }}</span>
                    </a>
                </li>
                <li>
                    <a href="https://twitter.com/{{ $screen_name }}/followers" title="{{ $followers_count }} Followers">
                        <span class="stat-label ">Followers</span>
                        <span class="stat-value">{{ $followers_count }}</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>