<?php

declare(strict_types=1);

return [
    /*
     * The disk where the exports will be stored by default.
     */
    'disk' => 'personal-data-exports',

    /*
     * The amount of days the exports will be available.
     */
    'delete_after_days' => 5,

    /*
     * Determines whether the user should be logged in to be able
     * to access the export.
     */
    'authentication_required' => true,

    /*
     * The notification which will be sent to the user when the export
     * has been created.
     */
    'notification' => \App\User\Mail\PersonalDataExport::class,

    /*
     * Configure the queue and connection used by `CreatePersonalDataExportJob`
     * which will create the export.
     */
    'job' => [
        'queue'      => null,
        'connection' => null,
    ],
];
