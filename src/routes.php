<?php
// Routes

error_reporting(7);
ini_set('display_errors', 'On');
ini_set('max_execution_time', 300); //300 seconds = 5 minutes

$app->get('/123', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->renderer->render($response, 'index.phtml', $args);
});


$app->get('/', '\controllers\galleryController:showPage');

$app->post('/search', '\controllers\galleryController:searchImage');