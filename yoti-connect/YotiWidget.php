<?php
class YotiWidget extends WP_Widget
{
    /**
     * YotiWidget constructor.
     * @param string $id_base
     * @param string $name
     * @param array $widget_options
     * @param array $control_options
     */
    public function __construct($id_base = 'yoti_connect_widget', $name = 'Yoti Connect Widget', array $widget_options = array(), array $control_options = array())
    {
        $widget_options = array_merge($widget_options, array(
            'classname' => 'yoti-widget',
            'description' => 'Yoti Connect Widget',
        ));

        parent::__construct($id_base, $name, $widget_options, $control_options);
    }


    public function widget($args, $instance)
    {

    }

    public function form($instance)
    {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'New title', 'text_domain' );
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'text_domain' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance)
    {

    }
}
