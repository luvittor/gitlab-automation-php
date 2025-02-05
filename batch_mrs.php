<?php
require __DIR__ . '/src/header.php';

use GitLabAutomation\GitLabHelper;
use GuzzleHttp\Exception\RequestException;

if ($argc < 2) {
    die("Usage: php batch_mrs.php <tasks_file>\n");
}

$helper = new GitLabHelper();
$tasksFile = $argv[1];

if (!file_exists($tasksFile)) {
    die("Error: File $tasksFile not found!\n");
}

$handle = fopen($tasksFile, 'r');
if (!$handle) {
    die("Error: Could not open $tasksFile\n");
}

while (($line = fgets($handle)) !== false) {
    $line = trim($line);
    if (empty($line)) continue;
    
    $parts = preg_split('/\s+/', $line);
    $operation = strtoupper($parts[0]);
    
    try {
        switch ($operation) {
            case 'MR':
                $srcUrl = $parts[1];
                $destUrl = $parts[2];
                handleMergeRequest($helper, $srcUrl, $destUrl);
                break;
                
            default:
                echo "Warning: Unknown operation $operation\n";
                break;
        }
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

fclose($handle);
echo "Done!\n";

function handleMergeRequest(GitLabHelper $helper, $srcUrl, $destUrl)
{
    $src = $helper->extractProjectAndBranch($srcUrl);
    $dest = $helper->extractProjectAndBranch($destUrl);
    
    if ($src['project'] !== $dest['project']) {
        throw new \RuntimeException("Source and destination must be in the same project");
    }

    $projectId = $helper->getProjectId($src['project']);
    $title = "MR from {$src['branch']} to {$dest['branch']}";
    
    try {
        $response = $helper->client->post("projects/$projectId/merge_requests", [
            'form_params' => [
                'source_branch' => $src['branch'],
                'target_branch' => $dest['branch'],
                'title' => $title
            ]
        ]);
        
        $data = json_decode($response->getBody(), true);
        echo "MR: Created from '{$src['branch']}' to '{$dest['branch']}' in project '{$src['project']}'\n";
        echo "    URL: {$data['web_url']}\n";
        
    } catch (RequestException $e) {
        $response = $e->getResponse();
        if ($response && $response->getStatusCode() === 400) {
            echo "MR: Already exists for '{$src['branch']}' -> '{$dest['branch']}' in project '{$src['project']}'\n";
        } else {
            throw new \RuntimeException("Failed to create merge request: " . $e->getMessage());
        }
    }
}