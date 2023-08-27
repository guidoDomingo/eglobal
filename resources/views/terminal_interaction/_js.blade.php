<!-- jQuery 2.1.4 -->
<script src="{{ '/bower_components/admin-lte/plugins/jQuery/jQuery-2.1.4.min.js' }}"></script>
<script src="/js/jquery-ui.js"></script>

<!-- Bootstrap 3.3.4 -->
<script src="{{ '/bower_components/admin-lte/bootstrap/js/bootstrap.min.js' }}"></script>

<!-- AdminLTE App -->
<script src="{{ '/bower_components/admin-lte/dist/js/app.min.js' }}"></script>
<script src="{{ '/assets/js/libs/libs.js' }}"></script>
<script>
    $(document).ready(function() {
        $(window).scroll(function() {
            if ($(this).scrollTop() > 50) {
                $('#back-to-top').fadeIn();
            } else {
                $('#back-to-top').fadeOut();
            }
        });

        $('#back-to-top').click(function() {
            $('#back-to-top').tooltip('hide');
            $('body,html').animate({
                scrollTop: 0
            }, 800);
            return false;
        });

        $('#back-to-top').tooltip('show');
    });
    var token = "{{ csrf_token() }}";
</script>
@yield('page_scripts')
