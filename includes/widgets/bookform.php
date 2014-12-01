<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class SR_Widget_Bookform extends WP_Widget {

    public function SR_Widget_Bookform() {
        parent::__construct( false, 'SR: Boekformulier' );
    }

    function widget( $args, $instance ) {
        // Widget output
        ?>
        <div class="panel panel-default">
            <div class="panel-heading"><?php echo $instance['title'] ?></div>
            <div class="panel-body">
                <form ng-submit="search()">
                    <div class="form-group">
                        <label for="start">Periode</label>
                        <div class="row">
                            <div class="col-xs-6">
                                <input class="form-control" id="start" readonly="readonly" ng-model="form.start">
                            </div>
                            <div class="col-xs-6">
                                <input class="form-control" id="end" readonly="readonly" ng-model="form.end">
                            </div>
                        </div>
                        <div id="widgetCalendar"></div>
                    </div>
                    <?php if($instance['filter_types']): ?>
                    <div class="form-group">
                        <label for="type">Type</label>
                        <select id="type" class="form-control" ng-model="form.type">
                            <option value="">Geen voorkeur</option>
                            <option value="47">Chalet</option>
                            <option value="48">Camphome</option>
                            <option value="49">Vakantietent</option>
                            <option value="50">Hotel</option>
                            <option value="51">Groepshuizen</option>
                            <option value="52">Trekkershut</option>
                            <option value="53">Kampeerplaats</option>
                            <option value="54">Vakantiehuis</option>
                        </select>
                    </div>
                    <?php endif ?>
                    <?php if($instance['filter_bookgroups']): ?>
                    <div class="form-group">
                        <label for="bookgroup">Reisgezelschap</label>
                        <input id="bookgroup" class="form-control" value="2 Volwassenen" readonly="readonly"/>
                        <div id="bookgroup-panel" style="display:none">
                            <div class="form-group" ng-repeat="bookgroup in form.bookgroups">
                                <label for="group1">{{ bookgroup.title }}</label>
                                <input type="number" class="form-control" id="group1" ng-model="bookgroup.nr">
                            </div>
                            <div class="form-group">
                                <a id="bookgroup-ok" class="btn btn-primary withripple pull-right" ng-click="setBookgroups()">OK</a>
                            </div>
                        </div>
                    </div>
                    <?php endif ?>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary withripple pull-right" ng-click="test()"><?php echo $instance['button_text'] ?></button>
                    </div>
                    <?php if(!empty($instance['cta_text'])): ?>
                        <p class="cta"><?php echo $instance['cta_text'] ?></p>
                    <?php endif ?>
                </form>
            </div>
        </div>
        <div class="panel panel-default filters">
            <div class="panel-heading">Filter</div>
            <div class="panel-body">
                <ul>
                    <li class="withripple" ng-repeat="facility in facilities" ng-click="toggleFacility()" ng-class="(facility.selected ? 'selected' : '')">
                        <span class="fa fa-{{facility.icon}}"></span> {{ facility.title }}
                    </li>
                </ul>
            </div>
        </div>
        <?php
    }

    function update( $new_instance, $old_instance ) {

        $instance = $new_instance;

        return $instance;
    }

    function form( $instance ) {
        $instance = wp_parse_args(
            (array) $instance
        );
        $title = !empty( $instance['title'] ) ? $instance['title'] : 'Zoek en Boek';
        $button_text = !empty( $instance['button_text'] ) ? $instance['button_text'] : 'Zoeken';
        $cta_text = !empty( $instance['cta_text'] ) ? $instance['cta_text'] : 'Zoeken';
        $filter_bookgroups = $instance['filter_bookgroups'];
        $filter_types = $instance['filter_types'];
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Titel:' ); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'button_text' ); ?>"><?php _e( 'Tekst zoekknop:' ); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id( 'button_text' ); ?>" name="<?php echo $this->get_field_name( 'button_text' ); ?>" type="text" value="<?php echo esc_attr( $button_text ); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'cta_text' ); ?>"><?php _e( 'Call to action:' ); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id( 'cta_text' ); ?>" name="<?php echo $this->get_field_name( 'cta_text' ); ?>" type="text" value="<?php echo esc_attr( $cta_text ); ?>">
        </p>
        <p>
            <input type="checkbox" name="<?php echo $this->get_field_name( 'filter_types' ); ?>" id="<?php echo $this->get_field_id( 'filter_types' ); ?>" value="1" <?php checked($filter_types) ?> />
            <label for="<?php echo $this->get_field_id( 'filter_types' ); ?>"><?php _e( 'Filter op type' ); ?></label>
        </p>
        <p>
            <input type="checkbox" name="<?php echo $this->get_field_name( 'filter_bookgroups' ); ?>" id="<?php echo $this->get_field_id( 'filter_bookgroups' ); ?>" value="1" <?php checked($filter_bookgroups) ?> />
            <label for="<?php echo $this->get_field_id( 'filter_bookgroups' ); ?>"><?php _e( 'Filter op reisgezelschap' ); ?></label>
        </p>

        <?php
    }

    public static function register($sr) {
        register_widget('SR_Widget_Bookform');
    }
}

SR_Widget_Bookform::register($this);