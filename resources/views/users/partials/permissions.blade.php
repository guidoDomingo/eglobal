
@section('page_styles')
@parent
<!-- iCheck for checkboxes and radio inputs -->
<link href="{{ '/bower_components/admin-lte/plugins/iCheck/all.css' }}" rel="stylesheet" type="text/css" />
@append


<div class="row">
@for ($i = 0; $i < $permissions->count(); $i++)
    <div class="col-md-12">
        <input type="checkbox" name="permissions[{{ $permissions[$i]->permission }}][state]"
            id="{{ $permissions[$i]->id }}" @if ($permissions[$i]->has) checked @endif value="true" />

        <label for="{{ $permissions[$i]->id }}">
            {{ $permissions[$i]->description }}
        </label>

        <input name="permissions[{{ $permissions[$i]->permission }}][inherited]" type="hidden"
            value="{{ is_null($permissions[$i]->inherited) ? null : ($permissions[$i]->inherited ? 1 : 0) }}" />
    </div>

    <br /><br />
@endfor
</div>

@section('page_scripts')
@parent
<!-- iCheck 1.0.1 -->
<script src="{{ '/bower_components/admin-lte/plugins/iCheck/icheck.min.js' }}" type="text/javascript"></script>

<script>
    $('input[type=checkbox], input[type=radio]').each(function() {
        var self = $(this),
            label = self.next(),
            label_text = label.text();

        var inputName = self.attr('name');
        inputName = inputName.replace('state', 'inherited');

        var inheritedInput = $('[name="' + inputName + '"]');

        var checkedCheckboxClass = 'icheckbox_line-blue checked';
        var checkedRadioClass = 'iradio_line-blue checked';
        var uncheckedCheckboxClass = "icheckbox_line-grey";
        var uncheckedRadioClass = "iradio_line-grey";

        if (inheritedInput.val() == '0') {
            checkedCheckboxClass = 'icheckbox_line-purple checked';
            checkedRadioClass = 'iradio_line-purple checked';
            uncheckedCheckboxClass = "icheckbox_line-aero";
            uncheckedRadioClass = "iradio_line-aero";
        }

        label.remove();
        self.iCheck({
            checkedCheckboxClass: checkedCheckboxClass,
            checkedRadioClass: checkedRadioClass,
            uncheckedCheckboxClass: uncheckedCheckboxClass,
            uncheckedRadioClass: uncheckedRadioClass,
            insert: '<div class="icheck_line-icon"></div>' + label_text
        });

        self.on('ifChanged', function(event) {
            var input = event.target;

            var inputName = $(input).attr('name');
            inputName = inputName.replace('state', 'inherited');

            var inheritedInput = $('[name="' + inputName + '"]');
            $(inheritedInput).val('0');

            $(input).iCheck({
                checkedCheckboxClass: 'icheckbox_line-purple checked',
                checkedRadioClass: 'iradio_line-purple checked',
                uncheckedCheckboxClass: 'icheckbox_line-aero',
                uncheckedRadioClass: 'iradio_line-aero',
                insert: '<div class="icheck_line-icon"></div>' + label_text.trim()
            });
        });
    });
</script>
@append