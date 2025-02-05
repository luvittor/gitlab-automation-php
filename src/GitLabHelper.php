<?php

namespace GitLabAutomation;

use GuzzleHttp\Client;

class GitLabHelper
{
    public $client;
    protected $baseUrl;
    protected $token;

    public function __construct()
    {
        $this->setEnvs();

        $this->client = new Client([
            'base_uri' => $this->baseUrl . '/api/v4/',
            'headers' => [
                'PRIVATE-TOKEN' => $this->token
            ],
            'debug' => false
        ]);
    }

    protected function setEnvs()
    {
        if (empty($_ENV['GITLAB_URL']) || empty($_ENV['PRIVATE_TOKEN'])) {
            throw new \RuntimeException('Missing required environment variables');
        }

        $this->baseUrl = rtrim($_ENV['GITLAB_URL'], '/');
        $this->token = $_ENV['PRIVATE_TOKEN'];
    }

    public function extractProjectAndBranch($url)
    {
        $base = parse_url($this->baseUrl, PHP_URL_HOST);
        $parsed = parse_url($url);
        
        $path = ltrim($parsed['path'], '/');
        $basePath = parse_url($this->baseUrl, PHP_URL_PATH);
        if ($basePath) {
            $path = substr($path, strlen($basePath));
        }
        
        $parts = explode('/tree/', $path);
        return [
            'project' => $parts[0] ?? '',
            'branch' => $parts[1] ?? ''
        ];
    }

    public function getProjectId($projectPath)
    {
        try {
            $response = $this->client->get('projects/' . urlencode($projectPath));
            $data = json_decode($response->getBody(), true);
            return $data['id'];
        } catch (\Exception $e) {
            throw new \RuntimeException("Failed to get project ID for $projectPath: " . $e->getMessage());
        }
    }
}