<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Service_requests extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            redirect(site_url('login'));
        }
        $this->load->library(array('form_validation'));
        $this->load->helper(array('form', 'url'));
        $this->load->model('service_requests_model');
        $this->load->model('products_model');
    }

    public function service_site_report($tab = 'customer')
    {
        $this->load->library('user_agent');
        $isMobile = $this->agent->is_mobile();

        // Common data for both views
        $this->data['tab'] = $tab;
        $this->data['customers'] = $this->service_requests_model->getCustomers();
        $this->data['service_types'] = $this->service_requests_model->getServiceTypes();
        $this->data['equipments'] = $this->service_requests_model->getEquipmentsForServiceSiteReport();
        $this->data['pm_parameters'] = $this->service_requests_model->getPmLogParameters();
        $this->data['spare_parts'] = $this->products_model->getSpareParts();

        if ($isMobile) {
            // Mobile Specific Logic
            if (!$this->session->userdata('ssr_mobile_context_ref')) {
                $this->session->set_userdata('ssr_mobile_context_ref', 'SSR-' . time() . '-' . mt_rand(1000, 9999));
            }
            $this->data['service_site_otp_context_ref'] = $this->session->userdata('ssr_mobile_context_ref');

            $draft = $this->session->userdata('ssr_mobile_draft') ?: array();
            $this->data['draft'] = $draft;

            // Fetch customer_locations if customer already selected in draft
            $this->data['customer_locations'] = array();
            if (!empty($draft['customer_id'])) {
                $this->data['customer_locations'] = $this->service_requests_model->getCustomerLocationsByCompanyId($draft['customer_id']);
            }

            // Fetch equipment tags if customer / location selected in draft
            $this->data['equipment_tags'] = array();
            if (!empty($draft['customer_id']) && !empty($draft['customer_location_id'])) {
                $actualLocationId = $draft['customer_location_id'];
                if (!empty($this->data['customer_locations'])) {
                    foreach ($this->data['customer_locations'] as $loc) {
                        if (isset($loc['id']) && $loc['id'] == $draft['customer_location_id']) {
                            if (!empty($loc['location_id'])) {
                                $actualLocationId = $loc['location_id'];
                            }
                            break;
                        }
                    }
                }
                $this->data['actualLocationId'] = $actualLocationId;
                $this->data['equipment_tags'] = $this->service_requests_model->getEquipmentTagsForServiceSiteReport($draft['customer_id'], $actualLocationId);
            }

            // Fetch PM Log data if customer is selected
            $this->data['pm_log_rows'] = array();
            if (!empty($draft['customer_id'])) {
                $payload = $this->service_requests_model->get_latest_service_site_report_payload_by_customer($draft['customer_id']);
                if (is_array($payload) && isset($payload['pm_log_grid']) && $payload['pm_log_grid'] !== '') {
                    $decoded = json_decode((string) $payload['pm_log_grid'], true);
                    if (is_array($decoded)) {
                        $this->data['pm_log_rows'] = $decoded;
                    }
                }
            }
            
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $bc = array(
                array('link' => base_url(), 'page' => lang('home')),
                array('link' => site_url('reports'), 'page' => lang('reports')),
                array('link' => '#', 'page' => 'Service Site Report')
            );
            $meta = array('page_title' => 'Customers & Service', 'bc' => $bc);
            $this->page_construct('service_requests/service_site_report_mobileview', $meta, $this->data);
        } else {
            // Desktop Specific Logic
            $this->data['service_site_otp_context_ref'] = 'SSR-' . time() . '-' . mt_rand(1000, 9999);
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $bc = array(
                array('link' => base_url(), 'page' => lang('home')),
                array('link' => site_url('reports'), 'page' => lang('reports')),
                array('link' => '#', 'page' => 'Service Site Report')
            );
            $meta = array('page_title' => 'Service Site Report', 'bc' => $bc);
            $this->page_construct('service_requests/service_site_report', $meta, $this->data);
        }
    }

    // Keep service_site_report_mobile as a redirect for backward compatibility
    public function service_site_report_mobile($tab = 'customer')
    {
        redirect('service_requests/service_site_report/' . $tab);
    }

    public function save_mobile_tab_data()
    {
        $active_tab = $this->input->post('active_tab');
        $draft = $this->session->userdata('ssr_mobile_draft') ?: array();
        
        // Keep raw POST to preserve JSON fields like pm_log_grid_json.
        $post = $this->input->post(NULL, TRUE);
        unset($post['active_tab']);
        // Avoid wiping PM grid when saving from non-PM tabs.
        if ($active_tab !== 'pm_log' && isset($post['pm_log_grid_json'])) {
            unset($post['pm_log_grid_json']);
        }
        
        $new_draft = array_merge($draft, $post);
        $this->session->set_userdata('ssr_mobile_draft', $new_draft);

        // Routing logic to next tab
        $tabs = array('customer', 'equipment', 'work', 'pm_log', 'report_trigger', 'signatures');
        $index = array_search($active_tab, $tabs);
        
        if ($index !== FALSE && $this->input->post('action') == 'next' && $index < count($tabs) - 1) {
            $next_tab = $tabs[$index + 1];
            redirect('service_requests/service_site_report/' . $next_tab);
        } elseif ($this->input->post('action') == 'submit') {
            // Merge draft into POST so form_validation and buildPayload can see it
            foreach ($new_draft as $k => $v) {
                if (!isset($_POST[$k])) {
                    $_POST[$k] = $v;
                }
            }
            return $this->save_service_site_report();
        } else {
            // Default to staying on current tab or going back to active
            redirect('service_requests/service_site_report/' . $active_tab);
        }
    }

    public function save_mobile_tab_data_json()
    {
        $active_tab = $this->input->post('active_tab');
        $draft = $this->session->userdata('ssr_mobile_draft') ?: array();
        // Keep raw POST to preserve JSON fields like pm_log_grid_json.
        $post = $this->input->post(NULL, FALSE);
        unset($post['active_tab']);
        // Avoid wiping PM grid when saving from non-PM tabs.
        if ($active_tab !== 'pm_log' && isset($post['pm_log_grid_json'])) {
            unset($post['pm_log_grid_json']);
        }
        
        $new_draft = array_merge($draft, $post);
        $this->session->set_userdata('ssr_mobile_draft', $new_draft);
        
        return $this->json(array(
            'status'   => 'success',
            'csrfHash' => $this->security->get_csrf_hash(),
        ));
    }

    public function save_service_site_report()
    {
        $this->form_validation->set_rules('customer_id', 'Customer', 'required');
        $this->form_validation->set_rules('service_report_no', 'Service Report No', 'required');
        $this->form_validation->set_rules('service_date', 'Date', 'required');
        $this->form_validation->set_rules('report_sent_confirmed', 'Report Send Confirmation', 'required');
        $this->form_validation->set_rules('otp_context_ref', 'OTP Context', 'required');
        $this->form_validation->set_rules('otp_challenge_id', 'OTP Challenge', 'required');
        $this->form_validation->set_rules('otp_verification_token', 'OTP Verification Token', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            return $this->input->post('is_mobile') ? $this->service_site_report_mobile('signatures') : $this->service_site_report();
        }

        $data = $this->buildServiceSiteReportPayloadFromPost();
        $otpContextRef = trim((string) $this->input->post('otp_context_ref'));
        if (!$this->hasReportSentForContext($otpContextRef)) {
            $this->session->set_flashdata('error', 'Please send report to customer before OTP verification and submit');
            return $this->input->post('is_mobile') ? $this->service_site_report_mobile('signatures') : $this->service_site_report();
        }

        $otpChallengeId = (int) $this->input->post('otp_challenge_id');
        $otpVerificationToken = trim((string) $this->input->post('otp_verification_token'));
        $otpOk = $this->service_requests_model->validate_verified_service_report_otp($otpChallengeId, $otpVerificationToken, $otpContextRef);
        if (!$otpOk) {
            $this->session->set_flashdata('error', 'OTP verification is required before saving report');
            return $this->input->post('is_mobile') ? $this->service_site_report_mobile('signatures') : $this->service_site_report();
        }

        $savedLogId = $this->service_requests_model->save_service_site_report($data);
        if ($savedLogId) {
            $cachedStatus = $this->getReportSentDispatchStatus($otpContextRef);
            if (is_array($cachedStatus) && !empty($cachedStatus)) {
                $cachedStatus['submitted_at'] = date('Y-m-d H:i:s');
                $this->service_requests_model->update_service_site_report_dispatch_status((int) $savedLogId, $cachedStatus);
            }
            $this->session->set_flashdata('message', 'Service site report submitted successfully');
            $this->clearReportSentForContext($otpContextRef);
            if ($this->input->post('is_mobile')) {
                // Clear mobile draft upon successful submission
                $this->session->unset_userdata('ssr_mobile_draft');
                redirect('service_requests/service_site_report/customer');
            }
        } else {
            $this->session->set_flashdata('error', 'Failed to save service site report');
            if ($this->input->post('is_mobile')) {
                return $this->service_site_report_mobile('signatures');
            }
        }

        redirect('service_requests/service_site_report');
    }

    /**
     * Logo-style signature upload endpoint.
     * JS sends the canvas as a real file blob → CI Upload saves JPG → returns filename.
     * The filename is then stored in the hidden field and submitted with the form.
     */
    public function upload_signature()
    {
        if (!$this->input->is_ajax_request()) {
            return $this->json(array('status' => 'error', 'message' => 'Invalid request'));
        }

        $uploadPath = 'assets/uploads/signatures/';
        $fullPath   = FCPATH . $uploadPath;

        if (!is_dir($fullPath)) {
            @mkdir($fullPath, 0777, true);
        }

        $this->load->library('upload');
        $config = array(
            'upload_path'   => $fullPath,
            'allowed_types' => 'jpg|jpeg|png|gif|webp',
            'max_size'      => 2048,
            'encrypt_name'  => true,
        );
        $this->upload->initialize($config);

        if (!$this->upload->do_upload('signature_file')) {
            return $this->json(array('status' => 'error', 'message' => strip_tags($this->upload->display_errors())));
        }

        $uploadData = $this->upload->data();
        $fileName   = $uploadData['file_name'];

        return $this->json(array(
            'status'    => 'success',
            'filename'  => $fileName,
            'csrfHash'  => $this->security->get_csrf_hash(),
        ));
    }

    public function get_customer_pm_log_grid()
    {
        if (!$this->input->is_ajax_request()) {
            return $this->json(array('status' => 'error', 'message' => 'Invalid request'));
        }
        $customerId = trim((string) $this->input->post('customer_id', true));
        if ($customerId === '') {
            return $this->json(array('status' => 'error', 'message' => 'Customer is required'));
        }
        $payload = $this->service_requests_model->get_latest_service_site_report_payload_by_customer($customerId);
        $gridRows = array();
        if (is_array($payload) && isset($payload['pm_log_grid']) && $payload['pm_log_grid'] !== '') {
            $decoded = json_decode((string) $payload['pm_log_grid'], true);
            if (is_array($decoded)) {
                $gridRows = $decoded;
            }
        }
        return $this->json(array('status' => 'success', 'message' => 'OK', 'grid_rows' => $gridRows));
    }

    public function get_customer_locations()
    {
        if (!$this->input->is_ajax_request()) {
            return $this->json(array('status' => 'error', 'message' => 'Invalid request'));
        }
        $companyId = (int) $this->input->post('company_id', true);
        if ($companyId <= 0) {
            $companyId = (int) $this->input->post('customer_id', true);
        }
        if ($companyId <= 0) {
            return $this->json(array('status' => 'error', 'message' => 'Company is required'));
        }
        $locations = $this->service_requests_model->getCustomerLocationsByCompanyId($companyId);
        return $this->json(array('status' => 'success', 'message' => 'OK', 'locations' => $locations));
    }

    public function get_next_service_report_number_json()
    {
        if (!$this->input->is_ajax_request()) {
            return $this->json(array('status' => 'error', 'message' => 'Invalid request'));
        }
        $companyId = (int) $this->input->post('company_id', true);
        $locationId = (int) $this->input->post('location_id', true);
        if ($companyId <= 0) {
            return $this->json(array('status' => 'error', 'message' => 'Company is required'));
        }
        $nextNumber = $this->service_requests_model->get_next_service_report_number($companyId, $locationId);
        return $this->json(array('status' => 'success', 'message' => 'OK', 'next_number' => $nextNumber));
    }

    public function get_equipment_details_for_service_site_report()
    {
        if (!$this->input->is_ajax_request()) {
            return $this->json(array('status' => 'error', 'message' => 'Invalid request'));
        }
        $equipmentTag = trim((string) $this->input->post('equipment_tag', true));
        $companyId = (int) $this->input->post('company_id', true);
        if ($companyId <= 0) {
            $companyId = (int) $this->input->post('customer_id', true);
        }
        $locationId = (int) $this->input->post('location_id', true);
        $details = $this->service_requests_model->getEquipmentDetailsForServiceSiteReport($equipmentTag, $companyId, $locationId);
        return $this->json(array('status' => 'success', 'message' => 'OK', 'details' => $details));
    }

    public function get_equipment_tags_for_service_site_report()
    {
        if (!$this->input->is_ajax_request()) {
            return $this->json(array('status' => 'error', 'message' => 'Invalid request'));
        }
        $companyId = (int) $this->input->post('company_id', true);
        if ($companyId <= 0) {
            $companyId = (int) $this->input->post('customer_id', true);
        }
        $locationId = (int) $this->input->post('location_id', true);
        if ($companyId <= 0 || $locationId <= 0) {
            return $this->json(array('status' => 'success', 'message' => 'OK', 'tags' => array()));
        }
        $tags = $this->service_requests_model->getEquipmentTagsForServiceSiteReport($companyId, $locationId);
        return $this->json(array('status' => 'success', 'message' => 'OK', 'tags' => $tags));
    }


    public function save_pm_log_section()
    {
        if (!$this->input->is_ajax_request()) {
            return $this->json(array('status' => 'error', 'message' => 'Invalid request'));
        }

        $this->form_validation->set_rules('customer_id', 'Customer', 'required');
        $this->form_validation->set_rules('customer_location_id', 'Job Site Name', 'required');
        $this->form_validation->set_rules('eqpt_tag_no', 'Equipment Tag', 'required');
        $this->form_validation->set_rules('service_date', 'Date', 'required');

        if ($this->form_validation->run() == false) {
            return $this->json(array('status' => 'error', 'message' => strip_tags(validation_errors())));
        }

        $data = $this->buildServiceSiteReportPayloadFromPost();
        $ok = $this->service_requests_model->save_pm_log_only($data);
        if (!$ok) {
            return $this->json(array('status' => 'error', 'message' => 'Failed to save PM log'));
        }
        return $this->json(array('status' => 'success', 'message' => 'PM log saved successfully'));
    }

    public function send_service_site_report_otp()
    {
        if (!$this->input->is_ajax_request()) {
            return $this->json(array('status' => 'error', 'message' => 'Invalid request'));
        }
        $channel = strtolower(trim((string) $this->input->post('channel', true)));
        $destination = trim((string) $this->input->post('destination', true));
        $customerId = (int) $this->input->post('customer_id', true);
        $contextRef = trim((string) $this->input->post('context_ref', true));
        if ($contextRef === '') {
            $contextRef = 'SSR-' . time() . '-' . mt_rand(1000, 9999);
        }
        if (!$this->hasReportSentForContext($contextRef)) {
            return $this->json(array('status' => 'error', 'message' => 'Send report to customer first, then request OTP'));
        }
        if (!in_array($channel, array('sms', 'email', 'whatsapp'), true)) {
            return $this->json(array('status' => 'error', 'message' => 'Invalid OTP channel'));
        }
        if ($destination === '') {
            return $this->json(array('status' => 'error', 'message' => 'Destination is required'));
        }
        $customerName = '';
        if ($customerId > 0) {
            $customer = $this->service_requests_model->get_customer_company_by_id($customerId);
            if ($customer && isset($customer->name)) {
                $customerName = (string) $customer->name;
            }
        }
        $challenge = $this->service_requests_model->create_service_report_otp_challenge(
            $contextRef,
            $channel,
            $destination,
            (int) $this->session->userdata('user_id'),
            300,
            5,
            45
        );
        if ($challenge['status'] !== 'success') {
            return $this->json(array(
                'status' => $challenge['status'],
                'message' => isset($challenge['message']) ? $challenge['message'] : 'Unable to generate OTP',
                'retry_after_seconds' => isset($challenge['retry_after_seconds']) ? (int) $challenge['retry_after_seconds'] : 0,
                'context_ref' => $contextRef,
            ));
        }
        $dispatch = $this->dispatchServiceReportOtp($channel, $destination, $challenge['otp'], $customerName);
        if (!$dispatch['ok']) {
            return $this->json(array('status' => 'error', 'message' => $dispatch['message'], 'context_ref' => $contextRef));
        }
        return $this->json(array(
            'status' => 'success',
            'message' => 'OTP sent successfully',
            'challenge_id' => (int) $challenge['challenge_id'],
            'context_ref' => $contextRef,
            'expires_in_seconds' => (int) $challenge['expires_in_seconds'],
        ));
    }

    public function send_service_site_report_to_customer()
    {
        if (!$this->input->is_ajax_request()) {
            return $this->json(array('status' => 'error', 'message' => 'Invalid request'));
        }
        
        if ($this->input->post('is_mobile')) {
            $draft = $this->session->userdata('ssr_mobile_draft') ?: array();
            foreach ($draft as $k => $v) {
                if (!isset($_POST[$k]) || $_POST[$k] === '') {
                    $_POST[$k] = $v;
                }
            }
        }

        $this->form_validation->set_rules('customer_id', 'Customer', 'required');
        $this->form_validation->set_rules('service_report_no', 'Service Report No', 'required');
        $this->form_validation->set_rules('service_date', 'Date', 'required');
        $this->form_validation->set_rules('otp_context_ref', 'OTP Context', 'required');
        if ($this->form_validation->run() == false) {
            return $this->json(array('status' => 'error', 'message' => strip_tags(validation_errors())));
        }

        $data = $this->buildServiceSiteReportPayloadFromPost();
        $this->service_requests_model->save_pm_log_only($data);
        
        $contextRef = trim((string) $this->input->post('otp_context_ref', true));
        $customer = $this->service_requests_model->get_customer_company_by_id($data['customer_id']);
        if (!$customer) {
            return $this->json(array('status' => 'error', 'message' => 'Customer not found'));
        }
        $dispatch = $this->dispatchServiceSiteReportToCustomer($data, $customer);
        $pdfGenerated = isset($dispatch['status']['pdf']) && $dispatch['status']['pdf'] === 'generated';
        if (!(bool) $dispatch['sent_any'] && !$pdfGenerated) {
            return $this->json(array(
                'status' => 'error',
                'message' => 'Report could not be delivered to customer',
                'dispatch_status' => $dispatch['status'],
                'dispatch_message' => implode(' | ', $dispatch['messages']),
                'context_ref' => $contextRef,
            ));
        }
        $statusData = $dispatch['status'];
        $statusData['sent_at'] = date('Y-m-d H:i:s');
        $this->markReportSentForContext($contextRef, $statusData);
        $successMessage = 'Report sent to customer successfully. Now send OTP.';
        if (!(bool) $dispatch['sent_any'] && $pdfGenerated) {
            $successMessage = 'Report generated successfully. Delivery channel not available, you can continue with OTP.';
        }
        return $this->json(array(
            'status' => 'success',
            'message' => $successMessage,
            'dispatch_status' => $statusData,
            'dispatch_message' => implode(' | ', $dispatch['messages']),
            'context_ref' => $contextRef,
        ));
    }

    public function verify_service_site_report_otp()
    {
        if (!$this->input->is_ajax_request()) {
            return $this->json(array('status' => 'error', 'message' => 'Invalid request'));
        }
        
        if ($this->input->post('is_mobile')) {
            $draft = $this->session->userdata('ssr_mobile_draft') ?: array();
            foreach ($draft as $k => $v) {
                if (!isset($_POST[$k]) || $_POST[$k] === '') {
                    $_POST[$k] = $v;
                }
            }
        }

        $challengeId = (int) $this->input->post('challenge_id', true);
        $otpCode = trim((string) $this->input->post('otp_code', true));
        $contextRef = trim((string) $this->input->post('context_ref', true));
        $channel = strtolower(trim((string) $this->input->post('channel', true)));
        $res = $this->service_requests_model->verify_service_report_otp($challengeId, $otpCode, $contextRef);
        
        if ($res['status'] !== 'success') {
            return $this->json(array('status' => 'error', 'message' => isset($res['message']) ? $res['message'] : 'OTP verification failed'));
        }

        $data = $this->buildServiceSiteReportPayloadFromPost();
        $this->service_requests_model->save_pm_log_only($data);

        return $this->json(array(
            'status' => 'success',
            'message' => 'OTP verified',
            'verification_token' => $res['verification_token'],
            'challenge_id' => $challengeId,
            'context_ref' => $contextRef,
            'channel' => $channel,
        ));
    }

    private function json($payload)
    {
        if (!is_array($payload)) {
            $payload = array();
        }
        $payload['csrfHash'] = $this->security->get_csrf_hash();
        return $this->output->set_content_type('application/json')->set_output(json_encode($payload));
    }

    private function dispatchServiceReportOtp($channel, $destination, $otp, $customerName = '')
    {
        $otp = trim((string) $otp);
        $siteName = isset($this->Settings->site_name) ? $this->Settings->site_name : 'Service Portal';
        $customerLabel = trim((string) $customerName) !== '' ? trim((string) $customerName) : 'Customer';
        if ($channel === 'sms') {
            $msg = 'Your OTP for service report verification is ' . $otp . '.';
            $smsResponse = $this->sma->SendSMS($destination, $msg, 'ESHOP_SIGNUP_OTP');
            if (stripos((string) $smsResponse, 'error') !== false) {
                return array('ok' => false, 'message' => 'Failed to send OTP on SMS');
            }
            return array('ok' => true, 'message' => 'SMS sent');
        }
        if ($channel === 'email') {
            $subject = 'OTP for Service Site Report';
            $body = '<p>Dear ' . htmlspecialchars($customerLabel, ENT_QUOTES, 'UTF-8') . ',</p>'
                . '<p>Your OTP is: <strong>' . htmlspecialchars($otp, ENT_QUOTES, 'UTF-8') . '</strong></p>'
                . '<p>This OTP is valid for 5 minutes.</p>'
                . '<p>Regards,<br>' . htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') . '</p>';
            $ok = $this->sma->send_email($destination, $subject, $body);
            return array('ok' => (bool) $ok, 'message' => $ok ? 'Email sent' : 'Failed to send OTP on email');
        }
        $waMessage = 'Your OTP for service report verification is ' . $otp . '. Valid for 5 minutes.';
        $waResponse = $this->sma->send_whatsapp_raw($destination, $waMessage);
        $ok = is_array($waResponse) && isset($waResponse['status']) && $waResponse['status'] === 'success';
        return array('ok' => $ok, 'message' => $ok ? 'WhatsApp sent' : 'Failed to send OTP on WhatsApp');
    }

    private function buildServiceSiteReportPayloadFromPost()
    {
        $serviceDateRaw = trim((string) $this->input->post('service_date'));
        $serviceDateDb = $serviceDateRaw;
        if ($serviceDateRaw !== '') {
            $dt = DateTime::createFromFormat('d/m/Y', $serviceDateRaw);
            if ($dt instanceof DateTime) {
                $serviceDateDb = $dt->format('Y-m-d');
            }
        }
        
        $pmJson = trim((string) $this->input->post('pm_log_grid_json'));
        if ($pmJson === '' && $this->input->post('pm') !== null) {
            $pmArray = array();
            foreach ((array) $this->input->post('pm') as $section => $params) {
                foreach ((array) $params as $parameter => $ckts) {
                    $pmArray[] = array(
                        'section' => $section,
                        'parameter' => $parameter,
                        'ckt_01' => isset($ckts['ckt_01']) ? trim((string) $ckts['ckt_01']) : '',
                        'ckt_02' => isset($ckts['ckt_02']) ? trim((string) $ckts['ckt_02']) : '',
                        'ckt_03' => isset($ckts['ckt_03']) ? trim((string) $ckts['ckt_03']) : '',
                        'ckt_04' => isset($ckts['ckt_04']) ? trim((string) $ckts['ckt_04']) : '',
                    );
                }
            }
            $pmJson = json_encode($pmArray);
        }

        return array(
            'customer_id' => $this->input->post('customer_id'),
            'service_report_no' => $this->input->post('service_report_no'),
            'service_date' => $serviceDateDb,
            'service_type' => $this->input->post('service_type'),
            'job_site_name' => $this->input->post('job_site_name'),
            'customer_location_id' => $this->input->post('customer_location_id'),
            'job_site_address' => $this->input->post('job_site_address'),
            'eqpt_tag_no' => $this->input->post('eqpt_tag_no'),
            'model_no' => $this->input->post('model_no'),
            'serial_no' => $this->input->post('serial_no'),
            'activity' => $this->input->post('activity'),
            'action_list' => $this->input->post('action_list'),
            'visit_date_1' => $this->input->post('visit_date_1'),
            'visit_engineer_name_1' => $this->input->post('visit_engineer_name_1'),
            'visit_engineer_id_1' => $this->input->post('visit_engineer_id_1'),
            'visit_time_in_1' => $this->input->post('visit_time_in_1'),
            'visit_time_out_1' => $this->input->post('visit_time_out_1'),
            'visit_travel_hours_1' => $this->input->post('visit_travel_hours_1'),
            'work_order_status' => $this->input->post('work_order_status'),
            'job_completed' => $this->input->post('job_completed'),
            'quotation_required' => $this->input->post('quotation_required'),
            'used_spare_parts' => is_array($this->input->post('used_spare_parts')) ? implode(', ', $this->input->post('used_spare_parts')) : $this->input->post('used_spare_parts'),
            'required_spare_parts' => is_array($this->input->post('required_spare_parts')) ? implode(', ', $this->input->post('required_spare_parts')) : $this->input->post('required_spare_parts'),
            'quotation_description' => $this->input->post('quotation_description'),
            'engineer_name' => $this->input->post('engineer_name'),
            'engineer_signature' => $this->input->post('engineer_signature_data'),
            'engineer_remarks' => $this->input->post('engineer_remarks'),
            'customer_signature_name' => $this->input->post('customer_signature_name'),
            'customer_signature' => $this->input->post('customer_signature_data'),
            'customer_remark' => $this->input->post('customer_remark'),
            'trane_chiller_log' => $this->input->post('trane_chiller_log'),
            'pm_log_grid' => $pmJson,
            'otp_verified_channel' => $this->input->post('otp_verified_channel'),
            'created_by' => $this->session->userdata('user_id'),
        );
    }

    private function dispatchServiceSiteReportToCustomer(array $data, $customer)
    {
        $dispatchMessages = array();
        $dispatchStatus = array();
        $sentAny = false;
        $pdfPath = $this->generateServiceSiteReportPdf($data, $customer);
        if ($pdfPath) {
            if (!empty($customer->email)) {
                $emailSubject = 'Service Site Report - ' . (string) $data['service_report_no'];
                $emailBody = '<p>Dear ' . htmlspecialchars($customer->name, ENT_QUOTES, 'UTF-8') . ',</p>'
                    . '<p>Please find attached Service Site Report <strong>' . htmlspecialchars((string) $data['service_report_no'], ENT_QUOTES, 'UTF-8') . '</strong>.</p>'
                    . '<p>Regards,<br>' . htmlspecialchars($this->Settings->site_name, ENT_QUOTES, 'UTF-8') . '</p>';
                $emailSent = $this->sma->send_email($customer->email, $emailSubject, $emailBody, null, null, $pdfPath);
                $dispatchMessages[] = $emailSent ? 'Email sent' : 'Email failed';
                $dispatchStatus['email'] = $emailSent ? 'sent' : 'failed';
                $sentAny = $sentAny || $emailSent;
            }
            if (!empty($customer->phone)) {
                $shareUrl = $this->createFileShareUrl($pdfPath);
                $waMessage = 'Service report ' . (string) $data['service_report_no'] . ' is ready.';
                if ($shareUrl) {
                    $waMessage .= "\n" . $shareUrl;
                }
                $waResult = $this->sma->send_whatsapp_raw($customer->phone, $waMessage);
                $waOk = is_array($waResult) && isset($waResult['status']) && $waResult['status'] === 'success';
                $dispatchMessages[] = $waOk ? 'WhatsApp sent' : 'WhatsApp failed';
                $dispatchStatus['whatsapp'] = $waOk ? 'sent' : 'failed';
                $dispatchStatus['whatsapp_share_url'] = $shareUrl;
                $sentAny = $sentAny || $waOk;
            }
            $absolutePath = FCPATH . ltrim($pdfPath, '/');
            if (is_file($absolutePath)) {
                @unlink($absolutePath);
            }
            $dispatchStatus['pdf'] = 'generated';
        } else {
            $dispatchMessages[] = 'Report file generation failed';
            $dispatchStatus['pdf'] = 'failed';
        }
        return array('sent_any' => $sentAny, 'messages' => $dispatchMessages, 'status' => $dispatchStatus);
    }

    private function markReportSentForContext($contextRef, array $dispatchStatus)
    {
        $all = $this->session->userdata('service_requests_report_sent_refs');
        if (!is_array($all))
            $all = array();
        $all[$contextRef] = array('time' => time(), 'dispatch_status' => $dispatchStatus);
        $this->session->set_userdata('service_requests_report_sent_refs', $all);
    }

    private function hasReportSentForContext($contextRef)
    {
        $all = $this->session->userdata('service_requests_report_sent_refs');
        return trim((string) $contextRef) !== '' && is_array($all) && isset($all[$contextRef]);
    }

    private function getReportSentDispatchStatus($contextRef)
    {
        $all = $this->session->userdata('service_requests_report_sent_refs');
        if (!is_array($all) || !isset($all[$contextRef]['dispatch_status'])) {
            return array();
        }
        return (array) $all[$contextRef]['dispatch_status'];
    }

    private function clearReportSentForContext($contextRef)
    {
        $all = $this->session->userdata('service_requests_report_sent_refs');
        if (is_array($all) && isset($all[$contextRef])) {
            unset($all[$contextRef]);
            $this->session->set_userdata('service_requests_report_sent_refs', $all);
        }
    }

    private function generateServiceSiteReportPdf(array $payload, $customer = null)
    {
        $this->data['report'] = $payload;
        $this->data['customer'] = $customer;
        $html = $this->load->view($this->theme . 'service_requests/service_site_report_pdf', $this->data, true);
        if (!$this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }
        $safeRef = preg_replace('/[^A-Za-z0-9\-_]/', '_', (string) $payload['service_report_no']);
        $fileName = 'service_site_report_' . ($safeRef ?: time()) . '.pdf';
        return $this->sma->generate_pdf($html, $fileName, 'S');
    }

    private function createFileShareUrl($relativePath)
    {
        $filename = basename((string) $relativePath);
        if ($filename === '') {
            return null;
        }
        $payload = array('f' => $filename);
        $payloadB64 = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');
        $secret = isset($this->config->config['encryption_key']) ? $this->config->config['encryption_key'] : 'sma-secret';
        $sig = hash_hmac('sha256', $payloadB64, $secret);
        return site_url('file_manager/s/' . $payloadB64 . '.' . $sig);
    }
    public function get_brands() {
        $brands = $this->db->select('id, name')->get('brands')->result_array();
        return $this->json(array('status' => 'success', 'data' => $brands));
    }

    public function get_unmapped_equipment_products() {
        $customerId = (int) $this->input->get('customer_id');
        if ($customerId <= 0) {
            return $this->json(array('status' => 'error', 'message' => 'Invalid customer ID'));
        }

        $this->db->like('name', 'Equipment', 'both');
        $this->db->or_like('code', 'Equipment', 'both');
        $cat = $this->db->get('categories')->row();
        
        $categoryId = $cat ? $cat->id : null;
        if (!$categoryId) {
            return $this->json(array('status' => 'success', 'data' => array()));
        }

        $this->db->select('id, name, code, brand');
        $this->db->from('products');
        $this->db->where('category_id', $categoryId);
        $products = $this->db->get()->result_array();

        return $this->json(array('status' => 'success', 'data' => $products));
    }

    public function add_new_equipment() {
        if (!$this->input->is_ajax_request()) {
            return $this->json(array('status' => 'error', 'message' => 'Invalid request'));
        }

        $customerId = (int) $this->input->post('customer_id');
        $locationId = (int) $this->input->post('location_id');
        $productId = (int) $this->input->post('product_id'); 

        $productName = trim((string)$this->input->post('product_name'));
        $brandIdOrName = trim((string)$this->input->post('brand'));
        $modelNo = trim((string)$this->input->post('model_no'));
        $serialNo = trim((string)$this->input->post('serial_no'));

        // Prevent double entry within a short timeframe
        $existing = $this->db->get_where('equipmentdetails', array(
            'customer_id' => $customerId,
            'eqpt_no' => $productName,
            'model_no' => $modelNo,
            'serial_no' => $serialNo,
            'is_active' => 1
        ))->row();

        if ($existing) {
            return $this->json(array('status' => 'success', 'message' => 'Equipment already exists', 'equipment' => $existing));
        }

        if ($customerId <= 0) {
            return $this->json(array('status' => 'error', 'message' => 'Customer is required'));
        }

        if ($productId > 0) {
            // Mapping existing product
            $prod = $this->db->get_where('products', array('id' => $productId))->row();
            if (!$prod) {
                return $this->json(array('status' => 'error', 'message' => 'Product not found'));
            }
            $productName = $prod->name;
        } else {
            // Creating new product
            if (empty($productName)) {
                return $this->json(array('status' => 'error', 'message' => 'Product Name is required'));
            }

            $this->db->like('name', 'Equipment', 'both');
            $this->db->or_like('code', 'Equipment', 'both');
            $cat = $this->db->get('categories')->row();
            $categoryId = 0;
            if ($cat) {
                $categoryId = $cat->id;
            } else {
                $this->db->insert('categories', array('code' => 'EQP', 'name' => 'Equipment'));
                $categoryId = $this->db->insert_id();
            }

            $brandId = 0;
            if (!empty($brandIdOrName)) {
                if (is_numeric($brandIdOrName)) {
                    $brandId = (int)$brandIdOrName;
                } else {
                    $b = $this->db->get_where('brands', array('name' => $brandIdOrName))->row();
                    if ($b) {
                        $brandId = $b->id;
                    } else {
                        $this->db->insert('brands', array('name' => $brandIdOrName, 'code' => strtoupper(substr($brandIdOrName, 0, 3))));
                        $brandId = $this->db->insert_id();
                    }
                }
            }

            // Fetch first tax rate
            $tax_rate_id = null;
            $tr = $this->db->order_by('id', 'ASC')->limit(1)->get('tax_rates')->row();
            if ($tr) {
                $tax_rate_id = $tr->id;
            }

            $code = time() . mt_rand(10, 99); // Removed 'EQP'
            $prodData = array(
                'name' => $productName,
                'code' => $code,
                'category_id' => $categoryId,
                'type' => 'standard',
                'brand' => $brandId,
                'cost' => 0,
                'price' => 0,
                'mrp' => 0,
                'unit' => 1,
                'sale_unit' => 1,
                'purchase_unit' => 1,
                'flag_visible' => 1,
                'tax_rate' => $tax_rate_id,
                'is_active' => 1
            );
            $this->db->insert('products', $prodData);
            $productId = $this->db->insert_id();
        }

        // Map to customer
        $eqData = array(
            'customer_id' => $customerId,
            'location_id' => $locationId > 0 ? $locationId : null,
            'product_id' => $productId,
            'equipment_id' => $productId, // Set equipment_id same as product_id
            'eqpt_no' => $productName,
            'model_no' => $modelNo,
            'serial_no' => $serialNo,
            'is_active' => 1
        );
        $this->db->insert('equipmentdetails', $eqData);

        return $this->json(array(
            'status' => 'success', 
            'message' => 'Equipment added successfully',
            'equipment' => array(
                'tag_no' => $productName,
                'model_no' => $modelNo,
                'serial_no' => $serialNo
            )
        ));
    }
}
