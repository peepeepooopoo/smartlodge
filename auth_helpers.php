<?php
/**
 * Authentication helper functions for SmartLodge
 */

/**
 * Check if a user is logged in as admin
 * 
 * @return boolean True if user is logged in as admin
 */
function is_admin_logged_in() {
    return isset($_SESSION['admin_id']) && $_SESSION['is_admin'] === true;
}

/**
 * Check if a user is logged in as guest
 * 
 * @return boolean True if user is logged in as guest
 */
function is_guest_logged_in() {
    return isset($_SESSION['guest_user_id']) && $_SESSION['is_guest'] === true;
}

/**
 * Get the currently logged in admin's ID
 * 
 * @return int|null Admin ID or null if not logged in
 */
function get_admin_id() {
    return is_admin_logged_in() ? $_SESSION['admin_id'] : null;
}

/**
 * Get the currently logged in guest's ID
 * 
 * @return int|null Guest ID or null if not logged in
 */
function get_guest_id() {
    return is_guest_logged_in() ? $_SESSION['guest_user_id'] : null;
}

/**
 * Get the currently logged in admin's name
 * 
 * @return string|null Admin name or null if not logged in
 */
function get_admin_name() {
    return is_admin_logged_in() ? $_SESSION['admin_name'] : null;
}

/**
 * Get the currently logged in guest's name
 * 
 * @return string|null Guest name or null if not logged in
 */
function get_guest_name() {
    return is_guest_logged_in() ? $_SESSION['guest_name'] : null;
}

/**
 * Require admin authentication for a page
 * Redirects to login page if not logged in as admin
 */
function require_admin_auth() {
    if (!is_admin_logged_in()) {
        $_SESSION['login_error'] = "Admin access required. Please log in.";
        header("Location: login.php?role=admin");
        exit();
    }
}

/**
 * Require guest authentication for a page
 * Redirects to login page if not logged in as guest
 */
function require_guest_auth() {
    if (!is_guest_logged_in()) {
        $_SESSION['login_error'] = "Guest access required. Please log in.";
        header("Location: login.php?role=guest");
        exit();
    }
}