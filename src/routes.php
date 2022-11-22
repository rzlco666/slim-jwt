<?php

use Firebase\JWT\JWT;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

return function (App $app) {
    $container = $app->getContainer();

    $app->get('/[{name}]', function (Request $request, Response $response, array $args) use ($container) {
        // Sample log message
        $container->get('logger')->info("Slim-Skeleton '/' route");

        // Render index view
        return $container->get('renderer')->render($response, 'index.phtml', $args);
    });

    /**
     * User authentication
     */
    $app->post('/login', function (Request $request, Response $response, array $args) {
        $input = $request->getParsedBody();
        $Username = trim(strip_tags($input['Username']));
        $Password = trim(strip_tags($input['Password']));
        $sql = "SELECT IdUser, Username  FROM `user` WHERE Username=:Username AND `Password`=:Password";
        $sth = $this->db->prepare($sql);
        $sth->bindParam("Username", $Username);
        $sth->bindParam("Password", $Password);
        $sth->execute();
        $user = $sth->fetchObject();
        if (!$user) {
            return $this->response->withJson(['status' => 'error', 'message' => 'These credentials do not match our records username.']);
        }
        $settings = $this->get('settings');
        $token = array(
            'IdUser' => $user->IdUser,
            'Username' => $user->Username
        );
        $token = JWT::encode($token, $settings['jwt']['secret'], "HS256");
        return $this->response->withJson(['status' => 'success', 'data' => $user, 'token' => $token]);
    });

    $app->post('/register', function (Request $request, Response $response, array $args) {
        $input = $request->getParsedBody();
        $Username = trim(strip_tags($input['Username']));
        $NamaLengkap = trim(strip_tags($input['NamaLengkap']));
        $Email = trim(strip_tags($input['Email']));
        $NoHp = trim(strip_tags($input['NoHp']));
        $Password = trim(strip_tags($input['Password']));
        $sql = "INSERT INTO user(Username, NamaLengkap, Email, NoHp, Password) 
            VALUES(:Username, :NamaLengkap, :Email, :NoHp, :Password)";
        $sth = $this->db->prepare($sql);
        $sth->bindParam("Username", $Username);
        $sth->bindParam("NamaLengkap", $NamaLengkap);
        $sth->bindParam("Email", $Email);
        $sth->bindParam("NoHp", $NoHp);
        $sth->bindParam("Password", $Password);
        $StatusInsert = $sth->execute();
        if ($StatusInsert) {
            $IdUser = $this->db->lastInsertId();
            $settings = $this->get('settings');
            $token = array(
                'IdUser' => $IdUser,
                'Username' => $Username
            );
            $token = JWT::encode($token, $settings['jwt']['secret'], "HS256");
            $dataUser = array(
                'IdUser' => $IdUser,
                'Username' => $Username
            );
            return $this->response->withJson(['status' => 'success', 'data' => $dataUser, 'token' => $token]);
        } else {
            return $this->response->withJson(['status' => 'error', 'data' => 'error insert user.']);
        }
    });

    /**
     * Group API
     */
    $app->group('/api', function (App $app) {
        $app->get("/user/{IdUser}", function (Request $request, Response $response, array $args) {
            $IdUser = $args["IdUser"];
            $sql = "SELECT IdUser, Username, NamaLengkap, Email, NoHp FROM `user` WHERE IdUser=:IdUser";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam("IdUser", $IdUser);
            $stmt->execute();
            $mainCount = $stmt->rowCount();
            $result = $stmt->fetchObject();
            if ($mainCount == 0) {
                return $this->response->withJson(['status' => 'error', 'message' => 'no result data.']);
            }
            return $response->withJson(["status" => "success", "data" => $result], 200);
        });
    });
};
