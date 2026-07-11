<?php defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('attendance_euclidean_distance')) {
    function attendance_euclidean_distance(array $a, array $b)
    {
        $countA = count($a);
        $countB = count($b);
        if ($countA === 0 || $countA !== $countB) {
            return null;
        }

        $sum = 0.0;
        for ($i = 0; $i < $countA; $i++) {
            $diff = ((float) $a[$i]) - ((float) $b[$i]);
            $sum += ($diff * $diff);
        }

        return sqrt($sum);
    }
}

if (!function_exists('attendance_haversine_distance_meters')) {
    function attendance_haversine_distance_meters($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;

        $latFrom = deg2rad((float) $lat1);
        $lonFrom = deg2rad((float) $lon1);
        $latTo = deg2rad((float) $lat2);
        $lonTo = deg2rad((float) $lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }
}

if (!function_exists('attendance_sma_view_all_records')) {
    /**
     * Same as auth profile dropdown: view_right 1 / '1' = All records; 0 / '0' / null = Own records only.
     */
    function attendance_sma_view_all_records($Owner, $Admin, $view_right)
    {
        if (!empty($Owner) || !empty($Admin)) {
            return true;
        }
        return ($view_right === 1 || $view_right === '1');
    }
}

if (!function_exists('attendance_sma_edit_any_record')) {
    /**
     * edit_right 1 / '1' = may edit/delete others' rows (if also allowed to view them).
     */
    function attendance_sma_edit_any_record($Owner, $Admin, $edit_right)
    {
        if (!empty($Owner) || !empty($Admin)) {
            return true;
        }
        return ($edit_right === 1 || $edit_right === '1');
    }
}

if (!function_exists('attendance_display_name_for_row')) {
    /**
     * For the logged-in user's own attendance row, show their current profile name from session user.
     * Helps when the user has view_right and the list mixes own and others' rows.
     *
     * @param object $row Row with user_id, first_name, last_name
     * @param int    $session_uid
     * @param string $logged_in_name Trimmed "First Last" from site->getUser()
     */
    function attendance_display_name_for_row($row, $session_uid, $logged_in_name = '')
    {
        $session_uid = (int) $session_uid;
        $row_uid = isset($row->user_id) ? (int) $row->user_id : 0;
        if ($row_uid === $session_uid && $logged_in_name !== '') {
            return $logged_in_name;
        }
        $fn = isset($row->first_name) ? $row->first_name : '';
        $ln = isset($row->last_name) ? $row->last_name : '';
        return trim($fn . ' ' . $ln);
    }
}

