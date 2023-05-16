<?php

declare(strict_types=1);

namespace Tests;

use Domain\Coin\Models\Coin;
use Domain\Collaborator\Models\Collaborator;
use Domain\SecureShell\Scripts\Concerns\LocatesScript;
use Domain\Token\Enums\TokenStatusEnum;
use Domain\Token\Models\Token;
use Domain\User\Models\User;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ViewErrorBag;
use Illuminate\View\Component;
// use JMac\Testing\Traits\AdditionalAssertions;
use Ramsey\Uuid\Uuid;
use Spatie\Snapshots\MatchesSnapshots;
use Support\Services\Haiku;
use Tests\Concerns\CreatesModels;
use Tests\Plugins\Encryption\FakeEncrypter;
use TiMacDonald\Log\LogFake;

abstract class TestCase extends BaseTestCase
{
    // use AdditionalAssertions;
    use CreatesApplication;
    use CreatesModels;
    use MatchesSnapshots;
    use WithFaker;
    use LocatesScript;

    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();
        Mail::fake();
        Notification::fake();
        Queue::fake();
        Storage::fake();

        LogFake::bind();

        $this->app->bind('encrypter', FakeEncrypter::class);
    }

    protected function fixture(string $name): string
    {
        return file_get_contents(base_path("tests/fixtures/{$name}.json"));
    }

    protected function getDeployerUploadFile(): UploadedFile
    {
        return new UploadedFile(
            base_path('tests/fixtures/config.json'),
            'config.json',
            'application/json',
            filesize(base_path('tests/fixtures/config.json')),
            true
        );
    }

    protected function assertComponentMatchesSnapshot(Component $component)
    {
        $view = $component->render();

        $this->assertMatchesSnapshot($view->with($component->data() + ['errors' => new ViewErrorBag()])->render());
    }

    protected function createRequestException(int $statusCode, string $body): RequestException
    {
        return new RequestException(new Response(new GuzzleResponse($statusCode, [], $body)));
    }

    protected function createInvitation(Token $token, User $user): int
    {
        return $token->invitations()->create([
            'uuid'        => Uuid::uuid4(),
            'user_id'     => $user->id,
            'email'       => $user->email,
            'role'        => 'collaborator',
            'permissions' => Collaborator::availablePermissions(),
        ])->id;
    }

    protected function createModels(): array
    {
        $user = $this->user();

        $token = $this->token();
        $token->shareWith($user, 'collaborator');

        return [$user, $token];
    }

    protected function createToken(User $user): Token
    {
        $coin = Coin::factory()->create();

        $token = Token::create([
            'user_id'  => $user->id,
            'coin_id'  => $coin->id,
            'name'     => Haiku::withToken(),
            'config'   => null,
        ]);

        $token->setStatus(TokenStatusEnum::PENDING);

        return $token->fresh();
    }
}
