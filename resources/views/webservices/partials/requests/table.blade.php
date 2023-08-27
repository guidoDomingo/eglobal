<div class="box box-danger">
<div class="box-header">
              <h3 class="box-title">Listado de Peticiones del Web Service</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body no-padding">
              <table id="wsrequests-table" class="table table-striped">
                <tbody><tr>
                  <th style="width: 10px">#</th>
                  <th>Endpoint</th>
                  <th >Clave</th>
                  <th>Acciones</th>
                </tr>
              @foreach($requests as $request)
  		          <tr data-id="{{ $request->id  }}">
  		            <td>{{ $request->id }}.</td>
  		            <td>{{ $request->endpoint }}</td>
  		            <td>{{ $request->keyword }}</td>
  		            <td>
                        @if (Sentinel::hasAnyAccess('webservices.add|edit'))
                        <a href="#" class="btn-edit-request"><i class="fa fa-edit"></i></a> |
                        @endif
                        @if (Sentinel::hasAnyAccess('webservices.delete'))
  		                <a href="#" class="btn-delete-request"><i class="fa fa-remove"></i></a>
                        @endif
  		            </td>
  		          </tr>
		            @endforeach
                </tbody>
              </table>
            </div>
            <!-- /.box-body -->
            {!! Form::open(['route' => ['webservicerequests.destroy',':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete-request']) !!}
			      {!! Form::close() !!}	
</div>