<?php
header('Content-Type: application/json; charset=utf-8');
// todo o teu código de session, config, curl, json_decode…
echo json_encode([
  'articles'     => $articles,
  'totalResults' => $totalResults
]);
