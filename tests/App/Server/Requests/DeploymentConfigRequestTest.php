<?php

declare(strict_types=1);

use App\Token\Requests\DeploymentConfigRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

it('requires a file', function () {
    $request = new DeploymentConfigRequest();

    $validator = Validator::make(['file' => []], $request->rules());

    $this->assertFalse($validator->passes());
    $this->assertSame('file', $validator->errors()->keys()['0']);
});

it('requires a zip file', function () {
    $request = new DeploymentConfigRequest();

    $file = UploadedFile::fake()->create('config.exe');

    $validator = Validator::make(['file' => $file], $request->rules());

    $this->assertFalse($validator->passes());
    $this->assertSame('file', $validator->errors()->keys()['0']);
});

it('accepts a zip file', function () {
    $request = new DeploymentConfigRequest();

    $file = UploadedFile::fake()->create('config.zip', 15, 'application/zip');

    $validator = Validator::make(['file' => $file], $request->rules());

    $this->assertTrue($validator->passes());
});

it('doesnt accepts a file longer than 50K', function () {
    $request = new DeploymentConfigRequest();

    $file = UploadedFile::fake()->create('config.zip', 51, 'application/zip');

    $validator = Validator::make(['file' => $file], $request->rules());

    $this->assertFalse($validator->passes());
    $this->assertSame('file', $validator->errors()->keys()['0']);
});

it('doesnt accepts a file with no size', function () {
    $request = new DeploymentConfigRequest();

    $file = UploadedFile::fake()->create('config.zip', 0, 'application/zip');

    $validator = Validator::make(['file' => $file], $request->rules());

    $this->assertFalse($validator->passes());
    $this->assertSame('file', $validator->errors()->keys()['0']);
});

it('doesnt accept something that is not a file', function () {
    $request = new DeploymentConfigRequest();

    $validator = Validator::make(['file' => 'hello world'], $request->rules());

    $this->assertFalse($validator->passes());
    $this->assertSame('file', $validator->errors()->keys()['0']);
});
