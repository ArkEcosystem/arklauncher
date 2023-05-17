<?php

declare(strict_types=1);

namespace Support\Builders;

use Domain\Collaborator\Models\Invitation;
use Domain\Server\Models\Server;
use Domain\Server\Models\ServerProvider;
use Domain\Token\Models\Token;
use Domain\User\Models\User;

final class NotificationBuilder
{
    public array $content = [];

    public string $type = 'success';

    public function fromInvitation(Invitation $invitation, array $data): self
    {
        $this->content = $this->fromToken(
            $invitation->token ?? null,
            array_merge($data, ['invitation' => $invitation->id]),
        )->getContent();

        return $this;
    }

    public function fromServer(Server $server, array $data, ?Token $token = null): self
    {
        $this->content = $this->fromToken(
            $token ?? $server->token,
            array_merge($data, ['server' => $server->id]),
        )->getContent();

        return $this;
    }

    public function fromServerProvider(ServerProvider $serverProvider, array $data): self
    {
        $this->content = $this->fromToken(
            $serverProvider->token,
            array_merge($data, ['serverProvider' => $serverProvider->id]),
        )->getContent();

        return $this;
    }

    public function fromToken(?Token $token, array $data): self
    {
        $this->content = array_merge($data, [
            'token'          => $token->id ?? null,
            'type'           => $this->type,
            'relatable_id'   => $token->id ?? null,
            'relatable_type' => Token::class,
        ]);

        return $this;
    }

    public function withAction(string $title, string $url): self
    {
        $this->content = array_merge($this->content, ['action' => ['title' => $title, 'url' => $url]]);

        return $this;
    }

    public function withUser(User $user): self
    {
        $this->content = array_merge($this->content, ['user' => $user->id]);

        return $this;
    }

    public function success(): self
    {
        $this->content['type'] = 'success';

        return $this;
    }

    public function danger(): self
    {
        $this->content['type'] = 'danger';

        return $this;
    }

    public function warning(): self
    {
        $this->content['type'] = 'warning';

        return $this;
    }

    public function getContent(): array
    {
        return $this->content;
    }
}
