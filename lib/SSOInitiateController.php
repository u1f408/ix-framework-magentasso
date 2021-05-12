<?php
declare(strict_types=1);
namespace ixMagentaSSO;

use ix\HookMachine;
use ixMagentaSSO\Controller;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use MagentaSSO\MagentaRequest;

class SSOInitiateController extends Controller {
	/**
	 * Initiate a MagentaSSO request
	 *
	 * @param Request $request
	 * @param Response $response
	 * @param mixed[] $args
	 * @return Response
	 */
	public function requestGET(Request $request, Response $response, ?array $args = []): Response {
		$magenta_request = new MagentaRequest(
			$_ENV[IX_ENVBASE . '_MAGENTASSO_CLIENT_ID'],
			$_ENV[IX_ENVBASE . '_MAGENTASSO_CLIENT_SECRET'],
			null,
			['profile'],
			$this->fullUrlFor($request, "sso-callback"),
		);

		return $response
			->withHeader('Location', "{$_ENV[IX_ENVBASE . '_MAGENTASSO_SERVER_URL']}?{$magenta_request}")
			->withStatus(302);
	}
}
