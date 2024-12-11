<?php
// Подключение автозагрузки через composer
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;

$file = 'public/users.json';
$container = new Container();
$container->set('render', function () {
    // Параметром передается базовая директория, в которой будут храниться шаблоны
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});
$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);

$users = ['mike', 'mishel', 'adel', 'keks', 'kamila','mike', 'mishel', 'adel', 'keks', 'kamila'];

$app->get('/', function ($request, $response) {
    $response->getBody()->write('Welcome to Slim!');
    return $response;
    // Благодаря пакету slim/http этот же код можно записать короче
    // return $response->write('Welcome to Slim!');
});

$app->get('/users', function ($request, $response) use ($users) {
    $includes = $request->getQueryParam('includes'); // Получение параметра из запроса
    $filteredStrings = [];
    
    foreach ($users as $user) {
        if (str_contains($user, $includes)) { // Проверяем, содержится ли строка
            $filteredStrings[] = $user; // Добавляем в результат
        }
    }
    
    $params = [
        'users' => $filteredStrings // Правильный синтаксис массива
    ];
    
    return $this->get('render')->render($response, 'users/index.phtml', $params); // Возвращаем отрендеренный ответ
});


$app->get('/usersForm', function($request, $response){
    return $this->get('render')->render($response, 'users/register.phtml');
});
$app->post('/users', function($request,$response) use($file){
    $user = $request->getParsedBodyParam('user');
    $userJson = json_encode($user);
    $current = file_get_contents($file);
    $current .= $userJson;
    file_put_contents($file, $current);
    $params = ['id' => $user['name']];
    return $this->get('render')->render($response, 'users/show.phtml',$params);
});
$app->get('/courses/{id}', function($request, $response, array $args){
    $id = $args['id'];
    return $response->write("Course id {$id}");
    return $this->get('render')->render($response, 'users/show.phtml');
});


$app->get('/users/{id}', function ($request, $response, $args) {
    $params = ['id' => $args['id'], 'nickname' => 'user-' . $args['id']];
    // Указанный путь считается относительно базовой директории для шаблонов, заданной на этапе конфигурации
    // $this доступен внутри анонимной функции благодаря https://php.net/manual/ru/closure.bindto.php
    // $this в Slim это контейнер зависимостей
    return $this->get('render')->render($response, 'users/show.phtml', $params);
});



$app->run();

