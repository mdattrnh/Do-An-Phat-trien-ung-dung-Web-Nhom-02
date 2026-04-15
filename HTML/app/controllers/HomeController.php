<?php
/**
 * HomeController
 * Serves the single page application interface.
 */
class HomeController {
    public function index() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        require BASE_PATH . '/app/views/home.php';
    }
}
