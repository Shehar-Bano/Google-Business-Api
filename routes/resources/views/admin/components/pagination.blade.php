@if($paginator->total() > 0)
    <div class="admin-table-footer d-flex flex-wrap justify-content-between align-items-center gap-3 mt-3">
        <p class="mb-0 small text-muted">
            Showing {{ $paginator->firstItem() ?? 0 }} to {{ $paginator->lastItem() ?? 0 }} of {{ $paginator->total() }} records
        </p>
        <div class="admin-pagination-wrap">
            {{ $paginator->onEachSide(1)->links() }}
        </div>
    </div>
@else
    <div class="admin-table-footer mt-3">
        <p class="mb-0 small text-muted">Showing 0 to 0 of 0 records</p>
    </div>
@endif
