<div class="d-flex justify-content-between align-items-center mt-3">
  <div>
    <small class="text-muted">Affichage {{ $merchantSupplies->firstItem() ?? 0 }} - {{ $merchantSupplies->lastItem() ?? 0 }} sur {{ $merchantSupplies->total() }} fournitures</small>
  </div>
  <div>
    {!! $merchantSupplies->links('pagination::bootstrap-5') !!}
  </div>
</div>
