<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="robots" content="noindex, nofollow" />
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />
        <title>Custom Video Player</title>
        <script src="https://watchonline.nom.es/player/v1/current/player.min.js"></script>
        <link rel="stylesheet" href="<?php echo esc_url( CVP_URL . 'public/assets/css/styles.css?v=' . CVP_VERSION ); ?>" />
    </head>
    <body>
        <?php
            include 'views/player.php';
        ?>
        <script>
            const player = JSON.parse('<?php echo $player->get_config(); ?>');
        </script>
        <script src="<?php echo CVP_URL . 'public/assets/js/script.js' ?>"></script>
    </body>
</html>
