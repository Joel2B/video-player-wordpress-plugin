<?php namespace Xbox\Includes;

class FieldTypes {
    protected $field = null;

    /*
    |---------------------------------------------------------------------------------------------------
    | Constructor de la clase
    |---------------------------------------------------------------------------------------------------
     */
    public function __construct( $field = null ) {
        $this->field = $field;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Función por defecto, permite contruir un tipo de campo inexsistente
    |---------------------------------------------------------------------------------------------------
     */
    public function __call( $field_type, $arguments ) {
        ob_start();
        do_action( "xbox_build_{$field_type}", $this->field->xbox, $this->field, $this->field->get_value(), $this );
        return ob_get_clean();
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Construye el campo
    |---------------------------------------------------------------------------------------------------
     */
    public function build() {
        $type = $this->field->arg( 'type' );
        return $this->{$type}( $type );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Build field type: textarea
    |---------------------------------------------------------------------------------------------------
     */
    public function code_editor( $type = '' ) {
        $return   = '';
        $value    = $this->field->get_value( true, 'esc_textarea' );
        $id       = Functions::get_id_attribute_by_name( $this->field->get_name() );
        $language = $this->field->arg( 'options', 'language' );
        $theme    = $this->field->arg( 'options', 'theme' );
        $height   = $this->field->arg( 'options', 'height' );
        $return .= "<div class='xbox-code-editor' id='{$id}-ace' data-language='$language' data-theme='$theme' style='height: $height'>$value</div>";
        $return .= $this->build_textarea( 'code_editor' );
        return $return;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Build field type: colorpicker
    |---------------------------------------------------------------------------------------------------
     */
    public function colorpicker( $type = '' ) {
        $value  = $this->field->get_value();
        $value  = $this->field->validate_colorpicker( $value );
        $return = $this->build_input( 'text', $value );
        $return .= "<div class='xbox-colorpicker-preview'>";
        $return .= "<span class='xbox-colorpicker-color' value='$value'></span>";
        $return .= "</div>";
        return $return;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Build field type: checkbox
    |---------------------------------------------------------------------------------------------------
     */
    public function checkbox( $type = '' ) {
        return $this->radio( 'checkbox' );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Build field type: file
    |---------------------------------------------------------------------------------------------------
     */
    public function file( $type = '' ) {
        $return            = '';
        $name              = $this->field->get_name();
        $value             = $this->field->get_value( true );
        $options           = $this->field->arg( 'options' );
        $preview_size      = $options['preview_size'];
        $data_preview_size = json_encode( $preview_size );
        $attachment_field  = $this->field->get_parent()->get_field( $this->field->id . '_id' );
        $attachment_name   = $attachment_field->get_name( $this->field->index );

        $btn_class  = "xbox-btn-input xbox-btn xbox-btn-icon xbox-btn-small xbox-btn-teal xbox-upload-file {$options['upload_file_class']}";
        $wrap_class = "xbox-wrap-preview xbox-wrap-preview-file";

        if ( $options['multiple'] === true ) {
            $btn_class .= " xbox-btn-preview-multiple";
            $wrap_class .= " xbox-wrap-preview-multiple";
        } else {
            $return .= $this->build_input( 'text' );
        }

        $return .= "<a class='$btn_class' data-field-name='$name' title='{$options['upload_file_text']}'><i class='xbox-icon xbox-icon-upload'></i></a>";
        $return .= "<ul class='$wrap_class xbox-clearfix' data-field-name='$attachment_name' data-preview-size='$data_preview_size' data-synchronize-selector='{$options['synchronize_selector']}'>";

        if ( ! Functions::is_empty( $value ) ) {
            if ( $options['multiple'] === true ) {
                foreach ( $value as $index => $val ) {
                    $return .= $this->build_file_item( $preview_size, $val, $options['multiple'], $attachment_field, $index );
                }
            } else {
                $return .= $this->build_file_item( $preview_size, $value, $options['multiple'], $attachment_field, null );
            }
        }
        $return .= "</ul>";
        return $return;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Build item file
    |---------------------------------------------------------------------------------------------------
     */
    private function build_file_item( $preview_size, $value, $multiple, $attachment_field, $index = null ) {
        $return     = '';
        $mime_types = (array) $this->field->arg( 'options', 'mime_types' );
        if ( ! Functions::is_empty( $mime_types ) ) {
            $extension = Functions::get_file_extension( $value );
            if ( ! $extension || ! in_array( $extension, $mime_types ) ) {
                return '';
            }
        }

        $attachment_name = $attachment_field->get_name( $this->field->index );
        $attachment_id   = $attachment_field->get_value( true, 'esc_attr', $this->field->index );

        if ( $multiple === true && ! empty( $attachment_id ) ) {
            $attachment_id = isset( $attachment_id[$index] ) ? $attachment_id[$index] : false;
        }

        if ( empty( $attachment_id ) ) {
            $attachment_id = Functions::get_attachment_id_by_url( $value );
        }

        $item_class = 'xbox-preview-item xbox-preview-file';
        $item_body  = '';
        $inputs     = $multiple == true ? $this->build_input( 'hidden', $value ) : '';
        $inputs .= "<input type='hidden' name='{$attachment_name}' value='{$attachment_id}' class='xbox-attachment-id'>";

        if ( $this->is_image_file( $value ) ) {
            $item_class .= ' xbox-preview-image';
            if ( ! empty( $attachment_id ) ) {
                $width     = (int) $preview_size['width'];
                $height    = ( $preview_size['height'] == 'auto' ) ? $width : (int) $preview_size['height'];
                $item_body = wp_get_attachment_image( $attachment_id, array( $width, $height ), false, array( 'class' => 'xbox-image xbox-preview-handler' ) );
            } else {
                $item_body = "<img src='$value' style='width: {$preview_size['width']}; height: {$preview_size['height']}' class='xbox-image xbox-preview-handler'>";
            }
        } else if ( $this->is_video_file( $value ) ) {
            //Thumbnail
            global $post;
            $thumb = get_post_meta( $post->ID, 'thumb', true );
            if ( has_post_thumbnail() ) {
                $thumb_id  = get_post_thumbnail_id();
                $thumb_url = wp_get_attachment_image_src( $thumb_id, 'wp' . 'st_thumb_large', true );
                $poster    = $thumb_url[0];
            } else {
                $poster = $thumb;
            }
            $item_class .= ' xbox-preview-video';
            $format_path = parse_url( $value, PHP_URL_PATH );
            $format      = explode( '.', $format_path );
            $format      = end( $format );

            // get domain name from url to prevent false positive (eg. bexvideos.com).
            $video_domain = str_ireplace( 'www.', '', wp_parse_url( $value, PHP_URL_HOST ) );

            $source_website = '';
            if ( 'pornhub.com' === $video_domain ) {
                $source_website = 'pornhub';
            }
            if ( 'redtube.com' === $video_domain ) {
                $source_website = 'redtube';
            }
            if ( 'spankwire.com' === $video_domain ) {
                $source_website = 'spankwire';
            }
            if ( 'tube8.com' === $video_domain ) {
                $source_website = 'tube8';
            }
            if ( 'xhamster.com' === $video_domain ) {
                $source_website = 'xhamster';
            }
            if ( 'xvideos.com' === $video_domain ) {
                $source_website = 'xvideos';
            }
            if ( 'youporn.com' === $video_domain ) {
                $source_website = 'youporn';
            }
            if ( 'drive.google.com' === $video_domain ) {
                $source_website = 'google_drive';
            }
            if ( 'youtube.com' === $video_domain ) {
                $source_website = 'youtube';
            }

            if ( ! empty( $attachment_id ) ) {
                $width     = (int) $preview_size['width'];
                $height    = ( $preview_size['height'] == 'auto' ) ? $width : (int) $preview_size['height'];
                $item_body = wp_get_attachment_image( $attachment_id, array( $width, $height ), false, array( 'class' => 'xbox-image xbox-preview-handler' ) );

            } else if ( $source_website == 'pornhub' ) {

                $source_id = explode( '/', $value );
                $source_id = str_replace( 'view_video.php?viewkey=', '', $source_id[3] );
                $item_body = '<iframe src="https://www.pornhub.com/embed/' . $source_id . '" frameborder="0" width="560" height="340" scrolling="no" allowfullscreen></iframe>';

            } else if ( $source_website == 'redtube' ) {

                $source_id = explode( '/', $value );
                $source_id = $source_id[3];
                $item_body = '<iframe src="https://embed.redtube.com/?id=' . $source_id . '&bgcolor=000000" frameborder="0" width="560" height="315" scrolling="no" allowfullscreen></iframe>';

            } else if ( $source_website == 'spankwire' ) {

                $source_id = explode( '/', $value );
                $source_id = str_replace( 'video', '', $source_id[4] );
                $item_body = '<iframe src="https://www.spankwire.com/EmbedPlayer.aspx?ArticleId=' . $source_id . '" frameborder="0" height="537" width="660" scrolling="no" name="spankwire_embed_video"></iframe>';

            } else if ( $source_website == 'tube8' ) {

                $exploded_url    = explode( '/', $value );
                $source_category = $exploded_url[3];
                $source_slug     = $exploded_url[4];
                $source_id       = $exploded_url[5];
                $item_body       = '<iframe src="https://www.tube8.com/embed/' . $source_category . '/' . $source_slug . '/' . $source_id . '" frameborder="0" width="640" height="360" scrolling="no" name="t8_embed_video"></iframe>';

            } else if ( $source_website == 'xhamster' ) {

                $source_id = explode( '/', $value );
                $source_id = explode( '-', $source_id[4] );
                $source_id = end( $source_id );
                $item_body = '<iframe src="//xhamster.com/xembed.php?video=' . $source_id . '" frameborder="0" width="640" height="360" scrolling="no"></iframe>';

            } else if ( $source_website == 'xvideos' ) {

                $source_id = explode( '/', $value );
                $source_id = str_replace( 'video', '', $source_id[3] );
                $item_body = '<iframe src="https://flashservice.xvideos.com/embedframe/' . $source_id . '" frameborder="0" width="640" height="360" scrolling="no"></iframe>';

            } else if ( $source_website == 'youporn' ) {

                $source_id   = explode( '/', $value );
                $source_id   = $source_id[4];
                $source_slug = $source_id[5];
                $item_body   = '<iframe src="https://www.youporn.com/embed/' . $source_id . '/' . $source_slug . '" frameborder="0" width="640" height="360" scrolling="no"></iframe>';

            } else if ( $source_website == 'google_drive' ) {

                $video_url_gd = str_replace( 'view', 'preview', $value );
                $item_body    = '<iframe src="' . $video_url_gd . '" frameborder="0" width="640" height="360" scrolling="no"></iframe>';

            } else if ( $source_website == 'youtube' ) {
                $youtube_array = explode( '?v=', $value );
                $youtube_id    = $youtube_array[1];
                $item_body     = '<iframe width="560" height="315" src="https://www.youtube.com/embed/' . $youtube_id . '" frameborder="0" gesture="media" allow="encrypted-media" allowfullscreen></iframe>';

            } else {

                $item_body = '<video controls poster="' . $poster . '" width="640" height="360"><source src="' . $value . '" type="video/' . $format . '"></video>';
            }
        } else {
            $file_link     = $value;
            $file_mime     = 'aplication';
            $file_name     = 'Filename';
            $file_icon_url = wp_mime_type_icon();
            if ( $file = get_post( $attachment_id, ARRAY_A ) ) {
                $file_link     = isset( $file['guid'] ) ? $file['guid'] : $file_link;
                $file_mime     = isset( $file['post_mime_type'] ) ? $file['post_mime_type'] : $file_mime;
                $file_name     = wp_basename( get_attached_file( $attachment_id ) );
                $file_icon_url = wp_mime_type_icon( $attachment_id );
            }
            $item_body = "<img src='$file_icon_url' class='xbox-preview-icon-file xbox-preview-handler'><a href='$file_link' class='xbox-preview-download-link'>$file_name</a><span class='xbox-preview-mime xbox-preview-handler'>$file_mime</span>";
        }

        $return .= "<li class='{$item_class}'>";
        $return .= $inputs;
        $return .= $item_body;
        $return .= "<a class='xbox-btn xbox-btn-iconize xbox-btn-small xbox-btn-red xbox-remove-preview'><i class='xbox-icon xbox-icon-times-circle'></i></a>";
        $return .= "</li>";

        return $return;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Build field type: html
    |---------------------------------------------------------------------------------------------------
     */
    public function html( $type = '' ) {
        $return = '';
        $name   = $this->field->get_name();

        switch ( $name ) {
            case 'thumbnails':
                global $post;
                $thumbs = get_post_meta( $post->ID, 'thumbs', false );
                if ( is_array( $thumbs ) ) {
                    $return .= '<button class="select-thumbs xbox-btn xbox-btn-pink" type="button">' . __( 'Add thumbnails', 'xbox' ) . ' <i class="xbox-icon xbox-icon-upload"></i></button>';
                    $return .= '<ul class="thumbs-list">';
                    foreach ( (array) $thumbs as $thumb ) {
                        if ( ! empty( $thumb ) ) {
                            $return .= '<li><img class="xbox-image" src="' . $thumb . '"/>';
                            $return .= "<a class='xbox-btn xbox-btn-iconize xbox-btn-small xbox-btn-red xbox-remove-preview'";
                            /*if( empty( $value ) ){
                            $return .= " style='display: none;'";
                            }*/
                            $return .= "><i class='xbox-icon xbox-icon-times-circle'></i></a><i class='fa fa-spinner fa-pulse fa-3x fa-fw'></i>";
                            $return .= '</li>';
                        }
                    }
                    $return .= '</ul><div class="clear"></div>';
                }
                return $return;
            case 'duration':
                global $post;
                $hh       = '';
                $mm       = '';
                $ss       = '';
                $duration = intval( get_post_meta( $post->ID, 'duration', true ) );
                if ( $duration ) {
                    $hh = gmdate( "H", $duration );
                    $mm = gmdate( "i", $duration );
                    $ss = gmdate( "s", $duration );
                }
                $return .= '<select name="duration_hh">
								<option ' . selected( $hh, '00', false ) . ' value="00">00</option>
								<option ' . selected( $hh, '01', false ) . ' value="01">01</option>
								<option ' . selected( $hh, '02', false ) . ' value="02">02</option>
								<option ' . selected( $hh, '03', false ) . ' value="03">03</option>
								<option ' . selected( $hh, '04', false ) . ' value="04">04</option>
								<option ' . selected( $hh, '05', false ) . ' value="05">05</option>
								<option ' . selected( $hh, '06', false ) . ' value="06">06</option>
								<option ' . selected( $hh, '07', false ) . ' value="07">07</option>
								<option ' . selected( $hh, '08', false ) . ' value="08">08</option>
								<option ' . selected( $hh, '09', false ) . ' value="09">09</option>
								<option ' . selected( $hh, '10', false ) . ' value="10">10</option>
							</select>
							<span class="xbox-unit xbox-noselect">H</span>
							<select name="duration_mm">
								<option ' . selected( $mm, '00', false ) . ' value="00">00</option>
								<option ' . selected( $mm, '01', false ) . ' value="01">01</option>
								<option ' . selected( $mm, '02', false ) . ' value="02">02</option>
								<option ' . selected( $mm, '03', false ) . ' value="03">03</option>
								<option ' . selected( $mm, '04', false ) . ' value="04">04</option>
								<option ' . selected( $mm, '05', false ) . ' value="05">05</option>
								<option ' . selected( $mm, '06', false ) . ' value="06">06</option>
								<option ' . selected( $mm, '07', false ) . ' value="07">07</option>
								<option ' . selected( $mm, '08', false ) . ' value="08">08</option>
								<option ' . selected( $mm, '09', false ) . ' value="09">09</option>
								<option ' . selected( $mm, '10', false ) . ' value="10">10</option>
								<option ' . selected( $mm, '11', false ) . ' value="11">11</option>
								<option ' . selected( $mm, '12', false ) . ' value="12">12</option>
								<option ' . selected( $mm, '13', false ) . ' value="13">13</option>
								<option ' . selected( $mm, '14', false ) . ' value="14">14</option>
								<option ' . selected( $mm, '15', false ) . ' value="15">15</option>
								<option ' . selected( $mm, '16', false ) . ' value="16">16</option>
								<option ' . selected( $mm, '17', false ) . ' value="17">17</option>
								<option ' . selected( $mm, '18', false ) . ' value="18">18</option>
								<option ' . selected( $mm, '19', false ) . ' value="19">19</option>
								<option ' . selected( $mm, '20', false ) . ' value="20">20</option>
								<option ' . selected( $mm, '21', false ) . ' value="21">21</option>
								<option ' . selected( $mm, '22', false ) . ' value="22">22</option>
								<option ' . selected( $mm, '23', false ) . ' value="23">23</option>
								<option ' . selected( $mm, '24', false ) . ' value="24">24</option>
								<option ' . selected( $mm, '25', false ) . ' value="25">25</option>
								<option ' . selected( $mm, '26', false ) . ' value="26">26</option>
								<option ' . selected( $mm, '27', false ) . ' value="27">27</option>
								<option ' . selected( $mm, '28', false ) . ' value="28">28</option>
								<option ' . selected( $mm, '29', false ) . ' value="29">29</option>
								<option ' . selected( $mm, '30', false ) . ' value="30">30</option>
								<option ' . selected( $mm, '31', false ) . ' value="31">31</option>
								<option ' . selected( $mm, '32', false ) . ' value="32">32</option>
								<option ' . selected( $mm, '33', false ) . ' value="33">33</option>
								<option ' . selected( $mm, '34', false ) . ' value="34">34</option>
								<option ' . selected( $mm, '35', false ) . ' value="35">35</option>
								<option ' . selected( $mm, '36', false ) . ' value="36">36</option>
								<option ' . selected( $mm, '37', false ) . ' value="37">37</option>
								<option ' . selected( $mm, '38', false ) . ' value="38">38</option>
								<option ' . selected( $mm, '39', false ) . ' value="39">39</option>
								<option ' . selected( $mm, '40', false ) . ' value="40">40</option>
								<option ' . selected( $mm, '41', false ) . ' value="41">41</option>
								<option ' . selected( $mm, '42', false ) . ' value="42">42</option>
								<option ' . selected( $mm, '43', false ) . ' value="43">43</option>
								<option ' . selected( $mm, '44', false ) . ' value="44">44</option>
								<option ' . selected( $mm, '45', false ) . ' value="45">45</option>
								<option ' . selected( $mm, '46', false ) . ' value="46">46</option>
								<option ' . selected( $mm, '47', false ) . ' value="47">47</option>
								<option ' . selected( $mm, '48', false ) . ' value="48">48</option>
								<option ' . selected( $mm, '49', false ) . ' value="49">49</option>
								<option ' . selected( $mm, '50', false ) . ' value="50">50</option>
								<option ' . selected( $mm, '51', false ) . ' value="51">51</option>
								<option ' . selected( $mm, '52', false ) . ' value="52">52</option>
								<option ' . selected( $mm, '53', false ) . ' value="53">53</option>
								<option ' . selected( $mm, '54', false ) . ' value="54">54</option>
								<option ' . selected( $mm, '55', false ) . ' value="55">55</option>
								<option ' . selected( $mm, '56', false ) . ' value="56">56</option>
								<option ' . selected( $mm, '57', false ) . ' value="57">57</option>
								<option ' . selected( $mm, '58', false ) . ' value="58">58</option>
								<option ' . selected( $mm, '59', false ) . ' value="59">59</option>
							</select>
							<span class="xbox-unit xbox-noselect">MIN</span>
							<select name="duration_ss">
								<option ' . selected( $ss, '00', false ) . ' value="00">00</option>
								<option ' . selected( $ss, '01', false ) . ' value="01">01</option>
								<option ' . selected( $ss, '02', false ) . ' value="02">02</option>
								<option ' . selected( $ss, '03', false ) . ' value="03">03</option>
								<option ' . selected( $ss, '04', false ) . ' value="04">04</option>
								<option ' . selected( $ss, '05', false ) . ' value="05">05</option>
								<option ' . selected( $ss, '06', false ) . ' value="06">06</option>
								<option ' . selected( $ss, '07', false ) . ' value="07">07</option>
								<option ' . selected( $ss, '08', false ) . ' value="08">08</option>
								<option ' . selected( $ss, '09', false ) . ' value="09">09</option>
								<option ' . selected( $ss, '10', false ) . ' value="10">10</option>
								<option ' . selected( $ss, '11', false ) . ' value="11">11</option>
								<option ' . selected( $ss, '12', false ) . ' value="12">12</option>
								<option ' . selected( $ss, '13', false ) . ' value="13">13</option>
								<option ' . selected( $ss, '14', false ) . ' value="14">14</option>
								<option ' . selected( $ss, '15', false ) . ' value="15">15</option>
								<option ' . selected( $ss, '16', false ) . ' value="16">16</option>
								<option ' . selected( $ss, '17', false ) . ' value="17">17</option>
								<option ' . selected( $ss, '18', false ) . ' value="18">18</option>
								<option ' . selected( $ss, '19', false ) . ' value="19">19</option>
								<option ' . selected( $ss, '20', false ) . ' value="20">20</option>
								<option ' . selected( $ss, '21', false ) . ' value="21">21</option>
								<option ' . selected( $ss, '22', false ) . ' value="22">22</option>
								<option ' . selected( $ss, '23', false ) . ' value="23">23</option>
								<option ' . selected( $ss, '24', false ) . ' value="24">24</option>
								<option ' . selected( $ss, '25', false ) . ' value="25">25</option>
								<option ' . selected( $ss, '26', false ) . ' value="26">26</option>
								<option ' . selected( $ss, '27', false ) . ' value="27">27</option>
								<option ' . selected( $ss, '28', false ) . ' value="28">28</option>
								<option ' . selected( $ss, '29', false ) . ' value="29">29</option>
								<option ' . selected( $ss, '30', false ) . ' value="30">30</option>
								<option ' . selected( $ss, '31', false ) . ' value="31">31</option>
								<option ' . selected( $ss, '32', false ) . ' value="32">32</option>
								<option ' . selected( $ss, '33', false ) . ' value="33">33</option>
								<option ' . selected( $ss, '34', false ) . ' value="34">34</option>
								<option ' . selected( $ss, '35', false ) . ' value="35">35</option>
								<option ' . selected( $ss, '36', false ) . ' value="36">36</option>
								<option ' . selected( $ss, '37', false ) . ' value="37">37</option>
								<option ' . selected( $ss, '38', false ) . ' value="38">38</option>
								<option ' . selected( $ss, '39', false ) . ' value="39">39</option>
								<option ' . selected( $ss, '40', false ) . ' value="40">40</option>
								<option ' . selected( $ss, '41', false ) . ' value="41">41</option>
								<option ' . selected( $ss, '42', false ) . ' value="42">42</option>
								<option ' . selected( $ss, '43', false ) . ' value="43">43</option>
								<option ' . selected( $ss, '44', false ) . ' value="44">44</option>
								<option ' . selected( $ss, '45', false ) . ' value="45">45</option>
								<option ' . selected( $ss, '46', false ) . ' value="46">46</option>
								<option ' . selected( $ss, '47', false ) . ' value="47">47</option>
								<option ' . selected( $ss, '48', false ) . ' value="48">48</option>
								<option ' . selected( $ss, '49', false ) . ' value="49">49</option>
								<option ' . selected( $ss, '50', false ) . ' value="50">50</option>
								<option ' . selected( $ss, '51', false ) . ' value="51">51</option>
								<option ' . selected( $ss, '52', false ) . ' value="52">52</option>
								<option ' . selected( $ss, '53', false ) . ' value="53">53</option>
								<option ' . selected( $ss, '54', false ) . ' value="54">54</option>
								<option ' . selected( $ss, '55', false ) . ' value="55">55</option>
								<option ' . selected( $ss, '56', false ) . ' value="56">56</option>
								<option ' . selected( $ss, '57', false ) . ' value="57">57</option>
								<option ' . selected( $ss, '58', false ) . ' value="58">58</option>
								<option ' . selected( $ss, '59', false ) . ' value="59">59</option>
							</select>
							<span class="xbox-unit xbox-noselect">SEC</span>';
                return $return;

            default:
                return $this->field->get_result_callback( 'content' );
        }
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Build field type: image
    |---------------------------------------------------------------------------------------------------
     */
    public function image( $type = '' ) {
        $return      = '';
        $value       = $this->field->get_value();
        $image_class = 'xbox-element-image ' . $this->field->arg( 'options', 'image_class' );

        if ( $this->field->arg( 'options', 'hide_input' ) ) {
            $return .= $this->build_input( 'hidden' );
        } else {
            $return .= $this->build_input( 'text' );
            $return .= "<a class='xbox-btn-input xbox-btn xbox-btn-icon xbox-btn-small xbox-btn-teal xbox-get-image' title='Preview'><i class='xbox-icon xbox-icon-refresh'></i></a>";
        }

        $return .= "<ul class='xbox-wrap-preview xbox-wrap-image xbox-clearfix' data-image-class='{$image_class}'>";
        $return .= "<li class='xbox-preview-item xbox-preview-image'>";
        $return .= "<img src='{$value}' class='{$image_class}'";
        if ( empty( $value ) ) {
            $return .= " style='display: none;'";
        }
        $return .= ">";
        $return .= "<a class='xbox-btn xbox-btn-iconize xbox-btn-small xbox-btn-red xbox-remove-preview'";
        if ( empty( $value ) ) {
            $return .= " style='display: none;'";
        }
        $return .= "><i class='xbox-icon xbox-icon-times-circle'></i></a>";
        $return .= "</li>";
        $return .= "</ul>";
        return $return;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Build field type: image_seletor
    |---------------------------------------------------------------------------------------------------
     */
    public function import( $type = '' ) {
        $return     = '';
        $items      = $this->field->arg( 'items' );
        $items_desc = $this->field->arg( 'items_desc' );
        $options    = $this->field->arg( 'options' );
        $value      = $this->field->get_value();
        if ( ! Functions::is_empty( $items ) ) {
            $has_images = false;
            foreach ( $items as $item_key => $item_val ) {
                if ( Functions::get_file_extension( $item_val ) ) {
                    $has_images = true;
                }
            }
            if ( $has_images ) {
                $return .= $this->image_selector();
            } else {
                $return .= $this->radio( 'radio' );
            }
        }

        if ( ! Functions::is_empty( $items_desc ) ) {
            foreach ( $items_desc as $item_key => $import_data ) {
                if ( is_array( $import_data ) ) {
                    foreach ( $import_data as $import_key => $import_val ) {
                        if ( Functions::starts_with( 'import_', $import_key ) ) {
                            $return .= "<input type='hidden' name='xbox-import-data[$item_key][$import_key]' value='$import_val'>";
                        }
                    }
                }
            }
        }

        if ( $options['import_from_file'] ) {
            $return .= "<div class='xbox-wrap-input-file'>";
            $return .= "<input type='file' name='xbox-import-file'>";
            $return .= "</div>";
        }
        if ( $options['import_from_url'] ) {
            $return .= "<div class='xbox-wrap-input-url'>";
            $placeholder = __( 'Enter a valid json url', 'xbox' );
            $return .= "<input type='text' name='xbox-import-url' placeholder='$placeholder'>";
            $return .= "</div>";
        }

        $return .= "<input type='button' name='xbox-import' id='xbox-import' class='xbox-btn xbox-btn-{$this->field->xbox->arg( 'skin' )}' value='{$options['import_button_text']}'>";

        return $return;
    }

    public function export( $type = '' ) {
        $return         = '';
        $options        = $this->field->arg( 'options' );
        $data           = $this->field->xbox->get_fields_data( 'json' );
        $file_base_name = "xbox-backup-{$this->field->xbox->id}";
        if ( isset( $options['export_file_name'] ) ) {
            $file_base_name = $options['export_file_name'];
        }
        $file_name = $file_base_name . '-' . date( 'd-m-Y' ) . '.json';
        $return .= "<textarea>$data</textarea>";

        $dir = XBOX_DIR;
        if ( is_dir( $dir . 'backups' ) ) {
            $dir = $dir . 'backups/';
        } else {
            if ( mkdir( $dir . 'backups', 0777, true ) ) {
                $dir = $dir . 'backups/';
            }
        }
        $opendir = opendir( $dir );
        while ( $file = readdir( $opendir ) ) {
            if ( preg_match( "/^({$file_base_name}-.*.json)/i", $file, $name ) ) {
                if ( isset( $name[0] ) && is_writable( $dir . $name[0] ) ) {
                    unlink( $dir . $name[0] );
                }
            }
        }

        if ( false !== file_put_contents( $dir . $file_name, $data ) ) {
            $file_url = XBOX_URL . $file_name;
            if ( stripos( $dir, 'backups' ) !== false ) {
                $file_url = XBOX_URL . 'backups/' . $file_name;
            }
            $return .= "<a href='$file_url' id='xbox-export-btn' class='xbox-btn xbox-btn-{$this->field->xbox->arg( 'skin' )}' target='_blank' download>{$options['export_button_text']}</a>";
        }
        return $return;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Build field type: image_seletor
    |---------------------------------------------------------------------------------------------------
     */
    public function image_selector( $type = '' ) {
        $items = $this->field->arg( 'items' );
        if ( Functions::is_empty( $items ) ) {
            return '';
        }
        $items_desc = $this->field->arg( 'items_desc' );
        $options    = $this->field->arg( 'options' );
        $wrap_class = 'xbox-radiochecks init-image-selector';
        if ( $this->field->arg( 'options', 'in_line' ) == false ) {
            $wrap_class .= ' xbox-vertical';
        }
        $data_image_chooser = json_encode( $options );
        $return             = "<div class='$wrap_class' data-image-selector='$data_image_chooser'>";
        foreach ( $items as $key => $image ) {
            $item_class = "xbox-item-image-selector";
            if (  ( $key == 'from_file' || $key == 'from_url' ) && ( $options['import_from_file'] || $options['import_from_url'] ) ) {
                $item_class .= " xbox-block";
            }
            $return .= "<div class='$item_class' style='width: {$options['width']}'>";
            $label_class = "";
            if ( ! Functions::get_file_extension( $image ) ) {
                $label_class .= "no-image";
            }
            $return .= "<label class='$label_class'>";
            $return .= $this->build_input( $options['like_checkbox'] ? 'checkbox' : 'radio', $key, array( 'data-image' => $image ) );
            $return .= "<span>$image</span>";
            $return .= "</label>";
            if ( isset( $items_desc[$key] ) ) {
                $return .= "<div class='xbox-item-desc'>";
                if ( is_array( $items_desc[$key] ) ) {
                    if ( isset( $items_desc[$key]['title'] ) ) {
                        $return .= "<div class='xbox-item-desc-title'>{$items_desc[$key]['title']}</div>";
                    }
                    if ( isset( $items_desc[$key]['content'] ) ) {
                        $return .= "<div class='xbox-item-desc-content'>{$items_desc[$key]['content']}</div>";
                    }
                } else {
                    $return .= "<div class='xbox-item-desc'>{$items_desc[$key]}</div>";
                }
                $return .= "</div>";
            }
            $return .= "</div>";
        }
        $return .= "</div>";
        return $return;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Build field type: number
    |---------------------------------------------------------------------------------------------------
     */
    public function number( $type = '' ) {
        $attributes = $this->field->arg( 'attributes' );
        $options    = $this->field->arg( 'options' );
        if ( ! Functions::is_empty( $attributes ) ) {
            foreach ( $attributes as $attr => $val ) {
                if ( in_array( $attr, array( 'min', 'max', 'step', 'precision' ) ) ) {
                    $this->field->args['attributes']['data-' . $attr] = $val;
                }
            }
        }

        $unit   = $options['show_unit'] ? $options['unit'] : '';
        $return = $this->build_input( 'text', '', array(), 'esc_attr', array( 'min', 'max', 'step', 'precision' ) );
        $return .= "<div class='xbox-unit xbox-noselect'>{$unit}";
        $return .= "<a href='javascript:;' class='xbox-spinner-control' data-spin='up'><i class='xbox-icon xbox-icon-caret-up'></i>></a>";
        $return .= "<a href='javascript:;' class='xbox-spinner-control' data-spin='down'><i class='xbox-icon xbox-icon-caret-down'></i>></a>";
        $return .= "</div>";
        return $return;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Build field type: oembed
    |---------------------------------------------------------------------------------------------------
     */
    public function oembed( $type = '' ) {
        global $post, $wp_embed;
        $return            = '';
        $oembed_url        = $this->field->get_value();
        $oembed_class      = 'xbox-element-oembed ' . $this->field->arg( 'options', 'oembed_class' );
        $preview_size      = $this->field->arg( 'options', 'preview_size' );
        $data_preview_size = json_encode( $preview_size );
        $return .= $this->build_input( 'text' );
        $return .= "<a class='xbox-btn-input xbox-btn xbox-btn-icon xbox-btn-small xbox-btn-teal xbox-get-oembed' title='{$this->field->arg( 'options', 'get_preview_text' )}'><i class='xbox-icon xbox-icon-refresh'></i></a>";
        $full_width = Functions::ends_with( '%', $preview_size['width'] ) ? 'xbox-oembed-full-width' : '';

        $return .= "<ul class='xbox-wrap-preview xbox-wrap-oembed $full_width xbox-clearfix' data-preview-size='$data_preview_size' data-preview-onload='{$this->field->arg( 'options', 'preview_onload' )}'>";

        /*
        Oembed relentiza la carga de la página. Ahora lo hacemos mediante Ajax, es mucho más rápido.
        Ver includes/class-ajax.php -> get_oembed_ajax();
         */
        if ( ! empty( $oembed_url ) && $this->field->arg( 'options', 'preview_onload' ) ) {
            $oembed = Functions::get_oembed( $oembed_url, $preview_size );
            if ( $oembed['success'] ) {
                $provider = strtolower( Functions::get_oembed_provider( $oembed_url ) );
                $return .= "<li class='xbox-preview-item xbox-preview-oembed'>";
                $return .= "<div class='xbox-oembed xbox-oembed-provider-$provider $oembed_class'>";
                $return .= $oembed['oembed'];
                $return .= "</div>";
                $return .= "<a class='xbox-btn xbox-btn-iconize xbox-btn-small xbox-btn-red xbox-remove-preview'><i class='xbox-icon xbox-icon-times-circle'></i></a>";
                $return .= "</li>";
            } else {
                $return .= $oembed['message'];
            }
        } else {
            $return .= '';
        }

        $return .= "</ul>";
        return $return;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Build field type: radio
    |---------------------------------------------------------------------------------------------------
     */
    public function radio( $type = '' ) {
        $items = $this->field->arg( 'items' );
        if ( Functions::is_empty( $items ) ) {
            return '';
        }
        $wrap_class = "xbox-radiochecks init-icheck";
        if ( $this->field->arg( 'options', 'in_line' ) == false ) {
            $wrap_class .= ' xbox-vertical';
        }
        $return = "<div class='$wrap_class'>";
        foreach ( $items as $key => $display ) {
            $key = (string) $key; //Permite 0 como clave
            $return .= "<label>";
            $return .= $this->build_input( $type, $key ) . $display;
            $return .= "</label>";
        }
        $return .= "</div>";
        return $return;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Build field type: select
    |---------------------------------------------------------------------------------------------------
     */
    public function select( $type = '' ) {
        return $this->build_select( 'select' );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Build field type: switcher
    |---------------------------------------------------------------------------------------------------
     */
    public function switcher( $type = '' ) {
        $attributes                  = $this->field->arg( 'attributes' );
        $attributes['data-switcher'] = json_encode( $this->field->arg( 'options' ) );
        $attributes                  = Functions::nice_array_merge(
            $attributes,
            array( 'class' => 'xbox-element-switcher' ),
            array(),
            array( 'class' => ' ' )
        );
        return $this->build_input( 'hidden', '', $attributes );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Build field type: text
    |---------------------------------------------------------------------------------------------------
     */
    public function text( $type = '' ) {
        return $this->build_input( 'text' );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Build field type: title
    |---------------------------------------------------------------------------------------------------
     */
    public function title() {
        $title_class = $this->field->arg( 'attributes', 'class' );
        $return      = "<h3 class='xbox-field-title $title_class'>{$this->field->arg( 'name' )}</h3>";
        return $return;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Build field type: textarea
    |---------------------------------------------------------------------------------------------------
     */
    public function textarea( $type = '' ) {
        return $this->build_textarea( 'textarea' );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Build field type: textarea
    |---------------------------------------------------------------------------------------------------
     */
    public function build_textarea( $type = '' ) {
        $return     = '';
        $attributes = $this->field->arg( 'attributes' );
        $value      = $this->field->get_value( true, 'esc_textarea' );

        //INTEGRER PREVIEW EMBED
        $name = $this->field->get_name();

        $element_attributes = array(
            'name'  => $this->field->get_name(),
            'id'    => Functions::get_id_attribute_by_name( $this->field->get_name() ),
            'class' => "xbox-element xbox-element-{$type}",
        );

        // Une todos los atributos. Evita el reemplazo de ('name', 'id', 'value', 'checked')
        // y une los valores del atributo 'class'
        $attributes = Functions::nice_array_merge(
            $element_attributes,
            $attributes,
            array( 'name', 'id' ),
            array( 'class' => ' ' )
        );

        foreach ( $attributes as $attr => $val ) {
            if ( is_array( $val ) || $attr == 'value' ) {
                unset( $attributes[$attr] );
            }
        }
        if ( $name == 'embed' && ! empty( $value ) ) {
            return sprintf( '<textarea %s>%s</textarea>', $this->join_attributes( $attributes ), $value ) . '<div class="responsive-player">' . html_entity_decode( $value ) . '</div>';
        } else {
            return sprintf( '<textarea %s>%s</textarea>', $this->join_attributes( $attributes ), $value );
        }
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Build field type: textarea
    |---------------------------------------------------------------------------------------------------
     */
    public function wp_editor( $type = '' ) {
        $return                                        = '';
        $attributes                                    = $this->field->arg( 'attributes' );
        $value                                         = $this->field->get_value( true, 'stripslashes' );
        $id                                            = Functions::get_id_attribute_by_name( $this->field->get_name() );
        $this->field->args['options']['textarea_name'] = $this->field->get_name();

        ob_start();
        wp_editor( $value, $id, $this->field->arg( 'options' ) );
        $return = ob_get_contents();
        ob_end_clean();

        return $return;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Build input
    |---------------------------------------------------------------------------------------------------
     */
    public function build_input( $type = 'text', $value = '', $attributes = array(), $escaping_function = 'esc_attr', $exclude_attributes = array() ) {
        $attributes  = wp_parse_args( $attributes, $this->field->arg( 'attributes' ) );
        $field_value = $this->field->get_value( true, $escaping_function );
        $value       = $value !== '' ? esc_attr( $value ) : $field_value;

        $element_attributes = array(
            'type'  => $type,
            'name'  => $this->field->get_name(),
            'id'    => Functions::get_id_attribute_by_name( $this->field->get_name() ),
            'value' => $value,
            'class' => "xbox-element xbox-element-{$type}",
        );

        if ( $type == 'radio' && $value == $field_value ) {
            $element_attributes['checked'] = 'checked';
        }
        if ( $type == 'checkbox' && is_array( $field_value ) && in_array( $value, $field_value ) ) {
            $element_attributes['checked'] = 'checked';
        }
        if ( $type == 'radio' || $type == 'checkbox' ) {
            unset( $element_attributes['id'] );
            unset( $attributes['id'] );
            if ( isset( $attributes['disabled'] ) ) {
                if ( is_array( $attributes['disabled'] ) && ! Functions::is_empty( $attributes['disabled'] ) ) {
                    if ( in_array( $value, $attributes['disabled'] ) ) {
                        $attributes['disabled'] = 'disabled';
                    } else {
                        unset( $attributes['disabled'] );
                    }
                } else if ( $attributes['disabled'] === true || $attributes['disabled'] == $value ) {
                    $attributes['disabled'] = 'disabled';
                } else {
                    unset( $attributes['disabled'] );
                }
            }
        }

        // Une todos los atributos. Evita el reemplazo de ('type', 'name', 'id', 'value', 'checked')
        // y une los valores del atributo 'class'
        $attributes = Functions::nice_array_merge(
            $element_attributes,
            $attributes,
            array( 'type', 'name', 'id', 'value', 'checked' ),
            array( 'class' => ' ' )
        );

        //Remove invalid attributes
        foreach ( $attributes as $attr => $val ) {
            if ( is_array( $val ) ) {
                unset( $attributes[$attr] );
            }
        }
        //Exclude attributes
        foreach ( $attributes as $attr => $val ) {
            if ( in_array( $attr, $exclude_attributes ) ) {
                unset( $attributes[$attr] );
            }
        }

        $input = sprintf( '<input %s>', $this->join_attributes( $attributes ) );
        return $input;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Build select
    |---------------------------------------------------------------------------------------------------
     */
    public function build_select( $type = 'select', $value = '', $attributes = array(), $escaping_function = 'esc_attr' ) {
        $items = $this->field->arg( 'items' );
        if ( Functions::is_empty( $items ) ) {
            return '';
        }
        $attributes   = wp_parse_args( $attributes, $this->field->arg( 'attributes' ) );
        $options      = $this->field->arg( 'options' );
        $items_select = "";

        //Option none
        if ( isset( $items[''] ) ) {
            $items_select .= "<div class='item' data-value=''>{$items['']}</div>";
            unset( $items[''] );
        }
        if ( $options['sort'] ) {
            $items = Functions::sort( $items, $options['sort'] );
        }

        foreach ( $items as $key => $display ) {
            if ( is_array( $display ) && ! Functions::is_empty( $display ) ) {
                $items_select .= "<div class='divider'></div>";
                $items_select .= "<div class='header'><i class='xbox-icon xbox-icon-tags'></i>$key</div>";
                if ( $options['sort'] ) {
                    $display = Functions::sort( $display, $options['sort'] );
                }
                foreach ( $display as $i => $d ) {
                    $items_select .= "<div class='item' data-value='$i'>$d</div>";
                }
            } else {
                $items_select .= "<div class='item' data-value='$key'>$display</div>";
            }
        }

        $dropdown_class = "xbox-element xbox-element-$type ui fluid selection dropdown";

        if ( $options['search'] === true ) {
            $dropdown_class .= " search";
        }
        if ( $options['multiple'] === true ) {
            $dropdown_class .= " multiple";
        }
        if ( isset( $attributes['class'] ) ) {
            $dropdown_class .= " {$attributes['class']}";
        }

        $name  = $this->field->get_name();
        $value = $this->field->get_value( true, $escaping_function );

        if ( $options['multiple'] === true ) {
            $value = implode( ',', (array) $value );
        }

        $return = "<div class='$dropdown_class' data-max-selections='{$options['max_selections']}'>";
        $return .= "<input type='hidden' name='{$name}' value='$value'>";
        $return .= "<i class='dropdown icon'></i>";
        $return .= "<div class='default text'>{$attributes['placeholder']}</div>";
        $return .= "<div class='menu'>";
        $return .= "<div class='xbox-ui-inner-menu'>";
        $return .= $items_select;
        $return .= "</div>";
        $return .= "</div>";
        $return .= "</div>";

        return $return;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Comprueba si la extensión de una imagen es válida
    |---------------------------------------------------------------------------------------------------
     */
    public function is_image_file( $file_path = '' ) {
        $extension = Functions::get_file_extension( $file_path );
        if ( $extension && in_array( $extension, array( 'png', 'jpg', 'jpeg', 'gif', 'ico', 'svg' ) ) ) {
            return true;
        }
        return false;
    }

    public function is_video_file( $file_path = '' ) {
        $extension = Functions::get_file_extension( $file_path );
        global $post;
        $video_url = get_post_meta( $post->ID, 'video_url', true );
        $format    = explode( '.', $video_url );
        $format    = $format[count( $format ) - 1];
        if ( $extension && in_array( $extension, array( 'flv', 'mp4', 'webm' ) ) ) {
            return true;
        }

        // get domain name from url to prevent false positive (eg. bexvideos.com).
        $video_domain = str_ireplace( 'www.', '', wp_parse_url( $video_url, PHP_URL_HOST ) );
        $tube_domains = array(
            'pornhub.com',
            'redtube.com',
            'spankwire.com',
            'tube8.com',
            'xhamster.com',
            'xvideos.com',
            'youporn.com',
            'drive.google.com',
            'youtube.com',
        );

        if ( $video_domain && in_array( $video_domain, $tube_domains, true ) ) {
            return true;
        }

        return false;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Une los atributos de un campo
    |---------------------------------------------------------------------------------------------------
     */
    public function join_attributes( $attrs = array() ) {
        $attributes = '';
        foreach ( $attrs as $attr => $value ) {
            $quotes = '"';
            if ( stripos( $attr, 'data-' ) !== false ) {
                $quotes = "'";
            }
            $attributes .= sprintf( ' %1$s=%3$s%2$s%3$s', $attr, $value, $quotes );
        }
        return $attributes;
    }
}
