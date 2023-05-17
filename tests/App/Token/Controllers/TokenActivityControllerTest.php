<?php

declare(strict_types=1);

it('guests can not view the onboarding', function () {
    $token = $this->token();

    $this->get(route('tokens.activity-log', $token))
        ->assertRedirect(route('login'));
});

it('users can view the onboarding', function () {
    $this->withoutExceptionHandling();
    $token = $this->token();

    activity('Server')
        ->performedOn($token)
        ->causedBy($token)
        ->withProperties(['user_id' => $token->user->id, 'preset' => 'relay'])
        ->log('test');

    $this->actingAs($token->user)
        ->get(route('tokens.activity-log', $token))
        ->assertViewIs('app.tokens.activity');
});
