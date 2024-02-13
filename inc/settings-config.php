<?php

$settings_sections = array(
    array(
        'id' => 'hki_events_settings_import',
        'title' => __( 'Import', 'hki_events' ),
        'fields' => array(
            array(
                'id' => 'hki_events_import_schedule',
                'label' => __( 'Schedule', 'hki_events' ),
                'elements' => array(
                    array(
                        'type' => 'SelectElement',
                        'label' => '',
                        'name' => '',
                        'value' => array(
                            'Off'         => 'off',
                            'Once Hourly' => 'hourly',
                            'Twice Daily' => 'twicedaily',
                            'Once Daily' => 'daily',
                            'Once Weekly' => 'weekly'
                        )
                    )
                    ),
                'description' => 'To start importing, select the desired import schedule and save settings.'
            )
        )
    ),
    array(
        'id' => 'hki_events_settings_options',
        'title' => __( 'Options', 'hki_events' ),
        'fields' => array(
            array(
                'id' => 'hki_events_time_span',
                'label' => __( 'Time span', 'hki_events' ),
                'section' => 'hki_events_settings_options',
                'elements' => array(
                    array(
                        'type' => 'SelectElement',
                        'label' => '',
                        'name' => '',
                        'value' => array(
                            '1 month' => 1,
                            '2 months' => 2,
                            '3 months' => 3,
                            '6 months' => 6,
                        )
                    )
                ),
                'description' => 'Time span on which the queried events will take place. 1, 2, 3 or 6 months.'
            ),
            array(
                'id' => 'hki_events_categories',
                'label' => __( 'Categories', 'hki_events' ),
                'section' => 'hki_events_settings_options',
                'elements' => array(
                    array(
                        'type' => 'CheckboxElement',
                        'label' => __( 'Music', 'hki_events' ),
                        'name' => 'music',
                        'value' => ''
                    ),
                    array(
                        'type' => 'CheckboxElement',
                        'label' => __( 'Sports', 'hki_events' ),
                        'name' => 'sports',
                        'value' => ''
                    ),
                    array(
                        'type' => 'CheckboxElement',
                        'label' => __( 'Visual arts', 'hki_events' ),
                        'name' => 'visual arts',
                        'value' => ''
                    ),
                    array(
                        'type' => 'CheckboxElement',
                        'label' => __( 'Movies', 'hki_events' ),
                        'name' => 'movies',
                        'value' => ''
                    ),
                    array(
                        'type' => 'CheckboxElement',
                        'label' => __( 'Theatre', 'hki_events' ),
                        'name' => 'theatre',
                        'value' => ''
                    ),
                    array(
                        'type' => 'CheckboxElement',
                        'label' => __( 'History & museums', 'hki_events' ),
                        'name' => 'history and museums',
                        'value' => ''
                    ),
                    array(
                        'type' => 'CheckboxElement',
                        'label' => __( 'Literature', 'hki_events' ),
                        'name' => 'literature',
                        'value' => ''
                    ),
                    array(
                        'type' => 'CheckboxElement',
                        'label' => __( 'Dance', 'hki_events' ),
                        'name' => 'dance',
                        'value' => ''
                    ),
                    array(
                        'type' => 'CheckboxElement',
                        'label' => __( 'Fitness and well-being', 'hki_events' ),
                        'name' => 'fitness and well-being',
                        'value' => ''
                    )
                ),
                'description' => 'Select categories to include in the query. Each category represents a group of Linked Events (yso) keywords.'
            ),
            array(
                'id' => 'hki_events_demographic_groups',
                'label' => __( 'Demographic Groups', 'hki_events' ),
                'section' => 'hki_events_settings_options',
                'elements' => array(
                    array(
                        'type' => 'CheckboxElement',
                        'label' => __( 'Seniors', 'hki_events' ),
                        'name' => 'seniors',
                        'value' => ''
                    ),
                    array(
                        'type' => 'CheckboxElement',
                        'label' => __( 'Youth', 'hki_events' ),
                        'name' => 'youth',
                        'value' => ''
                    ),
                    array(
                        'type' => 'CheckboxElement',
                        'label' => __( 'Children', 'hki_events' ),
                        'name' => 'children',
                        'value' => ''
                    ),
                    array(
                        'type' => 'CheckboxElement',
                        'label' => __( 'Immigrants', 'hki_events' ),
                        'name' => 'immigrants',
                        'value' => ''
                    ),
                    array(
                        'type' => 'CheckboxElement',
                        'label' => __( 'Infants', 'hki_events' ),
                        'name' => 'infants',
                        'value' => ''
                    )
                ),
                'description' => 'Include demographic-specific events. Excluded by default.'
            ),
        )
    )
);