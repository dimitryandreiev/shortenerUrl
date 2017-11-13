<?php
namespace DownsMaster\Controllers;
use DownsMaster\Controllers\Controller;
use Slim\Http\Request;
use Slim\Http\Response;

Class UserController extends Controller
{

	/*
	Retorna estatísticas das urls de um usuário.

	@params $request requisições enviadas
	@params $responde resposta do sistema
	@params $args argumentoss passados pela url
	*/
	public function getStats($request, $response, $args) {
		$id = $request->getAttribute('userId');
		$conn = $this->getConn;

		$sqlUserCheck = "SELECT id FROM users WHERE id=:id";
		$stmt = $conn->prepare($sqlUserCheck);
		$stmt->bindParam("id",$id);
		$stmt->execute();
		$user = $stmt->fetchObject();

		if (empty($user)) {
			return $response->withJson(
				[
					'code' => 404,
					'message' => 'Not found'
				],
				404
			);
		}

		$sql = "SELECT 
			SUM(hits) as hits,
			COUNT(*) as urlCount
			FROM `urls`
			WHERE user_id=:id";
		$stmt = $conn->prepare($sql);
		$stmt->bindParam("id",$id);
		$stmt->execute();
		$stats = $stmt->fetchObject();

		$sqlTopUrl = "SELECT id, hits, url, short_url as shortUrl
				FROM `urls`
				WHERE user_id=:id
				ORDER BY hits DESC LIMIT 10";
		$stmt = $conn->prepare($sqlTopUrl);
		$stmt->bindParam("id",$id);
		$stmt->execute();
		$stats->topUrls = $stmt->fetchAll($this->fetchAll);

		return $response->withJson($stats, 200);
	}

	/*
	Adiciona um usuário ao sistema.

	@params $request requisições enviadas
	@params $responde resposta do sistema
	@params $args argumentoss passados pela url
	*/
	public function add($request, $response, $args) {
		$user = $request->getParsedBody();

		$sqlVerifyUser = "SELECT id FROM users WHERE id=:id";
		$conn = $this->getConn;
		$stmt = $conn->prepare($sqlVerifyUser);
		$stmt->bindParam("id",$user['id']);
		$stmt->execute();
		$repeatedUser = $stmt->fetchObject();

		if (!empty($repeatedUser)) {
			return $response->withJson(
				[
					'code' => 409,
					'message' => 'Conflict'
				],
				409
			);
		}

		$sql = "INSERT INTO "
			. "users (id) "
			. "values (:id) ";
		$stmt = $conn->prepare($sql);
		$stmt->bindParam("id",$user['id']);
		$stmt->execute();

		return $response->withJson($user, 201);
	}

	/*
	Deleta um usuário do sistema.

	@params $request requisições enviadas
	@params $responde resposta do sistema
	@params $args argumentoss passados pela url
	*/
	public function delete($request, $response, $args)   {
		$id = $args['id'];
		$sql = "DELETE FROM users WHERE id=:id";
		$conn = $this->getConn;
		$stmt = $conn->prepare($sql);
		$stmt->bindParam("id",$id);
		$stmt->execute();

		return;
	}

}