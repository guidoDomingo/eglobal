<div class="box box-danger">
<div class="box-header">
              <h3 class="box-title">Listado de Requests Relacionados</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body no-padding">
              <table id="wsrequests-table" class="table table-striped">
                <tbody><tr>
                  <th style="width: 10px">#</th>
                  <th>Endpoint</th>
                  <th>Clave</th>
                </tr>
              @if(isset($wsrequests))
                @foreach($wsrequests as $request)
    		          <tr data-id="{{ $request->id  }}">
    		            <td>{{ $request->id }}.</td>
    		            <td>{{ $request->endpoint }}</td>
    		            <td>{{ $request->keyword }}</td>
    		          </tr>
  		            @endforeach
              @endif    
                </tbody>
              </table>
            </div>
            <!-- /.box-body -->
</div>