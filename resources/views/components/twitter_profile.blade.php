<!-- resources/views/components/twitter_profile.blade.php -->

<div class="twitter-profile">
    @if (!empty($profile_banner_url))
        <a class="background" style="background-image: url({{ $profile_banner_url }})"></a>
    @else
        <a class="background"></a>
    @endif

    <div>
        <a title="{{ $name }}" href="https://twitter.com/{{ $screen_name }}" class="avatar" target="_blank">
            <img alt="{{ $name }}" src="{{ $profile_image_url }}">
        </a>

        <div class="user">
            <div class="name">
                <a href="{{ route('users.view', $id) }}">{{ $name }}</a>
            </div>
            <span>
                <a href="https://twitter.com/{{ $screen_name }}" target="_blank">@<span>{{ $screen_name }}</span></a>
            </span>
            @if (isset($user))
                <div class="attributes">
                    @if ($user->friend && $user->follower)
                        <span class="fa fa-check text-success" title="Friend & Follower"></span>
                    @elseif ($user->friend)
                        <span class="fa fa-check text-primary" title="Friend"></span>
                    @elseif ($user->follower)
                        <span class="fa fa-check text-muted" title="Follower"></span>
                    @endif

                    @if ($user->vip)
                        <span class="fa fa-star" style="color: #f1c40f" title="VIP"></span>
                    @endif

                    @if ($user->muted)
                        <span class="fa fa-microphone-slash text-danger" title="Muted"></span>
                    @endif

                    @if ($user->blocked)
                        <span class="fa fa-ban text-danger" title="Blocked"></span>
                    @endif
                </div>
            @endif
        </div>

        <div class="stats">
            <ul class="arrange">
                <li>
                    <a href="https://twitter.com/{{ $screen_name }}" title="{{ $statuses_count }} Tweet" target="_blank">
                        <span class="stat-label">Tweets</span>
                        <span class="stat-value">{{ $statuses_count }}</span>
                    </a>
                </li>
                <li>
                    <a href="https://twitter.com/{{ $screen_name }}/following" title="{{ $friends_count }} Following"  target="_blank">
                        <span class="stat-label">Following</span>
                        <span class="stat-value">{{ $friends_count }}</span>
                    </a>
                </li>
                <li>
                    <a href="https://twitter.com/{{ $screen_name }}/followers" title="{{ $followers_count }} Followers" target="_blank">
                        <span class="stat-label ">Followers</span>
                        <span class="stat-value">{{ $followers_count }}</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>