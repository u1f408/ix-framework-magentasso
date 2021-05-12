<?php
declare(strict_types=1);
namespace ixMagentaSSO;

use ix\HookMachine;
use ixMagentaSSO\Controller;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;
use MagentaSSO\MagentaResponse;
use MagentaSSO\MagentaSignatureException;

class SSOCallbackController extends Controller {
	/**
	 * Process an incoming MagentaSSO response
	 *
	 * @param Request $request
	 * @param Response $response
	 * @param mixed[] $args UNUSED
	 * @return Response
	 */
	public function requestGET(Request $request, Response $response, ?array $args = []): Response {
		$query_values = $request->getQueryParams();
		if (!array_key_exists('payload', $query_values) || !array_key_exists('signature', $query_values)) {
			throw new HttpBadRequestException($request);
		}

		// Decode and verify the response
		try {
			$magenta_response = new MagentaResponse(
				$_ENV[IX_ENVBASE . '_MAGENTASSO_CLIENT_ID'],
				$_ENV[IX_ENVBASE . '_MAGENTASSO_CLIENT_SECRET'],
			);
			$magenta_response->decode(
				$query_values['payload'],
				$query_values['signature'],
			);
		} catch (MagentaSignatureException $e) {
			throw new HttpBadRequestException($request);
		}

		/* If we get this far, the response verified okay! */
		
		// Get user profile data
		$user_profile_data =
			array_key_exists('profile', $magenta_response->data['scope_data'])
			? $magenta_response->data['scope_data']['profile']
			: [];

		// Construct user data array
		$user_data = [
			'external_id' => $magenta_response->data['user_data']['external_id'],
			'email' => $magenta_response->data['user_data']['email'],
			'name_first' => array_key_exists('name_first', $user_profile_data) ? $user_profile_data['name_first'] : 'Unknown',
			'name_last' => array_key_exists('name_last', $user_profile_data) ? $user_profile_data['name_last'] : 'Unknown',
		];

		// Hook: Set current user
		$this->setCurrentUser($request, $user_data);

		// Redirect to index
		return $response
			->withHeader('Location', $this->fullUrlFor($request, "index"))
			->withStatus(302);
	}
}
