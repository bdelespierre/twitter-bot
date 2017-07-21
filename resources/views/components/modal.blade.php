<div class="modal fade" id="{{ $id }}">
    <div class="modal-dialog" role="document">
        <form action="{{ $action ?? '' }}" method="{{ $method ?? 'post' }}" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $title }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {{ $slot }}
            </div>
            <div class="modal-footer">
                {{ csrf_field() }}
                <button type="submit" class="btn btn-primary">{{ $btn_primary ?? 'Save' }}</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ $btn_secondary ?? 'Close' }}</button>
            </div>
        </form>
    </div>
</div>