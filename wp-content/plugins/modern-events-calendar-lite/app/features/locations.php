<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC locations class.
 * @author Webnus <info@webnus.biz>
 */
class MEC_feature_locations extends MEC_base
{
    public $factory;
    public $main;
    public $settings;

    /**
     * Constructor method
     * @author Webnus <info@webnus.biz>
     */
    public function __construct()
    {
        // Import MEC Factory
        $this->factory = $this->getFactory();
        
        // Import MEC Main
        $this->main = $this->getMain();
        
        // MEC Settings
        $this->settings = $this->main->get_settings();
    }
    
    /**
     * Initialize locations feature
     * @author Webnus <info@webnus.biz>
     */
    public function init()
    {
        $this->factory->action('init', array($this, 'register_taxonomy'), 20);
        $this->factory->action('mec_location_edit_form_fields', array($this, 'edit_form'));
        $this->factory->action('mec_location_add_form_fields', array($this, 'add_form'));
        $this->factory->action('edited_mec_location', array($this, 'save_metadata'));
        $this->factory->action('created_mec_location', array($this, 'save_metadata'));
        
        $this->factory->action('mec_metabox_details', array($this, 'meta_box_location'), 30);
        if(!isset($this->settings['fes_section_location']) or (isset($this->settings['fes_section_location']) and $this->settings['fes_section_location'])) $this->factory->action('mec_fes_metabox_details', array($this, 'meta_box_location'), 30);
        
        $this->factory->filter('manage_edit-mec_location_columns', array($this, 'filter_columns'));
        $this->factory->filter('manage_mec_location_custom_column', array($this, 'filter_columns_content'), 10, 3);
        
        $this->factory->action('save_post', array($this, 'save_event'), 1);
    }
    
    /**
     * Registers location taxonomy
     * @author Webnus <info@webnus.biz>
     */
    public function register_taxonomy()
    {
        $singular_label = $this->main->m('taxonomy_location', esc_html__('Location', 'modern-events-calendar-lite'));
        $plural_label = $this->main->m('taxonomy_locations', esc_html__('Locations', 'modern-events-calendar-lite'));

        register_taxonomy(
            'mec_location',
            $this->main->get_main_post_type(),
            array(
                'label'=>$plural_label,
                'labels'=>array(
                    'name'=>$plural_label,
                    'singular_name'=>$singular_label,
                    'all_items'=>sprintf(esc_html__('All %s', 'modern-events-calendar-lite'), $plural_label),
                    'edit_item'=>sprintf(esc_html__('Edit %s', 'modern-events-calendar-lite'), $singular_label),
                    'view_item'=>sprintf(esc_html__('View %s', 'modern-events-calendar-lite'), $singular_label),
                    'update_item'=>sprintf(esc_html__('Update %s', 'modern-events-calendar-lite'), $singular_label),
                    'add_new_item'=>sprintf(esc_html__('Add New %s', 'modern-events-calendar-lite'), $singular_label),
                    'new_item_name'=>sprintf(esc_html__('New %s Name', 'modern-events-calendar-lite'), $singular_label),
                    'popular_items'=>sprintf(esc_html__('Popular %s', 'modern-events-calendar-lite'), $plural_label),
                    'search_items'=>sprintf(esc_html__('Search %s', 'modern-events-calendar-lite'), $plural_label),
                    'back_to_items'=>sprintf(esc_html__('← Back to %s', 'modern-events-calendar-lite'), $plural_label),
                    'not_found'=>sprintf(esc_html__('no %s found.', 'modern-events-calendar-lite'), strtolower($plural_label)),
                ),
                'rewrite'=>array('slug'=>'events-location'),
                'public'=>false,
                'show_ui'=>true,
                'hierarchical'=>false,
            )
        );
        
        register_taxonomy_for_object_type('mec_location', $this->main->get_main_post_type());
    }
    
    /**
     * Show edit form of location taxonomy
     * @author Webnus <info@webnus.biz>
     * @param object $term
     */
    public function edit_form($term)
    {
        $this->main->load_map_assets(true);

        $address = get_metadata('term', $term->term_id, 'address', true);
        $latitude = get_metadata('term', $term->term_id, 'latitude', true);
        $longitude = get_metadata('term', $term->term_id, 'longitude', true);
        $url = get_metadata('term', $term->term_id, 'url', true);
        $thumbnail = get_metadata('term', $term->term_id, 'thumbnail', true);

        // Map Options
        $status = isset($this->settings['google_maps_status']) ? $this->settings['google_maps_status'] : 1;
        $api_key = isset($this->settings['google_maps_api_key']) ? $this->settings['google_maps_api_key'] : '';
    ?>
        <tr class="form-field">
            <th scope="row">
                <label for="mec_address"><?php esc_html_e('Address', 'modern-events-calendar-lite'); ?></label>
            </th>
            <td>
                <input class="mec-has-tip" type="text" placeholder="<?php esc_attr_e('Enter the location address', 'modern-events-calendar-lite'); ?>" name="address" id="mec_address" value="<?php echo esc_attr($address); ?>" />

                <?php if($status and trim($api_key)): ?>
                <script type="text/javascript">
                jQuery(document).ready(function()
                {
                    if(typeof google !== 'undefined')
                    {
                        new google.maps.places.Autocomplete(document.getElementById('mec_address'));
                    }
                });
                </script>
                <?php endif; ?>
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row">
                <label for="mec_latitude"><?php esc_html_e('Latitude', 'modern-events-calendar-lite'); ?></label>
            </th>
            <td>
                <input class="mec-has-tip" type="text" placeholder="<?php esc_attr_e('Geo latitude (Optional for Lite)', 'modern-events-calendar-lite'); ?>" name="latitude" id="mec_latitude" value="<?php echo esc_attr($latitude); ?>" />
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row">
                <label for="mec_longitude"><?php esc_html_e('Longitude', 'modern-events-calendar-lite'); ?></label>
            </th>
            <td>
                <input class="mec-has-tip" type="text" placeholder="<?php esc_attr_e('Geo longitude (Optional for Lite)', 'modern-events-calendar-lite'); ?>" name="longitude" id="mec_longitude" value="<?php echo esc_attr($longitude); ?>" />
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row">
                <label for="mec_url"><?php esc_html_e('Location Website', 'modern-events-calendar-lite'); ?></label>
            </th>
            <td>
                <input type="url" placeholder="<?php esc_attr_e('Location Website (Optional)', 'modern-events-calendar-lite'); ?>" name="url" id="mec_url" value="<?php echo esc_attr($url); ?>" />
            </td>
        </tr>
        <?php do_action('mec_location_after_edit_form', $term); ?>
        <tr class="form-field">
            <th scope="row">
                <label for="mec_thumbnail_button"><?php esc_html_e('Thumbnail', 'modern-events-calendar-lite'); ?></label>
            </th>
            <td>
                <div id="mec_thumbnail_img"><?php if(trim($thumbnail) != '') echo '<img src="'.esc_url($thumbnail).'" />'; ?></div>
                <input type="hidden" name="thumbnail" id="mec_thumbnail" value="<?php echo esc_attr($thumbnail); ?>" />
                <button type="button" class="mec_upload_image_button button" id="mec_thumbnail_button"><?php echo esc_html__('Upload/Add image', 'modern-events-calendar-lite'); ?></button>
                <button type="button" class="mec_remove_image_button button <?php echo (!trim($thumbnail) ? 'mec-util-hidden' : ''); ?>"><?php echo esc_html__('Remove image', 'modern-events-calendar-lite'); ?></button>
            </td>
        </tr>
    <?php
    }
    
    /**
     * Show add form of organizer taxonomy
     * @author Webnus <info@webnus.biz>
     */
    public function add_form()
    {
        $this->main->load_map_assets(true);

        // Map Options
        $status = isset($this->settings['google_maps_status']) ? $this->settings['google_maps_status'] : 1;
        $api_key = isset($this->settings['google_maps_api_key']) ? $this->settings['google_maps_api_key'] : '';
    ?>
        <div class="form-field">
            <label for="mec_address"><?php esc_html_e('Address', 'modern-events-calendar-lite'); ?></label>
            <input type="text" name="address"  placeholder="<?php esc_attr_e('Enter the location address', 'modern-events-calendar-lite'); ?>" id="mec_address" value="" />

            <?php if($status and trim($api_key)): ?>
            <script type="text/javascript">
            jQuery(document).ready(function()
            {
                if(typeof google !== 'undefined')
                {
                    new google.maps.places.Autocomplete(document.getElementById('mec_address'));
                }
            });
            </script>
            <?php endif; ?>
        </div>
        <div class="form-field">
            <label for="mec_latitude"><?php esc_html_e('Latitude', 'modern-events-calendar-lite'); ?></label>
            <input type="text" name="latitude"  placeholder="<?php esc_attr_e('Geo latitude (Optional for Lite)', 'modern-events-calendar-lite'); ?>" id="mec_latitude" value="" />
        </div>
        <div class="form-field">
            <label for="mec_longitude"><?php esc_html_e('Longitude', 'modern-events-calendar-lite'); ?></label>
            <input type="text" name="longitude"  placeholder="<?php esc_attr_e('Geo longitude (Optional for Lite)', 'modern-events-calendar-lite'); ?>" id="mec_longitude" value="" />
        </div>
        <div class="form-field">
            <label for="mec_url"><?php esc_html_e('Location Website', 'modern-events-calendar-lite'); ?></label>
            <input type="url" name="url"  placeholder="<?php esc_attr_e('Location Website (Optional)', 'modern-events-calendar-lite'); ?>" id="mec_url" value="" />
        </div>
        <?php do_action('mec_location_after_add_form'); ?>
        <div class="form-field">
            <label for="mec_thumbnail_button"><?php esc_html_e('Thumbnail', 'modern-events-calendar-lite'); ?></label>
            <div id="mec_thumbnail_img"></div>
            <input type="hidden" name="thumbnail" id="mec_thumbnail" value="" />
            <button type="button" class="mec_upload_image_button button" id="mec_thumbnail_button"><?php echo esc_html__('Upload/Add image', 'modern-events-calendar-lite'); ?></button>
            <button type="button" class="mec_remove_image_button button mec-util-hidden"><?php echo esc_html__('Remove image', 'modern-events-calendar-lite'); ?></button>
        </div>
    <?php
    }
    
    /**
     * Save meta data of location taxonomy
     * @author Webnus <info@webnus.biz>
     * @param int $term_id
     */
    public function save_metadata($term_id)
    {
        // Quick Edit
        if(!isset($_POST['address'])) return;

        $address = isset($_POST['address']) ? sanitize_text_field($_POST['address']) : '';
        $latitude = isset($_POST['latitude']) ? floatval(sanitize_text_field($_POST['latitude'])) : '0';
        $longitude = isset($_POST['longitude']) ? floatval(sanitize_text_field($_POST['longitude'])) : '0';
        $url = (isset($_POST['url']) and trim($_POST['url'])) ? sanitize_url($_POST['url']) : '';
        $thumbnail = isset($_POST['thumbnail']) ? sanitize_text_field($_POST['thumbnail']) : '';

        // Geo Point is Empty or Address Changed
        if(!floatval($latitude) or !floatval($longitude) or (trim($address) and ($address != get_term_meta($term_id, 'address', true))))
        {
            $geo_point = $this->main->get_lat_lng($address);
            
            if(isset($geo_point[0]) and trim($geo_point[0])) $latitude = $geo_point[0];
            if(isset($geo_point[1]) and trim($geo_point[1])) $longitude = $geo_point[1];
        }
        
        update_term_meta($term_id, 'address', $address);
        update_term_meta($term_id, 'latitude', $latitude);
        update_term_meta($term_id, 'longitude', $longitude);
        update_term_meta($term_id, 'url', $url);
        update_term_meta($term_id, 'thumbnail', $thumbnail);

        do_action('mec_save_location_extra_fields', $term_id);
    }
    
    /**
     * Filter columns of location taxonomy
     * @author Webnus <info@webnus.biz>
     * @param array $columns
     * @return array
     */
    public function filter_columns($columns)
    {
        unset($columns['name']);
        unset($columns['slug']);
        unset($columns['description']);
        unset($columns['posts']);
        
        $columns['id'] = esc_html__('ID', 'modern-events-calendar-lite');
        $columns['name'] = esc_html__('Location', 'modern-events-calendar-lite');
        $columns['address'] = esc_html__('Address', 'modern-events-calendar-lite');
        $columns['posts'] = esc_html__('Count', 'modern-events-calendar-lite');
        $columns['slug'] = esc_html__('Slug', 'modern-events-calendar-lite');

        return $columns;
    }
    
    /**
     * Filter content of location taxonomy columns
     * @author Webnus <info@webnus.biz>
     * @param string $content
     * @param string $column_name
     * @param int $term_id
     * @return string
     */
    public function filter_columns_content($content, $column_name, $term_id)
    {
        switch($column_name)
        {
            case 'id':
                
                $content = $term_id;
                break;

            case 'address':
                
                $content = get_metadata('term', $term_id, 'address', true);
                break;

            default:
                break;
        }

        return $content;
    }
    
    /**
     * Show location meta box
     * @author Webnus <info@webnus.biz>
     * @param object $post
     */
    public function meta_box_location($post)
    {
        $this->main->load_map_assets();

        $locations = get_terms('mec_location', array('orderby'=>'name', 'hide_empty'=>'0'));
        $dont_show_map = get_post_meta($post->ID, 'mec_dont_show_map', true);

        $location_id = get_post_meta($post->ID, 'mec_location_id', true);
        $location_id = apply_filters('wpml_object_id', $location_id, 'mec_location', true);

        $location_ids = get_post_meta($post->ID, 'mec_additional_location_ids', true);
        if(!is_array($location_ids)) $location_ids = array();

        $additional_locations_status = (!isset($this->settings['additional_locations']) or (isset($this->settings['additional_locations']) and $this->settings['additional_locations'])) ? true : false;

        // Map Options
        $status = isset($this->settings['google_maps_status']) ? $this->settings['google_maps_status'] : 1;
        $api_key = isset($this->settings['google_maps_api_key']) ? $this->settings['google_maps_api_key'] : '';
    ?>
        <div class="mec-meta-box-fields mec-event-tab-content" id="mec-location">
            <h4><?php echo sprintf(esc_html__('Event Main %s', 'modern-events-calendar-lite'), $this->main->m('taxonomy_location', esc_html__('Location', 'modern-events-calendar-lite'))); ?></h4>
			<div class="mec-form-row">
				<select name="mec[location_id]" id="mec_location_id" title="<?php echo esc_attr__($this->main->m('taxonomy_location', esc_html__('Location', 'modern-events-calendar-lite')), 'modern-events-calendar-lite'); ?>">
                    <option value="1"><?php esc_html_e('Hide location', 'modern-events-calendar-lite'); ?></option>
                    <option value="0"><?php esc_html_e('Insert a new location', 'modern-events-calendar-lite'); ?></option>
					<?php foreach($locations as $location): ?>
					<option <?php if($location_id == $location->term_id) echo 'selected="selected"'; ?> value="<?php echo esc_attr($location->term_id); ?>"><?php echo esc_html($location->name); ?></option>
					<?php endforeach; ?>
				</select>
                <span class="mec-tooltip">
                    <div class="box top">
                        <h5 class="title"><?php esc_html_e('Location', 'modern-events-calendar-lite'); ?></h5>
                        <div class="content"><p><?php esc_attr_e('Choose one of saved locations or insert new one below.', 'modern-events-calendar-lite'); ?><a href="https://webnus.net/dox/modern-events-calendar/location/" target="_blank"><?php esc_html_e('Read More', 'modern-events-calendar-lite'); ?></a></p></div>
                    </div>
                    <i title="" class="dashicons-before dashicons-editor-help"></i>
                </span>	                
			</div>
			<div id="mec_location_new_container">
				<div class="mec-form-row">
					<input type="text" name="mec[location][name]" id="mec_location_name" value="" placeholder="<?php esc_html_e('Location Name', 'modern-events-calendar-lite'); ?>" />
					<p class="description"><?php esc_html_e('eg. City Hall', 'modern-events-calendar-lite'); ?></p>
				</div>
				<div class="mec-form-row">
					<input type="text" name="mec[location][address]" id="mec_location_address" value="" placeholder="<?php esc_html_e('Event Location', 'modern-events-calendar-lite'); ?>" />
					<p class="description"><?php esc_html_e('eg. City hall, Manhattan, New York', 'modern-events-calendar-lite'); ?></p>

                    <?php if($status and trim($api_key)): ?>
                    <script type="text/javascript">
                    jQuery(document).ready(function()
                    {
                        if(typeof google !== 'undefined')
                        {
                            var location_autocomplete = new google.maps.places.Autocomplete(document.getElementById('mec_location_address'));
                            google.maps.event.addListener(location_autocomplete, 'place_changed', function()
                            {
                                var place = location_autocomplete.getPlace();
                                jQuery('#mec_location_latitude').val(place.geometry.location.lat());
                                jQuery('#mec_location_longitude').val(place.geometry.location.lng());
                            });
                        }
                    });
                    </script>
                    <?php endif; ?>
				</div>
				<div class="mec-form-row mec-lat-lng-row">
					<input class="mec-has-tip" type="text" name="mec[location][latitude]" id="mec_location_latitude" value="" placeholder="<?php esc_html_e('Latitude', 'modern-events-calendar-lite'); ?>" />
					<input class="mec-has-tip" type="text" name="mec[location][longitude]" id="mec_location_longitude" value="" placeholder="<?php esc_html_e('Longitude', 'modern-events-calendar-lite'); ?>" />
                    <span class="mec-tooltip">
                        <div class="box top">
                            <h5 class="title"><?php esc_html_e('Latitude/Longitude', 'modern-events-calendar-lite'); ?></h5>
                            <div class="content"><p><?php esc_attr_e('If you leave the latitude and longitude empty, Modern Events Calendar tries to convert the location address to geopoint, Latitude and Longitude are the units that represent the coordinates at geographic coordinate system. To make a search, use the name of a place, city, state, or address, or click the location on the map to find lat long coordinates.', 'modern-events-calendar-lite'); ?><a href="https://latlong.net" target="_blank"><?php esc_html_e('Get Latitude and Longitude', 'modern-events-calendar-lite'); ?></a></p></div>
                        </div>
                        <i title="" class="dashicons-before dashicons-editor-help"></i>
                    </span>	                     
                </div>
                <div class="mec-form-row">
                    <input type="url" name="mec[location][url]" id="mec_location_url" value="" placeholder="<?php esc_html_e('Location Website', 'modern-events-calendar-lite'); ?>" />
                </div>
                <?php do_action('mec_location_after_new_form'); ?>
                <?php /* Don't show this section in FES */ if(is_admin()): ?>
				<div class="mec-form-row mec-thumbnail-row">
					<div id="mec_location_thumbnail_img"></div>
					<input type="hidden" name="mec[location][thumbnail]" id="mec_location_thumbnail" value="" />
					<button type="button" class="mec_location_upload_image_button button" id="mec_location_thumbnail_button"><?php echo esc_html__('Choose image', 'modern-events-calendar-lite'); ?></button>
					<button type="button" class="mec_location_remove_image_button button mec-util-hidden"><?php echo esc_html__('Remove image', 'modern-events-calendar-lite'); ?></button>
				</div>
                <?php else: ?>
                <div class="mec-form-row mec-thumbnail-row">
                    <span id="mec_fes_location_thumbnail_img"></span>
					<input type="hidden" name="mec[location][thumbnail]" id="mec_fes_location_thumbnail" value="" />
					<input type="file" id="mec_fes_location_thumbnail_file" onchange="mec_fes_upload_location_thumbnail();" />
                    <span class="mec_fes_location_remove_image_button button mec-util-hidden" id="mec_fes_location_remove_image_button"><?php echo esc_html__('Remove image', 'modern-events-calendar-lite'); ?></span>
				</div>
                <?php endif; ?>
			</div>
            <?php if($this->getPRO()): ?>
            <div class="mec-form-row">
                <input type="hidden" name="mec[dont_show_map]" value="0" />
                <input type="checkbox" id="mec_location_dont_show_map" name="mec[dont_show_map]" value="1" <?php echo ($dont_show_map ? 'checked="checked"' : ''); ?> /><label for="mec_location_dont_show_map"><?php echo esc_html__("Don't show map in single event page", 'modern-events-calendar-lite'); ?></label>
            </div>
            <?php endif; ?>
            <?php if($additional_locations_status and count($locations)): ?>
            <h4><?php echo esc_html($this->main->m('other_locations', esc_html__('Other Locations', 'modern-events-calendar-lite'))); ?></h4>
            <div class="mec-form-row">
                <p><?php esc_html_e('You can select extra locations in addition to main location if you like.', 'modern-events-calendar-lite'); ?></p>
                <div class="mec-additional-locations">
                    <select class="mec-select2-dropdown" name="mec[additional_location_ids][]" multiple="multiple">
                        <?php foreach($locations as $location): ?>
                            <option <?php if(in_array($location->term_id, $location_ids)) echo 'selected="selected"'; ?> value="<?php echo esc_attr($location->term_id); ?>">
                                <?php echo esc_html($location->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <?php endif; ?>
		</div>
    <?php
    }
    
    /**
     * Save event location data
     * @author Webnus <info@webnus.biz>
     * @param int $post_id
     * @return boolean
     */
    public function save_event($post_id)
    {
        // Check if our nonce is set.
        if(!isset($_POST['mec_event_nonce'])) return false;

        // Verify that the nonce is valid.
        if(!wp_verify_nonce(sanitize_text_field($_POST['mec_event_nonce']), 'mec_event_data')) return false;

        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if(defined('DOING_AUTOSAVE') and DOING_AUTOSAVE) return false;

        $action = (isset($_POST['action']) ? sanitize_text_field($_POST['action']) : '');
        if($action === 'mec_fes_form') return false;

        // Get Modern Events Calendar Data
        $_mec = isset($_POST['mec']) ? $this->main->sanitize_deep_array($_POST['mec']) : array();
        
        // Selected a saved location
        if(isset($_mec['location_id']) and $_mec['location_id'])
        {
            // Set term to the post
            wp_set_object_terms($post_id, (int) sanitize_text_field($_mec['location_id']), 'mec_location');
        
            return true;
        }
        
        $address = (isset($_mec['location']['address']) and trim($_mec['location']['address'])) ? sanitize_text_field($_mec['location']['address']) : '';
        $name = (isset($_mec['location']['name']) and trim($_mec['location']['name'])) ? sanitize_text_field($_mec['location']['name']) : (trim($address) ? $address : esc_html__('Location Name', 'modern-events-calendar-lite'));
        
        $term = get_term_by('name', $name, 'mec_location');
        
        // Term already exists
        if(is_object($term) and isset($term->term_id))
        {
            // Set term to the post
            wp_set_object_terms($post_id, (int) $term->term_id, 'mec_location');
            
            return true;
        }
        
        $term = wp_insert_term($name, 'mec_location');
        
        // An error ocurred
        if(is_wp_error($term)) return false;
        
        $location_id = $term['term_id'];
        if(!$location_id) return false;
        
        // Set Location ID to the parameters
        $_POST['mec']['location_id'] = $location_id;
        
        // Set term to the post
        wp_set_object_terms($post_id, (int) $location_id, 'mec_location');
        
        $latitude = (isset($_mec['location']['latitude']) and trim($_mec['location']['latitude'])) ? sanitize_text_field($_mec['location']['latitude']) : 0;
        $longitude = (isset($_mec['location']['longitude']) and trim($_mec['location']['longitude'])) ? sanitize_text_field($_mec['location']['longitude']) : 0;
        $url = (isset($_mec['location']['url']) and trim($_mec['location']['url'])) ? sanitize_url($_mec['location']['url']) : '';
        $thumbnail = (isset($_mec['location']['thumbnail']) and trim($_mec['location']['thumbnail'])) ? sanitize_text_field($_mec['location']['thumbnail']) : '';
        
        if((!trim($latitude) or !trim($longitude)) and trim($address))
        {
            $geo_point = $this->main->get_lat_lng($address);
            
            if(isset($geo_point[0]) and trim($geo_point[0])) $latitude = $geo_point[0];
            if(isset($geo_point[1]) and trim($geo_point[1])) $longitude = $geo_point[1];
        }
        
        update_term_meta($location_id, 'address', $address);
        update_term_meta($location_id, 'latitude', $latitude);
        update_term_meta($location_id, 'longitude', $longitude);
        update_term_meta($location_id, 'url', $url);
        update_term_meta($location_id, 'thumbnail', $thumbnail);

        return true;
    }
}