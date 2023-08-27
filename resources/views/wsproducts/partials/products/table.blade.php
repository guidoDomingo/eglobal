<div class="box box-success">
<div class="box-header">
              <h3 class="box-title">Listado de Productos Contables Relacionados</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body no-padding">
              <table id="products-table" class="table table-striped">
                <tbody><tr>
                  <th style="width: 10px">#</th>
                  <th>Nombre</th>
                  <th>Costo</th>
                </tr>
              @if(isset($products))
                @foreach($products as $product)
    		          <tr data-id="{{ $product->id  }}">
    		            <td>{{ $product->id }}.</td>
    		            <td>{{ $product->description }}</td>
    		            <td>{{ $product->cost }}</td>
    		          </tr>
  		            @endforeach
              @endif    
                </tbody>
              </table>
            </div>
            <!-- /.box-body -->
</div>