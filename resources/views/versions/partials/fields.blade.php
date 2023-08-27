<div class="form-group">
  {!! Form::label('tipos_pantallas', 'Tipos Pantallas') !!}
  {!! Form::select('primary_screen_id', $pantallas,null, array('class'=>'form-control')) !!}
</div>
<div class="form-group">
  {!! Form::label('version_name', 'Nombre') !!}
  {!! Form::text('name', null , ['class' => 'form-control', 'placeholder' => 'Nombre de la versión' ]) !!}
</div>
<div class="form-group">
  {!! Form::hidden('brands_selected', null , ['class' => 'form-control brands_selected', 'placeholder' => 'Valores asignados' ]) !!}
  {!! Form::hidden('app_id', $appId , ['class' => 'form-control', 'placeholder' => '' ]) !!}
</div>
<div class="row">
  <div class="col-sm-3">
    {!! Form::label('proveedores', 'Grupos de Proveedores de servicios') !!}
    {!! Form::select('ddlProviders', $service_providers_groups,null, array('id'=>'ddlProviders','class'=>'form-control')) !!}
  </div>
</div>
<br>
<div class="demo">
    <div class="row">
      <div class="col-md-6">
        {!! Form::label('proveedores', 'Proveedores de servicios disponibles') !!}
        <ul id="draggable">
        @foreach ($service_providers as $service_provider)
          <li data-group="1" data-brand = "{{$service_provider->id}}">{{$service_provider->name}}</li>
        @endforeach
          <li data-group="0" data-brand = "0">Otros Servicios</li>
        </ul>
      </div>
      <div class="col-md-6">
        {!! Form::label('proveedores', 'Proveedores de servicios asignados') !!}
        <ul id="droppable">
      </ul>
      </div>
    </div>
  </div>

<style>
  .demo { width: 620px; }
  .demo ul { width: 300px; padding: 5px; margin: 10px; list-style: none; }
  .demo ul li { cursor: pointer; }
  .demo .ui-state-highlight { background-color: #f5e79e;}
  #draggable {border-style: solid; border-color: #cccccc; border-width: 1px; min-height: 300px;}
  #droppable {border-style: solid; border-color: #cccccc; border-width: 1px; min-height: 300px; }
</style>
@section('page_scripts')
@parent
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
<script>
  var selectedClass = 'ui-state-highlight',
      clickDelay = 600,     // click time (milliseconds)
      lastClick, diffClick; // timestamps

  function makeEdit(){
    $("#draggable li")
    // Script to deferentiate a click from a mousedown for drag event
            .bind('mousedown mouseup', function(e){
              if (e.type=="mousedown") {
                lastClick = e.timeStamp; // get mousedown time
              } else {
                diffClick = e.timeStamp - lastClick;
                if ( diffClick < clickDelay ) {
                  // add selected class to group draggable objects
                  $(this).toggleClass(selectedClass);
                }
              }
            })
            .draggable({
              revertDuration: 10, // grouped items animate separately, so leave this number low
              containment: '.demo',
              start: function(e, ui) {
                ui.helper.addClass(selectedClass);
              },
              stop: function(e, ui) {
                // reset group positions
                $('.' + selectedClass).css({ top:0, left:0 });
              },
              drag: function(e, ui) {
                // set selected group position to main dragged object
                // this works because the position is relative to the starting position
                $('.' + selectedClass).css({
                  top : ui.position.top,
                  left: ui.position.left
                });
              }
            });

    $("#droppable, #draggable")
            .sortable({
              stop: function() {
                storeInput();
              }
            })
            .droppable({
              drop: function(e, ui) {
                $('.' + selectedClass)
                        .appendTo($(this))
                        .add(ui.draggable) // ui.draggable is appended by the script, so add it after
                        .removeClass(selectedClass)
                        .css({ top:0, left:0 });
                storeInput();
              }
            });
  }

  $(document).ready(function(){
      makeEdit();
  });

  function storeInput() {
    $('#brands_selected').empty();
    $value = '';
    $('#droppable').not('.placeholder').each(function() {
      //console.debug($(this).text());
      $('#droppable li').not('.placeholder').each(function() {
        //console.debug($(this).data('brand')+',');
        $value += $(this).data('group')+'-'+$(this).data('brand')+',';
      });
      $value = $value.slice(0, -1);
      $('.brands_selected').val($value);
    });
  }



  function getProviders_groups(group_id){
    if(group_id !== ""){
      $.ajax({
        url: '/providers_group/'+group_id,
        type: 'GET',
        dataType: 'json'
      }).done(function(data) {

        $('#draggable').empty();
        var values = data['values'];

        for (var i = 0, len = values.length; i < len; i++) {
          $('#draggable').append('<li data-group="'+values[i]['group']+'" data-brand="'+values[i]['id']+'" style="position: relative;" class="ui-draggable">'+values[i]['name']+'</li>');

        }

        if(values[0]['group']==1){
          $('#draggable').append('<li data-group="0" data-brand="0" style="position: relative;" class="ui-draggable">Otros Servicios</li>');
        }

        makeEdit();
      });
    }
  }


  $('#ddlProviders').change(function() {
    var id = $(this).val();
    getProviders_groups(id);
  });





</script>
@append
