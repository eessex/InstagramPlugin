<?php
  require 'vendor/autoload.php';
  use GuzzleHttp\Client;

  $client = new Client();

  define("CLIENT_ID", "YOUR CLIENT ID");
  define("CLIENT_SECRET", "YOUR CLIENT SECRET");
  define("REDIRECT_URL", "YOUR REDIRECT URL");

  app = new \Slim\Slim(array(
    'view' => new \Slim\Views\Twig() //use twig for handling views
  ));

  $view = $app->view();
  $view->parserOptions = array(
    'debug' => true, //enable error reporting in the view
    'cache' => dirname(__FILE__) . '/cache' //set directory for caching views
  );
?>
