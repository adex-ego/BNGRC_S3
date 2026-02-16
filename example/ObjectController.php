<?php
declare(strict_types=1);

namespace app\controllers;

use app\models\CategoryModel;
use app\models\ExchangeModel;
use app\models\ObjectModel;

class ObjectController extends BaseController
{
	public function mine(): void
	{
		$user = $this->requireLogin();
		$db = $this->app->db();

		$objects = ObjectModel::getMine($db, (int) $user['id']);
		$flash = $this->consumeFlash();

		$this->app->render('objects/mine', $flash + [
			'objects' => $objects,
		]);
	}

	public function createForm(): void
	{
		$this->requireLogin();
		$db = $this->app->db();
		$categories = CategoryModel::all($db);
		$flash = $this->consumeFlash();

		$this->app->render('objects/form', $flash + [
			'mode' => 'create',
			'categories' => $categories,
			'object' => null,
			'photos' => [],
		]);
	}

	public function store(): void
	{
		$user = $this->requireLogin();
		$request = $this->app->request();

		$name = trim((string) ($request->data->name ?? ''));
		$description = trim((string) ($request->data->description ?? ''));
		$price = trim((string) ($request->data->price ?? ''));
		$categoryId = (int) ($request->data->category_id ?? 0);

		if ($name === '' || $price === '') {
			$this->setFlash([ 'error' => 'Titre et prix requis.' ]);
			$this->app->redirect('/objects/create');
			return;
		}

		if (!is_numeric($price)) {
			$this->setFlash([ 'error' => 'Prix invalide.' ]);
			$this->app->redirect('/objects/create');
			return;
		}

		$uploads = $this->handleUploads($_FILES['photos'] ?? null);
		if ($uploads['error'] !== null) {
			$this->setFlash([ 'error' => $uploads['error'] ]);
			$this->app->redirect('/objects/create');
			return;
		}

		if (count($uploads['files']) === 0) {
			$this->setFlash([ 'error' => 'Au moins une photo est requise.' ]);
			$this->app->redirect('/objects/create');
			return;
		}

		try {
			$db = $this->app->db();
			$objectId = ObjectModel::create($db, [
				'owner_id' => (int) $user['id'],
				'category_id' => $categoryId > 0 ? $categoryId : null,
				'name' => $name,
				'description' => $description,
				'image' => $uploads['files'][0],
				'price' => (float) $price,
			]);

			ObjectModel::addPhotos($db, $objectId, $uploads['files']);
		} catch (\Throwable $e) {
			$this->setFlash([ 'error' => 'Erreur lors de la creation.' ]);
			$this->app->redirect('/objects/create');
			return;
		}

		$this->setFlash([ 'success' => 'Objet ajoute.' ]);
		$this->app->redirect('/objects/mine');
	}

	public function editForm(int $id): void
	{
		$user = $this->requireLogin();
		$db = $this->app->db();
		$object = ObjectModel::find($db, $id);
		if ($object === null || (int) $object->id_owner !== (int) $user['id']) {
			$this->setFlash([ 'error' => 'Objet introuvable.' ]);
			$this->app->redirect('/objects/mine');
			return;
		}

		$categories = CategoryModel::all($db);
		$photos = ObjectModel::getPhotos($db, $id);
		$flash = $this->consumeFlash();

		$this->app->render('objects/form', $flash + [
			'mode' => 'edit',
			'categories' => $categories,
			'object' => $object,
			'photos' => $photos,
		]);
	}

	public function update(int $id): void
	{
		$user = $this->requireLogin();
		$request = $this->app->request();

		$name = trim((string) ($request->data->name ?? ''));
		$description = trim((string) ($request->data->description ?? ''));
		$price = trim((string) ($request->data->price ?? ''));
		$categoryId = (int) ($request->data->category_id ?? 0);

		if ($name === '' || $price === '') {
			$this->setFlash([ 'error' => 'Titre et prix requis.' ]);
			$this->app->redirect('/objects/' . $id . '/edit');
			return;
		}

		if (!is_numeric($price)) {
			$this->setFlash([ 'error' => 'Prix invalide.' ]);
			$this->app->redirect('/objects/' . $id . '/edit');
			return;
		}

		$db = $this->app->db();
		$object = ObjectModel::find($db, $id);
		if ($object === null || (int) $object->id_owner !== (int) $user['id']) {
			$this->setFlash([ 'error' => 'Objet introuvable.' ]);
			$this->app->redirect('/objects/mine');
			return;
		}

		$uploads = $this->handleUploads($_FILES['photos'] ?? null, true);
		if ($uploads['error'] !== null) {
			$this->setFlash([ 'error' => $uploads['error'] ]);
			$this->app->redirect('/objects/' . $id . '/edit');
			return;
		}

		try {
			$mainImage = (string) $object->image;
			if (count($uploads['files']) > 0) {
				$mainImage = $uploads['files'][0];
			}

			ObjectModel::update($db, $id, [
				'category_id' => $categoryId > 0 ? $categoryId : null,
				'name' => $name,
				'description' => $description,
				'image' => $mainImage,
				'price' => (float) $price,
			]);

			if (count($uploads['files']) > 0) {
				ObjectModel::addPhotos($db, $id, $uploads['files']);
			}
		} catch (\Throwable $e) {
			$this->setFlash([ 'error' => 'Erreur lors de la mise a jour.' ]);
			$this->app->redirect('/objects/' . $id . '/edit');
			return;
		}

		$this->setFlash([ 'success' => 'Objet mis a jour.' ]);
		$this->app->redirect('/objects/mine');
	}

	public function delete(int $id): void
	{
		$user = $this->requireLogin();
		$db = $this->app->db();
		$object = ObjectModel::find($db, $id);
		if ($object === null || (int) $object->id_owner !== (int) $user['id']) {
			$this->setFlash([ 'error' => 'Objet introuvable.' ]);
			$this->app->redirect('/objects/mine');
			return;
		}

		try {
			ObjectModel::delete($db, $id);
		} catch (\Throwable $e) {
			$this->setFlash([ 'error' => 'Erreur lors de la suppression.' ]);
			$this->app->redirect('/objects/mine');
			return;
		}

		$this->setFlash([ 'success' => 'Objet supprime.' ]);
		$this->app->redirect('/objects/mine');
	}

	public function listOthers(): void
	{
		$user = $this->requireLogin();
		$db = $this->app->db();
		$objects = ObjectModel::getOthers($db, (int) $user['id']);

		$this->app->render('objects/list', [
			'objects' => $objects,
		]);
	}

	public function show(int $id): void
	{
		$user = $this->requireLogin();
		$db = $this->app->db();
		$object = ObjectModel::findWithOwner($db, $id);
		if ($object === null) {
			$this->setFlash([ 'error' => 'Objet introuvable.' ]);
			$this->app->redirect('/objects');
			return;
		}

		$photos = ObjectModel::getPhotos($db, $id);
		$myObjects = ObjectModel::getMine($db, (int) $user['id']);
		$ownershipHistory = ObjectModel::getOwnershipHistory($db, $id);
		$flash = $this->consumeFlash();

		$this->app->render('objects/show', $flash + [
			'object' => $object,
			'photos' => $photos,
			'my_objects' => $myObjects,
			'ownership_history' => $ownershipHistory,
		]);
	}

	public function propose(int $id): void
	{
		$user = $this->requireLogin();
		$request = $this->app->request();
		$proposedId = (int) ($request->data->proposed_id ?? 0);

		if ($proposedId <= 0) {
			$this->setFlash([ 'error' => 'Choisis un objet a proposer.' ]);
			$this->app->redirect('/objects/' . $id);
			return;
		}

		try {
			$db = $this->app->db();
			$object = ObjectModel::find($db, $id);
			$proposed = ObjectModel::find($db, $proposedId);

			if ($object === null || $proposed === null) {
				$this->setFlash([ 'error' => 'Objet introuvable.' ]);
				$this->app->redirect('/objects/' . $id);
				return;
			}

			if ((int) $proposed->id_owner !== (int) $user['id']) {
				$this->setFlash([ 'error' => 'Objet propose invalide.' ]);
				$this->app->redirect('/objects/' . $id);
				return;
			}

			if ((int) $object->id_owner === (int) $user['id']) {
				$this->setFlash([ 'error' => 'Impossible de proposer sur son propre objet.' ]);
				$this->app->redirect('/objects/' . $id);
				return;
			}

			ExchangeModel::createProposal($db, $id, $proposedId);
		} catch (\Throwable $e) {
			$this->setFlash([ 'error' => 'Erreur lors de la proposition.' ]);
			$this->app->redirect('/objects/' . $id);
			return;
		}

		$this->setFlash([ 'success' => 'Proposition envoyee.' ]);
		$this->app->redirect('/objects/' . $id);
	}

	private function handleUploads(?array $files, bool $allowEmpty = false): array
	{
		if ($files === null || !isset($files['name'])) {
			return [ 'files' => [], 'error' => $allowEmpty ? null : 'Aucune photo fournie.' ];
		}

		$normalized = $this->normalizeUploads($files);
		$uploads = [];
		$publicPath = rtrim((string) $this->app->get('app.public_path'), DIRECTORY_SEPARATOR);
		$uploadDir = $publicPath . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'uploads';

		if (!is_dir($uploadDir) && mkdir($uploadDir, 0755, true) === false) {
			return [ 'files' => [], 'error' => 'Impossible de creer le dossier upload.' ];
		}

		foreach ($normalized as $file) {
			if ($file['error'] !== UPLOAD_ERR_OK) {
				continue;
			}

			$info = @getimagesize($file['tmp_name']);
			if ($info === false) {
				return [ 'files' => [], 'error' => 'Format image invalide.' ];
			}

			$extension = image_type_to_extension($info[2], false);
			if ($extension === '' || $extension === 'bmp' || !in_array($extension, ['jpeg', 'jpg', 'png', 'gif', 'webp'], true)) {
				return [ 'files' => [], 'error' => 'Format image non supporte. Formats acceptes: JPEG, PNG, GIF, WEBP.' ];
			}

			$filename = uniqid('photo_', true) . '.' . $extension;
			$destination = $uploadDir . DIRECTORY_SEPARATOR . $filename;

			if (move_uploaded_file($file['tmp_name'], $destination) === false) {
				return [ 'files' => [], 'error' => 'Echec de telechargement.' ];
			}

			$uploads[] = $filename;
		}

		return [ 'files' => $uploads, 'error' => null ];
	}

	private function normalizeUploads(array $files): array
	{
		$normalized = [];
		if (is_array($files['name'])) {
			$count = count($files['name']);
			for ($i = 0; $i < $count; $i += 1) {
				$normalized[] = [
					'name' => $files['name'][$i] ?? '',
					'type' => $files['type'][$i] ?? '',
					'tmp_name' => $files['tmp_name'][$i] ?? '',
					'error' => $files['error'][$i] ?? UPLOAD_ERR_NO_FILE,
					'size' => $files['size'][$i] ?? 0,
				];
			}
		} else {
			$normalized[] = $files;
		}

		return $normalized;
	}

	public function search(): void
	{
		$request = $this->app->request();
		$query = trim((string) ($request->query->q ?? ''));
		$categoryId = (int) ($request->query->category_id ?? 0);
		$db = $this->app->db();

		$objects = ObjectModel::search($db, $query, $categoryId);
		$categories = CategoryModel::all($db);

		$this->app->render('objects/search', [
			'objects' => $objects,
			'categories' => $categories,
			'query' => $query,
			'selected_category_id' => $categoryId,
		]);
	}

	public function history(int $id): void
	{
		$db = $this->app->db();
		$object = ObjectModel::findWithOwner($db, $id);

		if ($object === null) {
			$this->app->notFound();
			return;
		}

		$history = ObjectModel::getOwnershipHistory($db, $id);
		$photos = ObjectModel::getPhotos($db, $id);

		$this->app->render('objects/history', [
			'object' => $object,
			'history' => $history,
			'photos' => $photos,
		]);
	}
}

