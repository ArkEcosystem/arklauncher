<x-ark-sidebar-link
    :name="trans('menus.tokens.details')"
    route="tokens.details"
    :params="[$token]"
    icon="grid"
    icon-alignment="left"
/>

<x-ark-sidebar-link
    :name="trans('menus.tokens.servers')"
    route="tokens.servers.index"
    :params="[$token]"
    :active="Route::currentRouteName() === 'tokens.servers.create'"
    icon="server"
    icon-alignment="left" />

<x-ark-sidebar-link
    :name="trans('menus.tokens.secure_shell_keys')"
    route="tokens.ssh-keys"
    :params="[$token]"
    icon="server-lock"
    icon-alignment="left"
/>

@canany(['createCollaborator', 'deleteCollaborator'], [Domain\Token\Models\Token::class, $token])
    <x-ark-sidebar-link
        :name="trans('menus.tokens.collaborators')"
        route="tokens.collaborators"
        :params="[$token]"
        icon="users"
        icon-alignment="left"
    />
@endcanany

<x-ark-sidebar-link
    :name="trans('menus.tokens.activity_log')"
    route="tokens.activity-log"
    :params="[$token]"
    icon="arrows.checkmark"
    icon-alignment="left"
/>

<x-tokens.sidebar-divider class="my-4" />

<x-ark-sidebar-link
    :name="trans('menus.tokens.server_providers')"
    route="tokens.server-providers"
    :params="[$token]"
    icon="globe-line"
    icon-alignment="left"
/>
