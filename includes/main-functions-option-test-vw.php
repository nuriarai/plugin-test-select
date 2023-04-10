<?php


function create_block_test_select_block_init()
{
    register_block_type(__DIR__ . '/build');
}
add_action('init', 'create_block_test_select_block_init');


// register meta box
function meta_fields_add_meta_box()
{
    add_meta_box(
        'meta_fields_meta_box',
        __('Custom Meta Box'),
        'meta_fields_build_meta_box_callback',
        'page',
        'side',
        'default'
    );
}

// build meta box
function meta_fields_build_meta_box_callback($post)
{
    wp_nonce_field('meta_fields_save_meta_box_data', 'meta_fields_meta_box_nonce');
    $title = get_post_meta($post->ID, '_meta_fields_sportsbook_select', true);

    $file = plugin_dir_path(__DIR__) . 'data.json';

    if (file_exists($file)) {
        $data = file_get_contents($file); //data read from json 
        $sportsbooks = json_decode($data, true);
    } else {
        //Todo: Error handling and an admin interface to get the file
        echo "File not exist";
    }
?>
    <div class="custom-inside">
        <label for="meta_fields_sportsbook_select">Sportsbook</label>
        <select type="text" id="meta_fields_sportsbook_select" name="meta_fields_sportsbook_select" value="<?php echo esc_attr($title); ?>">
            <?php foreach ($sportsbooks as $key => $value) { ?>
                <option value="<?php echo $value; ?>"><?php echo $value; ?></option>
            <?php } ?>
        </select>
    </div>
<?php
}

add_action('add_meta_boxes', 'meta_fields_add_meta_box');


// save metadata
function meta_fields_save_meta_box_data($post_id)
{
    if (!isset($_POST['meta_fields_meta_box_nonce']))
        return;
    if (!wp_verify_nonce($_POST['meta_fields_meta_box_nonce'], 'meta_fields_save_meta_box_data'))
        return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;
    if (!current_user_can('edit_post', $post_id))
        return;

    if (!isset($_POST['meta_fields_sportsbook_select']))
        return;

    $title = sanitize_text_field($_POST['meta_fields_sportsbook_select']);


    update_post_meta($post_id, '_meta_fields_sportsbook_select', $title);
}
add_action('save_post', 'meta_fields_save_meta_box_data');


function wpse_alter_title($title, $id)
{
    // alter the title here (only will run if the title is call with the_title() function in the theme)
    $new_title = $title;
    if (!is_admin()) {
        $new_title = '<h1 class="entry-title">' .  esc_attr(get_post_meta($id, '_meta_fields_sportsbook_select', true)) . " " . $title . "</h1>";
    }
    return $new_title;
}

function wpse_load_alter_title()
{
    add_filter('the_title', 'wpse_alter_title', 20, 2);
}
add_action('init', 'wpse_load_alter_title');
