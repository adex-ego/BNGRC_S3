<?php
declare(strict_types=1);

namespace app\controllers;

use flight\Engine;
use flight\util\Collection;

class LoginController
{
	protected Engine $app;

	public function __construct(Engine $app)
	{
		$this->app = $app;
	}

	public function show(): void
	{
		if (session_status() !== PHP_SESSION_ACTIVE) {
			session_start();
		}

		if (!empty($_SESSION['user'])) {
			$this->app->redirect('/home');
			return;
		}

		$flash = $_SESSION['flash'] ?? [];
		unset($_SESSION['flash']);

		$this->app->render('login', $flash);
	}

	public function authenticate(): void
	{
		if (session_status() !== PHP_SESSION_ACTIVE) {
			session_start();
		}

		$request = $this->app->request();
		$email = trim((string) ($request->data->email ?? ''));
		$password = (string) ($request->data->password ?? '');

		// Debug - afficher ce qui est reçu
		error_log("=== LOGIN DEBUG ===");
		error_log("Email reçu: " . $email);
		error_log("Password longueur: " . strlen($password));

		if ($email === '' || $password === '') {
			$this->setFlashAndRedirect([ 'error' => 'Email et mot de passe requis.' ], '/login');
			return;
		}

		if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
			$this->setFlashAndRedirect([ 'error' => 'Email invalide.' ], '/login');
			return;
		}

		if (mb_strlen($password) < 6) {
			$this->setFlashAndRedirect([ 'error' => 'Mot de passe trop court.' ], '/login');
			return;
		}

		try {
			$db = $this->app->db();
			error_log("DB connected");
			
			$user = $db->fetchRow(
				'SELECT id, username, email, hashedpassword FROM user_takalo WHERE email = ?',
				[ $email ]
			);
			
			error_log("fetchRow result type: " . gettype($user));
			error_log("fetchRow result: " . json_encode($user));
			
		} catch (\Throwable $e) {
			error_log("DB Error: " . $e->getMessage());
			$this->setFlashAndRedirect([ 'error' => 'Erreur de connexion à la base de données.' ], '/login');
			return;
		}

		if (!($user instanceof Collection) || $user->id === null) {
			error_log("User not found or invalid");
			$this->setFlashAndRedirect([ 'error' => 'Identifiants invalides.' ], '/login');
			return;
		}

		error_log("User found, hashes: " . $user->hashedpassword);
		$verify = password_verify($password, (string) $user->hashedpassword);
		error_log("Password verify: " . ($verify ? 'OK' : 'FAIL'));

		if (!$verify) {
			$this->setFlashAndRedirect([ 'error' => 'Identifiants invalides.' ], '/login');
			return;
		}

		$_SESSION['user'] = [
			'id' => (int) $user->id,
			'username' => (string) $user->username,
			'email' => (string) $user->email,
		];

		$this->app->redirect('/home');
	}

	public function register(): void
	{
		$request = $this->app->request();
		$username = trim((string) ($request->data->username ?? ''));
		$email = trim((string) ($request->data->email ?? ''));
		$password = (string) ($request->data->password ?? '');

		if ($username === '' || $email === '' || $password === '') {
			$this->setFlashAndRedirect([ 'register_error' => 'Tous les champs sont requis.' ], '/login');
			return;
		}

		if (mb_strlen($username) < 3 || mb_strlen($username) > 50) {
			$this->setFlashAndRedirect([ 'register_error' => 'Nom invalide.' ], '/login');
			return;
		}

		if (preg_match('/^[\p{L}0-9 _.-]+$/u', $username) !== 1) {
			$this->setFlashAndRedirect([ 'register_error' => 'Nom invalide.' ], '/login');
			return;
		}

		if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
			$this->setFlashAndRedirect([ 'register_error' => 'Email invalide.' ], '/login');
			return;
		}

		if (mb_strlen($password) < 6) {
			$this->setFlashAndRedirect([ 'register_error' => 'Mot de passe trop court.' ], '/login');
			return;
		}

		try {
			$db = $this->app->db();
			$existing = $db->fetchRow('SELECT id FROM user_takalo WHERE email = ?', [ $email ]);
			if ($existing instanceof Collection && $existing->id !== null) {
				$this->setFlashAndRedirect([ 'register_error' => 'Email déjà utilisé.' ], '/login');
				return;
			}

			$hash = password_hash($password, PASSWORD_DEFAULT);
			$db->runQuery(
				'INSERT INTO user_takalo (username, email, hashedpassword) VALUES (?, ?, ?)',
				[ $username, $email, $hash ]
			);
		} catch (\Throwable $e) {
			$this->setFlashAndRedirect([ 'register_error' => 'Erreur lors de l\'inscription.' ], '/login');
			return;
		}

		$this->setFlashAndRedirect([ 'register_success' => 'Compte créé. Connecte-toi.' ], '/login');
	}

	public function logout(): void
	{
		if (session_status() !== PHP_SESSION_ACTIVE) {
			session_start();
		}

		$_SESSION = [];
		if (ini_get('session.use_cookies')) {
			$params = session_get_cookie_params();
			setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
		}
		session_destroy();

		$this->app->redirect('/login');
	}

	protected function setFlashAndRedirect(array $flash, string $path): void
	{
		if (session_status() !== PHP_SESSION_ACTIVE) {
			session_start();
		}

		$_SESSION['flash'] = $flash;
		$this->app->redirect($path);
	}
}
