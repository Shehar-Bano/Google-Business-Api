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

            <form method="GET" class="row g-3 align-items-end">
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="direction" value="{{ $direction }}">
                <input type="hidden" name="per_page" value="{{ $perPage }}">

                <div class="{{ $searchCol }}">
                    <label class="form-label">Search</label>
                    <div class="input-group admin-search-group">
                        <span class="input-group-text"><i class="mdi mdi-magnify"></i></span>
                        <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search records...">
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
