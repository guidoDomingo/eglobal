@section('js')
<link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
<script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>

<!-- date-range-picker -->
<link href="/bower_components/admin-lte/plugins/datepicker/datepicker3.css" rel="stylesheet" type="text/css" />
<script src="/bower_components/admin-lte/plugins/datepicker/bootstrap-datepicker.js"></script>
<script src="/bower_components/admin-lte/plugins/datepicker/locales/bootstrap-datepicker.es.js" charset="UTF-8"></script>

<script type="text/javascript">
$('.select2').select2();

$('#recaudador').on('change', function(e){
    var ci_recaudador = e.target.value;
    $('#transactions').hide();
    $('#messages').hide();
    //alert(ci_recaudador);
    $.get('{{ url('depositos_arqueos') }}/ddl/recaudadores/' + ci_recaudador, function(recaudador) {                
        if(recaudador['count'] > 0)
        {
            let transactions = recaudador['data']; 
            let total = recaudador['total_amount']; 
            let row = ''; 
            var obj = { };
            let i = 1;         
            $.each( transactions, function( key, value ) {
                                
                row += '<tr>'+
                    '<td>'+ i++ +'</td>'+
                        '<td>'+ value['id'] +'</td>'+                                                
                        '<td>'+ value['description'] +'</td>'+
                        '<td style="text-align:right" id="row'+value['id']+'">'+ value['amount'] +'</td>'+
                        '<td><input type="checkbox" class="check_transaction" data-amount="'+value['amount']+'" data-id="'+value['id']+'" name="checkbox" checked></td>'+
                        '<td>'+ value['status'] +'</td>'+
                        '<td>'+ value['created_at'] +'</td>'+
                        '<td>'+ value['username'] +'</td>'+
                        '<td style="text-align:right">'+ value['doc_number'] +'</td>'+
                        '<td><button data-id="'+ value['id'] +'" type="button" class="editar_monto btn btn-block btn-success"><i data-id="'+ value['id'] +'" class="editar_monto fa fa-pencil"></i></button></td>'+
                    '</tr>'
                
                    obj[value['id']] = [value['amount'],''];     
            });
            
                        
            $('#transactions_list').val(JSON.stringify(obj));
            $('#transactions').show();
            $('#messages').hide();
            $('#transaction_body').html(row);
            $('#total_amount').val(total.replaceAll('.',''));
            $('#total').html(total);
        }else
        {
            $('#transactions').hide();            
            $('#messages').html('No existen arqueos para este recaudador');
            $('#messages').show();
        }                        
    });
});

$('#edit_amount').on('click', function(e){
    let amount = $('#amount').val();
    let motivo = $('#motivo').val();
    let transaction_id = $('.idTransaccion').text();
    let row = '#row'+transaction_id;
    let transactions = JSON.parse($('#transactions_list').val());
    
    
    if(transactions.hasOwnProperty(transaction_id) == true)
    {
        let monto_original = transactions[transaction_id][0];        
        let total          = $('#total').text();       
        let total_recalculado = parseInt(total.replaceAll('.','')) - parseInt(monto_original.replaceAll('.','')) + parseInt(amount.replaceAll('.',''));
    
        transactions[transaction_id][0] = amount;
        transactions[transaction_id][1] = motivo;
    
        $(row).text(amount);

        $('#total').text(var_format(total_recalculado));  
        $('#amount').val('');
        $('#motivo').val('');
        $('#transactions_list').val(JSON.stringify(transactions));
        $('#total_amount').val(total_recalculado);
    }
    else
    {
        transactions = JSON.stringify(transactions);
        var new_value = ',"'+transaction_id+'":["'+amount+'","'+motivo+'"]}';
        transactions = transactions.substring(0, transactions.length - 1);        

        let total          = $('#total').text();       
        let total_recalculado = parseInt(total.replaceAll('.','')) + parseInt(amount.replaceAll('.',''));

        $(row).text(amount);
        $('#total').text(var_format(total_recalculado));  
        $('#total_amount').val(total_recalculado);
        $('#amount').val('');
        $('#motivo').val('');
        $('#transactions_list').val(transactions+new_value);
    }
        
});



$(document).ready(function() {        
    //Date range picker
    $('#last_update').datepicker({
        language: 'es',
        format: 'yyyy-mm-dd',
        //startDate: min_date,
        //endDate: max_date,
        todayHighlight: true
    }).on('changeDate', function() {
        $(this).datepicker('hide');
    });

    $('#last_update').attr({
        'onkeydown': 'return false'
    });
});

//CHECK ALL / UNCHECK ALL
const checkbox = document.getElementById('all');
checkbox.addEventListener('change', (event) => {
  if(event.currentTarget.checked) {
    //value is checked, check all transactions
    const recaudador = document.getElementById('recaudador');
    ci_recaudador = recaudador.value;
    $.get('{{ url('depositos_arqueos') }}/ddl/recaudadores/' + ci_recaudador, function(recaudador) {                
        if(recaudador['count'] > 0)
        {
            let transactions = recaudador['data']; 
            let total = recaudador['total_amount']; 
            let row = ''; 
            var obj = { };
            let i = 1;         
            $.each( transactions, function( key, value ) {
                                                                
                obj[value['id']] = [value['amount'],''];     
            });
            
                        
            $('#transactions_list').val(JSON.stringify(obj));            
            $('#total').html(total);
            $('#total_amount').val(total.replaceAll('.',''));
        }else
        {
            $('#transactions').hide();            
            $('#messages').html('No existen arqueos para este recaudador');
            $('#messages').show();
        }                        
    });
    $('input[name="checkbox"]').each(function() {
            this.checked = true;            
    });    

  } else {
    //value is unchecked, uncheck all transactions    
    $('#transactions_list').val('');
    $('#total_amount').val('');
    $('#total').text('0');
    $('input[name="checkbox"]').each(function() {
            this.checked = false;
    });
  }
})


//Añadir listeners para habilitar el modal para edición de monto de los arqueos
const container = document.querySelector('#transaction_body');
container.addEventListener('click', function(e){    

    if(e.target.classList.contains('editar_monto')){        
        var transaction_id = e.target.getAttribute('data-id');      
        //console.log(transaction_id);  
        $(".idTransaccion").html(transaction_id);      
        $("#EditMontoModal").modal();
    }

    if(e.target.classList.contains('check_transaction')){                
        var transaction_id = e.target.getAttribute('data-id');
        var amount = e.target.getAttribute('data-amount');  
        var total = 0;      
        var subtotal = 0;
        const checkbox = e.target;         
            if (checkbox.checked) {                
                //agregar transaccion al hilo
                var transactions = $('#transactions_list').val();                                
                if(transactions != ""){
                    var new_value = ',"'+transaction_id+'":["'+amount+'",""]}';
                }else{
                    var new_value = '{"'+transaction_id+'":["'+amount+'",""]}';
                }    

                transactions = transactions.substring(0, transactions.length - 1);
                let row = '#row'+transaction_id;                                

                $(row).text(amount);
                $('#transactions_list').val(transactions+new_value);     

                var list = JSON.parse($('#transactions_list').val());
                for (let x of Object.keys(list)) {
                    subtotal = parseInt(list[x][0].replaceAll('.',''));
                    total    = total + subtotal;                    
                }
           

                $('#total').text(var_format(total));
                $('#total_amount').val(total);
            
            } else {                
            
                //quitar transaccion al hilo
                var transactions = JSON.parse($('#transactions_list').val());                                                
                //console.log("Original object:",transactions);
                delete transactions[transaction_id];   
                //console.log("Updated object: ",transactions);
                
                for (let x of Object.keys(transactions)) {
                    subtotal = parseInt(transactions[x][0].replaceAll('.',''));
                    total    = total + subtotal;
                }
                
                if(Object.keys(transactions).length === 0)
                {
                    $('#transactions_list').val('');    
                }
                else
                {
                    $('#transactions_list').val(JSON.stringify(transactions));
                }
                
                var total_previo = $('#total').text();
                                
                
                $('#total').text(var_format(total));
                $('#total_amount').val(total);
            }                                                                               
    }

});




function format(input){
    var num = input.value.replace(/\./g,'');
    if(!isNaN(num)){
        num = num.toString().split('').reverse().join('').replace(/(?=\d*\.?)(\d{3})/g,'$1.');
        num = num.split('').reverse().join('').replace(/^[\.]/,'');
        input.value = num;
    }else{ 
        alert('Solo se permiten numeros');
        input.value = input.value.replace(/[^\d\.]*/g,'');
    }
}

function var_format(num){    
    if(!isNaN(num)){
        num = num.toString().split('').reverse().join('').replace(/(?=\d*\.?)(\d{3})/g,'$1.');
        num = num.split('').reverse().join('').replace(/^[\.]/,'');
        
        return num;
    }
}

</script>

@endsection