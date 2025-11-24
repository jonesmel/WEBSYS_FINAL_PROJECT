<?php
class BarangayHelper {

    public static function getAll(): array {
        $path = '/mnt/data/barangays.txt';
        $list = [];

        if (file_exists($path) && is_readable($path)) {
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $l) {
                $clean = trim($l);
                if ($clean !== '') $list[] = $clean;
            }
        }

        // Fallback small list if file missing
        if (empty($list)) {
            $list = [
                'Ambiong','Loakan Proper','Pacdal','BGH Compound',
                'Bakakeng Central','Camp 7'
            ];
        }

        return $list;
    }
}
