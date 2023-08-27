<script type="text/javascript">
  $(document).ready(function () {
      /* Clears form and reset action url */
      function clearWSRequestForm(clear_alert){
        if (typeof(clear_alert)==='undefined') clear_alert = true;
        var form = $('#form-ws-request');
        var add_url = '{{ route("admin.webservicerequests.store") }}';
        form.attr('action',add_url);
        $('#form-ws-request').trigger('reset');
        if($('#form-alert').length !== 0 && clear_alert == true){
          $('#form-alert').fadeOut();
        }
        if($('#_method').length !== 0){
          $('#_method').remove();
        }
      }
      function displayAlert(type, title ,message ,errors){
        if($('#form-alert').length == 0){
          var alert_box = '<div id="form-alert" class="" style="display:none">'+
              '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>'+
              '<h4><i class="icon fa fa-check"></i></h4>'+
              '<p></p><ul></ul></div>';
          $('#form-alert-container').append(alert_box);
        }
        var form = $('#form-alert');
        form.find('ul').find('li').remove();
         $.each(errors, function (key, value) {
            form.find('ul').append('<li>'+value+'</li>');
         });
        form.find('p').text(message);
        form.attr('class', 'alert alert-dismissable alert-'+type);
        form.find('h4').text(title);
        form.fadeIn();
      }
      /* On clear form button click */
      $('#wsrequest-form-clear').click(function(e){
         e.preventDefault();
         clearWSRequestForm();
      });
      /* On delete WSRequest button click */
      $(document).on('click', '.btn-delete-request', function(e){
          e.preventDefault();
          var row = $(this).parents('tr');
          var id = row.data('id');
          swal({
              title: "Atención!",
              text: "Está a punto de borrar el registro, está seguro?.",
              type: "warning",
              showCancelButton: true,
              confirmButtonColor: "#DD6B55",
              confirmButtonText: "Si, eliminar!",
              cancelButtonText: "No, cancelar!",
              closeOnConfirm: true,
              closeOnCancel: true
           },
           function(isConfirm){
            if (isConfirm) {
              var form = $('#form-delete-request');
              var url = form.attr('action').replace(':ROW_ID',id);
              var data = form.serialize();
              var type = "";
              var title = ""
              $.post(url,data, function(result){
                if(result.error == false){
                  row.fadeOut();
                  type = "success";
                  title = "Operación realizada!";
                }else{
                  type = "error";
                  title =  "No se pudo realizar la operación"
                }
                swal({   title: title,   text: result.message,   type: type,   confirmButtonText: "Aceptar" });
              }).fail(function (){
                swal('No se pudo realizar la petición.');
              });
            }
           });
      });
      /* On Add/Update WSRequest submit button click */ 
      $('#wsrequest-submit').click(function (e){
            e.preventDefault();
              var form = $('#form-ws-request');
              var url = form.attr('action');              
              values = form.serializeArray();
              values.push({
                  name: "service_id",
                  value: {{ $webservice->id}}
              });
              var data = jQuery.param(values);;
              var type = "";
              var title = ""
              
              $.ajax({
                  type: "post",
                  url: url,
                  data: data,
                  error: function(returnval) {
                     var errors = $.parseJSON(returnval.responseText);
                     type = "error";
                     title = "Por favor corrija los errores";
                     message = "";
                     displayAlert(type, title ,message ,errors);
                  },
                  success: function (response) {
                    $('#form-alert').find('ul').find('li').remove();
                    if(response.error == false){
                      type = "success";
                      title = "Operación realizada!";
                    }else{
                      type = "error";
                      title =  "No se pudo realizar la operación";
                    }
                    var errors = [];
                    displayAlert(type, title ,response.message ,errors);
                    
                    if(response.error == false){
                      var clear_alert = false;
                      clearWSRequestForm(clear_alert);
                      var foundTr = $('#wsrequests-table').find('tbody').find('tr[data-id='+response.object.id+']');
                      var tdHTML = '<td>'+response.object.id+'.</td>'+
                          '<td>'+response.object.endpoint+'</td>'+
                          '<td>'+response.object.keyword+'</td>'+
                          '<td>'+
                            '<a href="#" class="btn-edit-request"><i class="fa fa-edit"></i></a> |'+
                            '<a href="#" class="btn-delete-request"><i class="fa fa-remove"></i></a>'+
                          '</td>';
                      if( foundTr.length !== 0){
                        foundTr.html(tdHTML);
                      }else{
                        var newTr = '<tr data-id="'+response.object.id+'">'+
                          tdHTML+
                        '</tr>';
                        $('#wsrequests-table').find('tbody').append(newTr);
                      }
                    }
                  }
              });
      });
      /* On edit WSRequest button click 
        Loads model data into form
      */
      $(document).on('click', '.btn-edit-request', function(e){
          e.preventDefault();
          var row = $(this).parents('tr');
          var id = row.data('id');
          var form = $('#form-ws-request');
          var url = '{{ route("admin.webservicerequests.show","") }}'+'/'+id;
          var type = "";
          var title = ""
          $.ajax({
              type: "get",
              url: url,
              error: function(returnval) {
                 console.log(returnval);
              },
              success: function (object) {
                $('#endpoint').val(object.endpoint).focus();
                $('#keyword').val(object.keyword);
                $('#cacheable').prop('checked', object.cacheable);
                $('#transactional').prop('checked', object.transactional);
                input_method = '<input type="hidden" name="_method" id="_method" value="PUT" />';
                form.append(input_method);
                var edit_url = '{{ route("admin.webservicerequests.update","") }}'+'/'+id;
                form.attr('action',edit_url);
              }
          });
      });
  });
</script>
