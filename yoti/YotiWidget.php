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

        $view = function () use ($args, $config, $title, $instance)
        {
            require __DIR__ . '/views/widget.php';
        };
        $view();
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
        $title = isset( $instance['title'] ) ? $instance['title'] : '';
        $scenario_id = isset( $instance['yoti_scenario_id'] ) ? $instance['yoti_scenario_id'] : '';
        $button_text = isset( $instance['yoti_button_text'] ) ? $instance['yoti_button_text'] : '';
        ?>
        <p>
        <label for="<?php esc_attr_e($this->get_field_id('title')); ?>">Title:</label>
        <input
          class="widefat"
          id="<?php esc_attr_e($this->get_field_id('title')); ?>"
          name="<?php esc_attr_e($this->get_field_name('title')); ?>"
          type="text" value="<?php esc_attr_e($title); ?>">
        <label for="<?php esc_attr_e($this->get_field_id('yoti_button_text')); ?>">Button Text <em>(optional)</em>:</label>
        <input
          class="widefat"
          id="<?php esc_attr_e($this->get_field_id('yoti_button_text')); ?>"
          name="<?php esc_attr_e($this->get_field_name('yoti_button_text')); ?>"
          type="text" value="<?php esc_attr_e($button_text); ?>">
        <label for="<?php esc_attr_e($this->get_field_id('yoti_scenario_id')); ?>">Scenario ID <em>(optional)</em>:</label>
        <input
          class="widefat"
          id="<?php esc_attr_e($this->get_field_id('yoti_scenario_id')); ?>"
          name="<?php esc_attr_e($this->get_field_name('yoti_scenario_id')); ?>"
          type="text" value="<?php esc_attr_e($scenario_id); ?>">
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
        $instance['yoti_scenario_id'] = sanitize_text_field($new_instance['yoti_scenario_id']);
        $instance['yoti_button_text'] = sanitize_text_field($new_instance['yoti_button_text']);

        return $instance;
    }
}