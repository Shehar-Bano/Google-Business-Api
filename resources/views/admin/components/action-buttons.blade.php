@php
    $type = $type ?? 'view';
    $href = $href ?? null;
    $title = $title ?? null;
    $formAction = $formAction ?? null;
    $confirm = $confirm ?? 'Are you sure you want to delete this? This action cannot be undone.';
    $method = $method ?? 'DELETE';

    $config = [
        'view'       => ['class' => 'action-icon-view',       'icon' => 'mdi-eye-outline',        'title' => 'View'],
        'edit'       => ['class' => 'action-icon-edit',       'icon' => 'mdi-pencil-outline',     'title' => 'Edit'],
        'delete'     => ['class' => 'action-icon-delete',     'icon' => 'mdi-trash-can-outline',  'title' => 'Delete'],
        'permission' => ['class' => 'action-icon-permission', 'icon' => 'mdi-shield-key-outline', 'title' => 'Permissions'],
    ][$type] ?? ['class' => 'action-icon-view', 'icon' => 'mdi-eye-outline', 'title' => 'Action'];

    $resolvedTitle = $title ?? $config['title'];
    $formId = 'delete-form-' . uniqid();
@endphp

@if($formAction)
    <form id="{{ $formId }}" action="{{ $formAction }}" method="POST" class="d-inline">
        @csrf
        @method($method)
        <button
            type="button"
            class="action-icon-btn {{ $config['class'] }}"
            data-bs-toggle="tooltip"
            title="{{ $resolvedTitle }}"
            onclick="confirmDelete('{{ $formId }}', '{{ addslashes($confirm) }}')"
        >
            <i class="mdi {{ $config['icon'] }}"></i>
        </button>
    </form>
@elseif($href)
    <a href="{{ $href }}" class="action-icon-btn {{ $config['class'] }}" data-bs-toggle="tooltip" title="{{ $resolvedTitle }}">
        <i class="mdi {{ $config['icon'] }}"></i>
    </a>
@endif

@once
    @push('my-script')
    <script>
        function confirmDelete(formId, message) {
            Swal.fire({
                title: 'Are you sure?',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#E53935',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
            }).then(function(result) {
                if (result.isConfirmed) {
                    document.getElementById(formId).submit();
                }
            });
        }
    </script>
    @endpush
@endonce
