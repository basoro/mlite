<?php

namespace Plugins\Bpjs_emr;

use Systems\SiteModule;

class Site extends SiteModule
{
    public function init()
    {
        // Initialization for frontend if required
    }

    public function routes()
    {
        // Add routes if public API endpoints for EMR are required
        $this->route('bpjs_emr/api/bundle', 'apiBundle');
    }

    public function apiBundle()
    {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'message' => 'BPJS EMR API endpoint ready.'
        ]);
        exit;
    }
}
