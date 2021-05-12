# ix-framework-magentasso

A set of controllers for [ix-framework][]
to implement [MagentaSSO][] authentication;
using the framework `HookMachine` to allow
for modifying session behaviour, etc.

[ix-framework]: https://github.com/u1f408/ix-framework
[magentasso]: https://github.com/magentasso

## Usage

Assuming you already have `\ix\Session\Session` enabled:

```php
HookMachine::add([\ixMagentaSSO\Controller::class, 'getIsLoggedIn'], (function ($key, $params) {
	list($ctrl, $request, $_) = $params;
	$isLoggedIn = array_key_exists('user', $ctrl->container->get('session')->retrieve()->session_data);
	return [$ctrl, $request, $isLoggedIn];
}));

HookMachine::add([\ixMagentaSSO\Controller::class, 'setCurrentUser'], (function ($key, $params) {
	list($ctrl, $request, $sso_data) = $params;

	$session = $ctrl->container->get('session')->ensure_create()->retrieve();
	$session->session_data['user'] = $sso_data['external_id'];
	$session->update();

	return [$ctrl, $request, $sso_data];
}));

HookMachine::add([\ixMagentaSSO\Controller::class, 'destroySession'], (function ($key, $params) {
	list($ctrl, $request) = $params;
	$ctrl->container->get('session')->destroy();
	return [$ctrl, $request];
}));

HookMachine::add([Application::class, 'create_app', 'routeRegister'], (function ($key, $app) {
	$app->get('/sso', \ixMagentaSSO\SSOInitiateController::class)->setName('sso-initiate');
	$app->get('/sso/callback', \ixMagentaSSO\SSOCallbackController::class)->setName('sso-callback');
	$app->any('/sso/logout', \ixMagentaSSO\SSOLogoutController::class)->setName('sso-logout');

	return $app;
}));
```

## License

ix-framework-magentasso is licensed under the terms of
the MIT License, the text of which can be found
in [the LICENSE file](./LICENSE).
