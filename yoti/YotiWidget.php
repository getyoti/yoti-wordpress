<?php

class YotiWidget extends WP_Widget
{
    const YOTI_WIDGET_DEFAULT_TITLE = 'Authenticate with Yoti';

    /**
     * Register widget with WordPress.
     */
    public function __construct()
    {
        $widget_options = ['classname' => 'yoti_widget', 'description' => __('Yoti button')];
        parent::__construct(
            'yoti-widget', // Base ID
            esc_html__('Yoti Widget'), // Name
            $widget_options
        );
    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget($args, $instance)
    {
        if ( ! isset( $args['widget_id'] ) ) {
            $args['widget_id'] = $this->id;
        }
        $title = (!empty( $instance['title'])) ? $instance['title'] : __(self::YOTI_WIDGET_DEFAULT_TITLE);

        $title = apply_filters('widget_title', $title, $instance, $this->id_base);

        wp_enqueue_style('yoti-asset-css', plugin_dir_url(__FILE__) . 'assets/styles.css');
        $config = YotiHelper::getConfig();
        $widgetTitleHtml = '';
        $widgetContent = '<strong>Yoti not configured.</strong>';
        // Apply widget title html
        if(!empty($title)){
            $widgetTitleHtml = $args['before_title'] . $title . $args['after_title'];
        }
        if (!empty($config['yoti_sdk_id']) && !empty($config['yoti_pem']['contents'])) {
            $widgetContent = YotiButton::render(NULL, TRUE);
        }
        echo $args['before_widget'];
        echo $widgetTitleHtml . "<ul><li>$widgetContent</li></ul>";
        echo $args['after_widget'];
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form($instance)
    {
        $title     = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
        ?>
      <p>
		<label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_attr_e('Title:'); ?></label>
		<input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo $title; ?>">
		</p>
        <?php
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update($new_instance, $old_instance)
    {
        $instance = [];
        $instance['title'] = sanitize_text_field($new_instance['title']);

        return $instance;
    }
}