<?php
/*
 * Helper functions used by every controller and view.
 */

/** Escape output so user input cannot break the page. */
function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/** Build a link:  url('patient/book')  ->  index.php?page=patient/book */
function url($page = 'home')
{
    return 'index.php?page=' . $page;
}

/** Go to a page and stop. */
function redirect($page)
{
    header('Location: ' . url(str_replace(["\r", "\n"], '', $page)));
    exit;
}

/** Load a view inside the shared layout. */
function view($name, $data = [])
{
    extract($data);
    require __DIR__ . '/../views/layout/header.php';
    require __DIR__ . '/../views/' . $name . '.php';
    require __DIR__ . '/../views/layout/footer.php';
}

/* ---------- one-time messages ---------- */

function set_message($text, $type = 'success')
{
    $_SESSION['flash'] = ['text' => $text, 'type' => $type];
}

function get_message()
{
    if (isset($_SESSION['flash'])) {
        $m = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $m;
    }
    return null;
}

/* ---------- login state ----------
 * One session array for all three roles:
 *   ['user_id', 'name', 'role', 'ref_id']
 * ref_id is the Patient_ID or the Doctor_ID, so pages do not have to
 * look it up again on every request.
 */

function current_user()
{
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

function is_logged_in()
{
    return isset($_SESSION['user']);
}

function user_role()
{
    return isset($_SESSION['user']) ? $_SESSION['user']['role'] : '';
}

function user_name()
{
    return isset($_SESSION['user']) ? $_SESSION['user']['name'] : '';
}

/** Patient_ID or Doctor_ID of the logged in user. */
function ref_id()
{
    return isset($_SESSION['user']) ? $_SESSION['user']['ref_id'] : 0;
}

/** Block a page unless the user has this role. */
function require_role($role)
{
    if (user_role() === $role) {
        return;
    }
    set_message('Please log in to open that page.', 'error');
    redirect('login&role=' . $role);
}

/** Where each role goes after logging in. */
function home_for_role($role)
{
    if ($role === 'Patient') { return 'patient/dashboard'; }
    if ($role === 'Doctor')  { return 'doctor/dashboard'; }
    return 'staff/queue';
}

/* ---------- small formatting helpers ---------- */

function time_label($time)
{
    return date('h:i A', strtotime($time));
}

function date_label($date)
{
    return date('d M Y', strtotime($date));
}

function money($amount)
{
    return 'Rs. ' . number_format((float)$amount, 2);
}

/** Two digit queue number: 8 -> 08 */
function queue_label($no)
{
    return $no ? str_pad($no, 2, '0', STR_PAD_LEFT) : '-';
}
