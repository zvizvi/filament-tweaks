<?php

// config for Dowhile/FilamentTweaks
return [
    /*
    |--------------------------------------------------------------------------
    | Feature Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can enable or disable specific features of FilamentTweaks.
    | Set a feature to false to disable it.
    |
    */

    'features' => [
        // Disable default readOnlyRelationManagersOnResourceViewPagesByDefault
        'disable_readonly_relation_managers' => true,

        // Center form actions alignment
        'center_form_actions' => true,

        // Disable CreateAndCreateAnother functionality
        'disable_create_another' => true,

        // Translate labels for all components
        'translate_labels' => true,

        // Use non-native select inputs
        'non_native_select' => true,

        // Configure DateTimePicker defaults (no seconds, week starts on Sunday)
        'configure_datetime_picker' => true,

        // Configure table styling (striped and 25 items per page)
        'configure_table_styling' => true,

        // Customize system icons (sidebar, buttons, etc.)
        'customize_system_icons' => false,

        // Enable all columns toggleable
        'enable_all_columns_toggleable' => true,

        // Enable currency mask macro for TextInput
        'enable_currency_mask' => true,

        // Enable autogrow macro for Textarea
        'enable_autogrow_textarea' => true,

        // Configure date range picker
        'configure_date_range_picker' => true,
    ],

    'formats' => [
        // 'date' => 'M j, Y',
        // 'time' => 'H:i:s',
        // 'datetime' => 'M j, Y H:i:s',
        // 'currency' => 'usd',
    ],
];
