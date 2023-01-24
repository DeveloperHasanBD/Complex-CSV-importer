<h4>Upload Brand CSV </h4>
<?php
brand_csv_import_processing();
?>
<form id="brand_csv_file_importer" enctype='multipart/form-data' action='' method='post'>
    <input class="form-control" type='file' name='brand_csv_file'>
    <input class="form-control mt-4 btn btn-info" type="submit" value="Upload Brand CSV" name="brand_csv_submit_btn">
</form>



<?php

function brand_csv_import_processing()
{

    global $wpdb;
    $terms_table   = $wpdb->prefix . 'terms';
    $term_taxonomy_table   = $wpdb->prefix . 'term_taxonomy';
    $db_name    = $wpdb->dbname;
    $servername = "localhost";
    $username   = "root";
    $password   = "";
    $db         = $db_name;



    $brand_csv_submit_btn = $_POST['brand_csv_submit_btn'] ?? '';
    if ('Upload Brand CSV' == $brand_csv_submit_btn) {

        $allowed_file_type = array('csv');
        $filename = $_FILES['brand_csv_file']['name'];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (in_array($ext, $allowed_file_type)) {
            $handle = fopen($_FILES['brand_csv_file']['tmp_name'], "r");

            while (($data = fgetcsv($handle)) !== FALSE) {

                global $wpdb;
                if ('ID' == $data[0]) {
                } else {

                    $first = $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."terms` WHERE `name` = '".$data[1]."'");
                    $second = $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."terms` WHERE `name` = '".$data[2]."'");
                    $third = $wpdb->get_row("SELECT * FROM `".$wpdb->prefix."terms` WHERE `name` = '".$data[3]."'");
                    
                    $cat_id = 0;
                    if(isset($first->term_id))
                    {
                        $cat_id = $first->term_id;
                    }else{
                        $cat_defaults = array(
                            'taxonomy'             => 'brand_cat',
                            'cat_name'             => $data[1],
                            'category_description' => '',
                            'category_nicename'    => '',
                            'category_parent'      => '',
                        );
                        $cat_id =   wp_insert_category($cat_defaults);
                    }

                    $reg_id = 0;
                    if(isset($second->term_id))
                    {
                        $reg_id = $second->term_id;
                    }else{
                        $reg_defaults = array(
                            'taxonomy'             => 'brand_cat',
                            'cat_name'             => $data[2],
                            'category_description' => '',
                            'category_nicename'    => '',
                            'category_parent'      => $cat_id,
                        );
                        $reg_id =   wp_insert_category($reg_defaults);
                    }
                    $city_id = 0;

                    if(isset($third->term_id))
                    {
                        $city_id = $third->term_id;
                    }else{
                        $city_defaults = array(
                            'taxonomy'             => 'brand_cat',
                            'cat_name'             => $data[3],
                            'category_description' => '',
                            'category_nicename'    => '',
                            'category_parent'      => $reg_id,
                        );
                        $city_id =   wp_insert_category($city_defaults);
                    }

                    $my_post = array(
                        'post_title'    => $data[7],
                        'post_status'   => 'publish',
                        'post_author'   => 1,
                        'post_type'   => 'brand',
                        'tax_input' => array(
                            'brand_cat' => array($cat_id, $reg_id, $city_id)
                        )
                    );

                    $post_ID = wp_insert_post($my_post);
                    add_post_meta($post_ID, 'set_brnd_heading', $data[7]);
                    add_post_meta($post_ID, 'set_brnd_lattitude', $data[4]);
                    add_post_meta($post_ID, 'set_brnd_longitude', $data[5]);

                    $image = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='%s';", $data[6] ));

                    if(isset($image[0]))
                    {
                        add_post_meta($post_ID, 'brlf_brnd_logo', $image[0]);
                    }else{
                        // add_post_meta($post_ID, 'brlf_brnd_logo', 'undefined');
                    }

                    add_post_meta($post_ID, 'brlf_brnd_info_one', $data[8]);
                    add_post_meta($post_ID, 'brlf_brnd_info_two', $data[9]);
                    add_post_meta($post_ID, 'blb_go_to_map', $data[10]);
                }


                //   add_post_meta( $post_ID, $meta_key, mixed $meta_value, bool $unique = false )

            }



?>
            <div class="alert alert-success">
                <strong>Successfully!</strong> Imported CSV file
            </div>
        <?php
        } else {
        ?>
            <div class="alert alert-danger">
                <strong>Please</strong> Upload only CSV file
            </div>
<?php
        }
    }
}
?>
