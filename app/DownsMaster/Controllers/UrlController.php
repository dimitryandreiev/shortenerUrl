<?php
namespace DownsMaster\Controllers;
use DownsMaster\Controllers\Controller;
use Slim\Http\Request;
use Slim\Http\Response;

Class UrlController extends Controller
{

	/*
	Busca url original cadastrada no banco

	@params $url endereço que será convertido
	@return $code code encurtado da url
	*/
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
	public function redirect($request, $response, $args) {
		$id = $request->getAttribute('id');
		$conn = $this->getConn;
		$sql = "SELECT url, hits FROM urls WHERE id=:id";

		$stmt = $conn->prepare($sql);
		$stmt->bindParam("id",$id);
		$stmt->execute();
		$url = $stmt->fetchObject();

		if (empty($url)) {
			return $response->withJson(
				[
					'code' => 404,
					'message' => 'Not found'
				],
				404
			);
		}

		// atualiza os hits a cada redirecionamento
		$hits = $url->hits + 1;
		$sqlHits = "UPDATE urls SET hits=:hits WHERE id=:id";
		$stmt = $conn->prepare($sqlHits);
		$stmt->bindParam("hits",$hits);
		$stmt->bindParam("id",$id);
		$stmt->execute();

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
	public function getStats($request, $response, $args) {
		$id = $request->getAttribute('id');
		$conn = $this->getConn;
		$sql = "SELECT id, hits, url, short_url as shortUrl FROM urls WHERE id=:id";
		$stmt = $conn->prepare($sql);
		$stmt->bindParam("id",$id);
		$stmt->execute();
		$url = $stmt->fetchObject();

		if (empty($url)) {
			return $response->withJson(
				[
					'code' => 404,
					'message' => 'Not found'
				],
				404
			);
		}

		return $response->withJson($url, 200);
	}

	/*
	Busca estatísticas globais do sistema

	@params $request requisições enviadas
	@params $responde resposta do sistema
	@params $args argumentoss passados pela url
	*/
	public function getGlobalStats($request, $response, $args) {
		$conn = $this->getConn;
		$sql = "SELECT 
			SUM(hits) as hits,
			COUNT(*) as urlCount
			FROM `urls`";
		$stmt = $conn->prepare($sql);
		$stmt->execute();
		$stats = $stmt->fetchObject();

	    $sqlTopUrl = "SELECT id, hits, url, short_url as shortUrl
				FROM `urls`
				ORDER BY hits DESC LIMIT 10";
		$stmt = $this->getConn->query( $sqlTopUrl);
		$stats->topUrls = $stmt->fetchAll($this->fetchAll);

		return $response->withJson($stats, 200);
	}

	/*
	Adiciona uma url atraves de um usuário do sistema.

	@params $request requisições enviadas
	@params $responde resposta do sistema
	@params $args argumentoss passados pela url
	@return $response código 201 caso add
	*/
	public function add($request, $response, $args) {
		$userId = $request->getAttribute('userId');
		$url = $request->getParsedBody();
		$code = $this->randomShortUrl($url['url']);
		$shortUrl = "http://localhost:8000/" . $code;

		$sql = "INSERT INTO "
			. "urls (url, short_url, user_id) "
			. "values (:url,:short_url,:user_id) ";
		$conn = $this->getConn;
		$stmt = $conn->prepare($sql);
		$stmt->bindParam("url",$url['url']);
		$stmt->bindParam("short_url",$shortUrl);
		$stmt->bindParam("user_id",$userId);
			$stmt->execute();
		$result['id'] = $conn->lastInsertId();
		$result['url'] = $url['url'];
		$result['hits'] = 0;
		$result['short_url'] = $shortUrl;

		return $response->withJson($result, 201);
	}

	/*
	Deleta uma url do sistema.

	@params $request requisições enviadas
	@params $responde resposta do sistema
	@params $args argumentoss passados pela url
	*/
	public function delete($request, $response, $args)   {
		$id = $args['id'];
		$sql = "DELETE FROM urls WHERE id=:id";
		$conn = $this->getConn;
		$stmt = $conn->prepare($sql);
		$stmt->bindParam("id",$id);
		$stmt->execute();
		return;
	}
}