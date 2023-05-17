<?php

declare(strict_types=1);

use App\User\Mail\PersonalDataExport;

it('generates the export URL', function () {
    expect((new PersonalDataExport('test.zip'))->toMail(null)->viewData['url'])
        ->toBe(route('personal-data-exports', 'test.zip'));
});

it('return via parameter', function () {
    expect((new PersonalDataExport('test.zip'))->via(null))->toBe(['mail']);
});

it('should generate with the correct email subject', function () {
    $email = (new PersonalDataExport('test.zip'))->toMail(null);

    expect($email->subject)->toBe(trans('mails.subjects.download_personal_data'));
    expect($email->viewData['subject'])->toBe(trans('mails.subjects.download_personal_data'));
});
