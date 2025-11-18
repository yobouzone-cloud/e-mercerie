@forelse($merchantSupplies as $supply)
  <tr>
    <td><p class="fw-medium">{{ $supply->supply->name }}</p></td>
    <td><p>{{ number_format($supply->price, 0, ',', ' ') }}</p></td>
    <td><p>{{ $supply->stock_quantity }}</p></td>
    <td class="d-flex justify-content-center gap-3">
      <a href="{{ route('merchant.supplies.edit', $supply->id) }}" class="edit-icone" title="Modifier">
        <i class="fa-solid fa-pencil"></i>
      </a>
      <form action="{{ route('merchant.supplies.destroy', $supply->id) }}" method="POST" class="delete-form d-inline">
        @csrf
        @method('DELETE')
        <button type="button" class="text-danger btn-delete border-0 bg-transparent p-0" title="Supprimer">
          <i class="fa-solid fa-trash"></i>
        </button>
      </form>
    </td>
  </tr>
@empty
  <tr>
    <td colspan="4" class="text-center py-4 text-muted">Aucune fourniture enregistrée</td>
  </tr>
@endforelse
