<!-- Scripts necesarios -->
<script src="/sisgeasic/assets/vendor/jquery/jquery.min.js"></script>
<script src="/sisgeasic/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="/sisgeasic/assets/vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="/sisgeasic/assets/js/sb-admin-2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Forzar cierre de dropdown al hacer clic fuera
    $(document).on('click', function (event) {
        var $trigger = $(".nav-item.dropdown");
        if($trigger !== event.target && !$trigger.has(event.target).length){
            $trigger.find('.dropdown-menu').removeClass('show');
        }
    });
});
</script>
<!-- ...NO debe haber </body> ni </html> aquí... -->