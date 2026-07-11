<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Service_requests_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getCustomers()
    {
        $this->db->select("c.id, c.company, c.name, c.phone, c.email, NULL as address_id, '' as address_name, '' as line1, '' as line2, '' as city, '' as state, '' as postal_code, '' as country", false);
        $this->db->from('sma_companies c');
        $this->db->where('c.group_name', 'customer');
        $this->db->order_by('c.name', 'ASC');
        return $this->db->get()->result();
    }

    public function getServiceTypes()
    {
        $this->db->select('id, name');
        $this->db->from('sma_service_types');
        $this->db->order_by('name', 'ASC');
        return $this->db->get()->result();
    }

    public function getEquipmentsForServiceSiteReport()
    {
        if (!$this->db->table_exists('sma_equipments')) {
            return array();
        }

        $this->db->select('id, name, eqpt_no, model_no, serial_no');
        $this->db->from('sma_equipments');
        $this->db->where('eqpt_no IS NOT NULL');
        $this->db->where('eqpt_no !=', '');
        
        if ($this->db->field_exists('is_active', 'sma_equipments')) {
            $this->db->where('is_active', 1);
        }
        $this->db->order_by('eqpt_no', 'ASC');
        $rows = $this->db->get()->result_array();

        $equipments = array();
        foreach ($rows as $row) {
            $tagNo = trim((string)$row['eqpt_no']);
            if ($tagNo === '') {
                continue;
            }
            $equipments[] = (object) array(
                'id' => isset($row['id']) ? $row['id'] : null,
                'eqpt_no' => $tagNo,
                'model_no' => isset($row['model_no']) ? $row['model_no'] : '',
                'serial_no' => isset($row['serial_no']) ? $row['serial_no'] : '',
                'asset_no' => $tagNo,
            );
        }
        return $equipments;
    }

    public function getPmLogParameters()
    {
        $this->db->from('sma_preventive_maintenance_log_param');
        $this->db->where('isactiveflag', 1);
        $this->db->order_by('id', 'ASC');
        $rows = $this->db->get()->result_array();
        if (empty($rows)) {
            return array();
        }

        $sections = array();
        foreach ($rows as $row) {
            $section = '';
            foreach (array('section', 'section_name', 'group_name', 'category', 'parameter_group') as $field) {
                if (isset($row[$field]) && trim((string) $row[$field]) !== '') {
                    $section = trim((string) $row[$field]);
                    break;
                }
            }
            if ($section === '') {
                $section = 'General Parameters';
            }

            $parameter = '';
            foreach (array('parameter', 'parameter_name', 'name', 'param_name', 'title') as $field) {
                if (isset($row[$field]) && trim((string) $row[$field]) !== '') {
                    $parameter = trim((string) $row[$field]);
                    break;
                }
            }
            if ($parameter === '') {
                continue;
            }
            if (!isset($sections[$section])) {
                $sections[$section] = array();
            }
            $sections[$section][] = $parameter;
        }
        return $sections;
    }

    public function save_service_site_report($data)
    {
        $payload = is_array($data) ? $data : array();
        $table = $this->ensure_service_site_report_log_table();
        if ($table === '') {
            return false;
        }

        $serviceDate = isset($payload['service_date']) ? (string) $payload['service_date'] : date('Y-m-d');
        $equipmentTag = isset($payload['eqpt_tag_no']) ? trim((string) $payload['eqpt_tag_no']) : '';
        $customerId = isset($payload['customer_id']) ? (int) $payload['customer_id'] : 0;
        $locationId = isset($payload['customer_location_id']) ? (int) $payload['customer_location_id'] : 0;
        $equipmentIdFromPayload = isset($payload['equipment_id']) ? (int) $payload['equipment_id'] : 0;
        if ($equipmentIdFromPayload > 0) {
            $equipmentId = $equipmentIdFromPayload;
            $equipmentRecord = $this->db->where('id', $equipmentId)->get('sma_equipmentdetails')->row_array();
        } else {
            $equipmentRecord = $this->getEquipmentDetailRecordByTag($equipmentTag, $customerId, $locationId);
            $equipmentId = isset($equipmentRecord['id']) ? (int) $equipmentRecord['id'] : 0;
        }
        if ($locationId <= 0 && !empty($equipmentRecord['location_id'])) {
            $locationId = (int) $equipmentRecord['location_id'];
            $payload['customer_location_id'] = $locationId;
        }

        // Synchronize log_reference_no with the service_report_no shown on screen
        $logRef = isset($payload['service_report_no']) ? trim((string) $payload['service_report_no']) : '';
        if ($logRef === '') {
            $logRef = $this->buildLogReference($customerId, $locationId, $serviceDate);
        }

        // Smart Merge: delete any existing UNSIGNED report for this SPECIFIC number
        if ($logRef !== '') {
            $this->db->where('log_reference_no', $logRef);
            $this->db->group_start();
            $this->db->where('customer_signature', null);
            $this->db->or_where('customer_signature', '');
            $this->db->group_end();
            $this->db->delete($table);
        }



        $insert = array(
            'date' => $serviceDate,
            'log_reference_no' => $logRef !== '' ? $logRef : null,
            'equipment_id' => $equipmentId > 0 ? $equipmentId : null,
            'parameter' => 'service_site_report',
            'value' => json_encode($payload),
        );

        $this->db->trans_start();
        $this->db->insert($table, $insert);
        $this->savePmGridRows($payload, $table, $serviceDate, $logRef, $equipmentRecord);
        $this->upsertAmcForPmEntry($payload, $serviceDate, $equipmentRecord);
        $this->db->trans_complete();

        return $this->db->trans_status() ? $logRef : false;
    }

    public function save_pm_log_only($data)
    {
        $payload = is_array($data) ? $data : array();
        $table = $this->ensure_service_site_report_log_table();
        if ($table === '') {
            return false;
        }

        $serviceDate = isset($payload['service_date']) ? (string) $payload['service_date'] : date('Y-m-d');
        $equipmentTag = isset($payload['eqpt_tag_no']) ? trim((string) $payload['eqpt_tag_no']) : '';
        $customerId = isset($payload['customer_id']) ? (int) $payload['customer_id'] : 0;
        $locationId = isset($payload['customer_location_id']) ? (int) $payload['customer_location_id'] : 0;
        $equipmentIdFromPayload = isset($payload['equipment_id']) ? (int) $payload['equipment_id'] : 0;
        if ($equipmentIdFromPayload > 0) {
            $equipmentId = $equipmentIdFromPayload;
            $equipmentRecord = $this->db->where('id', $equipmentId)->get('sma_equipmentdetails')->row_array();
        } else {
            $equipmentRecord = $this->getEquipmentDetailRecordByTag($equipmentTag, $customerId, $locationId);
            $equipmentId = isset($equipmentRecord['id']) ? (int) $equipmentRecord['id'] : 0;
        }

        // Synchronize log_reference_no with the service_report_no shown on screen
        $logRef = isset($payload['service_report_no']) ? trim((string) $payload['service_report_no']) : '';
        if ($logRef === '') {
            $logRef = $this->buildLogReference($customerId, $locationId, $serviceDate);
        }

        // Smart Merge: delete any existing UNSIGNED report for this SPECIFIC number
        if ($logRef !== '') {
            $this->db->where('log_reference_no', $logRef);
            $this->db->group_start();
            $this->db->where('customer_signature', null);
            $this->db->or_where('customer_signature', '');
            $this->db->group_end();
            $this->db->delete($table);
        }


        $insert = array(
            'date' => $serviceDate,
            'log_reference_no' => $logRef !== '' ? $logRef : null,
            'equipment_id' => $equipmentId > 0 ? $equipmentId : null,
            'parameter' => 'service_site_report',
            'value' => json_encode($payload),
        );

        $this->db->trans_start();
        $this->db->insert($table, $insert);
        $this->savePmGridRows($payload, $table, $serviceDate, $logRef, $equipmentRecord);
        $this->upsertAmcForPmEntry($payload, $serviceDate, $equipmentRecord);
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    public function update_service_site_report_dispatch_status($logId, array $statusData)
    {
        $logId = (int) $logId;
        if ($logId <= 0) {
            return false;
        }
        $table = $this->resolve_service_site_report_log_table();
        if ($table === '') {
            return false;
        }
        $row = $this->db->select('value')->where('id', $logId)->get($table)->row_array();
        if (empty($row) || !isset($row['value'])) {
            return false;
        }
        $payload = json_decode((string) $row['value'], true);
        if (!is_array($payload)) {
            $payload = array();
        }
        $payload['dispatch_status'] = $statusData;
        $this->db->where('id', $logId);
        return $this->db->update($table, array('value' => json_encode($payload)));
    }

    public function get_latest_service_site_report_payload_by_customer($customerId, $scanLimit = 300)
    {
        $customerId = trim((string) $customerId);
        if ($customerId === '') {
            return null;
        }
        $table = $this->resolve_service_site_report_log_table();
        if ($table === '') {
            return null;
        }

        $this->db->select('value');
        $this->db->from($table);
        $this->db->where('parameter', 'service_site_report');
        if ($this->db->field_exists('id', $table)) {
            $this->db->order_by('id', 'DESC');
        } elseif ($this->db->field_exists('date', $table)) {
            $this->db->order_by('date', 'DESC');
        }
        $this->db->limit((int) $scanLimit);

        $rows = $this->db->get()->result_array();
        if (empty($rows)) {
            return null;
        }
        foreach ($rows as $row) {
            $raw = isset($row['value']) ? $row['value'] : '';
            if ($raw === '' || $raw === null) {
                continue;
            }
            $payload = json_decode($raw, true);
            if (!is_array($payload)) {
                continue;
            }
            $payloadCustomerId = isset($payload['customer_id']) ? trim((string) $payload['customer_id']) : '';
            if ($payloadCustomerId !== '' && $payloadCustomerId === $customerId) {
                return $payload;
            }
        }
        return null;
    }

    public function countCustomerServiceReports($customerId, $locationId = null)
    {
        $customerId = (int) $customerId;
        if ($customerId <= 0) return 0;

        $table = $this->resolve_service_site_report_log_table();
        if ($table === '') return 0;

        // Get location code for the prefix (Synced with buildLogReference logic)
        $locCode = 'LOC';
        if ($locationId !== null && (int)$locationId > 0 && $this->db->table_exists('sma_addresses')) {
            // Check for column existence to prevent SQL errors on different schemas
            if ($this->db->field_exists('location_id', 'sma_addresses') && $this->db->table_exists('sma_warehouses')) {
                $this->db->select('w.code');
                $this->db->from('sma_addresses a');
                $this->db->join('sma_warehouses w', 'w.id = a.location_id', 'left');
                $this->db->where('a.id', (int)$locationId);
                $row = $this->db->get()->row();
                if ($row && !empty($row->code)) {
                    $locCode = trim((string) $row->code);
                }
            }
        }

        $prefix = $customerId . '/' . $locCode . '/';
        
        $this->db->select('COUNT(DISTINCT log_reference_no) as total', false);
        $this->db->from($table);
        $this->db->like('log_reference_no', $prefix, 'after');
        
        $query = $this->db->get();
        $row = $query->row_array();
        return isset($row['total']) ? (int) $row['total'] : 0;
    }

    public function getCustomerLocationsByCompanyId($companyId)
    {
        $companyId = (int) $companyId;
        if ($companyId <= 0 || !$this->db->table_exists('sma_addresses')) {
            return array();
        }
        $locationIds = array();
        $floorMap = array();
        $this->db->select('DISTINCT d.location_id', false);
        $this->db->from('sma_equipmentdetails d');
        $this->db->where('d.customer_id', $companyId);
        $this->db->where('d.location_id IS NOT NULL', null, false);
        $this->db->where('d.is_active', 1);
        $locationRows = $this->db->get()->result_array();

        foreach ($locationRows as $row) {
            $locId = isset($row['location_id']) ? (int) $row['location_id'] : 0;
            if ($locId > 0) {
                $locationIds[] = $locId;
            }
        }
        $locationIds = array_values(array_unique($locationIds));

        if (!empty($locationIds)) {
            $this->db->select('d.location_id, GROUP_CONCAT(DISTINCT NULLIF(TRIM(d.floor_details), "") SEPARATOR ", ") AS floor_details', false);
            $this->db->from('sma_equipmentdetails d');
            $this->db->where('d.customer_id', $companyId);
            $this->db->where_in('d.location_id', $locationIds);
            $this->db->where('d.is_active', 1);
            $this->db->group_by('d.location_id');
            $floorRows = $this->db->get()->result_array();
            foreach ($floorRows as $row) {
                $locId = isset($row['location_id']) ? (int) $row['location_id'] : 0;
                if ($locId > 0) {
                    $floorMap[$locId] = isset($row['floor_details']) ? trim((string) $row['floor_details']) : '';
                }
            }
        }

        $hasLocationId = $this->db->field_exists('location_id', 'sma_addresses');
        $hasWarehouses = $this->db->table_exists('sma_warehouses');
        
        $select = 'a.id, a.address_name, a.line1, a.line2, a.city, a.state, a.postal_code, a.country, a.company_name, a.location_name';
        if ($hasLocationId) {
            $select .= ', a.location_id';
        }
        $this->db->select($select);

        if ($hasLocationId && $hasWarehouses) {
            $this->db->select('w.name AS warehouse_name, w.code');
        } else {
            $this->db->select('NULL AS warehouse_name, NULL AS code', false);
        }

        $this->db->from('sma_addresses a');
        if ($hasLocationId && $hasWarehouses) {
            $this->db->join('sma_warehouses w', 'w.id = a.location_id', 'left');
        }
        $this->db->where('a.company_id', $companyId);
        if (!empty($locationIds)) {
            $this->db->group_start();
            if ($hasLocationId) {
                $this->db->where_in('a.location_id', $locationIds);
            }
            $this->db->or_where_in('a.id', $locationIds);
            $this->db->group_end();
        }
        if ($this->db->field_exists('is_deleted', 'sma_addresses')) {
            $this->db->where('a.is_deleted', 0);
        }
        $this->db->order_by('a.id', 'ASC');
        $rows = $this->db->get()->result_array();
        if (empty($rows)) {
            return array();
        }

        foreach ($rows as &$row) {
            $locId = isset($row['location_id']) ? (int) $row['location_id'] : 0;
            $row['floor_details'] = ($locId > 0 && isset($floorMap[$locId])) ? $floorMap[$locId] : '';
        }
        unset($row);
        return $rows;
    }

    public function getEquipmentDetailsForServiceSiteReport($equipmentName, $companyId = 0, $locationId = 0)
    {
        $equipmentName = trim((string) $equipmentName);
        $companyId = (int) $companyId;
        $locationId = (int) $locationId;
        $this->db->select('d.id, d.eqpt_no, d.model_no, d.serial_no', false);
        $this->db->from('sma_equipmentdetails d');
        if ($equipmentName !== '') {
            $this->db->where('d.eqpt_no', $equipmentName);
        }
        if ($companyId > 0) {
            $this->db->where('d.customer_id', $companyId);
        }
        if ($locationId > 0) {
            $this->db->where('d.location_id', $locationId);
        }
        $this->db->where('d.is_active', 1);
        $this->db->order_by('d.id', 'DESC');
        return $this->db->get()->result_array();
    }

    public function getEquipmentTagsForServiceSiteReport($companyId = 0, $locationId = 0)
    {
        $companyId = (int) $companyId;
        $locationId = (int) $locationId;
        if ($companyId <= 0 || $locationId <= 0) {
            return array();
        }

        $this->db->select('d.eqpt_no', false);
        $this->db->from('sma_equipmentdetails d');
        $this->db->where('d.customer_id', $companyId);
        $this->db->where('d.location_id', $locationId);
        $this->db->where('d.eqpt_no IS NOT NULL', null, false);
        $this->db->where('TRIM(d.eqpt_no) <> ""', null, false);
        if ($this->db->field_exists('is_active', 'sma_equipmentdetails')) {
            $this->db->where('d.is_active', 1);
        }
        $this->db->group_by('d.eqpt_no');
        $this->db->order_by('d.eqpt_no', 'ASC');
        $rows = $this->db->get()->result_array();
        if (empty($rows)) {
            return array();
        }

        $tags = array();
        foreach ($rows as $row) {
            $tag = isset($row['eqpt_no']) ? trim((string) $row['eqpt_no']) : '';
            if ($tag !== '') {
                $tags[] = array('eqpt_no' => $tag);
            }
        }
        return $tags;
    }

    public function get_customer_company_by_id($customerId)
    {
        $customerId = (int) $customerId;
        if ($customerId <= 0) {
            return null;
        }
        $this->db->select('id, name, phone, email');
        $this->db->from('sma_companies');
        $this->db->where('id', $customerId);
        $this->db->where('group_name', 'customer');
        return $this->db->get()->row();
    }

    public function create_service_report_otp_challenge($contextRef, $channel, $destination, $createdBy = null, $ttlSeconds = 300, $maxAttempts = 5, $throttleSeconds = 45)
    {
        if (!$this->ensure_service_report_otp_table()) {
            return array('status' => 'error', 'message' => 'OTP table is not available');
        }
        $contextRef = trim((string) $contextRef);
        $channel = strtolower(trim((string) $channel));
        $destination = trim((string) $destination);
        $createdBy = $createdBy !== null ? (int) $createdBy : null;

        if ($contextRef === '' || $destination === '' || !in_array($channel, array('sms', 'email', 'whatsapp'), true)) {
            return array('status' => 'error', 'message' => 'Invalid OTP challenge inputs');
        }

        $nowTs = time();
        $now = date('Y-m-d H:i:s', $nowTs);
        $this->expire_service_report_otp_challenges($now);

        $this->db->select('id, created_at');
        $this->db->from('sma_service_site_report_otp');
        $this->db->where('context_ref', $contextRef);
        $this->db->where('channel', $channel);
        $this->db->where('destination', $destination);
        $this->db->where('status', 'pending');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $recent = $this->db->get()->row_array();
        if (!empty($recent) && !empty($recent['created_at'])) {
            $lastTs = strtotime($recent['created_at']);
            if ($lastTs && ($nowTs - $lastTs) < (int) $throttleSeconds) {
                return array(
                    'status' => 'throttled',
                    'message' => 'Please wait before requesting another OTP',
                    'retry_after_seconds' => (int) $throttleSeconds - ($nowTs - $lastTs),
                );
            }
        }

        $otp = (string) mt_rand(100000, 999999);
        $otpHash = password_hash($otp, PASSWORD_BCRYPT);
        if ($otpHash === false) {
            return array('status' => 'error', 'message' => 'Failed to generate OTP hash');
        }

        $insert = array(
            'context_ref' => $contextRef,
            'channel' => $channel,
            'destination' => $destination,
            'otp_hash' => $otpHash,
            'expires_at' => date('Y-m-d H:i:s', $nowTs + (int) $ttlSeconds),
            'attempt_count' => 0,
            'max_attempts' => (int) $maxAttempts,
            'status' => 'pending',
            'created_by' => $createdBy,
            'created_at' => $now,
        );
        if (!$this->db->insert('sma_service_site_report_otp', $insert)) {
            return array('status' => 'error', 'message' => 'Failed to create OTP challenge');
        }

        return array(
            'status' => 'success',
            'challenge_id' => (int) $this->db->insert_id(),
            'otp' => $otp,
            'expires_in_seconds' => (int) $ttlSeconds,
        );
    }

    public function verify_service_report_otp($challengeId, $otpCode, $contextRef)
    {
        if (!$this->ensure_service_report_otp_table()) {
            return array('status' => 'error', 'message' => 'OTP table is not available');
        }
        $challengeId = (int) $challengeId;
        $otpCode = trim((string) $otpCode);
        $contextRef = trim((string) $contextRef);
        if ($challengeId <= 0 || $otpCode === '' || strlen($otpCode) !== 6 || !ctype_digit($otpCode) || $contextRef === '') {
            return array('status' => 'error', 'message' => 'Invalid OTP verification input');
        }

        $now = date('Y-m-d H:i:s');
        $this->expire_service_report_otp_challenges($now);
        $this->db->select('*');
        $this->db->from('sma_service_site_report_otp');
        $this->db->where('id', $challengeId);
        $this->db->where('context_ref', $contextRef);
        $row = $this->db->get()->row_array();
        if (empty($row)) {
            return array('status' => 'error', 'message' => 'OTP challenge not found');
        }
        if ($row['status'] === 'verified' && !empty($row['verified_token'])) {
            return array('status' => 'success', 'message' => 'OTP already verified', 'verification_token' => $row['verified_token']);
        }
        if ($row['status'] !== 'pending') {
            return array('status' => 'error', 'message' => 'OTP challenge is not active');
        }
        if ((int) $row['attempt_count'] >= (int) $row['max_attempts']) {
            $this->db->where('id', $challengeId)->update('sma_service_site_report_otp', array('status' => 'locked'));
            return array('status' => 'error', 'message' => 'OTP attempts exceeded');
        }
        if (strtotime((string) $row['expires_at']) < time()) {
            $this->db->where('id', $challengeId)->update('sma_service_site_report_otp', array('status' => 'expired'));
            return array('status' => 'error', 'message' => 'OTP expired');
        }
        $isValid = password_verify($otpCode, (string) $row['otp_hash']);
        if (!$isValid) {
            $this->db->set('attempt_count', 'attempt_count+1', false);
            $this->db->where('id', $challengeId);
            $this->db->update('sma_service_site_report_otp');
            return array('status' => 'error', 'message' => 'Invalid OTP');
        }

        $verificationToken = hash('sha256', $row['id'] . '|' . $row['context_ref'] . '|' . microtime(true) . '|' . mt_rand(1000, 9999));
        $update = array(
            'status' => 'verified',
            'verified_at' => $now,
            'verified_token' => $verificationToken,
        );
        $this->db->where('id', $challengeId)->update('sma_service_site_report_otp', $update);
        return array('status' => 'success', 'message' => 'OTP verified', 'verification_token' => $verificationToken);
    }

    public function validate_verified_service_report_otp($challengeId, $verificationToken, $contextRef)
    {
        if (!$this->ensure_service_report_otp_table()) {
            return false;
        }
        $challengeId = (int) $challengeId;
        $verificationToken = trim((string) $verificationToken);
        $contextRef = trim((string) $contextRef);
        if ($challengeId <= 0 || $verificationToken === '' || $contextRef === '') {
            return false;
        }
        $this->db->select('id');
        $this->db->from('sma_service_site_report_otp');
        $this->db->where('id', $challengeId);
        $this->db->where('context_ref', $contextRef);
        $this->db->where('status', 'verified');
        $this->db->where('verified_token', $verificationToken);
        return $this->db->get()->num_rows() > 0;
    }

    public function ensure_service_report_otp_table()
    {
        if ($this->db->table_exists('sma_service_site_report_otp')) {
            return true;
        }
        $sql = "CREATE TABLE IF NOT EXISTS `sma_service_site_report_otp` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `context_ref` VARCHAR(80) NOT NULL,
            `channel` VARCHAR(20) NOT NULL,
            `destination` VARCHAR(190) NOT NULL,
            `otp_hash` VARCHAR(255) NOT NULL,
            `expires_at` DATETIME NOT NULL,
            `attempt_count` INT(11) NOT NULL DEFAULT 0,
            `max_attempts` INT(11) NOT NULL DEFAULT 5,
            `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
            `created_by` INT(11) DEFAULT NULL,
            `created_at` DATETIME NOT NULL,
            `verified_at` DATETIME DEFAULT NULL,
            `verified_token` VARCHAR(120) DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_context_ref` (`context_ref`),
            KEY `idx_status_exp` (`status`, `expires_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        return (bool) $this->db->query($sql);
    }

    public function expire_service_report_otp_challenges($now = null)
    {
        if (!$this->db->table_exists('sma_service_site_report_otp')) {
            return;
        }
        $now = $now ?: date('Y-m-d H:i:s');
        $this->db->where('status', 'pending');
        $this->db->where('expires_at <', $now);
        $this->db->update('sma_service_site_report_otp', array('status' => 'expired'));
    }

    private function resolve_service_site_report_log_table()
    {
        $candidates = array('sma_preventive_maintenance_log');
        foreach ($candidates as $table) {
            if ($this->db->table_exists($table)) {
                return $table;
            }
        }
        return '';
    }

    private function ensure_service_site_report_log_table()
    {
        $existing = $this->resolve_service_site_report_log_table();
        if ($existing !== '') {
            return $existing;
        }

        $sql = "CREATE TABLE IF NOT EXISTS `sma_preventive_maintenance_log` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `date` DATE DEFAULT NULL,
            `log_reference_no` VARCHAR(80) DEFAULT NULL,
            `equipment_id` INT(11) DEFAULT NULL,
            `parameter` VARCHAR(120) DEFAULT NULL,
            `value` LONGTEXT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_parameter` (`parameter`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        $ok = (bool) $this->db->query($sql);
        if (!$ok) {
            return '';
        }
        return 'sma_preventive_maintenance_log';
    }

    private function getEquipmentDetailRecordByTag($equipmentTag, $customerId = 0, $locationId = 0)
    {
        $equipmentTag = trim((string) $equipmentTag);
        $customerId = (int) $customerId;
        $locationId = (int) $locationId;
        if ($equipmentTag === '') {
            return array();
        }

        // Backup (existing closure style):
        // $queryBuilder = function ($useLocation) use ($equipmentTag, $customerId, $locationId) {
        //     $this->db->select('d.id, d.customer_id, d.location_id, d.eqpt_no');
        //     $this->db->from('sma_equipmentdetails d');
        //     $this->db->where('d.eqpt_no', $equipmentTag);
        //     if ($customerId > 0) {
        //         $this->db->where('d.customer_id', $customerId);
        //     }
        //     if ($useLocation && $locationId > 0) {
        //         $this->db->where('d.location_id', $locationId);
        //     }
        //     $this->db->where('d.is_active', 1);
        //     $this->db->order_by('d.id', 'DESC');
        //     return $this->db->get()->row_array();
        // };

        // 1) Strict match: tag + customer + location (if available)
        $this->db->select('d.id, d.customer_id, d.location_id, d.eqpt_no');
        $this->db->from('sma_equipmentdetails d');
        $this->db->where('d.eqpt_no', $equipmentTag);
        if ($customerId > 0) {
            $this->db->where('d.customer_id', $customerId);
        }
        if ($locationId > 0) {
            $this->db->where('d.location_id', $locationId);
        }
        $this->db->where('d.is_active', 1);
        $this->db->order_by('d.id', 'DESC');
        $row = $this->db->get()->row_array();

        // 2) Fallback match: tag + customer (ignore location)
        if (empty($row)) {
            $this->db->select('d.id, d.customer_id, d.location_id, d.eqpt_no');
            $this->db->from('sma_equipmentdetails d');
            $this->db->where('d.eqpt_no', $equipmentTag);
            if ($customerId > 0) {
                $this->db->where('d.customer_id', $customerId);
            }
            $this->db->where('d.is_active', 1);
            $this->db->order_by('d.id', 'DESC');
            $row = $this->db->get()->row_array();
        }
        return is_array($row) ? $row : array();
    }

    private function savePmGridRows(array $payload, $table, $serviceDate, $logRef, array $equipmentRecord = array())
    {
        if ($table === '') {
            return;
        }
        $gridRaw = isset($payload['pm_log_grid']) ? $payload['pm_log_grid'] : '';
        $gridRows = is_array($gridRaw) ? $gridRaw : json_decode((string) $gridRaw, true);
        if (!is_array($gridRows) || empty($gridRows)) {
            return;
        }

        $customerId = isset($payload['customer_id']) ? (int) $payload['customer_id'] : 0;
        $locationId = isset($payload['customer_location_id']) ? (int) $payload['customer_location_id'] : 0;
        $equipmentId = isset($equipmentRecord['id']) ? (int) $equipmentRecord['id'] : 0;
        if ($locationId <= 0 && isset($equipmentRecord['location_id'])) {
            $locationId = (int) $equipmentRecord['location_id'];
        }

        $cktMap = array(
            'ckt_01' => 'CKT 01',
            'ckt_02' => 'CKT 02',
            'ckt_03' => 'CKT 03',
            'ckt_04' => 'CKT 04',
        );

        if (trim((string) $logRef) !== '') {
            $this->db->where('log_reference_no', trim((string) $logRef));
            if ($equipmentId > 0 && $this->db->field_exists('equipment_id', $table)) {
                $this->db->where('equipment_id', $equipmentId);
            }
            $this->db->delete($table);
        }

        $amcId = 0;
        if ($this->db->table_exists('sma_amc') && $equipmentId > 0) {
            $amcRow = $this->db->select('id')->where('equipment_id', $equipmentId)->where('customer_id', $customerId)->get('sma_amc')->row();
            if ($amcRow) { $amcId = $amcRow->id; }
        }

        foreach ($gridRows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $parameter = isset($row['parameter']) ? trim((string) $row['parameter']) : '';
            if ($parameter === '') {
                continue;
            }

            $c01 = isset($row['ckt_01']) ? trim((string) $row['ckt_01']) : '';
            $c02 = isset($row['ckt_02']) ? trim((string) $row['ckt_02']) : '';
            $c03 = isset($row['ckt_03']) ? trim((string) $row['ckt_03']) : '';
            $c04 = isset($row['ckt_04']) ? trim((string) $row['ckt_04']) : '';

            // User Request: Only insert if at least one circuit has data
            if ($c01 === '' && $c02 === '' && $c03 === '' && $c04 === '') {
                continue;
            }

            $logInsert = array(
                'date' => $serviceDate,
                'amc_id' => $amcId > 0 ? $amcId : null,
                'log_reference_no' => $logRef !== '' ? $logRef : null,
                'equipment_id' => $equipmentId > 0 ? $equipmentId : null,
                'parameter' => $parameter,
                'ckt01' => $c01,
                'ckt02' => $c02,
                'ckt03' => $c03,
                'ckt04' => $c04,
            );

            // If any additional fields exist in the table, add them
            if ($this->db->field_exists('customer_id', $table) && $customerId > 0) {
                $logInsert['customer_id'] = $customerId;
            }
            if ($this->db->field_exists('location_id', $table) && $locationId > 0) {
                $logInsert['location_id'] = $locationId;
            }
            if ($this->db->field_exists('customer_signature', $table)) {
                $logInsert['customer_signature'] = isset($payload['customer_signature']) ? $payload['customer_signature'] : null;
            }
            if ($this->db->field_exists('engineer_sign', $table)) {
                $logInsert['engineer_sign'] = isset($payload['engineer_signature']) ? $payload['engineer_signature'] : null;
            }
            if ($this->db->field_exists('customer_authority_name', $table)) {
                $logInsert['customer_authority_name'] = isset($payload['customer_signature_name']) ? $payload['customer_signature_name'] : null;
            }


            $this->db->insert($table, $logInsert);
        }
    }

    private function upsertAmcForPmEntry(array $payload, $serviceDate, array $equipmentRecord = array())
    {
        if (!$this->db->table_exists('sma_amc')) {
            return;
        }
        $customerId = isset($payload['customer_id']) ? (int) $payload['customer_id'] : 0;
        $locationId = isset($payload['customer_location_id']) ? (int) $payload['customer_location_id'] : 0;
        $equipmentId = isset($equipmentRecord['id']) ? (int) $equipmentRecord['id'] : 0;
        // AMC location should follow equipmentDetails.location_id (warehouse/location reference),
        // not address row id from UI dropdown.
        if (isset($equipmentRecord['location_id']) && (int) $equipmentRecord['location_id'] > 0) {
            $locationId = (int) $equipmentRecord['location_id'];
        }
        if ($customerId <= 0 || $locationId <= 0 || $equipmentId <= 0) {
            return;
        }

        $this->db->select('id');
        if ($this->db->field_exists('pm_services_done', 'sma_amc')) {
            $this->db->select('pm_services_done');
        }
        $this->db->from('sma_amc');
        $this->db->where('customer_id', $customerId);
        $this->db->where('location_id', $locationId);
        $this->db->where('equipment_id', $equipmentId);
        if ($this->db->field_exists('is_active', 'sma_amc')) {
            $this->db->where('is_active', 1);
        }
        $this->db->order_by('id', 'DESC');
        $row = $this->db->get()->row_array();

        if (!empty($row) && isset($row['id'])) {
            $update = array();
            if ($this->db->field_exists('pm_services_done', 'sma_amc')) {
                $done = isset($row['pm_services_done']) ? (int) $row['pm_services_done'] : 0;
                $update['pm_services_done'] = $done + 1;
            }
            if (!empty($update)) {
                $this->db->where('id', (int) $row['id']);
                $this->db->update('sma_amc', $update);
            }
            return;
        }

        $insert = array();
        if ($this->db->field_exists('customer_id', 'sma_amc')) {
            $insert['customer_id'] = $customerId;
        }
        if ($this->db->field_exists('location_id', 'sma_amc')) {
            $insert['location_id'] = $locationId;
        }
        if ($this->db->field_exists('equipment_id', 'sma_amc')) {
            $insert['equipment_id'] = $equipmentId;
        }
        if ($this->db->field_exists('start_date', 'sma_amc')) {
            $insert['start_date'] = $serviceDate;
        }
        if ($this->db->field_exists('end_date', 'sma_amc')) {
            $insert['end_date'] = date('Y-m-d', strtotime($serviceDate . ' +1 year'));
        }
        if ($this->db->field_exists('pm_services_per_year', 'sma_amc')) {
            $insert['pm_services_per_year'] = 1;
        }
        if ($this->db->field_exists('pm_services_done', 'sma_amc')) {
            $insert['pm_services_done'] = 1;
        }
        if ($this->db->field_exists('is_active', 'sma_amc')) {
            $insert['is_active'] = 1;
        }
        if (!empty($insert)) {
            $this->db->insert('sma_amc', $insert);
        }
    }

    /**
     * Build the log_reference_no in the format: CustId/LocationCode/Date[/Time]
     * Example with time:    1000/AVP/2026-04-29/22:04
     * Example without time: 1000/AVP/2026-04-29  (used as LIKE prefix for dedup)
     */
    private function buildLogReference($customerId, $locationId, $serviceDate = null, $withTime = true)
    {
        $custPart = $customerId > 0 ? (string) $customerId : '0';

        // Get location code from sma_warehouses
        $locCode = 'LOC';
        if ($locationId > 0 && $this->db->table_exists('sma_addresses')) {
            // Check if $locationId is an address ID that points to a warehouse
            $hasLocationId = $this->db->field_exists('location_id', 'sma_addresses');
            if ($hasLocationId) {
                $addr = $this->db->select('location_id')->where('id', $locationId)->get('sma_addresses')->row_array();
                $warehouseId = !empty($addr['location_id']) ? (int) $addr['location_id'] : $locationId;
            } else {
                $warehouseId = $locationId;
            }

            if ($this->db->table_exists('sma_warehouses')) {
                $wRow = $this->db->select('code')
                    ->where('id', $warehouseId)
                    ->get('sma_warehouses')
                    ->row_array();
                if (!empty($wRow['code'])) {
                    $locCode = trim($wRow['code']);
                }
            }
        }

        $date = !empty($serviceDate) ? $serviceDate : date('Y-m-d');
        $base = $custPart . '/' . $locCode . '/' . $date;

        if ($withTime) {
            return $base . '/' . date('H:i'); // e.g. 1000/AVP/2026-04-29/22:04
        }
        return $base; // prefix only, used for LIKE-based dedup
    }

    public function get_next_service_report_number($customerId, $locationId)
    {
        $customerId = (int) $customerId;
        $locationId = (int) $locationId;
        if ($customerId <= 0) return '';

        $count = $this->countCustomerServiceReports($customerId, $locationId);
        $nextIncrement = str_pad($count + 1, 2, '0', STR_PAD_LEFT);

        // Get location code
        $locCode = 'LOC';
        if ($locationId > 0 && $this->db->table_exists('sma_addresses')) {
            $hasLocationId = $this->db->field_exists('location_id', 'sma_addresses');
            if ($hasLocationId) {
                $addr = $this->db->select('location_id')->where('id', $locationId)->get('sma_addresses')->row_array();
                $warehouseId = !empty($addr['location_id']) ? (int) $addr['location_id'] : $locationId;
            } else {
                $warehouseId = $locationId;
            }

            if ($this->db->table_exists('sma_warehouses')) {
                $wRow = $this->db->select('code')
                    ->where('id', $warehouseId)
                    ->get('sma_warehouses')
                    ->row_array();
                if (!empty($wRow['code'])) {
                    $locCode = trim($wRow['code']);
                }
            }
        }

        return $customerId . '/' . $locCode . '/' . $nextIncrement;
    }
}

