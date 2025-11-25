<?php
class Flash {
    public static function set($type, $message) {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }

    public static function display() {
        if (!empty($_SESSION['flash'])) {
            $type = $_SESSION['flash']['type'];
            $msg  = $_SESSION['flash']['message'];

            echo "
            <div class='container mt-3'>
                <div class='alert alert-$type alert-dismissible fade show' role='alert'>
                    $msg
                    <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                </div>
            </div>";

            unset($_SESSION['flash']);
        }
    }
}
