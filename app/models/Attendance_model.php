<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Attendance_model extends CI_Model
{
    const TABLE_ATTENDANCE = 'attendance';
    const TABLE_USERS = 'users';
    const TABLE_USER_FACES = 'user_faces';
    const TABLE_GROUPS = 'groups';
    const TABLE_PM_LOG_PARAM = 'sma_preventive_maintenance_log_param';
    const TABLE_PM_LOG = 'sma_preventive_maintenance_log';
    const TABLE_WAREHOUSES = 'warehouses';
    const TABLE_SERVICE_REPORT_OTP = 'sma_service_site_report_otp';
    const DEFAULT_SITE_RADIUS_METERS = 100;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('auth_model');
        $this->load->helper('attendance');
    }

    public function get_attendance($user_id = null)
    {
        $this->db->select('a.id, a.user_id, a.check_in, a.check_out, a.latitude, a.longitude, a.landmark, a.derived_location, a.created_at, u.first_name, u.last_name, u.phone');
        $this->db->from(self::TABLE_ATTENDANCE . ' a');
        $this->db->join(self::TABLE_USERS . ' u', 'u.id = a.user_id', 'left');
        if (!empty($user_id)) {
            $this->db->where('a.user_id', (int) $user_id);
        }
        $this->db->order_by('a.id', 'desc');
        return $this->db->get()->result();
    }

    public function get_attendance_filtered($scope_user_id = null, $filter_user_id = null, $attendance_date = null)
    {
        $this->db->select('a.id, a.user_id, a.check_in, a.check_out, a.latitude, a.longitude, a.landmark, a.derived_location, a.created_at, u.first_name, u.last_name, u.phone');
        $this->db->from(self::TABLE_ATTENDANCE . ' a');
        $this->db->join(self::TABLE_USERS . ' u', 'u.id = a.user_id', 'left');

        if (!empty($scope_user_id)) {
            $this->db->where('a.user_id', (int) $scope_user_id);
        } elseif (!empty($filter_user_id)) {
            $this->db->where('a.user_id', (int) $filter_user_id);
        }

        if (!empty($attendance_date)) {
            $this->db->where('DATE(a.check_in)', $attendance_date);
        }

        $this->db->order_by('a.id', 'desc');
        return $this->db->get()->result();
    }

    public function get_attendance_filtered_range($scope_user_id = null, $filter_user_id = null, $start_date = null, $end_date = null)
    {
        $this->db->select('a.id, a.user_id, a.check_in, a.check_out, a.latitude, a.longitude, a.landmark, a.derived_location, a.created_at, u.first_name, u.last_name, u.phone');
        $this->db->from(self::TABLE_ATTENDANCE . ' a');
        $this->db->join(self::TABLE_USERS . ' u', 'u.id = a.user_id', 'left');

        if (!empty($scope_user_id)) {
            $this->db->where('a.user_id', (int) $scope_user_id);
        } elseif (!empty($filter_user_id)) {
            $this->db->where('a.user_id', (int) $filter_user_id);
        }

        if (!empty($start_date)) {
            $this->db->where('DATE(a.check_in) >=', $start_date);
        }
        if (!empty($end_date)) {
            $this->db->where('DATE(a.check_in) <=', $end_date);
        }

        $this->db->order_by('a.id', 'desc');
        return $this->db->get()->result();
    }

    public function get_attendance_filter_users()
    {
        $this->db->select('id, first_name, last_name, phone');
        $this->db->from(self::TABLE_USERS);
        $this->db->where('company_id', null);
        $this->db->order_by('first_name', 'asc');
        $this->db->order_by('last_name', 'asc');
        return $this->db->get()->result();
    }

    public function get_attendance_by_id($id)
    {
        $this->db->select('a.id, a.user_id, a.check_in, a.check_out, a.latitude, a.longitude, a.landmark, a.derived_location, a.created_at, u.first_name, u.last_name, u.phone');
        $this->db->from(self::TABLE_ATTENDANCE . ' a');
        $this->db->join(self::TABLE_USERS . ' u', 'u.id = a.user_id', 'left');
        $this->db->where('a.id', (int) $id);
        return $this->db->get()->row();
    }

    public function get_attendance_by_ids(array $ids)
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        $ids = array_values(array_filter($ids, function ($v) {
            return $v > 0;
        }));
        if (empty($ids)) {
            return array();
        }

        $this->db->select('a.id, a.user_id, a.check_in, a.check_out, a.latitude, a.longitude, a.landmark, a.derived_location, a.created_at, u.first_name, u.last_name, u.phone');
        $this->db->from(self::TABLE_ATTENDANCE . ' a');
        $this->db->join(self::TABLE_USERS . ' u', 'u.id = a.user_id', 'left');
        $this->db->where_in('a.id', $ids);
        $this->db->order_by('a.id', 'desc');
        return $this->db->get()->result();
    }

    public function insert_attendance($data)
    {
        return $this->db->insert(self::TABLE_ATTENDANCE, $data);
    }

    public function update_attendance_checkout($id, $data)
    {
        $this->db->where('id', (int) $id);
        return $this->db->update(self::TABLE_ATTENDANCE, $data);
    }

    public function update_attendance($id, $data)
    {
        $this->db->where('id', (int) $id);
        return $this->db->update(self::TABLE_ATTENDANCE, $data);
    }

    public function delete_attendance($id)
    {
        return $this->db->delete(self::TABLE_ATTENDANCE, array('id' => (int) $id));
    }

    public function check_today_attendance($user_id)
    {
        $todayStart = date('Y-m-d 00:00:00');
        $tomorrowStart = date('Y-m-d 00:00:00', strtotime('+1 day'));
        $this->db->from(self::TABLE_ATTENDANCE);
        $this->db->where('user_id', (int) $user_id);
        $this->db->where('check_in >=', $todayStart);
        $this->db->where('check_in <', $tomorrowStart);
        $this->db->order_by('id', 'desc');
        return $this->db->get()->row();
    }

    /**
     * Open attendance interval(s) today for a user (check_out IS NULL).
     */
    public function get_today_open_attendances($user_id)
    {
        $todayStart = date('Y-m-d 00:00:00');
        $tomorrowStart = date('Y-m-d 00:00:00', strtotime('+1 day'));

        $this->db->from(self::TABLE_ATTENDANCE);
        $this->db->where('user_id', (int) $user_id);
        $this->db->where('check_in >=', $todayStart);
        $this->db->where('check_in <', $tomorrowStart);
        $this->db->where('check_out IS NULL', null, false);
        $this->db->order_by('id', 'desc');

        return $this->db->get()->result();
    }

    /**
     * Single latest open attendance today for this derived location.
     */
    public function check_today_open_attendance_by_location($user_id, $derived_location)
    {
        $todayStart = date('Y-m-d 00:00:00');
        $tomorrowStart = date('Y-m-d 00:00:00', strtotime('+1 day'));

        $this->db->from(self::TABLE_ATTENDANCE);
        $this->db->where('user_id', (int) $user_id);
        $this->db->where('check_in >=', $todayStart);
        $this->db->where('check_in <', $tomorrowStart);
        $this->db->where('check_out IS NULL', null, false);

        $derived_location = (string) $derived_location;
        if (trim($derived_location) === '') {
            // Match both '' and NULL when stored derived_location is empty.
            $this->db->group_start();
            $this->db->where('derived_location IS NULL', null, false);
            $this->db->or_where('derived_location', '');
            $this->db->group_end();
        } else {
            $this->db->where('derived_location', $derived_location);
        }

        $this->db->order_by('id', 'desc');
        return $this->db->get()->row();
    }

    /**
     * Latest attendance row today for user + location (open or closed).
     */
    public function get_today_latest_attendance_by_location($user_id, $derived_location)
    {
        $todayStart = date('Y-m-d 00:00:00');
        $tomorrowStart = date('Y-m-d 00:00:00', strtotime('+1 day'));

        $this->db->from(self::TABLE_ATTENDANCE);
        $this->db->where('user_id', (int) $user_id);
        $this->db->where('check_in >=', $todayStart);
        $this->db->where('check_in <', $tomorrowStart);

        $derived_location = (string) $derived_location;
        if (trim($derived_location) === '') {
            $this->db->group_start();
            $this->db->where('derived_location IS NULL', null, false);
            $this->db->or_where('derived_location', '');
            $this->db->group_end();
        } else {
            $this->db->where('derived_location', $derived_location);
        }

        $this->db->order_by('check_in', 'desc');
        $this->db->order_by('id', 'desc');
        return $this->db->get()->row();
    }

    /**
     * Latest checkout row (all-time) for user + location.
     */
    public function get_latest_checkout_by_location($user_id, $derived_location)
    {
        $this->db->from(self::TABLE_ATTENDANCE);
        $this->db->where('user_id', (int) $user_id);
        $this->db->where('check_out IS NOT NULL', null, false);

        $derived_location = (string) $derived_location;
        if (trim($derived_location) === '') {
            $this->db->group_start();
            $this->db->where('derived_location IS NULL', null, false);
            $this->db->or_where('derived_location', '');
            $this->db->group_end();
        } else {
            $this->db->where('derived_location', $derived_location);
        }

        $this->db->order_by('check_out', 'desc');
        $this->db->order_by('id', 'desc');
        return $this->db->get()->row();
    }

    // (duplicate methods removed)

    public function insert_user($data)
    {
        if (empty($data['password'])) {
            return false;
        }

        $password = $data['password'];
        unset($data['password']);

        $data['password'] = $this->auth_model->hash_password($password, false);
        $data['ip_address'] = $this->input->ip_address();
        $data['created_on'] = time();
        $data['last_login'] = time();

        if (!isset($data['active'])) {
            $data['active'] = 1;
        }

        $insertData = $this->auth_model->_filter_data('users', $data);
        if ($this->db->insert(self::TABLE_USERS, $insertData)) {
            return $this->db->insert_id();
        }

        return false;
    }

    public function update_user($id, $data)
    {
        if (isset($data['password']) && $data['password'] !== '') {
            $data['password'] = $this->auth_model->hash_password($data['password'], false);
        } else {
            unset($data['password']);
        }

        $updateData = $this->auth_model->_filter_data('users', $data);
        $this->db->where('id', (int) $id);
        return $this->db->update(self::TABLE_USERS, $updateData);
    }

    public function insert_face_descriptor($data)
    {
        return $this->db->insert(self::TABLE_USER_FACES, $data);
    }

    public function update_face_descriptor($user_id, $data)
    {
        $exists = $this->db->get_where(self::TABLE_USER_FACES, array('user_id' => (int) $user_id), 1)->row();
        if ($exists) {
            $this->db->where('user_id', (int) $user_id);
            return $this->db->update(self::TABLE_USER_FACES, $data);
        }

        $data['user_id'] = (int) $user_id;
        return $this->db->insert(self::TABLE_USER_FACES, $data);
    }

    public function get_user_faces($user_id = null)
    {
        $this->db->select('uf.id, uf.user_id, uf.descriptor, uf.image_path, u.first_name, u.last_name, u.active');
        $this->db->from(self::TABLE_USER_FACES . ' uf');
        $this->db->join(self::TABLE_USERS . ' u', 'u.id = uf.user_id', 'inner');
        $this->db->where('u.active', 1);
        if (!empty($user_id)) {
            $this->db->where('uf.user_id', (int) $user_id);
        }
        return $this->db->get()->result();
    }

    public function match_face_descriptor($descriptor, $threshold = 0.55, $user_id = null, $min_gap = 0.03)
    {
        $faces = $this->get_user_faces($user_id);
        if (empty($faces)) {
            return false;
        }

        $best = null;
        $secondBest = null;
        foreach ($faces as $face) {
            $saved = json_decode($face->descriptor, true);
            if (!is_array($saved)) {
                continue;
            }

            $distance = attendance_euclidean_distance($descriptor, $saved);
            if ($distance === null) {
                continue;
            }

            if ($best === null || $distance < $best['distance']) {
                $secondBest = $best;
                $best = array(
                    'user_id' => (int) $face->user_id,
                    'distance' => $distance
                );
            } elseif ($secondBest === null || $distance < $secondBest['distance']) {
                $secondBest = array(
                    'user_id' => (int) $face->user_id,
                    'distance' => $distance
                );
            }
        }

        if ($best !== null && $best['distance'] <= (float) $threshold) {
            if ($secondBest !== null) {
                $distanceGap = $secondBest['distance'] - $best['distance'];
                if ($distanceGap < (float) $min_gap) {
                    return false;
                }
            }
            return $best;
        }

        return false;
    }

    public function get_user_by_id($id)
    {
        return $this->db->get_where(self::TABLE_USERS, array('id' => (int) $id), 1)->row();
    }

    public function get_groups()
    {
        return $this->db->get(self::TABLE_GROUPS)->result();
    }

    /**
     * Effective geofence radius for a warehouse row (meters).
     *
     * @param object $site Row from get_site_locations()
     * @return int
     */
    public static function warehouse_geofence_radius_meters($site)
    {
        if (isset($site->location_radius_m) && (int) $site->location_radius_m > 0) {
            return (int) $site->location_radius_m;
        }
        return (int) self::DEFAULT_SITE_RADIUS_METERS;
    }

    public function get_site_locations()
    {
        if (!$this->db->field_exists('latitude', self::TABLE_WAREHOUSES) || !$this->db->field_exists('longitude', self::TABLE_WAREHOUSES)) {
            return array();
        }

        $select = 'id, name, latitude, longitude';
        if ($this->db->field_exists('location_radius_m', self::TABLE_WAREHOUSES)) {
            $select .= ', location_radius_m';
        }
        $this->db->select($select);
        $this->db->from(self::TABLE_WAREHOUSES);
        $this->db->where('latitude IS NOT NULL', null, false);
        $this->db->where('longitude IS NOT NULL', null, false);
        if ($this->db->field_exists('is_deleted', self::TABLE_WAREHOUSES)) {
            $this->db->where('is_deleted', 0);
        }
        if ($this->db->field_exists('is_disabled', self::TABLE_WAREHOUSES)) {
            $this->db->where('is_disabled', 0);
        }
        if ($this->db->field_exists('is_active', self::TABLE_WAREHOUSES)) {
            $this->db->where('is_active', 1);
        }
        return $this->db->get()->result();
    }

    /**
     * Find nearest site location within $maxMeters.
     *
     * @return array|null ['id'=>int,'name'=>string,'distance'=>float]
     */
    public function find_nearest_site_location($lat, $lng, $maxMeters = null)
    {
        unset($maxMeters);
        $sites = $this->get_site_locations();
        if (empty($sites)) {
            return null;
        }

        $best = null;
        foreach ($sites as $site) {
            if (!isset($site->latitude) || !isset($site->longitude)) {
                continue;
            }
            $distance = attendance_haversine_distance_meters($lat, $lng, $site->latitude, $site->longitude);
            $radiusM = self::warehouse_geofence_radius_meters($site);
            if ($distance > $radiusM) {
                continue;
            }
            if ($best === null || $distance < $best['distance']) {
                $best = array(
                    'id' => isset($site->id) ? (int) $site->id : 0,
                    'name' => (string) $site->name,
                    'distance' => (float) $distance,
                );
            }
        }

        return $best;
    }

    /**
     * Ensure there's a site location for these coordinates.
     * - If a site exists within DEFAULT_SITE_RADIUS_METERS, returns its name.
     * - Otherwise attempts to create a new site location with given $name and lat/lng.
     * - If insert fails, still returns $name (so attendance can store derived_location).
     */
    public function ensure_site_location($name, $lat, $lng)
    {
        $name = trim((string) $name);
        if ($name === '' || !is_numeric($lat) || !is_numeric($lng)) {
            return $name;
        }

        $nearest = $this->find_nearest_site_location((float) $lat, (float) $lng);
        if ($nearest) {
            return (string) $nearest['name'];
        }

        $createdName = $this->create_site_location($name, (float) $lat, (float) $lng);
        return $createdName !== '' ? $createdName : $name;
    }

    /**
     * Best-effort insert into warehouses as "Site Location".
     * Uses only columns that exist to avoid schema mismatches.
     *
     * @return string Inserted name or '' when not inserted
     */
    protected function create_site_location($name, $lat, $lng)
    {
        if (!$this->db->table_exists(self::TABLE_WAREHOUSES)) {
            return '';
        }
        if (!$this->db->field_exists('latitude', self::TABLE_WAREHOUSES) || !$this->db->field_exists('longitude', self::TABLE_WAREHOUSES)) {
            return '';
        }

        $data = array();

        // Core identity.
        if ($this->db->field_exists('name', self::TABLE_WAREHOUSES)) {
            $data['name'] = $name;
        }
        if ($this->db->field_exists('code', self::TABLE_WAREHOUSES)) {
            $data['code'] = $this->generate_site_code();
        }

        // Optional address-ish fields (safe defaults).
        if ($this->db->field_exists('country', self::TABLE_WAREHOUSES)) {
            $data['country'] = 'NA';
        }
        if ($this->db->field_exists('city', self::TABLE_WAREHOUSES)) {
            $data['city'] = 'NA';
        }
        if ($this->db->field_exists('state', self::TABLE_WAREHOUSES)) {
            $data['state'] = null;
        }
        if ($this->db->field_exists('state_code', self::TABLE_WAREHOUSES)) {
            $data['state_code'] = '';
        }
        if ($this->db->field_exists('postal_code', self::TABLE_WAREHOUSES)) {
            $data['postal_code'] = '000000';
        }
        if ($this->db->field_exists('address', self::TABLE_WAREHOUSES)) {
            $data['address'] = $name;
        }

        // Geo fields.
        $data['latitude'] = $lat;
        $data['longitude'] = $lng;
        if ($this->db->field_exists('location_radius_m', self::TABLE_WAREHOUSES)) {
            $data['location_radius_m'] = (int) self::DEFAULT_SITE_RADIUS_METERS;
        }

        // Status fields if present.
        if ($this->db->field_exists('is_active', self::TABLE_WAREHOUSES)) {
            $data['is_active'] = 1;
        }
        if ($this->db->field_exists('is_deleted', self::TABLE_WAREHOUSES)) {
            $data['is_deleted'] = 0;
        }
        if ($this->db->field_exists('is_disabled', self::TABLE_WAREHOUSES)) {
            $data['is_disabled'] = 0;
        }

        $ok = $this->db->insert(self::TABLE_WAREHOUSES, $data);
        if (!$ok) {
            log_message('error', 'Attendance: failed to auto-create site location in warehouses for "' . $name . '"');
            return '';
        }
        return $name;
    }

    protected function generate_site_code()
    {
        // Keep it short-ish and unique.
        return 'AUTO-' . date('YmdHis') . '-' . mt_rand(100, 999);
    }
     
}

