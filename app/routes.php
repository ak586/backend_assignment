<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use App\Models\DB;

return function (App $app) {
  $app->options('/{routes:.*}', function (Request $request, Response $response) {
    // CORS Pre-Flight OPTIONS Request Handler
    return $response;
  });

  $app->get('/', function (Request $request, Response $response) {
    

      $response->getBody()->write("hii");

      return $response;
  });


  $app->get('/api/ticker', function (Request $request, Response $response) {
    $params = $request->getQueryParams();
    $ticker = $params['ticker'];
    $columns = $params['column'];
    $period = (int) $params['period'][0];
    $sql = "SELECT $columns FROM stocks  WHERE ticker = '$ticker' AND date >= DATE_SUB(CURDATE(), INTERVAL $period YEAR)";

    try {
      $db = new Db();
      $conn = $db->connect();
      $stmt = $conn->query($sql);
      $customers = $stmt->fetchAll(PDO::FETCH_OBJ);
      $db = null;
      // $response->getBody()->write("hello");

      $response->getBody()->write(json_encode($customers));
      return $response
        ->withHeader('content-type', 'application/json')
        ->withStatus(200);
    } catch (PDOException $e) {
      $error = array (
        "message" => $e->getMessage()
      );

      $response->getBody()->write(json_encode($error));
      return $response
        ->withHeader('content-type', 'application/json')
        ->withStatus(500);
    }
  });

  $app->group('/users', function (Group $group) {
    $group->get('', ListUsersAction::class);
    $group->get('/{id}', ViewUserAction::class);
  });
};
