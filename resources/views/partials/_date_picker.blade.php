@section('page_styles')
@parent
<!-- iCheck for checkboxes and radio inputs -->
<link href="{{"/bower_components/admin-lte/plugins/datepicker/datepicker3.css" }}" rel="stylesheet" type="text/css"/>
@append
@section('page_scripts')
@parent
<script src="{{"/bower_components/admin-lte/plugins/datepicker/bootstrap-datepicker.js"}}" type="text/javascript"></script>
<script type="text/javascript">
  $(document).ready(function () {
	$('.datepicker').datepicker({format: "dd/mm/yyyy"});
  });
</script>
@append
