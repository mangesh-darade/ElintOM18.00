<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Attendance extends MY_Controller
{
    protected $faceUploadPath;
    protected $siteLocationsCache = null;

    public function __construct()
    {
        parent::__construct();

        // Public kiosk: capture + geo preview + save work without login (face + site rules enforce identity).
        $publicMethods = array('captured', 'store', 'derive_location_preview');
        $currentMethod = strtolower($this->router->fetch_method());
        if (!$this->loggedIn && !in_array($currentMethod, $publicMethods, true)) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            redirect(site_url('login'));
        }

        $this->load->library(array('form_validation', 'upload'));
        $this->load->helper(array('form', 'url', 'attendance'));
        $this->load->model('attendance_model');

        $this->faceUploadPath = 'assets/mdata/' . $this->Customer_assets . '/uploads/faces/';
        if (!is_dir(FCPATH . $this->faceUploadPath)) {
            @mkdir(FCPATH . $this->faceUploadPath, 0777, true);
        }
    }

    public function index()
    {
        if (!$this->sma->actionPermissions('index', 'attendance')) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'welcome');
        }

        $scopeUserId = null;
        if ($this->Owner || $this->Admin) {
            // all users' attendance
        } elseif ($this->Customer) {
            $scopeUserId = (int) $this->session->userdata('user_id');
        } elseif (!$this->attendanceHasViewAllRecordsAccess() && !$this->attendanceHasEditAnyRecordAccess()) {
            // own records only
            $scopeUserId = (int) $this->session->userdata('user_id');
        }

        $filters = $this->attendanceBuildFilters($scopeUserId);
        $rows = $this->attendance_model->get_attendance_filtered(
            $filters['scope_user_id'],
            $filters['filter_user_id'],
            $filters['filter_date']
        );
        $exportType = strtolower(trim((string) $this->input->get('export')));
        if (in_array($exportType, array('excel', 'pdf'), true)) {
            $this->attendanceExportRows($rows, 'Attendance List', 'attendance_list', $exportType);
            return;
        }

        $this->data['attendance_rows'] = $rows;
        $this->data['attendance_filter_user_id'] = $filters['filter_user_id'];
        $this->data['attendance_filter_date'] = $filters['filter_date'];
        $this->data['attendance_scope_user_id'] = $filters['scope_user_id'];
        $this->data['attendance_filter_users'] = ($filters['can_filter_by_user'])
            ? $this->attendance_model->get_attendance_filter_users()
            : array();
        $this->data['logged_in_display_name'] = $this->attendanceLoggedInDisplayName();
        $this->data['can_index_attendance'] = $this->sma->actionPermissions('index', 'attendance');
        $this->data['can_create_attendance'] = $this->sma->actionPermissions('create', 'attendance');
        $this->data['can_edit_attendance'] = $this->sma->actionPermissions('edit', 'attendance');
        $this->data['can_delete_attendance'] = $this->sma->actionPermissions('delete', 'attendance');
        $this->data['attendance_has_view_all'] = $this->attendanceHasViewAllRecordsAccess();
        $this->data['attendance_has_edit_any'] = $this->attendanceHasEditAnyRecordAccess();
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc = array(
            array('link' => base_url(), 'page' => lang('home')),
            array('link' => '#', 'page' => 'Attendance')
        );
        $meta = array('page_title' => 'Attendance', 'bc' => $bc);
        $this->page_construct('attendance/attendance_list', $meta, $this->data);
    }

    public function report()
    {
        if (!$this->sma->actionPermissions('index', 'attendance')) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'welcome');
        }

        $scopeUserId = null;
        if (!$this->Owner && !$this->Admin) {
            if ($this->Customer || (!$this->attendanceHasViewAllRecordsAccess() && !$this->attendanceHasEditAnyRecordAccess())) {
                $scopeUserId = (int) $this->session->userdata('user_id');
            }
        }

        $filters = $this->attendanceBuildReportFilters($scopeUserId);

        $startDate = $filters['filter_start_date'];
        $endDate = $filters['filter_end_date'];
        if (!empty($startDate) && !empty($endDate) && $startDate > $endDate) {
            // Swap so we always query a valid range.
            $tmp = $startDate;
            $startDate = $endDate;
            $endDate = $tmp;
        }

        $rows = $this->attendance_model->get_attendance_filtered_range(
            $filters['scope_user_id'],
            $filters['filter_user_id'],
            $startDate,
            $endDate
        );

        // Extra safety: enforce row-level view rights even if scope/filter is correct.
        $visibleRows = array();
        if (!empty($rows)) {
        foreach ($rows as $row) {
            if ($this->attendanceCanViewRow((int) $row->user_id)) {
                $visibleRows[] = $row;
            }
        }
        }
        $rows = $visibleRows;

        $exportType = strtolower(trim((string) $this->input->get('export')));
        if (in_array($exportType, array('excel', 'pdf'), true)) {
            $this->attendanceExportReportRows($rows, 'Attendance Report', 'attendance_report', $exportType);
            return;
        }

        $this->data['attendance_rows'] = $rows;
        $this->data['attendance_filter_user_id'] = $filters['filter_user_id'];
        $this->data['attendance_filter_start_date'] = $startDate;
        $this->data['attendance_filter_end_date'] = $endDate;
        $this->data['attendance_scope_user_id'] = $filters['scope_user_id'];
        $this->data['attendance_filter_users'] = ($filters['can_filter_by_user'])
            ? $this->attendance_model->get_attendance_filter_users()
            : array();
        $this->data['logged_in_display_name'] = $this->attendanceLoggedInDisplayName();
        $this->data['can_index_attendance'] = $this->sma->actionPermissions('index', 'attendance');
        $this->data['attendance_has_view_all'] = $this->attendanceHasViewAllRecordsAccess();
        $this->data['attendance_has_edit_any'] = $this->attendanceHasEditAnyRecordAccess();
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc = array(
            array('link' => base_url(), 'page' => lang('home')),
            array('link' => site_url('attendance'), 'page' => 'Attendance'),
            array('link' => '#', 'page' => 'Attendance Report')
        );
        $meta = array('page_title' => 'Attendance Report', 'bc' => $bc);
        $this->page_construct('attendance/attendance_report', $meta, $this->data);
    }

    /**
     * Bulk actions for Attendance List: export selected rows.
     * Triggered by global core.js using hidden form fields:
     * - POST: form_action = export_excel|export_pdf
     * - POST: val[] = attendance ids
     */
    public function list_actions()
    {
        if (!$this->sma->actionPermissions('index', 'attendance')) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'welcome');
        }

        if (strtolower($this->input->method()) !== 'post') {
            redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'attendance');
        }

        $this->form_validation->set_rules('form_action', lang("form_action"), 'required');
        if ($this->form_validation->run() === false) {
            $this->session->set_flashdata('error', validation_errors() ? validation_errors() : lang('access_denied'));
            redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'attendance');
        }

        $formAction = (string) $this->input->post('form_action', true);
        if (!in_array($formAction, array('export_excel', 'export_pdf'), true)) {
            redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'attendance');
        }

        $selected = $this->input->post('val');
        if (!is_array($selected)) {
            $selected = array();
        }
        $selectedIds = array();
        foreach ($selected as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $selectedIds[] = $id;
            }
        }
        $selectedIds = array_values(array_unique($selectedIds));

        if (empty($selectedIds)) {
            $this->session->set_flashdata('error', 'No attendance selected for export');
            redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'attendance');
        }

        $rows = $this->attendance_model->get_attendance_by_ids($selectedIds);
        $visibleRows = array();
        if (!empty($rows)) {
        foreach ($rows as $row) {
            if ($this->attendanceCanViewRow((int) $row->user_id)) {
                $visibleRows[] = $row;
            }
        }
        }

        if (empty($visibleRows)) {
            $this->session->set_flashdata('error', 'No permitted attendance rows found for export');
            redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'attendance');
        }

        $exportType = ($formAction === 'export_pdf') ? 'pdf' : 'excel';
        $this->attendanceExportRows($visibleRows, 'Attendance List', 'attendance_list', $exportType);
    }

    public function captured()
    {
        if ($this->loggedIn) {
            $this->sma->checkPermissions('create', null, 'attendance');
        }

        if (!$this->loggedIn) {
            // Kiosk: server chooses check-in vs check-out from open row once user is identified.
            $defaultAttendanceType = 'auto';
        } else {
            $defaultAttendanceType = 'check_in';
            $loggedInUserId = (int) $this->session->userdata('user_id');
            if ($loggedInUserId > 0) {
                $openAttendances = $this->attendance_model->get_today_open_attendances($loggedInUserId);
                if (!empty($openAttendances)) {
                    $defaultAttendanceType = 'check_out';
                }
            }
        }

        $defaultAttendanceType = strtolower($defaultAttendanceType) === 'check_out' ? 'check_out' : (
            strtolower($defaultAttendanceType) === 'auto' ? 'auto' : 'check_in'
        );
        $this->data['default_attendance_type'] = $defaultAttendanceType;
        $this->data['attendance_kiosk_mode'] = !$this->loggedIn;
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(
            array('link' => base_url(), 'page' => lang('home')),
            array('link' => site_url('attendance'), 'page' => 'Attendance'),
            array('link' => '#', 'page' => 'Capture')
        );
        $meta = array('page_title' => 'Capture Attendance', 'bc' => $bc);
        $this->data['page_title'] = $meta['page_title'];
        $this->load->view($this->theme . 'attendance/attendance_capture', $this->data);
    }

    public function store()
    {
        $this->benchmark->mark('attendance_store_start');

        if (strtolower($this->input->method()) !== 'post') {
            return $this->jsonResponse('error', 'Invalid request method');
        }

        if ($this->loggedIn && !$this->sma->actionPermissions('create', 'attendance')) {
            return $this->jsonResponse('error', lang('access_denied'));
        }

        $descriptorRaw = $this->input->post('descriptor', true);
        $latitude = $this->input->post('latitude', true);
        $longitude = $this->input->post('longitude', true);
        $landmark = $this->input->post('landmark', true);
        $notes = trim((string) $this->input->post('notes', true));
        $attendanceType = strtolower(trim((string) $this->input->post('attendance_type', true)));
        if (!in_array($attendanceType, array('auto', 'check_in', 'check_out'), true)) {
            $attendanceType = 'auto';
        }

        if (empty($descriptorRaw)) {
            return $this->jsonResponse('error', 'Face descriptor is required');
        }

        $descriptor = json_decode($descriptorRaw, true);
        if (!is_array($descriptor) || count($descriptor) !== 128) {
            return $this->jsonResponse('error', 'Invalid descriptor');
        }
        foreach ($descriptor as $value) {
            if (!is_numeric($value)) {
                return $this->jsonResponse('error', 'Invalid descriptor');
            }
        }
        if (!$this->isValidCoordinate($latitude, true) || !$this->isValidCoordinate($longitude, false)) {
            return $this->jsonResponse('error', 'Invalid coordinates');
        }

        // Identify user by enrolled face only (active users with stored descriptors in user_faces).
        $match = $this->attendance_model->match_face_descriptor($descriptor, 0.52, null, 0.03);
        if (!$match || empty($match['user_id'])) {
            log_message('error', 'Attendance face match failed ip=' . $this->input->ip_address());
            return $this->jsonResponse('error', 'Face not recognized by system');
        }

        $userId = (int) $match['user_id'];
        $matchedUser = $this->attendance_model->get_user_by_id($userId);
        if (!$matchedUser || (int) $matchedUser->active !== 1) {
            return $this->jsonResponse('error', 'Face not recognized by system');
        }
        $selectedDerivedLocation = trim((string) $this->input->post('selected_derived_location', true));
        $locationResolution = $this->resolveDerivedLocation($latitude, $longitude, $selectedDerivedLocation);
        if (!$locationResolution['ok']) {
            return $this->jsonResponse('error', $locationResolution['message'], array(
                'requires_location_selection' => !empty($locationResolution['requires_selection']),
                'location_options' => isset($locationResolution['options']) ? $locationResolution['options'] : array()
            ));
        }
        $derivedLocation = $locationResolution['derived_location'];
        if (trim((string) $derivedLocation) === '' && empty($locationResolution['is_new_location'])) {
            return $this->jsonResponse('error', 'You are in different location');
        }
        $openAttendanceForLocation = $this->attendance_model->check_today_open_attendance_by_location($userId, $derivedLocation);
        $systemLog = !empty($locationResolution['is_new_location']) ? 'new location found' : '';
        $locationText = $this->prepareLocationText($landmark, $derivedLocation);

        $this->db->trans_begin();
        $actionTaken = 'check_in';

        // Auto mode toggles check-in/out per location.
        if ($attendanceType === 'auto') {
            $attendanceType = $openAttendanceForLocation ? 'check_out' : 'check_in';
        }

        if ($attendanceType === 'check_in') {
            if ($openAttendanceForLocation) {
                $this->db->trans_rollback();
                return $this->jsonResponse('error', 'Already checked in for this location. Please select check-out');
            }

            // Auto-checkout any other open locations for this user today.
            // This handles the scenario where a user moves from one geofence to another 
            // (e.g. Kothrud 1 to Kothrud 2) without manually checking out.
            $allOpenAttendances = $this->attendance_model->get_today_open_attendances($userId);
            foreach ($allOpenAttendances as $open) {
                $openLoc = trim((string)$open->derived_location);
                $newLoc = trim((string)$derivedLocation);
                if ($openLoc !== $newLoc) {
                    $checkoutData = array(
                        'check_out' => date('Y-m-d H:i:s'),
                        'latitude' => $latitude !== '' ? $latitude : $open->latitude,
                        'longitude' => $longitude !== '' ? $longitude : $open->longitude,
                        'landmark' => $this->buildCheckOutLandmark($open->landmark, ($open->derived_location ?: $locationText), 'Auto-checkout (moved to ' . ($derivedLocation ?: 'new location') . ')'),
                        'system_log' => trim((string)$open->system_log . ' | auto-checkout-moved')
                    );
                    $this->attendance_model->update_attendance_checkout($open->id, $checkoutData);
                    log_message('info', 'Attendance auto-checkout user_id=' . $userId . ' prev_location=' . $open->derived_location . ' new_location=' . $derivedLocation);
                }
            }

            $attendanceData = array(
                'user_id' => $userId,
                'check_in' => date('Y-m-d H:i:s'),
                'latitude' => $latitude !== '' ? $latitude : null,
                'longitude' => $longitude !== '' ? $longitude : null,
                'landmark' => $this->buildCheckInLandmark($locationText, $notes),
                'derived_location' => $derivedLocation,
                'system_log' => $systemLog,
                'created_at' => date('Y-m-d H:i:s')
            );
            $this->attendance_model->insert_attendance($attendanceData);
        } else { // check_out
            // Global check-out: find any open attendance for this user today.
            $allOpenAttendances = $this->attendance_model->get_today_open_attendances($userId);
            if (empty($allOpenAttendances)) {
                $this->db->trans_rollback();
                return $this->jsonResponse('error', 'No open check-in found today');
            }
            
            $actionTaken = 'check_out';
            foreach ($allOpenAttendances as $open) {
                $updateData = array(
                    'check_out' => date('Y-m-d H:i:s'),
                    'latitude' => $latitude !== '' ? $latitude : $open->latitude,
                    'longitude' => $longitude !== '' ? $longitude : $open->longitude,
                    'landmark' => $this->buildCheckOutLandmark($open->landmark, ($open->derived_location ?: $locationText), $notes),
                    'system_log' => trim((string)$open->system_log . ' | check_out-explicit')
                );
                // We keep the original derived_location for the record to maintain check-in context,
                // while the landmark captures where the checkout actually occurred.
                $this->attendance_model->update_attendance_checkout($open->id, $updateData);
            }
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            log_message('error', 'Attendance transaction failed for user: ' . $userId);
            return $this->jsonResponse('error', 'Could not mark attendance');
        }
        $this->db->trans_commit();

        $this->benchmark->mark('attendance_store_end');
        log_message('debug', 'Attendance store time: ' . $this->benchmark->elapsed_time('attendance_store_start', 'attendance_store_end'));
        log_message('info', 'Attendance marked user_id=' . $userId . ' action=' . $actionTaken
            . ' derived_location=' . $derivedLocation . ' ip=' . $this->input->ip_address()
            . ' kiosk=' . ($this->loggedIn ? '0' : '1'));

        return $this->jsonResponse('success', 'Attendance marked', array(
            'action' => $actionTaken,
            'next_action' => ($actionTaken === 'check_in') ? 'check_out' : 'check_in'
        ));
    }

    ///////////////////////////////////////////////////////////////attendance_module/////////////////////////////////////////////////////////////

    public function details($id = null)
    {
        if (!$this->sma->actionPermissions('index', 'attendance')) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'welcome');
        }

        $isModal = (bool) $this->input->get('modal');
        $id = (int) $id;
        $attendance = $this->attendance_model->get_attendance_by_id($id);
        if (!$attendance) {
            $this->session->set_flashdata('error', 'Attendance not found');
            return redirect('attendance');
        }

        if (!$this->attendanceCanViewRow((int) $attendance->user_id)) {
            $this->sma->view_rights($attendance->user_id);
        }

        $this->data['attendance'] = $attendance;
        $this->data['logged_in_display_name'] = $this->attendanceLoggedInDisplayName();
        $this->data['is_modal'] = $isModal;
        if ($isModal) {
            return $this->load->view($this->theme . 'attendance/attendance_details', $this->data);
        }
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(
            array('link' => base_url(), 'page' => lang('home')),
            array('link' => site_url('attendance'), 'page' => 'Attendance'),
            array('link' => '#', 'page' => 'Details')
        );
        $meta = array('page_title' => 'Attendance Details', 'bc' => $bc);
        $this->page_construct('attendance/attendance_details', $meta, $this->data);
    }

    public function edit($id = null)
    {
        if (!$this->sma->actionPermissions('edit', 'attendance')) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'attendance');
        }

        $isModal = (bool) $this->input->get('modal');
        $id = (int) $id;
        $attendance = $this->attendance_model->get_attendance_by_id($id);
        if (!$attendance) {
            $this->session->set_flashdata('error', 'Attendance not found');
            return redirect('attendance');
        }
        if (!$this->attendanceCanEditRow((int) $attendance->user_id)) {
            $this->sma->view_rights($attendance->user_id);
        }

        $this->data['attendance'] = $attendance;
        $this->data['logged_in_display_name'] = $this->attendanceLoggedInDisplayName();
        $this->data['is_modal'] = $isModal;
        if ($isModal) {
            return $this->load->view($this->theme . 'attendance/attendance_edit', $this->data);
        }
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $bc = array(
            array('link' => base_url(), 'page' => lang('home')),
            array('link' => site_url('attendance'), 'page' => 'Attendance'),
            array('link' => '#', 'page' => 'Edit Attendance')
        );
        $meta = array('page_title' => 'Edit Attendance', 'bc' => $bc);
        $this->page_construct('attendance/attendance_edit', $meta, $this->data);
    }

    public function update($id = null)
    {
        if (strtolower($this->input->method()) !== 'post') {
            $this->session->set_flashdata('error', 'Invalid request method');
            return redirect('attendance');
        }

        if (!$this->sma->actionPermissions('edit', 'attendance')) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'attendance');
        }

        $isModal = (bool) $this->input->post('modal');
        $id = (int) $id;
        $attendance = $this->attendance_model->get_attendance_by_id($id);
        if (!$attendance) {
            $this->session->set_flashdata('error', 'Attendance not found');
            return redirect('attendance');
        }
        if (!$this->attendanceCanEditRow((int) $attendance->user_id)) {
            $this->sma->view_rights($attendance->user_id);
        }

        $this->form_validation->set_rules('check_in', 'Check In', 'trim|required');
        $this->form_validation->set_rules('check_out', 'Check Out', 'trim');
        $this->form_validation->set_rules('latitude', 'Latitude', 'trim');
        $this->form_validation->set_rules('longitude', 'Longitude', 'trim');
        $this->form_validation->set_rules('landmark', 'Landmark', 'trim');
        $this->form_validation->set_rules('derived_location', 'Derived Location', 'trim');

        if ($this->form_validation->run() === false) {
            $this->session->set_flashdata('error', validation_errors());
            return redirect('attendance/edit/' . $id . ($isModal ? '?modal=1' : ''));
        }

        $checkInRaw = $this->input->post('check_in', true);
        $checkOutRaw = $this->input->post('check_out', true);
        $checkIn = $this->normalizeDateTimeInput($checkInRaw);
        $checkOut = $this->normalizeDateTimeInput($checkOutRaw);
        if ($checkIn === null) {
            $this->session->set_flashdata('error', 'Invalid Check In date/time format');
            return redirect('attendance/edit/' . $id . ($isModal ? '?modal=1' : ''));
        }
        if ((string) $checkOutRaw !== '' && $checkOut === null) {
            $this->session->set_flashdata('error', 'Invalid Check Out date/time format');
            return redirect('attendance/edit/' . $id . ($isModal ? '?modal=1' : ''));
        }
        if ($checkOut !== null && strtotime($checkOut) < strtotime($checkIn)) {
            $this->session->set_flashdata('error', 'Check Out cannot be earlier than Check In');
            return redirect('attendance/edit/' . $id . ($isModal ? '?modal=1' : ''));
        }

        $latitude = $this->input->post('latitude', true);
        $longitude = $this->input->post('longitude', true);
        $landmark = $this->input->post('landmark', true);
        $derivedLocation = $this->input->post('derived_location', true);
        if (trim((string) $derivedLocation) === '') {
            $derivedLocation = $this->deriveLocation($latitude, $longitude, $landmark, true);
        }

        $data = array(
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'landmark' => $landmark,
            'derived_location' => $derivedLocation
        );

        $this->attendance_model->update_attendance($id, $data);
        $this->session->set_flashdata('message', 'Attendance updated successfully');
        if ($isModal) {
            $this->output->set_output("<script type='text/javascript'>window.top.location.href='" . site_url('attendance') . "';</script>");
            return;
        }
        return redirect('attendance');
    }

    public function delete($id = null)
    {
        if (strtolower($this->input->method()) !== 'post') {
            $this->session->set_flashdata('error', 'Invalid request method');
            return redirect('attendance');
        }

        if (!$this->sma->actionPermissions('delete', 'attendance')) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            return redirect('attendance');
        }

        $id = (int) $id;
        $attendance = $this->attendance_model->get_attendance_by_id($id);
        if (!$attendance) {
            $this->session->set_flashdata('error', 'Attendance not found');
            return redirect('attendance');
        }

        if (!$this->attendanceCanEditRow((int) $attendance->user_id)) {
            $this->sma->view_rights($attendance->user_id);
        }

        $this->attendance_model->delete_attendance($id);
        $this->session->set_flashdata('message', 'Attendance deleted successfully');
        return redirect('attendance');
    }
    ///////////////////////////////////////////////////////////////attendance_module/////////////////////////////////////////////////////////////   
    ///////////////////////////////////////////////////////////////save_user/////////////////////////////////////////////////////////////   
    public function save_user()
    {
        if (strtolower($this->input->method()) !== 'post') {
            $this->session->set_flashdata('error', 'Invalid request method');
            return redirect('attendance');
        }

        if (!$this->sma->actionPermissions('enroll', 'attendance')) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('attendance');
        }

        $this->form_validation->set_rules('first_name', 'First Name', 'trim|required');
        $this->form_validation->set_rules('last_name', 'Last Name', 'trim|required');
        $this->form_validation->set_rules('phone', 'Phone', 'trim|required|is_unique[users.phone]');
        $this->form_validation->set_rules('email', 'Email', 'trim|valid_email');
        $this->form_validation->set_rules('group_id', 'Group', 'trim|required|integer');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[8]');
        $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'trim|required|matches[password]');

        if ($this->form_validation->run() === false) {
            $this->session->set_flashdata('error', validation_errors());
            return redirect('attendance/save_user');
        }

        $descriptorRaw = $this->input->post('descriptor', true);
        $descriptor = json_decode($descriptorRaw, true);
        if (!is_array($descriptor) || count($descriptor) !== 128 || !$this->isNumericDescriptor($descriptor)) {
            $this->session->set_flashdata('error', 'Invalid face descriptor');
            return redirect('attendance/save_user');
        }

        $imagePath = $this->saveFaceImage('face_image');
        if ($imagePath === false) {
            return redirect('attendance/save_user');
        }

        $userData = array(
            'first_name' => $this->input->post('first_name', true),
            'last_name' => $this->input->post('last_name', true),
            'gender' => $this->input->post('gender', true),
            'phone' => $this->input->post('phone', true),
            'email' => $this->input->post('email', true),
            'username' => $this->input->post('phone', true),
            'password' => $this->input->post('password', true),
            'company' => $this->input->post('company', true),
            'group_id' => (int) $this->input->post('group_id', true),
            'date_of_joining' => $this->input->post('date_of_joining', true),
            'active' => $this->input->post('active', true) ? 1 : 0,
            'created_by' => (int) $this->session->userdata('user_id')
        );

        $this->db->trans_begin();
        $userId = $this->attendance_model->insert_user($userData);
        if (!$userId) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', 'Could not create user');
            return redirect('attendance/save_user');
        }

        $faceData = array(
            'user_id' => $userId,
            'descriptor' => json_encode($descriptor),
            'image_path' => $imagePath
        );
        if (!$this->attendance_model->insert_face_descriptor($faceData)) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', 'Could not save face data');
            return redirect('attendance/save_user');
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', 'Could not save enrollment');
            return redirect('attendance/save_user');
        }
        $this->db->trans_commit();

        $this->session->set_flashdata('message', 'User enrolled successfully');
        return redirect('attendance');
    }

    ///////////////////////////////////////////////////////////////edit_user/////////////////////////////////////////////////////////////   
    public function edit_user($id = null)
    {
        if (!$this->sma->actionPermissions('enroll', 'attendance')) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('attendance');
        }

        $id = $id ? (int) $id : (int) $this->input->get('id');
        $user = $this->attendance_model->get_user_by_id($id);
        if (!$user) {
            $this->session->set_flashdata('error', 'User not found');
            redirect('attendance');
        }

        $this->data['user'] = $user;
        $this->data['groups'] = $this->attendance_model->get_groups();
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc = array(
            array('link' => base_url(), 'page' => lang('home')),
            array('link' => site_url('attendance'), 'page' => 'Attendance'),
            array('link' => '#', 'page' => 'Edit User')
        );
        $meta = array('page_title' => 'Edit User', 'bc' => $bc);
        $this->page_construct('attendance/edit_user', $meta, $this->data);
    }

    ///////////////////////////////////////////////////////////////update_user/////////////////////////////////////////////////////////////   
    public function update_user($id = null)
    {
        if (strtolower($this->input->method()) !== 'post') {
            $this->session->set_flashdata('error', 'Invalid request method');
            return redirect('attendance');
        }

        if (!$this->sma->actionPermissions('enroll', 'attendance')) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('attendance');
        }

        $id = (int) $id;
        $user = $this->attendance_model->get_user_by_id($id);
        if (!$user) {
            $this->session->set_flashdata('error', 'User not found');
            redirect('attendance');
        }

        $this->form_validation->set_rules('first_name', 'First Name', 'trim|required');
        $this->form_validation->set_rules('last_name', 'Last Name', 'trim|required');
        $this->form_validation->set_rules('phone', 'Phone', 'trim|required');
        $this->form_validation->set_rules('email', 'Email', 'trim|valid_email');
        $this->form_validation->set_rules('group_id', 'Group', 'trim|required|integer');

        if ($this->form_validation->run() === false) {
            $this->session->set_flashdata('error', validation_errors());
            return redirect('attendance/edit_user/' . $id);
        }

        $data = array(
            'first_name' => $this->input->post('first_name', true),
            'last_name' => $this->input->post('last_name', true),
            'gender' => $this->input->post('gender', true),
            'phone' => $this->input->post('phone', true),
            'email' => $this->input->post('email', true),
            'username' => $this->input->post('phone', true),
            'company' => $this->input->post('company', true),
            'group_id' => (int) $this->input->post('group_id', true),
            'date_of_joining' => $this->input->post('date_of_joining', true),
            'termination_date' => $this->input->post('termination_date', true),
            'active' => $this->input->post('active', true) ? 1 : 0
        );

        $password = $this->input->post('password', true);
        if ($password !== '') {
            $confirmPassword = $this->input->post('confirm_password', true);
            if ($password !== $confirmPassword || strlen($password) < 8) {
                $this->session->set_flashdata('error', 'Password validation failed');
                return redirect('attendance/edit_user/' . $id);
            }
            $data['password'] = $password;
        }

        $this->db->trans_begin();
        $this->attendance_model->update_user($id, $data);

        $descriptorRaw = $this->input->post('descriptor', true);
        if (!empty($descriptorRaw)) {
            $descriptor = json_decode($descriptorRaw, true);
            if (is_array($descriptor) && count($descriptor) === 128 && $this->isNumericDescriptor($descriptor)) {
                $faceData = array('descriptor' => json_encode($descriptor));
                $imagePath = $this->saveFaceImage('face_image');
                if ($imagePath !== false && $imagePath !== null) {
                    $faceData['image_path'] = $imagePath;
                }
                $this->attendance_model->update_face_descriptor($id, $faceData);
            }
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('error', 'Could not update user');
            return redirect('attendance/edit_user/' . $id);
        }
        $this->db->trans_commit();

        $this->session->set_flashdata('message', 'User updated successfully');
        return redirect('attendance');
    }

    /**
     * Current logged-in user's display name (for own attendance rows vs joined row data).
     */
    protected function attendanceLoggedInDisplayName()
    {
        $me = $this->site->getUser();
        if (!$me) {
            return '';
        }
        return trim($me->first_name . ' ' . $me->last_name);
    }

    /**
     * Profile "View Right": 1 / '1' = all records; otherwise own only (for non–Owner/Admin).
     */
    protected function attendanceHasViewAllRecordsAccess()
    {
        if ($this->Owner || $this->Admin) {
            return true;
        }
        $vr = $this->session->userdata('view_right');
        return ($vr === 1 || $vr === '1');
    }

    /**
     * Profile "Edit Right": 1 / '1' = may change others' rows; otherwise own only.
     */
    protected function attendanceHasEditAnyRecordAccess()
    {
        if ($this->Owner || $this->Admin) {
            return true;
        }
        $er = $this->session->userdata('edit_right');
        return ($er === 1 || $er === '1');
    }

    /**
     * A row is viewable when user has all-record view or edit-any right, or owns the row.
     */
    protected function attendanceCanViewRow($rowUserId)
    {
        $rowUserId = (int) $rowUserId;
        $sessionUserId = (int) $this->session->userdata('user_id');
        return $this->attendanceHasViewAllRecordsAccess()
            || $this->attendanceHasEditAnyRecordAccess()
            || ($rowUserId === $sessionUserId);
    }

    /**
     * A row is editable/deletable when user has edit-any right, or owns the row.
     */
    protected function attendanceCanEditRow($rowUserId)
    {
        $rowUserId = (int) $rowUserId;
        $sessionUserId = (int) $this->session->userdata('user_id');
        return $this->attendanceHasEditAnyRecordAccess()
            || ($rowUserId === $sessionUserId);
    }

    protected function saveFaceImage($fieldName)
    {
        if (empty($_FILES[$fieldName]['name'])) {
            return null;
        }

        $config = array(
            'upload_path' => FCPATH . $this->faceUploadPath,
            'allowed_types' => 'gif|jpg|jpeg|png|webp',
            'max_size' => 2048,
            'encrypt_name' => true
        );
        $this->upload->initialize($config);

        if (!$this->upload->do_upload($fieldName)) {
            $this->session->set_flashdata('error', strip_tags($this->upload->display_errors()));
            return false;
        }

        $uploadData = $this->upload->data();
        if (empty($uploadData['is_image'])) {
            @unlink($uploadData['full_path']);
            $this->session->set_flashdata('error', 'Invalid image file');
            return false;
        }
        return $this->faceUploadPath . $uploadData['file_name'];
    }

    protected function deriveLocation($latitude, $longitude, $landmark, $createSiteLocation = true)
    {
        $lat = is_numeric($latitude) ? (float) $latitude : null;
        $lng = is_numeric($longitude) ? (float) $longitude : null;
        // Enforce warehouse geofence: each site uses its own location_radius_m (or default 100m).
        if ($lat === null || $lng === null) {
            return '';
        }

        $nearby = $this->nearbySitesWithinRange($lat, $lng);
        if (!empty($nearby) && !empty($nearby[0]['name'])) {
            return (string) $nearby[0]['name'];
        }
        return '';
    }

    /**
     * Ajax endpoint used by capture screen to preview derived location.
     * IMPORTANT: does NOT auto-create site locations.
     */
    public function derive_location_preview()
    {
        if (strtolower($this->input->method()) !== 'post') {
            return $this->jsonResponse('error', 'Invalid request method');
        }

        if ($this->loggedIn && !$this->sma->actionPermissions('create', 'attendance')) {
            return $this->jsonResponse('error', lang('access_denied'));
        }

        $latitude = $this->input->post('latitude', true);
        $longitude = $this->input->post('longitude', true);
        $landmark = $this->input->post('landmark', true);

        $selectedDerivedLocation = trim((string) $this->input->post('selected_derived_location', true));
        $resolution = $this->resolveDerivedLocation($latitude, $longitude, $selectedDerivedLocation);

        if (!$resolution['ok']) {
            return $this->jsonResponse('error', $resolution['message'], array(
                'derived_location' => '',
                'is_new_location' => !empty($resolution['is_new_location']),
                'requires_location_selection' => !empty($resolution['requires_selection']),
                'location_options' => isset($resolution['options']) ? $resolution['options'] : array(),
                'suggested_attendance_type' => 'auto'
            ));
        }

        $suggestedAttendanceType = 'auto';
        $lastActivityType = '';
        $lastActivityAt = '';
        $selectedOutCheckInAt = '';
        $selectedInCheckOutAt = '';
        if ($this->loggedIn) {
            $loggedInUserId = (int) $this->session->userdata('user_id');
            if ($loggedInUserId > 0) {
                $resolvedLocation = isset($resolution['derived_location']) ? (string) $resolution['derived_location'] : '';
                $suggestedAttendanceType = $this->getSuggestedAttendanceTypeByLocation($loggedInUserId, $resolvedLocation);
                $openRow = $this->attendance_model->check_today_open_attendance_by_location($loggedInUserId, $resolvedLocation);
                if ($openRow && !empty($openRow->check_in)) {
                    $ts = strtotime((string) $openRow->check_in);
                    $selectedOutCheckInAt = $ts ? date('Y-m-d H:i:s', $ts) : (string) $openRow->check_in;
                }
                $lastCheckOutRow = $this->attendance_model->get_latest_checkout_by_location($loggedInUserId, $resolvedLocation);
                if ($lastCheckOutRow && !empty($lastCheckOutRow->check_out)) {
                    $ts = strtotime((string) $lastCheckOutRow->check_out);
                    $selectedInCheckOutAt = $ts ? date('Y-m-d H:i:s', $ts) : (string) $lastCheckOutRow->check_out;
                }
                $lastRow = $this->attendance_model->get_today_latest_attendance_by_location($loggedInUserId, $resolvedLocation);
                if ($lastRow) {
                    if (!empty($lastRow->check_out)) {
                        $lastActivityType = 'check_out';
                        $lastActivityAt = (string) $lastRow->check_out;
                    } elseif (!empty($lastRow->check_in)) {
                        $lastActivityType = 'check_in';
                        $lastActivityAt = (string) $lastRow->check_in;
                    }
                }
            }
        }

        return $this->jsonResponse('success', 'OK', array(
            'derived_location' => isset($resolution['derived_location']) ? $resolution['derived_location'] : '',
            'requires_location_selection' => !empty($resolution['requires_selection']),
            'location_options' => isset($resolution['options']) ? $resolution['options'] : array(),
            'suggested_attendance_type' => $suggestedAttendanceType,
            'last_activity_type' => $lastActivityType,
            'last_activity_at' => $lastActivityAt,
            'selected_out_checkin_at' => $selectedOutCheckInAt,
            'selected_in_checkout_at' => $selectedInCheckOutAt
        ));
    }

    protected function getSuggestedAttendanceTypeByLocation($userId, $derivedLocation)
    {
        $userId = (int) $userId;
        if ($userId <= 0) {
            return 'auto';
        }
        $derivedLocation = trim((string) $derivedLocation);
        if ($derivedLocation === '') {
            return 'auto';
        }
        $openRow = $this->attendance_model->check_today_open_attendance_by_location($userId, $derivedLocation);
        return $openRow ? 'check_out' : 'check_in';
    }

    protected function nearbySitesWithinRange($lat, $lng, $maxMeters = null)
    {
        unset($maxMeters);
        if ($this->siteLocationsCache === null) {
            $this->siteLocationsCache = $this->attendance_model->get_site_locations();
        }
        $sites = $this->siteLocationsCache;
        if (empty($sites)) {
            return array();
        }

        $allSitesWithDistance = array();
        $matchedRadii = array();

        // Step 1: Calculate distance to all sites and find which ones the user is actually inside
        foreach ($sites as $site) {
            if (!isset($site->latitude) || !isset($site->longitude)) {
                continue;
            }
            $distance = attendance_haversine_distance_meters($lat, $lng, $site->latitude, $site->longitude);
            $radiusM = Attendance_model::warehouse_geofence_radius_meters($site);
            
            $allSitesWithDistance[] = array(
                'name' => (string) $site->name,
                'distance' => (float) $distance,
                'radius_m' => (int) $radiusM
            );

            // Is the user inside this site's geofence?
            if ($distance <= $radiusM) {
                $matchedRadii[] = (int) $radiusM;
            }
        }

        // If the user is not inside ANY geofence, return empty
        if (empty($matchedRadii)) {
            return array();
        }

        // Step 2: Find the smallest radius among the matched geofences
        // This is the "effective radius" for the user's current location
        $effectiveRadius = min($matchedRadii);

        // Step 3: Return all locations that are within this effective radius
        $nearby = array();
        foreach ($allSitesWithDistance as $siteData) {
            if ($siteData['distance'] <= $effectiveRadius) {
                $nearby[] = $siteData;
            }
        }

        usort($nearby, function ($a, $b) {
            if ($a['distance'] === $b['distance']) {
                return strcmp((string) $a['name'], (string) $b['name']);
            }
            return ($a['distance'] < $b['distance']) ? -1 : 1;
        });

        return $nearby;
    }

    protected function resolveDerivedLocation($latitude, $longitude, $selectedDerivedLocation = '')
    {
        $lat = is_numeric($latitude) ? (float) $latitude : null;
        $lng = is_numeric($longitude) ? (float) $longitude : null;
        if ($lat === null || $lng === null) {
            return array(
                'ok' => false,
                'message' => 'Invalid coordinates',
                'requires_selection' => false,
                'options' => array()
            );
        }

        $nearby = $this->nearbySitesWithinRange($lat, $lng);
        if (empty($nearby)) {
            return array(
                'ok' => true,
                'derived_location' => '',
                'is_new_location' => true,
                'requires_selection' => false,
                'options' => array()
            );
        }

        // De-duplicate by normalized site name so duplicate warehouse rows
        // do not force unnecessary location selection in UI.
        $uniqueByName = array();
        foreach ($nearby as $site) {
            $siteName = trim((string) $site['name']);
            if ($siteName === '') {
                continue;
            }
            $key = $this->normalizeLocationKey($siteName);
            if (!isset($uniqueByName[$key]) || (float) $site['distance'] < (float) $uniqueByName[$key]['distance']) {
                $uniqueByName[$key] = array(
                    'name' => $siteName,
                    'distance' => (float) $site['distance']
                );
            }
        }

        if (empty($uniqueByName)) {
            return array(
                'ok' => true,
                'derived_location' => '',
                'is_new_location' => true,
                'requires_selection' => false,
                'options' => array()
            );
        }

        $uniqueNearby = array_values($uniqueByName);
        usort($uniqueNearby, function ($a, $b) {
            if ($a['distance'] === $b['distance']) {
                return strcmp((string) $a['name'], (string) $b['name']);
            }
            return ($a['distance'] < $b['distance']) ? -1 : 1;
        });

        $options = array();
        foreach ($uniqueNearby as $site) {
            $options[] = array(
                'name' => $site['name'],
                'distance' => round((float) $site['distance'], 2)
            );
        }

        if (count($uniqueNearby) === 1) {
            return array(
                'ok' => true,
                'derived_location' => (string) $uniqueNearby[0]['name'],
                'requires_selection' => false,
                'options' => $options
            );
        }

        $selectedDerivedLocation = $this->normalizeLocationLabel($selectedDerivedLocation);
        if ($selectedDerivedLocation !== '') {
            $selectedKey = $this->normalizeLocationKey($selectedDerivedLocation);
            $selectedLooseKey = $this->normalizeLocationLooseKey($selectedDerivedLocation);
            $fallbackByLooseKey = null;
            foreach ($uniqueNearby as $site) {
                $siteName = (string) $site['name'];
                $siteKey = $this->normalizeLocationKey($siteName);
                if ($siteKey === $selectedKey) {
                    return array(
                        'ok' => true,
                        'derived_location' => $siteName,
                        'requires_selection' => true,
                        'options' => $options
                    );
                }
                if ($fallbackByLooseKey === null && $this->normalizeLocationLooseKey($siteName) === $selectedLooseKey) {
                    $fallbackByLooseKey = $siteName;
                }
            }
            if ($fallbackByLooseKey !== null) {
                return array(
                    'ok' => true,
                    'derived_location' => $fallbackByLooseKey,
                    'requires_selection' => true,
                    'options' => $options
                );
            }
        }

        log_message('debug', 'Attendance location selection mismatch selected="' . $selectedDerivedLocation . '" options=' . json_encode($options));
        return array(
            'ok' => false,
            'message' => 'Multiple nearby locations found. Please select one location.',
            'requires_selection' => true,
            'options' => $options
        );
    }

    protected function normalizeLocationLabel($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }
        // Strip accidental " (12.3m)" suffix if client ever posts display text.
        $value = preg_replace('/\s*\([\d\.\,]+\s*m\)\s*$/iu', '', $value);
        return trim((string) $value);
    }

    protected function normalizeLocationKey($value)
    {
        $value = $this->normalizeLocationLabel($value);
        // Normalize all whitespace and invisible separators.
        $value = preg_replace('/[\x{00A0}\x{200B}-\x{200D}\x{FEFF}]/u', ' ', $value);
        $value = preg_replace('/\s+/u', ' ', $value);
        return strtolower(trim((string) $value));
    }

    protected function normalizeLocationLooseKey($value)
    {
        $value = $this->normalizeLocationKey($value);
        // Extra-tolerant key used only as fallback match.
        $value = preg_replace('/[^a-z0-9]/', '', (string) $value);
        return (string) $value;
    }

    protected function reverseGeocodeOpenStreet($lat, $lng)
    {
        $url = 'https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=' . urlencode($lat) . '&lon=' . urlencode($lng);
        $opts = array(
            'http' => array(
                'method' => 'GET',
                'header' => "User-Agent: ElintOm-Attendance/1.0\r\n",
                'timeout' => 5
            )
        );

        $context = stream_context_create($opts);
        $response = @file_get_contents($url, false, $context);
        if (!$response) {
            return '';
        }

        $json = json_decode($response, true);
        if (!is_array($json)) {
            return '';
        }

        return isset($json['display_name']) ? $json['display_name'] : '';
    }

    protected function jsonResponse($status, $message, $extra = array())
    {
        $response = array(
            'status' => $status,
            'message' => $message
        );
        if (is_array($extra) && !empty($extra)) {
            $response = array_merge($response, $extra);
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
        return;
    }

    protected function prepareLocationText($landmark, $derivedLocation)
    {
        $primary = trim((string) $derivedLocation);
        if ($primary !== '') {
            return $primary;
        }
        return trim((string) $landmark);
    }

    protected function buildCheckInLandmark($locationText, $notes = '')
    {
        $locationText = trim((string) $locationText);
        $notes = trim((string) $notes);
        $baseText = '';
        if ($locationText === '') {
            $baseText = 'Check In : NA';
        } else {
            $baseText = 'Check In : ' . $locationText;
        }
        if ($notes !== '') {
            $baseText .= ' | Notes : ' . $notes;
        }
        return $baseText;
    }

    protected function buildCheckOutLandmark($existingLandmark, $locationText, $notes = '')
    {
        $existingLandmark = trim((string) $existingLandmark);
        $locationText = trim((string) $locationText);
        $notes = trim((string) $notes);
        $checkOutText = 'Check Out : ' . ($locationText !== '' ? $locationText : 'NA');

        if ($existingLandmark === '') {
            $existingLandmark = 'Check In : NA';
        }
        if (strpos($existingLandmark, 'Check In :') === false && strpos($existingLandmark, 'Notes :') === false) {
            $existingLandmark = 'Check In : ' . $existingLandmark;
        }
        if (strpos($existingLandmark, 'Check Out :') === false) {
            $existingLandmark .= ' | ' . $checkOutText;
        }
        if ($notes !== '') {
            if (strpos($existingLandmark, 'Notes :') === false) {
                $existingLandmark .= ' | Notes : ' . $notes;
            } else {
                $existingLandmark = preg_replace('/\|\s*Notes\s*:\s*.*/i', '| Notes : ' . $notes, $existingLandmark);
            }
        }
        return $existingLandmark;
    }

    protected function normalizeDateTimeInput($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        $timestamp = strtotime(str_replace('T', ' ', $value));
        if ($timestamp === false) {
            return null;
        }
        return date('Y-m-d H:i:s', $timestamp);
    }

    protected function isNumericDescriptor(array $descriptor)
    {
        foreach ($descriptor as $value) {
            if (!is_numeric($value)) {
                return false;
            }
        }
        return true;
    }

    protected function isValidCoordinate($value, $isLatitude)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return true;
        }
        if (!is_numeric($value)) {
            return false;
        }
        $floatValue = (float) $value;
        if ($isLatitude) {
            return $floatValue >= -90 && $floatValue <= 90;
        }
        return $floatValue >= -180 && $floatValue <= 180;
    }

    protected function attendanceBuildFilters($scopeUserId = null)
    {
        $scopeUserId = !empty($scopeUserId) ? (int) $scopeUserId : null;
        $requestedUserId = (int) $this->input->get('user_id');
        $requestedDate = trim((string) $this->input->get('attendance_date'));
        $filterDate = '';
        if ($requestedDate !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $requestedDate)) {
            $dateObj = DateTime::createFromFormat('Y-m-d', $requestedDate);
            if ($dateObj && $dateObj->format('Y-m-d') === $requestedDate) {
                $filterDate = $requestedDate;
            }
        }

        $canFilterByUser = ($this->Owner || $this->Admin || $this->attendanceHasViewAllRecordsAccess() || $this->attendanceHasEditAnyRecordAccess()) && $scopeUserId === null;
        if ($canFilterByUser) {
            $filterUserId = $requestedUserId > 0 ? $requestedUserId : null;
        } else {
            $filterUserId = $scopeUserId;
        }

        return array(
            'scope_user_id' => $scopeUserId,
            'filter_user_id' => $filterUserId,
            'filter_date' => $filterDate,
            'can_filter_by_user' => $canFilterByUser
        );
    }

    protected function attendanceBuildReportFilters($scopeUserId = null)
    {
        $scopeUserId = !empty($scopeUserId) ? (int) $scopeUserId : null;
        $requestedUserId = (int) $this->input->get('user_id');

        $requestedStartDate = trim((string) $this->input->get('start_date'));
        $requestedEndDate = trim((string) $this->input->get('end_date'));

        $filterStartDate = '';
        if ($requestedStartDate !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $requestedStartDate)) {
            $dateObj = DateTime::createFromFormat('Y-m-d', $requestedStartDate);
            if ($dateObj && $dateObj->format('Y-m-d') === $requestedStartDate) {
                $filterStartDate = $requestedStartDate;
            }
        }

        $filterEndDate = '';
        if ($requestedEndDate !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $requestedEndDate)) {
            $dateObj = DateTime::createFromFormat('Y-m-d', $requestedEndDate);
            if ($dateObj && $dateObj->format('Y-m-d') === $requestedEndDate) {
                $filterEndDate = $requestedEndDate;
            }
        }

        $canFilterByUser = ($this->Owner || $this->Admin || $this->attendanceHasViewAllRecordsAccess() || $this->attendanceHasEditAnyRecordAccess()) && $scopeUserId === null;
        if ($canFilterByUser) {
            $filterUserId = $requestedUserId > 0 ? $requestedUserId : null;
        } else {
            $filterUserId = $scopeUserId;
        }

        return array(
            'scope_user_id' => $scopeUserId,
            'filter_user_id' => $filterUserId,
            'filter_start_date' => $filterStartDate,
            'filter_end_date' => $filterEndDate,
            'can_filter_by_user' => $canFilterByUser
        );
    }

    protected function attendanceExportRows(array $rows, $title, $filenamePrefix, $exportType)
    {
        $this->load->library('excel');
        $this->excel->setActiveSheetIndex(0);

        $sheet = $this->excel->getActiveSheet();
        $sheet->setTitle('Attendance');
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', $title);
        $sheet->setCellValue('A2', 'Sr. No');
        $sheet->setCellValue('B2', 'User Name');
        $sheet->setCellValue('C2', 'Check In');
        $sheet->setCellValue('D2', 'Check Out');
        $sheet->setCellValue('E2', 'Landmark');
        $sheet->setCellValue('F2', 'Derived Location');
        $sheet->setCellValue('G2', 'Created At');

        $headerStyle = array(
            'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
            'font' => array('bold' => true)
        );
        $sheet->getStyle('A1:G2')->applyFromArray($headerStyle);

        $row = 3;
        $i = 1;
        foreach ($rows as $attendanceRow) {
            $displayName = trim((string) $attendanceRow->first_name . ' ' . (string) $attendanceRow->last_name);
            if ($displayName === '') {
                $displayName = (string) $attendanceRow->phone;
            }
            $sheet->setCellValue('A' . $row, $i++);
            $sheet->setCellValue('B' . $row, $displayName);
            $sheet->setCellValue('C' . $row, (string) $attendanceRow->check_in);
            $sheet->setCellValue('D' . $row, (string) $attendanceRow->check_out);
            $sheet->setCellValue('E' . $row, (string) $attendanceRow->landmark);
            $sheet->setCellValue('F' . $row, (string) $attendanceRow->derived_location);
            $sheet->setCellValue('G' . $row, (string) $attendanceRow->created_at);
            $row++;
        }

        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(28);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(45);
        $sheet->getColumnDimension('F')->setWidth(35);
        $sheet->getColumnDimension('G')->setWidth(20);

        $lastDataRow = max(2, $row - 1);
        $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A2:G' . $lastDataRow)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

        $filename = $filenamePrefix . '_' . date('Y_m_d_H_i_s');
        if ($exportType === 'pdf') {
            $sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
            require_once(APPPATH . 'third_party' . DIRECTORY_SEPARATOR . 'MPDF' . DIRECTORY_SEPARATOR . 'mpdf.php');
            $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
            $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . 'MPDF';
            if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                show_error('PDF renderer is not configured.');
            }
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
            header('Cache-Control: max-age=0');
            $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
            $objWriter->save('php://output');
            exit();
        }

        ob_clean();
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
        $objWriter->save('php://output');
        exit();
    }

    protected function attendanceExportReportRows(array $rows, $title, $filenamePrefix, $exportType)
    {
        $this->load->library('excel');
        $this->excel->setActiveSheetIndex(0);

        $sheet = $this->excel->getActiveSheet();
        $sheet->setTitle('Attendance Report');
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', $title);

        $sheet->setCellValue('A2', 'User Name');
        $sheet->setCellValue('B2', 'Date');
        $sheet->setCellValue('C2', 'Latitude');
        $sheet->setCellValue('D2', 'Longitude');
        $sheet->setCellValue('E2', 'Landmark');
        $sheet->setCellValue('F2', 'Derived Location');
        $sheet->setCellValue('G2', 'Check In');
        $sheet->setCellValue('H2', 'Check Out');

        $headerStyle = array(
            'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
            'font' => array('bold' => true)
        );
        $sheet->getStyle('A1:H2')->applyFromArray($headerStyle);

        $row = 3;
        foreach ($rows as $attendanceRow) {
            $displayName = trim((string) $attendanceRow->first_name . ' ' . (string) $attendanceRow->last_name);
            if ($displayName === '') {
                $displayName = (string) $attendanceRow->phone;
            }

            $sheet->setCellValue('A' . $row, $displayName);
            $sheet->setCellValue('B' . $row, (string) $attendanceRow->created_at);
            $sheet->setCellValue('C' . $row, $attendanceRow->latitude);
            $sheet->setCellValue('D' . $row, $attendanceRow->longitude);
            $sheet->setCellValue('E' . $row, (string) $attendanceRow->landmark);
            $sheet->setCellValue('F' . $row, (string) $attendanceRow->derived_location);
            $sheet->setCellValue('G' . $row, (string) $attendanceRow->check_in);
            $sheet->setCellValue('H' . $row, (string) $attendanceRow->check_out);

            $row++;
        }

        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(12);
        $sheet->getColumnDimension('E')->setWidth(45);
        $sheet->getColumnDimension('F')->setWidth(35);
        $sheet->getColumnDimension('G')->setWidth(20);
        $sheet->getColumnDimension('H')->setWidth(20);

        $lastDataRow = max(2, $row - 1);
        $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A2:H' . $lastDataRow)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

        $filename = $filenamePrefix . '_' . date('Y_m_d_H_i_s');
        if ($exportType === 'pdf') {
            $sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
            require_once(APPPATH . 'third_party' . DIRECTORY_SEPARATOR . 'MPDF' . DIRECTORY_SEPARATOR . 'mpdf.php');
            $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
            $rendererLibraryPath = APPPATH . 'third_party' . DIRECTORY_SEPARATOR . 'MPDF';
            if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
                show_error('PDF renderer is not configured.');
            }
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
            header('Cache-Control: max-age=0');
            $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'PDF');
            $objWriter->save('php://output');
            exit();
        }

        ob_clean();
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
        $objWriter->save('php://output');
        exit();
    }

}

