{!! Form::open(['route' => ['admin.branches.destroy', $branch->id], 'method' => 'DELETE']) !!}
<button type="submit" class="btn btn-danger btn-delete-form">Borrar</button>
{!! Form::close() !!}
