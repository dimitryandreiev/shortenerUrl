<?php
require 'vendor/autoload.php';

$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true
    ]
]);

/*
routes
*/
$app->get('/', 'home')->setName('home');
$app->get('/urls','urlList');
$app->get('/users','userList');

$app->get('/urls/{id}','getUrl');
$app->get('/stats/','getStats');
$app->get('/users/{userId}/stats','getUserStats');
$app->get('/stats/{id}','getUrlStats');

$app->post('/users/{userId}/urls','addUrl');
$app->post('/users','addUser');
$app->delete('/urls/{id}','deleteUrl');
$app->delete('/users/{id}','deleteUser');


$app->run();

function home() {
    echo 'Estou na home';
}

function getConn() {
    return new PDO('mysql:host=localhost;dbname=url_shortener',
        'root',
        '',
        [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"]
    );
}


/*
Retorna todas as urls cadastradas

@params $request requisições enviadas
@params $responde resposta do sistema
@params $args argumentoss passados pela url
*/
function urlList($request, $response, $args) {
    $stmt = getConn()->query("SELECT * FROM urls");
    $urls = $stmt->fetchAll(PDO::FETCH_OBJ);
    echo "{url:".json_encode($urls)."}";
}

/*
Retorna todos os usuários cadastrados

@params $request requisições enviadas
@params $responde resposta do sistema
@params $args argumentoss passados pela url
*/
function userList($request, $response, $args) {
    $stmt = getConn()->query("SELECT * FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_OBJ);
    echo "{user:".json_encode($users)."}";
}

/*
Busca url original cadastrada no banco
@params $request requisições enviadas
@params $responde resposta do sistema
@params $args argumentoss passados pela url
*/
function getUrl($request, $response, $args) {
    $id = $request->getAttribute('id');
    $conn = getConn();
    $sql = "SELECT url FROM urls WHERE id=:id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam("id",$id);
    $stmt->execute();
    $url = $stmt->fetchObject();

    echo json_encode($url);
}

/*
Busca todas estatisticas de uma url pela ID

@params $request requisições enviadas
@params $responde resposta do sistema
@params $args argumentoss passados pela url
*/
function getUrlStats($request, $response, $args) {
    $id = $request->getAttribute('id');
    $conn = getConn();
    $sql = "SELECT * FROM urls WHERE id=:id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam("id",$id);
    $stmt->execute();
    $url = $stmt->fetchObject();

    echo json_encode($url);
}

/*
Busca estatísticas globais do sistema

@params $request requisições enviadas
@params $responde resposta do sistema
@params $args argumentoss passados pela url
*/
function getStats($request, $response, $args) {
    $id = $request->getAttribute('id');
    $conn = getConn();
    /*$sql = "SELECT * FROM urls WHERE id=:id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam("id",$id);
    $stmt->execute();
    $stats = $stmt->fetchObject();

    echo json_encode($stats);*/
}

/*
Retorna estatísticas das urls de um usuário.

@params $request requisições enviadas
@params $responde resposta do sistema
@params $args argumentoss passados pela url
*/
function getUserStats($request, $response, $args) {
    $id = $request->getAttribute('id');
    $conn = getConn();
    /*$sql = "SELECT * FROM urls WHERE id=:id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam("id",$id);
    $stmt->execute();
    $userStats = $stmt->fetchObject();

    echo json_encode($userStats);*/
}

/*
Adiciona um usuário ao sistema.

@params $request requisições enviadas
@params $responde resposta do sistema
@params $args argumentoss passados pela url
*/
function addUser($request, $response, $args) {
    $user = $request->getParsedBody();
    $sql = "INSERT INTO "
            . "users (nome,email) "
            . "values (:nome,:email) ";
    $conn = getConn();
    $stmt = $conn->prepare($sql);
    $stmt->bindParam("nome",$user['nome']);
    $stmt->bindParam("email",$user['email']);
    $stmt->execute();
    $user['id'] = $conn->lastInsertId();
}

/*
Adiciona uma url atraves de um usuário do sistema.

@params $request requisições enviadas
@params $responde resposta do sistema
@params $args argumentoss passados pela url
*/
function addUrl($request, $response, $args) {
    $userId = $request->getAttribute('userId');
    $url = $request->getParsedBody();
    //$short_url = ShortenerUrl($url['url']);
    $short_url = "http://chaordicUrl/blabla";

    $sql = "INSERT INTO "
            . "urls (url, short_url, user_id) "
            . "values (:url,:short_url,:user_id) ";
    $conn = getConn();
    $stmt = $conn->prepare($sql);
    $stmt->bindParam("url",$url['url']);
    $stmt->bindParam("short_url",$short_url);
    $stmt->bindParam("user_id",$userId);
    $stmt->execute();
    $url['id'] = $conn->lastInsertId();

    echo json_encode($url);
}

/*
Deleta um usuário do sistema.

@params $request requisições enviadas
@params $responde resposta do sistema
@params $args argumentoss passados pela url
*/
function deleteUser($request, $response, $args)   {
    $id = $args['id'];
    $sql = "DELETE FROM users WHERE id=:id";
    $conn = getConn();
    $stmt = $conn->prepare($sql);
    $stmt->bindParam("id",$id);
    $stmt->execute();
    echo "{'message':'Usuário apagado'}";
}

/*
Deleta uma url do sistema.

@params $request requisições enviadas
@params $responde resposta do sistema
@params $args argumentoss passados pela url
*/
function deleteUrl($request, $response, $args)   {
    $id = $args['id'];
    $sql = "DELETE FROM urls WHERE id=:id";
    $conn = getConn();
    $stmt = $conn->prepare($sql);
    $stmt->bindParam("id",$id);
    $stmt->execute();
    echo "{'message':'Url apagada'}";
}