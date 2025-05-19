<div style='text-align:right;'>
    <span class="badge rounded-pill {{ $statusClass }} badgeborder-radius">{{ $displayStatus }}</span>
    <div class="dropdown" style="display:inline;">
        <button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0 p-0" data-bs-toggle="dropdown">
            <i data-feather="more-vertical"></i>
        </button>
        <div class="dropdown-menu dropdown-menu-end">
            @foreach ($actions as $action)
                <a class="dropdown-item" href="{{ is_callable($action['url']) ? $action['url']($row) : $action['url'] }}">
                    <i data-feather="{{ $action['icon'] ?? 'edit' }}" class="me-50"></i>
                    <span>{{ $action['label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>
</div>
