<?php
// Подключение автозагрузки через composer
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;
use App\Validator;

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

$app->get('/users', function ($request, $response) use ($file) {
    $includes = $request->getQueryParam('includes'); // Получение параметра из запроса
    $filteredStrings = [];

    // Читаем JSON из файла
    $usersJson = file_get_contents($file);
    $users = json_decode($usersJson, true); // Декодируем как ассоциативный массив

    if (!is_array($users)) {
        $users = []; // Если файл пустой или содержит некорректный JSON
    }

    // Фильтруем пользователей
    foreach ($users as $user) {
        if (isset($user['name']) && str_contains($user['name'], $includes)) { // Проверяем, содержится ли строка
            $filteredStrings[] = $user; // Добавляем в результат
        }
    }

    $params = [
        'users' => $filteredStrings // Передаем фильтрованных пользователей в шаблон
    ];

    return $this->get('render')->render($response, 'users/index.phtml', $params); // Возвращаем отрендеренный ответ
});



$app->get('/usersForm', function($request, $response){
    return $this->get('render')->render($response, 'users/register.phtml');
});
$app->post('/users', function ($request, $response) use ($file) {
    $validator = new Validator();

    // Получаем данные пользователя из POST-запроса
    $user = $request->getParsedBodyParam('user');

    // Валидируем данные пользователя
    $errors = $validator->validate($user);
    if (count($errors) === 0) {
        // Читаем текущих пользователей из файла
        $current = file_get_contents($file);
        $users = $current ? json_decode($current, true) : []; // Если файл пустой, создаем новый массив

        if (!is_array($users)) {
            $users = []; // Перестраховка, если файл содержит некорректные данные
        }

        // Добавляем нового пользователя в массив
        $users[] = $user;

        // Сохраняем обновленный массив обратно в файл
        file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // Редирект на список пользователей
        return $response->withRedirect('/users', 302);
    }

    // Если есть ошибки, возвращаем форму с ошибками
    $params = [
        'user' => $user,
        'errors' => $errors,
    ];
    return $this->get('render')->render($response->withStatus(422), 'users/register.phtml', $params);
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

