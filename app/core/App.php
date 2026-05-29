<?php

class App
{
	protected string $controller = 'Auth';
	protected string $method = 'index';
	protected array $params = [];

	public function __construct()
	{
		$url = $this->parseUrl();

		if (isset($url[0]) && $url[0] !== '') {
			$controllerName = ucfirst(strtolower($url[0]));
			$controllerFile = ROOT_PATH . '/app/controllers/' . $controllerName . '.php';

			if (file_exists($controllerFile)) {
				$this->controller = $controllerName;
				require_once $controllerFile;
			} else {
				$this->handle404();
				return;
			}
			unset($url[0]);
		}

		require_once ROOT_PATH . '/app/controllers/' . $this->controller . '.php';

		$controllerInstance = new $this->controller();

		if (isset($url[1]) && $url[1] !== '') {
			$methodName = strtolower($url[1]);

			if (method_exists($controllerInstance, $methodName)) {
				$this->method = $methodName;
			} else {
				$this->handle404();
				return;
			}
			unset($url[1]);
		}

		$this->params = $url ? array_values($url) : [];

		call_user_func_array([$controllerInstance, $this->method], $this->params);
	}

	private function parseUrl(): array
	{
		if (isset($_GET['_url'])) {
			$url = rtrim($_GET['_url'], '/');
			$url = filter_var($url, FILTER_SANITIZE_URL);
			return explode('/', $url);
		}
		return [];
	}

	private function handle404(): void
	{
		http_response_code(404);
		echo '<h1>404 - Halaman Tidak Ditemukan</h1>';
		echo '<p>Controller atau method yang Anda minta tidak tersedia.</p>';
	}
}