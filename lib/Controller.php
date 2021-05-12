<?php
declare(strict_types=1);
namespace ixMagentaSSO;

use ix\HookMachine;
use ix\Helpers\HtmlRenderer;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use EasyCSRF\EasyCSRF;

class Controller extends \ix\Controller\Controller {
	/** @var HtmlRenderer $html */
	public $html;

	/** @var EasyCSRF $csrf */
	public $csrf;

	/**
	 * @param ?ContainerInterface $container
	 */
	public function __construct(?ContainerInterface $container) {
		parent::__construct($container);
		$this->html = new HtmlRenderer();

		if ($this->container->has('session')) {
			$session_provider = new \ix\Session\EasyCSRFSessionProvider($this->container->get('session'));
			$this->csrf = new EasyCSRF($session_provider);
		} else {
			$this->csrf = new EasyCSRF(new \EasyCSRF\NativeCookieProvider());
		}
	}

	/**
	 * Get whether or not there is a logged in user
	 *
	 * @param Request $request The current request
	 * @return bool Whether there is a logged in user
	 */
	protected function getIsLoggedIn(Request $request): bool {
		list($_, $_, $isLoggedIn) = HookMachine::execute(
			[self::class, 'getIsLoggedIn'],
			[$this, $request, null],
		);

		return $isLoggedIn;
	}

	/**
	 * Set the currently logged in user
	 *
	 * @param Request $request The current request
	 * @param array<string, mixed> $sso_data MagentaSSO authentication data
	 * @return void
	 */
	protected function setCurrentUser(Request $request, array $sso_data) {
		HookMachine::execute(
			[self::class, 'setCurrentUser'],
			[$this, $request, $sso_data],
		);
	}

	/**
	 * Destroy the current session
	 *
	 * @param Request $request The current request
	 * @return void
	 */
	protected function destroySession(Request $request) {
		HookMachine::execute([self::class, 'destroySession'], [$this, $request]);
	}

	/**
	 * Get the full URL for a named route
     *
	 * @param Request $request The current request
     * @param string $routeName Route name
     * @param array<string, string> $data Route placeholders
     * @param array<string, string> $queryParams Query parameters
     * @return string
	 */
	protected function fullUrlFor(Request $request, string $routeName, array $data = [], array $queryParams = []): string {
		return $this
			->container
			->get('Slim\\App')
			->getRouteCollector()
			->getRouteParser()
			->fullUrlFor(
				$request->getUri(),
				$routeName,
				$data,
				$queryParams,
			);
	}
}