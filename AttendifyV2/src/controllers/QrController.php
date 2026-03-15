<?php

// Placeholder controller, not strictly needed because we embed a Google Charts QR directly.
// Kept for extensibility (e.g., if you later switch to a PHP QR library).

class QrController
{
    public function show(): void
    {
        http_response_code(501);
        echo 'QR endpoint not implemented. QR codes are currently rendered via Google Charts API in the teacher session view.';
    }
}

