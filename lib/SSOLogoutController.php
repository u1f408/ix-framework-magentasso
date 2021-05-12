<?php
declare(strict_types=1);
namespace ixMagentaSSO;

use ix\HookMachine;
use ixMagentaSSO\Controller;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use EasyCSRF\Exceptions\InvalidCsrfTokenException;

class SSOLogoutController extends Controller {
	/**
	 * Show the log out confirmation form.
	 *
	 * @param Request $request
	 * @param Response $response
	 * @param mixed[] $args UNUSED
	 * @return Response
	 */
	public function requestGET(Request $request, Response $response, ?array $args = []): Response {
		// Redirect to index if not logged in
		if (!$this->getIsLoggedIn($request)) {
			return $response
				->withHeader('Location', $this->fullUrlFor($request, "index"))
				->withStatus(302);
		};

		$csrfToken = $this->csrf->generate('ixmagentasso');

		// Render form
		$response->getBody()->write($this->html->renderDocument(
			// <head> content
			[
				$this->html->tag('meta', ['charset' => 'utf-8']),
				$this->html->tag('meta', ['name' => 'viewport', 'content' => 'initial-scale=1, width=device-width']),
				$this->html->tag('link', ['rel' => 'stylesheet', 'href' => '/styles.css']),
			],

			// <body> content
			[
				$this->html->tagHasChildren('main', ['class' => 'main popup-container'], ...[
					$this->html->tagHasChildren('h1', [], 'Log out'),
					$this->html->tagHasChildren('form', ['method' => 'POST', 'class' => 'form-main'], ...[
						$this->html->tag('input', ['type' => 'hidden', 'name' => '_csrf', 'value' => $csrfToken]),
						$this->html->tagHasChildren('button', ['type' => 'submit', 'class' => 'button'], 'Log out'),
					]),
				]),
			],

			// <html> attributes
			[],

			// <body> attributes
			['class' => 'ixMagentaSSO'],
		));

		return $response;
	}

	/**
	 * Log out.
	 *
	 * @param Request $request
	 * @param Response $response
	 * @param mixed[] $args UNUSED
	 * @return Response
	 */
	public function requestPOST(Request $request, Response $response, ?array $args = []): Response {
		// Redirect to index if not logged in
		if (!$this->getIsLoggedIn($request)) {
			return $response
				->withHeader('Location', $this->fullUrlFor($request, "index"))
				->withStatus(302);
		};

		// CSRF check
		$requestBody = (array) $request->getParsedBody();
		$this->csrf->check('ixmagentasso', array_key_exists('_csrf', $requestBody) ? $requestBody['_csrf'] : '', null, true);

		// Hook: Destroy session
		$this->destroySession($request);

		// Redirect to index
		return $response
			->withHeader('Location', $this->fullUrlFor($request, "index"))
			->withStatus(302);
	}
}
