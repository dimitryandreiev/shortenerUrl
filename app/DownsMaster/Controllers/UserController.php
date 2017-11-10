<?php
namespace DownsMaster\Controllers;
use DownsMaster\Controllers\Controller;

Class UserController extends Controller{
	/*
	Retorna estatísticas das urls de um usuário.

	@params $request requisições enviadas
	@params $responde resposta do sistema
	@params $args argumentoss passados pela url
	*/
	public function getUserStats($request, $response, $args) {
	    $id = $request->getAttribute('userId');
	    $conn = $this->getConn;

	    $sqlUserCheck = "SELECT id FROM users WHERE id=:id";
	    $stmt = $conn->prepare($sqlUserCheck);
	    $stmt->bindParam("id",$id);
	    $stmt->execute();
	    $user = $stmt->fetchObject();

	    if (empty($user)) {
	    	 return $response
		        ->withStatus(404)
		        ->write('Not found');
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

	    $sqlTopUrl = "SELECT *
				FROM `urls`
				WHERE user_id=:id
				ORDER BY hits DESC LIMIT 10";
		$stmt = $conn->prepare($sqlTopUrl);
		$stmt->bindParam("id",$id);
		$stmt->execute();
	    $stats->topUrls = $stmt->fetchAll($this->fetchAll);

	    header('Content-type: application/json');
	    echo json_encode($stats);
	}

	/*
	Adiciona um usuário ao sistema.

	@params $request requisições enviadas
	@params $responde resposta do sistema
	@params $args argumentoss passados pela url
	*/
	public function addUser($request, $response, $args) {
	    $user = $request->getParsedBody();
	    $sql = "INSERT INTO "
	            . "users (nome,email) "
	            . "values (:nome,:email) ";
	    $conn = $this->getConn;
	    $stmt = $conn->prepare($sql);
	    $stmt->bindParam("nome",$user['nome']);
	    $stmt->bindParam("email",$user['email']);
	    $stmt->execute();
	    $user['id'] = $conn->lastInsertId();

	    header('Content-type: application/json');
	    echo json_encode($user);

	    return $response
	        ->withStatus(201)
	        ->write('Created');
	}

	/*
	Deleta um usuário do sistema.

	@params $request requisições enviadas
	@params $responde resposta do sistema
	@params $args argumentoss passados pela url
	*/
	public function deleteUser($request, $response, $args)   {
	    $id = $args['id'];
	    $sql = "DELETE FROM users WHERE id=:id";
	    $conn = $this->getConn;
	    $stmt = $conn->prepare($sql);
	    $stmt->bindParam("id",$id);
	    $stmt->execute();

	    return;
	}

}