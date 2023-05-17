<?php

declare(strict_types=1);

namespace App\Token\Controllers;

use Domain\Status\Models\Activity;
use Domain\Token\Models\Token;
use Domain\User\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Support\Http\Controllers\Controller;

final class TokenActivityController extends Controller
{
    public function __invoke(Token $token) : Renderable
    {
        $this->authorize('view', $token);

        $activity = Activity::causedBy($token)->latest()->paginate();

        // Since user_id is stored in the `properties` column of the activity, we'll
        // pre-load them here to prevent n+1 issues when displaying user's name in the UI.
        /** @var \Illuminate\Support\Collection<User> */
        $users = User::find(
            $activity->getCollection()
                ->map
                ->userId()
                ->filter()
                ->unique()
                ->values()
        );

        return view('app.tokens.activity', [
            'token'      => $token,
            'activities' => $activity->through(fn (Activity $activity) => [
                'activity' => $activity,
                'user'     => $users->firstWhere('id', $activity->userId()),
            ]),
        ]);
    }
}
