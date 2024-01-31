<?php

function fifu_woo_zoom() {
    return fifu_is_on('fifu_wc_zoom') ? 'inline' : 'none';
}

function fifu_woo_lbox() {
    return fifu_is_on('fifu_wc_lbox');
}

function fifu_woo_theme() {
    return file_exists(get_template_directory() . '/woocommerce');
}

define('FIFU_FIX_IMAGES_WITHOUT_DIMENSIONS',
        "function fix_images_without_dimensions() {
        jQuery('img[data-large_image_height=0]').each(function () {
            if (jQuery(this)[0].naturalWidth <= 2)
                return;

            jQuery(this)
                .attr('data-large_image_width', jQuery(this)[0].naturalWidth)
                .attr('data-large_image_height', jQuery(this)[0].naturalHeight);

            jQuery('div.flex-viewport').css('height', jQuery(this)[0].clientHeight);
        });
    }
    fix_images_without_dimensions();"
);

function fifu_woocommerce_gallery_image_html_attachment_image_params($params, $attachment_id, $image_size, $main_image) {
    // fix zoom
    if ($params['data-large_image_width'] == 0) {
        $params['data-large_image_width'] = 1920;
        $params['data-large_image_height'] = 0;
    }

    // fix lightbox
    if (is_product())
        $params['onload'] = FIFU_FIX_IMAGES_WITHOUT_DIMENSIONS;

    return $params;
}

add_filter('woocommerce_gallery_image_html_attachment_image_params', 'fifu_woocommerce_gallery_image_html_attachment_image_params', 10, 4);

add_action('woocommerce_product_duplicate', 'fifu_woocommerce_product_duplicate', 10, 1);

function fifu_woocommerce_product_duplicate($array) {
    if (!$array || !$array->get_meta_data())
        return;

    $post_id = $array->get_id();
    foreach ($array->get_meta_data() as $meta_data) {
        $data = $meta_data->get_data();
        if (in_array($data['key'], array('fifu_image_url'))) {
            delete_post_meta($post_id, '_thumbnail_id');
        }
    }
}

function fifu_woocommerce_order_item_thumbnail_filter($image, $item) {
    if (strpos($image, 'data-sizes="auto"') !== false)
        return str_replace('data-src', 'src', $image);

    return $image;
}

add_filter('woocommerce_order_item_thumbnail', 'fifu_woocommerce_order_item_thumbnail_filter', 10, 2);

function fifu_on_products_page() {
    return strpos($_SERVER['REQUEST_URI'], 'wp-admin/edit.php?post_type=product') !== false;
}

function fifu_on_categories_page() {
    return strpos($_SERVER['REQUEST_URI'], 'wp-admin/edit-tags.php?taxonomy=product_cat&post_type=product') !== false;
}

function fifu_get_pretty_variation_attributes_map($parent_product_id) {
    // Initialize an empty array to store the map
    $variation_map = [];

    // Get the parent product object
    $parent_product = wc_get_product($parent_product_id);

    // Check if it's a variable product
    if ($parent_product && $parent_product->is_type('variable')) {
        // Get the child variation IDs
        $variations = $parent_product->get_children();

        foreach ($variations as $variation_id) {
            // Load each variation product object
            $variation = wc_get_product($variation_id);

            // Get attributes of the variation
            $attributes = $variation->get_variation_attributes();

            // Get the pretty names of the attributes
            $pretty_names = fifu_get_pretty_attribute_names($parent_product_id);

            // Add to the map
            $variation_map[$variation_id] = array_combine($pretty_names, $attributes);
        }
    }

    return $variation_map;
}

function fifu_get_pretty_attribute_names($product_id) {
    // Get the product attributes
    $attributes = get_post_meta($product_id, '_product_attributes', true);

    // Initialize an empty array to store the pretty names
    $pretty_names = [];

    if (is_array($attributes)) {
        // Iterate over the attributes
        foreach ($attributes as $attribute) {
            if (!$attribute['is_variation'])
                continue;

            // Get the attribute name
            $name = $attribute['name'];

            // Get the pretty name
            $pretty_name = wc_attribute_label($name);

            // Add to the array
            $pretty_names[$name] = $pretty_name;
        }
    }

    return $pretty_names;
}

function fifu_is_variable_product($post_id) {
    if (class_exists("WooCommerce")) {
        $product = wc_get_product($post_id);
        if ($product)
            return $product->get_type() == "variable";
    }
    return false;
}

function fifu_array_to_sorted_html_table($data, $post_id) {
    global $FIFU_SESSION;

    // Initialize an empty string to store the HTML table
    $html = '';

    // Determine the column names dynamically
    $firstItem = reset($data);
    $columns = $firstItem ? array_keys($firstItem) : array();
    if ($columns) {
        array_unshift($columns, 'ID');  // Add 'ID' as the first column
        array_push($columns, '<center><span class="dashicons dashicons-camera" style="font-size:20px; text-align:right"></span></center>');  // Add 'Image' as the last column
        // Sort the array based on the values in the inner arrays
        uasort($data, function ($a, $b) {
            foreach ($a as $key => $value) {
                if ($a[$key] != $b[$key]) {
                    return $a[$key] <=> $b[$key];
                }
            }
            return 0;
        });
    }

    // Generate header row
    $html .= '<table id="fifu-variable-table" style="text-align:left; width:100%" post-parent="' . $post_id . '"><tbody>';
    $html .= '<tr class="color">';
    foreach ($columns as $col) {
        if (strpos($col, 'ID') !== false) {
            $html .= "<th style=\"width:64px\">$col</th>";
        } elseif (strpos($col, 'dashicons-camera') !== false) {
            $html .= "<th style=\"width:40px\">$col</th>";
        } else {
            $html .= "<th style=\"min-width:100px\">$col</th>";
        }
    }
    $html .= '</tr>';

    // Generate data rows
    foreach ($data as $id => $attributes) {
        $html .= '<tr class="color">';
        $html .= "<td>$id</td>";  // First column is the ID
        foreach ($columns as $col) {
            if ($col !== 'ID') {  // Skip the 'ID' column as it's already added
                if (strpos($col, 'dashicons-camera') !== false) {
                    // Add your image here. For example, using a placeholder image.
                    list($border, $height, $width, $video_url, $video_src, $is_ctgr, $is_variable, $image_url, $url, $vars) = fifu_column_featured($id);
                    $html .= "
                        <td>
                            <div
                                class=\"fifu-quick\"
                                post-id=\"{$id}\"
                                video-url=\"{$video_url}\"
                                video-src=\"{$video_src}\"
                                is-ctgr=\"{$is_ctgr}\"
                                image-url=\"{$image_url}\"
                                is-variable=\"{$is_variable}\"
                                style=\"height: {$height}px; width: {$height}px; background:url('{$url}') no-repeat center center; background-size:cover; {$border}; cursor:pointer;\">
                            </div>
                        </td>
                    ";
                    $FIFU_SESSION['fifu-quick-edit'][$id] = $vars;
                } else {
                    $html .= '<td>' . ($attributes[$col] ?? '') . '</td>';
                }
            }
        }
        $html .= '</tr>';
    }

    $html .= '</tbody></table>';

    return $html;
}

