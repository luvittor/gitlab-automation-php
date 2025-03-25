#!/usr/bin/env php
<?php
require __DIR__ . '/src/header.php';

if ($argc < 2) {
    die("Usage: php create_txt_for_gmud_mrs.php <gmud_number>\n");
}

$gmudNumber = $argv[1];

if (is_numeric($gmudNumber) == false) {
    die("Error: GMUD number must be an integer\n");
}

$repos = $_ENV['REPOS'];
$outputFile = "tasks/create_gmud_{$gmudNumber}_mrs.txt";
$content = '';

foreach ($repos as $repo) {
    $baseUrl = rtrim($_ENV['GITLAB_URL'], '/') . '/' . trim($repo, '/');

    $content .= "MR {$baseUrl}/tree/release/gmud-{$gmudNumber} {$baseUrl}/tree/gmud/{$gmudNumber}\n";
}

$content .= "\n";
foreach ($repos as $repo) {
    $baseUrl = rtrim($_ENV['GITLAB_URL'], '/') . '/' . trim($repo, '/');

    $content .= "MR {$baseUrl}/tree/gmud/{$gmudNumber} {$baseUrl}/tree/master\n";
}

file_put_contents($outputFile, trim($content));
echo "Created MR tasks for GMUD {$gmudNumber} in {$outputFile}\n";