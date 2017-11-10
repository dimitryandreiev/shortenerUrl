<?php
namespace DownsMaster\Controllers;
use DownsMaster\Controllers\Controller;

Class UrlController extends Controller{
	public function randomShortUrl($url) {
		$characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
		$code = '';
	 	$max = strlen($characters) - 1;

	 	for ($i = 0; $i < 10; $i++) {
	      	$code .= $characters[mt_rand(0, $max)];
	 	}

	 	return $code;
	}

	/*
	Busca url original cadastrada no banco
	@params $request requisições enviadas
	@params $responde resposta do sistema
	@params $args argumentoss passados pela url
	@return $response com código 301 caso tenha redirecionado, caso id não exista 404
	*/
	public function getUrl($request, $response, $args) {
	    $id = $request->getAttribute('id');
	    $conn = $this->getConn;
	    $sql = "SELECT url FROM urls WHERE id=:id";

	    $stmt = $conn->prepare($sql);
	    $stmt->bindParam("id",$id);
	    $stmt->execute();
	    $url = $stmt->fetchObject();

	    if (empty($url)) {
	    	 return $response
		        ->withStatus(404)
		        ->write('Not found');
	    }

	    return $response
	        ->withStatus(301)
	        ->withRedirect($url->url);
	}

	/*
	Busca todas estatisticas de uma url pela ID

	@params $request requisições enviadas
	@params $responde resposta do sistema
	@params $args argumentoss passados pela url
	*/
	public function getUrlStats($request, $response, $args) {
	    $id = $request->getAttribute('id');
	    $conn = $this->getConn;
	    $sql = "SELECT * FROM urls WHERE id=:id";
	    $stmt = $conn->prepare($sql);
	    $stmt->bindParam("id",$id);
	    $stmt->execute();
	    $url = $stmt->fetchObject();

	    if (empty($url)) {
	    	 return $response
		        ->withStatus(404)
		        ->write('Not found');
	    }

	    header('Content-type: application/json');
	    echo json_encode($url);
	}


	/*
	Busca estatísticas globais do sistema

	@params $request requisições enviadas
	@params $responde resposta do sistema
	@params $args argumentoss passados pela url
	*/
	public function getStats($request, $response, $args) {
	    $conn = $this->getConn;
	    $sql = "SELECT 
			SUM(hits) as hits,
			COUNT(*) as urlCount
			FROM `urls`";
	    $stmt = $conn->prepare($sql);
	    $stmt->execute();
	    $stats = $stmt->fetchObject();

	    $sqlTopUrl = "SELECT *
				FROM `urls`
				ORDER BY hits DESC LIMIT 10";
		$stmt = $this->getConn->query( $sqlTopUrl);
	    $stats->topUrls = $stmt->fetchAll($this->fetchAll);

	    header('Content-type: application/json');
	    echo json_encode($stats);

	    return;
	}

	/*
	Adiciona uma url atraves de um usuário do sistema.

	@params $request requisições enviadas
	@params $responde resposta do sistema
	@params $args argumentoss passados pela url
	@return $response código 201 caso add
	*/
	public function addUrl($request, $response, $args) {
	    $userId = $request->getAttribute('userId');
	    $url = $request->getParsedBody();
	    $code = $this->randomShortUrl($url['url']);
	    $shortUrl = "http://chaordic/" . $code;

	    $sql = "INSERT INTO "
	            . "urls (url, short_url, user_id) "
	            . "values (:url,:short_url,:user_id) ";
	    $conn = $this->getConn;
	    $stmt = $conn->prepare($sql);
	    $stmt->bindParam("url",$url['url']);
	    $stmt->bindParam("short_url",$shortUrl);
	    $stmt->bindParam("user_id",$userId);
	   	$stmt->execute();
	    $url['id'] = $conn->lastInsertId();

        header('Content-type: application/json');
	    echo json_encode($url);

	    return $response
	        ->withStatus(201)
	        ->write('Created');
	}

	/*
	Deleta uma url do sistema.

	@params $request requisições enviadas
	@params $responde resposta do sistema
	@params $args argumentoss passados pela url
	*/
	public function deleteUrl($request, $response, $args)   {
	    $id = $args['id'];
	    $sql = "DELETE FROM urls WHERE id=:id";
	    $conn = $this->getConn;
	    $stmt = $conn->prepare($sql);
	    $stmt->bindParam("id",$id);
	    $stmt->execute();
	    return;
	}
}