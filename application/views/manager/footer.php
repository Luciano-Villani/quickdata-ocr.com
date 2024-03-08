


<?php
        if (isset($_SESSION['save_data'])) {


        ?>
<div class="">
    <div id="mensaje"><?= $_SESSION['save_data']["mensaje"]?></div>
    <div id="seccion"><?= $_SESSION['save_data']["seccion"]?></div>
    <div id="esatdo"><?= $_SESSION['save_data']["estado"]?></div>
    <div id="status"><?= $_SESSION['save_data']["status"]?></div>
</div>
<?php 
        }
?>
<script>


    $(function() {
        <?php
        if (isset($_SESSION['save_data'])) {
        ?>

            new PNotify({
                title:$("#seccion").html(),
                text: $("#mensaje").html(),
                icon: 'icon-checkmark3',
                // saddclass: 'alert-styled-left',
                type: $("#status").html()
            });

        <?php


            $this->session->unset_userdata('save_data');
        }

        ?>
    });
</script>

<script>
    function alertas(data) {

        console.log('footer.php - 47');
        console.log(data);
        new PNotify({
                title:data.title,
                text: data.mensaje,
                icon: 'icon-checkmark3',
                addclass: 'alert-styled-left',
                type: data.status
            });

            <?php


            $this->session->unset_userdata('save_data');

        ?>
    }
    

</script>


</body>

</html>