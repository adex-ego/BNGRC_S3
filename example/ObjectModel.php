<?php
declare(strict_types=1);

namespace app\models;

class ObjectModel
{
	public static function getMine($db, int $userId): array
	{
		return $db->fetchAll(
			'SELECT o.*, c.nom AS categorie_nom FROM objet_takalo o LEFT JOIN categorie_takalo c ON c.id = o.id_categorie WHERE o.id_owner = ? ORDER BY o.id DESC',
			[ $userId ]
		);
	}

	public static function getOthers($db, int $userId): array
	{
		return $db->fetchAll(
			'SELECT o.*, u.username, c.nom AS categorie_nom FROM objet_takalo o JOIN user_takalo u ON u.id = o.id_owner LEFT JOIN categorie_takalo c ON c.id = o.id_categorie WHERE o.id_owner <> ? ORDER BY o.id DESC',
			[ $userId ]
		);
	}

	public static function getAllWithOwner($db): array
	{
		return $db->fetchAll(
			'SELECT o.*, u.username, c.nom AS categorie_nom FROM objet_takalo o JOIN user_takalo u ON u.id = o.id_owner LEFT JOIN categorie_takalo c ON c.id = o.id_categorie ORDER BY o.id DESC'
		);
	}

	public static function find($db, int $id)
	{
		return $db->fetchRow('SELECT * FROM objet_takalo WHERE id = ?', [ $id ]);
	}

	public static function findWithOwner($db, int $id)
	{
		return $db->fetchRow(
			'SELECT o.*, u.username, u.email, c.nom AS categorie_nom FROM objet_takalo o JOIN user_takalo u ON u.id = o.id_owner LEFT JOIN categorie_takalo c ON c.id = o.id_categorie WHERE o.id = ?',
			[ $id ]
		);
	}

	public static function create($db, array $data): int
	{
		$db->runQuery(
			'INSERT INTO objet_takalo (id_owner, id_categorie, nom_objet, description, image, price) VALUES (?, ?, ?, ?, ?, ?)',
			[
				$data['owner_id'],
				$data['category_id'],
				$data['name'],
				$data['description'],
				$data['image'],
				$data['price'],
			]
		);

		$objectId = (int) $db->fetchField('SELECT LAST_INSERT_ID()');

		// Record the initial ownership
		self::recordOwnershipChange($db, $objectId, $data['owner_id']);

		return $objectId;
	}

	public static function update($db, int $id, array $data): void
	{
		$db->runQuery(
			'UPDATE objet_takalo SET id_categorie = ?, nom_objet = ?, description = ?, image = ?, price = ? WHERE id = ?',
			[
				$data['category_id'],
				$data['name'],
				$data['description'],
				$data['image'],
				$data['price'],
				$id,
			]
		);
	}

	public static function delete($db, int $id): void
	{
		$db->runQuery('DELETE FROM objet_photo_takalo WHERE id_objet = ?', [ $id ]);
		$db->runQuery('DELETE FROM echange_takalo WHERE id_objet_demande = ? OR id_objet_propose = ?', [ $id, $id ]);
		$db->runQuery('DELETE FROM objet_owner_history_takalo WHERE id_objet = ?', [ $id ]);
		$db->runQuery('DELETE FROM objet_takalo WHERE id = ?', [ $id ]);
	}

	public static function getPhotos($db, int $objectId): array
	{
		return $db->fetchAll('SELECT id, filename FROM objet_photo_takalo WHERE id_objet = ? ORDER BY id ASC', [ $objectId ]);
	}

	public static function addPhotos($db, int $objectId, array $files): void
	{
		foreach ($files as $file) {
			$db->runQuery(
				'INSERT INTO objet_photo_takalo (id_objet, filename) VALUES (?, ?)',
				[ $objectId, $file ]
			);
		}
	}

	public static function search($db, string $query = '', int $categoryId = 0): array
	{
		$sql = 'SELECT o.*, u.username, c.nom AS categorie_nom FROM objet_takalo o JOIN user_takalo u ON u.id = o.id_owner LEFT JOIN categorie_takalo c ON c.id = o.id_categorie WHERE 1=1';
		$params = [];

		if ($query !== '') {
			$sql .= ' AND (o.nom_objet LIKE ? OR o.description LIKE ?)';
			$searchTerm = '%' . $query . '%';
			$params[] = $searchTerm;
			$params[] = $searchTerm;
		}

		if ($categoryId > 0) {
			$sql .= ' AND o.id_categorie = ?';
			$params[] = $categoryId;
		}

		$sql .= ' ORDER BY o.id DESC';

		return $db->fetchAll($sql, $params);
	}

	public static function getOwnershipHistory($db, int $objectId): array
	{
		return $db->fetchAll(
			'SELECT h.*, u.username, u.email FROM objet_owner_history_takalo h JOIN user_takalo u ON u.id = h.id_owner WHERE h.id_objet = ? ORDER BY h.changed_at ASC',
			[ $objectId ]
		);
	}

	public static function recordOwnershipChange($db, int $objectId, int $userId): void
	{
		$db->runQuery(
			'INSERT INTO objet_owner_history_takalo (id_objet, id_owner) VALUES (?, ?)',
			[ $objectId, $userId ]
		);
	}
}

