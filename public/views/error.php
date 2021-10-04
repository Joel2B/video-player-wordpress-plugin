<?php
    // Exit if accessed directly.
    defined( 'ABSPATH' ) || exit;

    $title = __( 'Video not available', 'cvp_lang' );
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="robots" content="noindex, nofollow" />
        <meta charset="UTF-8" />
        <title><?php echo $title; ?></title>
        <meta name="robots" content="noindex, nofollow" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, minimum-scale=1, user-scalable=no" />
        <link rel="stylesheet" href="<?php echo esc_url( CVP_URL . 'public/assets/css/styles.css?v=' . CVP_VERSION ); ?>" />
    </head>
    <body>
        <?php
            $related = new Related( false, 'video-not-available', $title );
            $related->render_view();
        ?>
    </body>
</html>
