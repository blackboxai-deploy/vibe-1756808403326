<?php
// Simple PHP server starter for development
echo "Starting MediCare Store PHP Server...\n";
echo "Access the application at: http://localhost:8000\n";
echo "Admin panel at: http://localhost:8000/admin/login.php\n";
echo "\nPress Ctrl+C to stop the server.\n\n";

// Start PHP built-in server
passthru('php -S 0.0.0.0:8000 -t . 2>&1');
?>