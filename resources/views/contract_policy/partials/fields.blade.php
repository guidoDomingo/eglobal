

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
           {!! Form::label('contract_id', 'Contrato') !!} 
            <div class="input-group">
                <div class="input-group-addon">
                    <i class="fa fa-filter"></i>
                </div>
                {!! Form::select('contract_id', $contracts, null, ['id' => 'contract_id','class' => 'form-control select2', 'style' => 'width: 100%','placeholder'=>'Seleccione un contrato...']) !!}
            </div>
       </div>
   </div>
       
   <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('insurance_policy_id', 'Pólizas') !!}
            <div class="input-group">
                <div class="input-group-addon">
                    <i class="fa fa-filter"></i>
                </div>
                {!! Form::select('insurance_policy_id', $insurances, null, ['id' => 'insurance_policy_id','class' => 'form-control select2', 'style' => 'width: 100%','placeholder'=>'Seleccione una Póliza...']) !!}
            </div>
        </div>
    </div>
   
</div>


