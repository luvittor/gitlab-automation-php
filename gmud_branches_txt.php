#!/usr/bin/env php
<?php
require __DIR__ . '/src/header.php';

if ($argc < 2) {
    die("Usage: php gmud_branches_txt.php <gmud_number>\n");
}

$gmudNumber = $argv[1];

if (is_numeric($gmudNumber) == false) {
    die("Error: GMUD number must be an integer\n");
}

$repos = $_ENV['REPOS'];
$outputFile = "tasks/create_gmud_{$gmudNumber}_branches.txt";
$content = '';

foreach ($repos as $repo) {
    $baseUrl = rtrim($_ENV['GITLAB_URL'], '/') . '/' . trim($repo, '/');
    
    $content .= "NEW {$baseUrl}/tree/master {$baseUrl}/tree/release/gmud-{$gmudNumber}\n";
    $content .= "NEW {$baseUrl}/tree/master {$baseUrl}/tree/gmud/{$gmudNumber}\n\n";
}

file_put_contents($outputFile, trim($content));
echo "Created branch tasks for GMUD {$gmudNumber} in {$outputFile}\n";