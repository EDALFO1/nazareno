@auth
    <script>
        (function () {
            if (!sessionStorage.getItem('ccd_sesion_pestana')) {
                window.location.replace(@js(route('sesion-expirada')));
            }
        })();
    </script>
@else
    <script>
        sessionStorage.setItem('ccd_sesion_pestana', '1');
    </script>
@endauth
