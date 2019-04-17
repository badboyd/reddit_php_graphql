<?php
// Test this using following command
// php -S localhost:8080 ./graphql.php &
require_once __DIR__ . '/vendor/autoload.php';

require_once("phapper.php");

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Schema;
use GraphQL\Server\StandardServer;

$redditClient = new Phapper();

try {
    $post = new ObjectType([
        'name' => 'Post',
        'fields' => [
            'title' => [
                'type' => Type::string(),
                'resolve' => function($root, $args) {
                    return $root->data->title;
                }
            ],
            'url' => [
                'type' => Type::string(),
                'resolve' => function($root, $args) {
                    return $root->data->url;
                }
            ],
            'score' =>[
                'type' => Type::int(),
                'resolve' => function($root, $args) {
                    return $root->data->score;
                }
            ],
            'author' => [
                'type' => Type::string(),
                'resolve' => function($root, $args) {
                    return $root->data->author;
                }
            ],
            'fullnameId' => [
                'type' => Type::string(),
                'resolve' => function($root, $args) {
                    return $root->data->fullnamId;
                }
            ],
            'numComments' => [
                'type' => Type::int(),
                'resolve' =>function($root, $args) {
                    return $root->data->numComments;
                }
            ],
            'subreddit' => [
                'type'=> Type::string(),
                'resolve' => function($root, $args) {
                    return $root->data->subreddit;
                }
            ],
            'name' => [
                'type' => Type::string(),
                'resolve' => function($root, $args) {
                    return $root->data->name;
                }
            ],
            'id' => [
                'type' => Type::string(),
                'resolve' => function($root, $args) {
                    return $root->data->id;
                }
            ],
            'thumbnail' => [
                'type' => Type::string(),
                'resolve' => function($root, $args) {
                    return $root->data->thumbnail;
                }
            ],
            'created_utc' => [
                'type' => Type::int(),
                'resolve' => function($root, $args) {
                    return $root->data->created_utc;
                }
            ],
            'permalink' => [
                'type' => Type::string(),
                'resolve' => function($root, $args) {
                    return $root->data->permalink;
                }
            ],
        ]
    ]);

    $timeInterval = new EnumType([
        'name' => 'TimeInterval',
        'values' => [ 'hour', 'day', 'week', 'month', 'year', 'all']
    ]);

    $subredditType = new EnumType([
        'name' => 'type',
        'values' => ['hot', 'top', 'controversial', 'new'],
    ]);

    $sortType = new EnumType([
        'name' => 'SortType',
        'values' => ['hot', 'top', 'new', 'relevance', 'comments'],
    ]);

    $queryType = new ObjectType([
        'name' => 'Query',
        'fields' => [
            'subreddit' => [
                'args' => [
                    'name' => Type::string(),
                    'after' => Type::string(),
                    'before' => Type::string(),
                    'limit' => Type::int(),
                    'count' => Type::int(),
                    'interval' => $timeInterval,
                    'type' => $subredditType,
                    'query' => Type::string(),
                    'sort' => $sortType,
                ],
                'type' => Type::listOf($post),
                'resolve' => function ($root, $args) {
                    global $redditClient;
                    $subredditName = $args['name'];
                    $searchQuery = $args['query'];
                    $limit = $args['limit'];
                    $after = $args['after'];
                    $before = $args['before'];
                    $time = $args['interval'];
                    $sort = $args['sort'];

                    if (!empty($searchQuery)) {
                        $posts = $redditClient->search($searchQuery, $subredditName, $sort, $time, 'link', $limit, $after, $before);
                        return $posts->data->children;
                    }

                    switch ($args['type']) {
                        case 'controversial':
                            $posts = $redditClient->getControversial($subredditName, $time, $limit, $after, $before);
                            return $posts->data->children;
                        case 'hot':
                            $posts = $redditClient->getHot($subredditName, $limit, $after, $before);
                            return $posts->data->children;
                        case 'new':
                            $posts = $redditClient->getNew($subredditName, $limit, $after, $before);
                            return $posts->data->children;
                        case 'top':
                        default:
                            $posts = $redditClient->getTop($subredditName, $time, $limit, $after, $before);
                            return $posts->data->children;
                    }
                    return;
                }
            ]
        ],
    ]);

    // See docs on schema options:
    // http://webonyx.github.io/graphql-php/type-system/schema/#configuration-options
    $schema = new Schema([
        'query' => $queryType,
    ]);

    // See docs on server options:
    // http://webonyx.github.io/graphql-php/executing-queries/#server-configuration-options
    $server = new StandardServer([
        'schema' => $schema
    ]);

    if (isset($_SERVER['HTTP_ORIGIN'])) {
       // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
       // you want to allow, and if so:
       header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
       header('Access-Control-Allow-Credentials: true');
       header('Access-Control-Max-Age: 86400');    // cache for 1 day
   }

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Do your CORS logic here
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
           // may also be using PUT, PATCH, HEAD etc
           header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

       if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
           header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    } else {
        $server->handleRequest();
    }
} catch (\Exception $e) {
    StandardServer::send500Error($e);
}
