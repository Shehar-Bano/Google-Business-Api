@php
    $title = $title ?? '';
    $subtitle = $subtitle ?? null;
    $paginator = $paginator ?? null;
    $createUrl = $createUrl ?? null;
    $createLabel = $createLabel ?? 'Create';
    $search = $search ?? '';
    $perPage = $perPage ?? 10;
    $sort = $sort ?? 'created_at';
    $direction = $direction ?? 'desc';
    $columns = $columns ?? [];
    $filters = $filters ?? [];
    $query = request()->query();

    $hasActiveFilters = filled($search);
    foreach ($filters as $f) {
        if (filled($f['value'] ?? '')) {
            $hasActiveFilters = true;
            break;
        }
    }

    $searchCol = $filters ? 'col-12 col-md-4 col-lg-5' : 'col-12 col-md-7 col-lg-8';
    $filterCol = $filters ? 'col-6 col-md-3 col-lg-2' : 'col-12 col-md-2 col-lg-2';
@endphp

<div class="admin-table-card card">
    <div class="admin-card-header card-header">
        <div>
            <h5 class="admin-card-header__title">{{ $title }}</h5>
            @if($subtitle)
                <p class="admin-card-header__subtitle">{{ $subtitle }}</p>
            @endif
        </div>

        <div class="d-flex align-items-center gap-2">
            @if($paginator)
                <span class="admin-card-header__meta"><strong>{{ number_format($paginator->total()) }}</strong> records</span>
            @endif

            @if($createUrl)
                <a href="{{ $createUrl }}" class="btn btn-sm admin-add-btn">
                    <i class="mdi mdi-plus"></i>
                    <span>{{ $createLabel }}</span>
                </a>
            @endif
        </div>
    </div>

    <div class="card-body">
        <div class="admin-toolbar-shell mb-3">
            <div class="admin-toolbar-shell__block">
                <span class="admin-toolbar-shell__label">{{ $title }} / page</span>
                <form method="GET" class="admin-length-form d-inline-flex align-items-center gap-2">
                    @foreach(request()->query() as $key => $value)
                        @if(!in_array($key, ['per_page', 'page'], true))
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <select name="per_page" class="form-select form-select-sm admin-per-page" onchange="this.form.submit()">
                        @foreach([10, 25, 50, 100] as $size)
                            <option value="{{ $size }}" @selected((int) $perPage === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div class="admin-toolbar-shell__block admin-toolbar-shell__block--meta">
                <span class="admin-toolbar-shell__label">Smart filters</span>
                <span class="admin-toolbar-shell__value">{{ $hasActiveFilters ? 'Active' : 'Ready' }}</span>
            </div>
        </div>

        <div class="admin-filter-card admin-filter-card--pro mb-3">
            <div class="admin-filter-card__top">
                <div>
                    <span class="admin-filter-card__label">Refine results</span>
                    <h6 class="admin-filter-card__title mb-0">Search, sort, and narrow records</h6>
                </div>

                @if($hasActiveFilters)
                    <div class="admin-filter-chip-row">
                        @if(filled($search))
                            <span class="admin-filter-chip">Search: {{ \Illuminate\Support\Str::limit($search, 22) }}</span>
                        @endif
                        @foreach($filters as $filter)
                            @if(filled($filter['value'] ?? ''))
                                <span class="admin-filter-chip">{{ $filter['label'] }}: {{ $filter['options'][$filter['value']] ?? $filter['value'] }}</span>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>

            <form method="GET" class="row g-3 align-items-end admin-datatable-filter-form">
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="direction" value="{{ $direction }}">
                <input type="hidden" name="per_page" value="{{ $perPage }}">

                <div class="{{ $searchCol }}">
                    <label class="form-label">Search</label>
                    <div class="input-group admin-search-group">
                        <span class="input-group-text"><i class="mdi mdi-magnify"></i></span>
                        <input type="text" name="search" value="{{ $search }}" class="form-control admin-global-search-input" placeholder="Search records...">
                    </div>
                </div>

                @foreach($filters as $filter)
                    <div class="{{ $filterCol }}">
                        <label class="form-label">{{ $filter['label'] }}</label>
                        @php($filterType = $filter['type'] ?? 'select')
                        @if($filterType === 'date')
                            <input type="date" name="{{ $filter['name'] }}" value="{{ $filter['value'] ?? '' }}" class="form-control">
                        @elseif($filterType === 'text')
                            <input type="text" name="{{ $filter['name'] }}" value="{{ $filter['value'] ?? '' }}" class="form-control" placeholder="{{ $filter['placeholder'] ?? '' }}">
                        @else
                            <select name="{{ $filter['name'] }}" class="form-select">
                                @foreach($filter['options'] as $val => $label)
                                    <option value="{{ $val }}" @selected(($filter['value'] ?? '') == $val)>{{ $label }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                @endforeach

                <div class="col-6 col-md-2 col-lg-2">
                    <button class="btn btn-dark w-100" type="submit">
                        <i class="mdi mdi-filter-variant me-1"></i> Apply
                    </button>
                </div>

                <div class="col-6 col-md-2 col-lg-2">
                    <a class="btn btn-outline-secondary w-100" href="{{ request()->url() }}">
                        <i class="mdi mdi-refresh me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="table-responsive admin-table-wrap">
            <table class="table admin-datatable">
                <thead>
                    <tr>
                        @foreach($columns as $column)
                            <th class="{{ !empty($column['actions']) ? 'text-end admin-actions-col' : '' }}">
                                @if(!empty($column['sortable']) && !empty($column['field']))
                                    <a class="admin-sort-link" href="{{ request()->url() . '?' . http_build_query(array_merge($query, ['sort' => $column['field'], 'direction' => (($sort === $column['field'] && $direction === 'asc') ? 'desc' : 'asc')])) }}">
                                        <span>{{ $column['label'] }}</span>
                                        @if($sort === $column['field'])
                                            <i class="mdi {{ $direction === 'asc' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}"></i>
                                        @else
                                            <i class="mdi mdi-swap-vertical"></i>
                                        @endif
                                    </a>
                                @else
                                    {{ $column['label'] }}
                                @endif
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    {{ $slot }}
                </tbody>
            </table>
        </div>

        @if($paginator)
            @include('admin.components.pagination', ['paginator' => $paginator])
        @endif
    </div>
</div>

@push('my-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.admin-table-card').forEach(function(card) {
                const filterForm = card.querySelector('.admin-datatable-filter-form');
                const lengthForm = card.querySelector('.admin-length-form');
                const searchInput = card.querySelector('.admin-global-search-input');
                const tbody = card.querySelector('.admin-datatable tbody');
                const tableHeader = card.querySelector('.admin-datatable thead');
                
                if (!filterForm) return;

                let searchTimeout;

                const performSearch = () => {
                    const formData = new FormData(filterForm);
                    if (lengthForm) {
                        const lengthData = new FormData(lengthForm);
                        for (let [key, val] of lengthData.entries()) {
                            if (!formData.has(key)) {
                                formData.append(key, val);
                            } else if (key === 'per_page') {
                                formData.set(key, val);
                            }
                        }
                    }
                    
                    const params = new URLSearchParams(formData);
                    const actionUrl = filterForm.getAttribute('action') || window.location.pathname;
                    const url = `${actionUrl}?${params.toString()}`;

                    fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        
                        const newTbody = doc.querySelector('.admin-datatable tbody');
                        if (newTbody && tbody) {
                            tbody.innerHTML = newTbody.innerHTML;
                        }
                        
                        const footerSelector = '.admin-table-footer';
                        const currentFooter = card.querySelector(footerSelector);
                        const newFooter = doc.querySelector(footerSelector);
                        if (currentFooter) {
                            if (newFooter) {
                                currentFooter.outerHTML = newFooter.outerHTML;
                            } else {
                                currentFooter.remove();
                            }
                        } else if (newFooter) {
                            const body = card.querySelector('.card-body');
                            if (body) body.appendChild(newFooter);
                        }

                        const chipsSelector = '.admin-filter-card__top';
                        const currentChips = card.querySelector(chipsSelector);
                        const newChips = doc.querySelector(chipsSelector);
                        if (currentChips && newChips) {
                            currentChips.innerHTML = newChips.innerHTML;
                        }

                        const badgeSelector = '.admin-card-header__meta';
                        const currentBadge = card.querySelector(badgeSelector);
                        const newBadge = doc.querySelector(badgeSelector);
                        if (currentBadge && newBadge) {
                            currentBadge.innerHTML = newBadge.innerHTML;
                        }

                        const metaSelector = '.admin-toolbar-shell__block--meta';
                        const currentMeta = card.querySelector(metaSelector);
                        const newMeta = doc.querySelector(metaSelector);
                        if (currentMeta && newMeta) {
                            currentMeta.innerHTML = newMeta.innerHTML;
                        }

                        const newHeader = doc.querySelector('.admin-datatable thead');
                        if (newHeader && tableHeader) {
                            tableHeader.innerHTML = newHeader.innerHTML;
                        }

                        const newLengthSelect = doc.querySelector('.admin-per-page');
                        const currentLengthSelect = card.querySelector('.admin-per-page');
                        if (newLengthSelect && currentLengthSelect) {
                            currentLengthSelect.value = newLengthSelect.value;
                        }
                        
                        window.history.pushState({}, '', url);
                    })
                    .catch(err => console.error('Error filtering datatable:', err));
                };

                if (searchInput) {
                    searchInput.addEventListener('input', function() {
                        clearTimeout(searchTimeout);
                        searchTimeout = setTimeout(performSearch, 300);
                    });
                }

                filterForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    performSearch();
                });

                filterForm.querySelectorAll('select, input[type="date"], input[type="text"]:not(.admin-global-search-input)').forEach(el => {
                    el.addEventListener('change', performSearch);
                });

                if (lengthForm) {
                    lengthForm.addEventListener('submit', function(e) {
                        e.preventDefault();
                    });
                    const lengthSelect = lengthForm.querySelector('.admin-per-page');
                    if (lengthSelect) {
                        lengthSelect.removeAttribute('onchange');
                        lengthSelect.addEventListener('change', performSearch);
                    }
                }
            });
        });
    </script>
@endpush
