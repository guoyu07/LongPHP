<?php
defined('BASE_PATH') OR exit('No direct script access allowed');
/**
 * Longphp
 * Author: William Jiang
 */


if (!function_exists('is_cli')) {
	/**
	 * 判断是否为cli访问
	 *
	 * @return bool
	 */
	function is_cli()
	{
		return defined('STDIN') || PHP_SAPI === 'cli' ? true : false;
	}
}

if (!function_exists('setHeader')) {
	/**
	 * @param int $code
	 */
	function setHeader($code = 200)
	{
		if (is_cli()) return;
		$code = intval($code);
		$status = array(
			100 => 'Continue',
			101 => 'Switching Protocols',

			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',

			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Found',
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			307 => 'Temporary Redirect',

			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Sys',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',
			422 => 'Unprocessable Entity',

			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported'
		);
		if (!isset($status[$code])) {
			throwError('Invalid error code', 500);
		}

		if (strpos(PHP_SAPI, 'cgi') === 0) {
			header('Status:' . $code . ' ' . $status[$code], true);
		} else {
			$protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
			header($protocol . ' ' . $code . ' ' . $status[$code], true, $code);
		}
	}
}


if (!function_exists('errorHandler')) {
	function errorHandler($severity, $errMsg, $errFile, $errLine, $errContext)
	{
		$is_error = (((E_ERROR | E_USER_ERROR | E_COMPILE_ERROR | E_CORE_ERROR | E_USER_ERROR) & $severity) === $severity);

		\Long\Long_Exception::logError($severity, $errMsg, $errFile, $errLine);

		if (($severity & error_reporting()) !== $severity) return;

		\Long\Long_Exception::showError($errMsg);
		/**
		 * 判断是否为致命错误
		 */
		if ($is_error) {
			setHeader(500);
			exit(1);
		}

	}
}

if (!function_exists('exceptionHandler')) {
	/**
	 * 显示处理异常
	 * @param Exception $exception
	 */
	function exceptionHandler($exception)
	{
		\Long\Long_Exception::logError('error', $exception->getMessage(), $exception->getFile(), $exception->getLine());

		if (str_ireplace(array('off', 'none', 'no', 'false', 'null'), '', ini_get('display_errors'))) {
			\Long\Long_Exception::showException($exception);
		}
		exit(1);
	}
}
if (!function_exists('throwError')) {
	function throwError($message = '', $status_code = 500, $isExit = true, $template = 'error_general')
	{
		\Long\Log\Log::writeLog($message, 'error');

		\Long\Long_Exception::showError($message, $status_code, $template);

		if ($isExit) {
			exit(1);
		}
	}
}

if (!function_exists('M')) {
	function &M($name)
	{
		$modelName = ucfirst($name) . 'Model';
		$modelFile = ucfirst($name) . 'Model.php';
		$filePath = APP_PATH . DIRECTORY_SEPARATOR . 'Model' . DIRECTORY_SEPARATOR . $modelFile;

		//判断文件是否存在
		if (!file_exists($filePath)) {
			throwError('Model ' . $name . 'does not exist');
		}
		$model = 'Model\\' . $modelName;
		$M = new $model();
		return $M;
	}
}
