<?php
require __DIR__ . '/src/header.php';

if ($argc < 3) {
    die("Usage: php task_mrs_for_gmud_txt.php <task_id> <gmud_number> [repo1] [repo2...]\n");
}

$taskId = $argv[1];
$gmudNumber = $argv[2];

if (is_numeric($gmudNumber) == false) {
    die("Error: GMUD number must be integer\n");
}

if ($argc > 3) {
    $repos = array_slice($argv, 3);
    echo "Using custom repos\n";
} else {
    $repos = $_ENV['REPOS'];
    echo "Using default repos\n";
}

$outputFile = "tasks/create_task_{$taskId}_mrs_to_gmud_{$gmudNumber}.txt";
$content = '';

foreach ($repos as $repo) {
    $baseUrl = rtrim($_ENV['GITLAB_URL'], '/') . '/' . ltrim($repo, '/');
    
    // To master
    $content .= "MR {$baseUrl}/tree/task/{$taskId} {$baseUrl}/tree/master\n";

}

$content .= "\n";

foreach ($repos as $repo) {
    $baseUrl = rtrim($_ENV['GITLAB_URL'], '/') . '/' . ltrim($repo, '/');
    
    // To GMUD release
    $content .= "MR {$baseUrl}/tree/task/{$taskId} {$baseUrl}/tree/release/gmud-{$gmudNumber}\n";

}

file_put_contents($outputFile, trim($content));
echo "Created task MRs for {$taskId} in {$outputFile}\n";