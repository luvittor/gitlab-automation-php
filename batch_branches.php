<?php
require __DIR__ . '/src/header.php';

use GitLabAutomation\GitLabHelper;
use GuzzleHttp\Exception\RequestException;

if ($argc < 2) {
    die("Usage: php batch_branches.php <tasks_file>\n");
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

$currentDate = date('Ymd');

while (($line = fgets($handle)) !== false) {
    $line = trim($line);
    if (empty($line)) continue;
    
    // Replace {DATE} with current date
    $line = str_replace('{DATE}', $currentDate, $line);
    
    $parts = preg_split('/\s+/', $line);
    $operation = strtoupper($parts[0]);
    
    try {
        switch ($operation) {
            case 'NEW':
                $srcUrl = $parts[1];
                $destUrl = $parts[2];
                handleNewBranch($helper, $srcUrl, $destUrl);
                break;
                
            case 'DEL':
                $branchUrl = $parts[1];
                handleDeleteBranch($helper, $branchUrl);
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

function handleNewBranch(GitLabHelper $helper, $srcUrl, $destUrl)
{
    $src = $helper->extractProjectAndBranch($srcUrl);
    $dest = $helper->extractProjectAndBranch($destUrl);
    
    if (empty($src['project']) || empty($src['branch'])) {
        throw new \RuntimeException("Invalid source URL: $srcUrl");
    }
    
    if (empty($dest['project']) || empty($dest['branch'])) {
        throw new \RuntimeException("Invalid destination URL: $destUrl");
    }

    $projectId = $helper->getProjectId($dest['project']);
    
    try {
        $response = $helper->client->post("projects/$projectId/repository/branches", [
            'form_params' => [
                'branch' => $dest['branch'],
                'ref' => $src['branch']
            ]
        ]);
        
        echo "NEW: Created branch '{$dest['branch']}' in project '{$dest['project']}'\n";
    } catch (RequestException $e) {
        $response = $e->getResponse();
        if ($response && $response->getStatusCode() === 400) {
            echo "NEW: Branch '{$dest['branch']}' already exists in project '{$dest['project']}'\n";
        } else {
            throw new \RuntimeException("Failed to create branch: " . $e->getMessage());
        }
    }
}

function handleDeleteBranch(GitLabHelper $helper, $branchUrl)
{
    $branchInfo = $helper->extractProjectAndBranch($branchUrl);
    
    if (empty($branchInfo['project']) || empty($branchInfo['branch'])) {
        throw new \RuntimeException("Invalid branch URL: $branchUrl");
    }

    $projectId = $helper->getProjectId($branchInfo['project']);
    
    try {
        $helper->client->delete("projects/$projectId/repository/branches/" . urlencode($branchInfo['branch']));
        echo "DEL: Deleted branch '{$branchInfo['branch']}' in project '{$branchInfo['project']}'\n";
    } catch (RequestException $e) {
        $response = $e->getResponse();
        if ($response && $response->getStatusCode() === 404) {
            echo "DEL: Branch '{$branchInfo['branch']}' not found in project '{$branchInfo['project']}'\n";
        } else {
            throw new \RuntimeException("Failed to delete branch: " . $e->getMessage());
        }
    }
}