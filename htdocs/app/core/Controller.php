<?php

class Controller
{
	protected function view(string $view, array $data = []): void
	{
		extract($data);

		$viewFile = ROOT_PATH . '/app/views/' . $view . '.php';

		if (file_exists($viewFile)) {
			require_once $viewFile;
		} else {
			throw new Exception(
				"View '{$view}' tidak ditemukan di: {$viewFile}"
			);
		}
	}

	protected function model(string $model): object
	{
		$modelFile = ROOT_PATH . '/app/models/' . $model . '.php';

		if (file_exists($modelFile)) {
			require_once $modelFile;
			return new $model();
		}

		throw new Exception(
			"Model '{$model}' tidak ditemukan di: {$modelFile}"
		);
	}
}