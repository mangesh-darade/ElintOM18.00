<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class File_manager extends MY_Controller {

    function __construct() {
        parent::__construct();
        
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        
        $this->load->model('file_manager_model');
        $this->upload_path = 'assets/mdata/'.$this->Customer_assets.'/uploads/';
        $this->data['Settings'] = $this->Settings;
        
        // Ensure upload directory exists
        if (!is_dir($this->upload_path)) {
            mkdir($this->upload_path, 0755, true);
        }
        
        // Auto-create pdf, csv, excel folders if they don't exist
        $required_folders = array('pdf', 'csv', 'excel');
        foreach ($required_folders as $folder) {
            $folder_path = $this->upload_path . $folder;
            if (!is_dir($folder_path)) {
                mkdir($folder_path, 0755, true);
            }
        }
    }
    
    /**
     * Send JSON response with CSRF token
     * @param array $data Response data
     */
    private function json_response($data) {
        $data['token'] = $this->security->get_csrf_hash();
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }
    
    /**
     * Find a file in uploads directory and subfolders
     * @param string $filename The filename to search for
     * @return string|false Returns full path or false if not found
     */
    private function findFileInUploads($filename) {
        // Sanitize filename
        $filename = basename($filename);
        
        log_message('debug', 'findFileInUploads - Searching for: ' . $filename);
        log_message('debug', 'findFileInUploads - Upload path: ' . $this->upload_path);
        
        // Check in root upload directory
        $root_file = $this->upload_path . $filename;
        log_message('debug', 'findFileInUploads - Checking root: ' . $root_file);
        
        if (file_exists($root_file)) {
            log_message('debug', 'findFileInUploads - ✓ Found in root!');
            return $root_file;
        }
        
        // Check in subfolders
        if (is_dir($this->upload_path)) {
            $dir = new DirectoryIterator($this->upload_path);
            foreach ($dir as $fileinfo) {
                if (!$fileinfo->isDot() && $fileinfo->isDir()) {
                    $subfolder = $fileinfo->getFilename();
                    
                    // Skip hidden folders and thumbs
                    if (substr($subfolder, 0, 1) === '.' || $subfolder === 'thumbs') {
                        continue;
                    }
                    
                    $subfolder_file = $this->upload_path . $subfolder . '/' . $filename;
                    log_message('debug', 'findFileInUploads - Checking subfolder: ' . $subfolder_file);
                    
                    if (file_exists($subfolder_file)) {
                        log_message('debug', 'findFileInUploads - ✓ Found in subfolder: ' . $subfolder);
                        return $subfolder_file;
                    }
                }
            }
        }
        
        log_message('debug', 'findFileInUploads - ✗ File not found anywhere');
        return false;
    }
    
    /**
     * Validate and sanitize file path to prevent directory traversal attacks
     * @param string $filename The filename to validate
     * @return string|false Returns sanitized path or false if invalid
     */
    private function validateFilePath($filename) {
        // Remove any directory traversal attempts
        $filename = basename($filename);
        
        // Additional security check - remove any remaining slashes
        $filename = str_replace(['/', '\\', '..'], '', $filename);
        
        if (empty($filename)) {
            return false;
        }
        
        $file_path = $this->upload_path . $filename;
        
        // Verify the real path is within upload directory
        $real_path = realpath($file_path);
        $real_upload = realpath($this->upload_path);
        
        if ($real_path === false) {
            // File doesn't exist yet (for upload), so construct expected path
            return $file_path;
        }
        
        if ($real_upload === false || strpos($real_path, $real_upload) !== 0) {
            return false;
        }
        
        return $file_path;
    }

    /**
     * Display the file manager index page
     */
    function index($folder = null) {
        // Allow admin and owner to access, otherwise check for specific permission
        if (!$this->Owner && !$this->Admin) {
            $this->sma->checkPermissions('index', false, 'products');
        }
        
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['message'] = $this->session->flashdata('message');
        $this->data['upload_path'] = $this->upload_path;
        
        // Get current folder
        $current_folder = $folder ? urldecode($folder) : 'all';
        $this->data['current_folder'] = $current_folder;
        
        // Get list of folders
        $folders = $this->file_manager_model->getFolders($this->upload_path);
        $this->data['folders'] = $folders;
        
        // Don't load folder statistics on page load for better performance
        // Statistics will be loaded via AJAX if needed
        $this->data['folder_stats'] = array();
        
        // Breadcrumbs for folder navigation
        $bc = array(
            array('link' => base_url(), 'page' => lang('home')), 
            array('link' => site_url('file_manager'), 'page' => 'File Manager')
        );
        
        if ($current_folder && $current_folder !== 'all' && $current_folder !== 'root') {
            $bc[] = array('link' => '#', 'page' => $current_folder);
        }
        
        $meta = array('page_title' => 'File Manager', 'bc' => $bc);
        $this->page_construct('file_manager/index', $meta, $this->data);
    }
    

    /**
     * Get files for DataTables (Server-side processing)
     */
    function getFiles() {
        // Check if user is logged in
        if (!$this->loggedIn) {
            $draw = $this->input->post('draw');
            $output = array(
                "draw" => $draw ? intval($draw) : 1,
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => array(),
                "error" => "Not logged in"
            );
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($output));
            return;
        }
        
        // Check permissions (skip for admin/owner, they always have access)
        if (!$this->Owner && !$this->Admin) {
            // For regular users, allow access if they're logged in
            // You can add more specific permission checks here if needed
        }
        
        // Get DataTables parameters
        $draw = $this->input->post('draw');
        $start = $this->input->post('start') ? intval($this->input->post('start')) : 0;
        $length = $this->input->post('length') ? intval($this->input->post('length')) : 25;
        
        // Get filter parameters
        $search = $this->input->post('search');
        $search_term = '';
        if (is_array($search) && isset($search['value'])) {
            $search_term = $search['value'];
        }
        
        $file_type = $this->input->post('file_type');
        if (!$file_type) {
            $file_type = 'all';
        }
        
        $folder_filter = $this->input->post('folder_filter');
        if (!$folder_filter) {
            $folder_filter = 'all';
        }
        
        // Debug logging
        log_message('debug', 'File Manager - Folder Filter: ' . $folder_filter);
        log_message('debug', 'File Manager - File Type: ' . $file_type);
        log_message('debug', 'File Manager - Search: ' . $search_term);
        log_message('debug', 'File Manager - Start: ' . $start . ', Length: ' . $length);
        
        // Get ALL files first (we'll paginate in PHP)
        try {
            $all_files = $this->file_manager_model->getFiles($this->upload_path, $search_term, $file_type, $folder_filter);
            $total_records = count($all_files);
            
            // Paginate the results
            $files = array_slice($all_files, $start, $length);
            
        } catch (Exception $e) {
            log_message('error', 'File Manager Error: ' . $e->getMessage());
            $output = array(
                "draw" => $draw ? intval($draw) : 1,
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => array(),
                "error" => "Error loading files: " . $e->getMessage()
            );
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($output));
            return;
        }
        
        $data = array();
        
        foreach ($files as $file) {
            $row = array();
            
            // Icon based on file type (PDF, Excel, CSV only)
            if ($file['extension'] == 'pdf') {
                $row[] = '<i class="fa fa-file-pdf-o fa-2x text-danger"></i>';
            } elseif (in_array($file['extension'], array('xls', 'xlsx'))) {
                $row[] = '<i class="fa fa-file-excel-o fa-2x text-success"></i>';
            } elseif ($file['extension'] == 'csv') {
                $row[] = '<i class="fa fa-file-text-o fa-2x text-info"></i>';
            } else {
                $row[] = '<i class="fa fa-file-o fa-2x text-muted"></i>';
            }
            
            // Folder badge (if any)
            $folder_badge = '';
            if (!empty($file['folder']) && $file['folder'] !== 'root') {
                $folder_badge = ' <span class="label label-info">' . $file['folder'] . '</span>';
            }
            
            // File name - clickable to expand row and show file data
            $row[] = '<a href="#" class="view-file-data" data-file="' . base_url($file['path']) . '" data-type="' . $file['extension'] . '" data-name="' . $file['name'] . '" style="font-weight: 500; color: #337ab7;"><i class="fa fa-file-o"></i> ' . $file['name'] . '</a>' . $folder_badge;
            
            // File type
            $row[] = strtoupper($file['extension']);
            
            // File size
            $row[] = $this->formatBytes($file['size']);
            
            // Upload date
            $row[] = date('Y-m-d H:i:s', $file['date']);
            
            // Actions (no preview/download)
            $actions = '<div class="text-center">';
            
            // Share dropdown
            $actions .= '<div class="btn-group">
                <button type="button" class="btn btn-xs btn-info dropdown-toggle" data-toggle="dropdown" title="Share">
                    <i class="fa fa-share-alt"></i> <span class="caret"></span>
                </button>
                <ul class="dropdown-menu pull-right">
                    <li><a href="#" class="share-whatsapp" data-file="' . base_url($file['path']) . '" data-filename="' . $file['name'] . '"><i class="fa fa-whatsapp text-success"></i> WhatsApp</a></li>
                    <li><a href="#" class="share-sms" data-file="' . base_url($file['path']) . '" data-filename="' . $file['name'] . '"><i class="fa fa-commenting text-info"></i> SMS</a></li>
                    <li><a href="#" class="share-email" data-file="' . base_url($file['path']) . '" data-filename="' . $file['name'] . '"><i class="fa fa-envelope text-primary"></i> Email</a></li>
                </ul>
            </div> ';
            
            // Delete button
            $actions .= '<a href="#" class="btn btn-xs btn-danger delete-file tip" title="Delete" data-file="' . $file['name'] . '"><i class="fa fa-trash-o"></i></a>';
            
            $actions .= '</div>';
            
            $row[] = $actions;
            
            $data[] = $row;
        }
        
        $output = array(
            "draw" => $draw ? intval($draw) : 1,
            "recordsTotal" => $total_records,
            "recordsFiltered" => $total_records,
            "data" => $data,
            "token" => $this->security->get_csrf_hash() // Return updated CSRF token
        );
        
        log_message('debug', 'File Manager Response - Total: ' . $total_records . ', Returned: ' . count($data));
        
        $this->output
            ->set_content_type('application/json')
            ->set_header('X-Content-Type-Options: nosniff')
            ->set_output(json_encode($output));
    }


  
    

    /**
     * Download file
     */
    function download($filename = null) {
        if (!$this->loggedIn) {
            $this->session->set_flashdata('error', 'Please login to download files.');
            redirect('login');
        }
        
        if (!$filename) {
            $filename = $this->input->get('file');
        }
        
        if (!$filename) {
            $this->session->set_flashdata('error', 'File not specified.');
            redirect('file_manager');
        }
        
        $filename = urldecode($filename);
        
        // First try to find the file in uploads directory (including subfolders)
        $file_path = $this->findFileInUploads($filename);
        
        if ($file_path === false) {
            // If not found, try validateFilePath as backup
            $file_path = $this->validateFilePath($filename);
        }
        
        if ($file_path === false || !file_exists($file_path)) {
            $this->session->set_flashdata('error', 'File not found or access denied. Filename: ' . $filename);
            redirect('file_manager');
        }
        
        $this->load->helper('download');
        force_download($file_path, NULL);
    }

    /**
     * Delete file
     */
    function delete() {
        if (!$this->loggedIn) {
            $this->json_response(array('status' => 'error', 'message' => 'Not logged in.'));
            return;
        }
        
        $filename = $this->input->post('file');
        
        if (!$filename) {
            $this->json_response(array('status' => 'error', 'message' => 'File not specified.'));
            return;
        }
        
        // First try to find the file in uploads directory (including subfolders)
        $file_path = $this->findFileInUploads($filename);
        
        if ($file_path === false) {
            // If not found, try validateFilePath as backup
            $file_path = $this->validateFilePath($filename);
        }
        
        if ($file_path === false) {
            $this->json_response(array('status' => 'error', 'message' => 'Invalid file path. File: ' . $filename));
            return;
        }
        
        if (!file_exists($file_path)) {
            $this->json_response(array('status' => 'error', 'message' => 'File not found. Path: ' . $file_path));
            return;
        }
        
        if (unlink($file_path)) {
            // Also delete thumbnail if exists
            $thumb_path = str_replace('/uploads/', '/uploads/thumbs/', $this->upload_path) . basename($filename);
            if (file_exists($thumb_path)) {
                @unlink($thumb_path);
            }
            
            $this->json_response(array('status' => 'success', 'message' => 'File deleted successfully.'));
        } else {
            $this->json_response(array('status' => 'error', 'message' => 'Failed to delete file.'));
        }
    }
    
    /**
     * Share file via SMS
     */
    function shareSMS() {
        if (!$this->loggedIn) {
            echo json_encode(array('status' => 'error', 'message' => 'Not logged in.'));
            return;
        }
        
        $phone = $this->input->post('phone');
        $message = $this->input->post('message');
        $file_url = $this->input->post('file_url');
        $filename = $this->input->post('file');
        
        if (!$phone) {
            echo json_encode(array('status' => 'error', 'message' => 'Phone number is required.'));
            return;
        }
        
        // Compose message with short link similar to invoice SMS
        if (!$message) { $message = 'Please check file: '; }
        if (!$file_url && $filename) {
            $file_url = $this->generateShareUrl($filename);
        }
        if ($file_url) {
            $message .= $file_url;
        }
        
        // Load existing SMS helper (used by invoices)
        $this->load->helper('sms_helper');
        
        try {
            if (function_exists('send_sms')) {
                $result = send_sms($phone, $message);
                if ($result) {
                    echo json_encode(array('status' => 'success', 'message' => 'SMS sent successfully to ' . $phone));
                } else {
                    echo json_encode(array('status' => 'error', 'message' => 'Failed to send SMS.'));
                }
            } else {
                echo json_encode(array('status' => 'error', 'message' => 'SMS gateway not configured.'));
            }
        } catch (Exception $e) {
            echo json_encode(array('status' => 'error', 'message' => 'Error sending SMS: ' . $e->getMessage()));
        }
    }
    
    /**
     * Share file via Email
     */
    function shareEmail() {
        if (!$this->loggedIn) {
            echo json_encode(array('status' => 'error', 'message' => 'Not logged in.'));
            return;
        }
        
        $email = $this->input->post('email');
        $subject = $this->input->post('subject');
        $message = $this->input->post('message');
        $file_url = $this->input->post('file_url');
        $filename = $this->input->post('filename');
        
        if (!$email || !$subject || !$message) {
            echo json_encode(array('status' => 'error', 'message' => 'Email, subject, and message are required.'));
            return;
        }
        
        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(array('status' => 'error', 'message' => 'Invalid email address.'));
            return;
        }
        
        // Load email library
        $this->load->library('email');
        
        // Configure email
        $config = array(
            'protocol' => $this->Settings->protocol ? $this->Settings->protocol : 'mail',
            'mailpath' => $this->Settings->mailpath ? $this->Settings->mailpath : '/usr/sbin/sendmail',
            'charset' => 'utf-8',
            'wordwrap' => TRUE,
            'mailtype' => 'html'
        );
        
        if ($this->Settings->smtp_host) {
            $config['smtp_host'] = $this->Settings->smtp_host;
            $config['smtp_user'] = $this->Settings->smtp_user;
            $config['smtp_pass'] = $this->Settings->smtp_pass;
            $config['smtp_port'] = $this->Settings->smtp_port;
            $config['smtp_crypto'] = $this->Settings->smtp_crypto;
        }
        
        $this->email->initialize($config);
        
        $this->email->from($this->Settings->default_email ? $this->Settings->default_email : 'noreply@yourdomain.com', 
                          $this->Settings->site_name ? $this->Settings->site_name : 'File Manager');
        $this->email->to($email);
        $this->email->subject($subject);
        
        // Create HTML message
        $html_message = '<html><body>';
        $html_message .= '<p>' . nl2br($message) . '</p>';
        $html_message .= '<hr>';
        $html_message .= '<p><strong>File:</strong> ' . $filename . '</p>';
        $html_message .= '<p><a href="' . $file_url . '" style="display: inline-block; padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px;">Download File</a></p>';
        $html_message .= '</body></html>';
        
        $this->email->message($html_message);
        
        if ($this->email->send()) {
            echo json_encode(array(
                'status' => 'success',
                'message' => 'Email sent successfully to ' . $email
            ));
        } else {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Failed to send email. ' . $this->email->print_debugger()
            ));
        }
    }

    
    /**
     * Get Excel file content for preview (XLS, XLSX)
     * Converts Excel to JSON grid format
     */
    function getExcelContent() {
        if (!$this->loggedIn) {
            $this->json_response(array('status' => 'error', 'message' => 'Not logged in.'));
            return;
        }
        
        $filename = $this->input->post('file');
        
        if (!$filename) {
            $this->json_response(array('status' => 'error', 'message' => 'File not specified.'));
            return;
        }
        
        // Find file in uploads directory
        $file_path = $this->findFileInUploads($filename);
        
        if ($file_path === false || !file_exists($file_path)) {
            $this->json_response(array('status' => 'error', 'message' => 'File not found: ' . $filename));
            return;
        }
        
        // Check file extension
        $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        if (!in_array($ext, array('xls', 'xlsx'))) {
            $this->json_response(array('status' => 'error', 'message' => 'Not an Excel file.'));
            return;
        }
        
        // Load PHPExcel library
        require_once APPPATH . 'third_party/PHPExcel/PHPExcel.php';
        
        try {
            // Load Excel file
            $objPHPExcel = PHPExcel_IOFactory::load($file_path);
            $worksheet = $objPHPExcel->getActiveSheet();
            
            // Get highest row and column
            $highestRow = $worksheet->getHighestRow();
            $highestColumn = $worksheet->getHighestColumn();
            $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
            
            // Limit rows for preview
            $maxRows = 500;
            $rowsToRead = min($highestRow, $maxRows);
            
            $data = array();
            
            // Read data row by row
            for ($row = 1; $row <= $rowsToRead; $row++) {
                $rowData = array();
                for ($col = 0; $col < $highestColumnIndex; $col++) {
                    $cell = $worksheet->getCellByColumnAndRow($col, $row);
                    $value = $cell->getValue();
                    
                    // Format the value
                    if ($value instanceof PHPExcel_RichText) {
                        $value = $value->getPlainText();
                    }
                    
                    // Format dates
                    if (PHPExcel_Shared_Date::isDateTime($cell)) {
                        $value = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($value));
                    }
                    
                    $rowData[] = $value;
                }
                $data[] = $rowData;
            }
            
            $this->json_response(array(
                'status' => 'success',
                'data' => $data,
                'rows' => $rowsToRead,
                'total_rows' => $highestRow,
                'columns' => $highestColumnIndex,
                'limited' => $highestRow > $maxRows
            ));
            
        } catch (Exception $e) {
            log_message('error', 'Excel preview error: ' . $e->getMessage());
            $this->json_response(array(
                'status' => 'error',
                'message' => 'Failed to read Excel file: ' . $e->getMessage()
            ));
        }
    }
    
    /**
     * Get file content for preview (CSV, TXT)
     */
    function getFileContent() {
        if (!$this->loggedIn) {
            $this->json_response(array('status' => 'error', 'message' => 'Not logged in.'));
            return;
        }
        
        $filename = $this->input->post('file');
        $fileUrl = $this->input->post('file_url');
        
        if (!$filename && !$fileUrl) {
            $this->json_response(array('status' => 'error', 'message' => 'File not specified.'));
            return;
        }
        
        // If filename provided, use it
        if ($filename) {
            // Security: Validate file path
            $file_path = $this->validateFilePath($filename);
            
            if ($file_path === false || !file_exists($file_path)) {
                $this->json_response(array('status' => 'error', 'message' => 'File not found or access denied. Path: ' . $filename));
                return;
            }
        } else if ($fileUrl) {
            // Extract file path from URL
            $file_path = str_replace(base_url(), '', $fileUrl);
            
            // Log for debugging
            log_message('debug', 'File Manager - getFileContent - URL: ' . $fileUrl);
            log_message('debug', 'File Manager - getFileContent - Extracted path: ' . $file_path);
            
            // Check if extracted path exists
            if (!file_exists($file_path)) {
                log_message('debug', 'File Manager - File not found at extracted path, searching...');
                
                // Get just the filename
                $just_filename = basename($fileUrl);
                log_message('debug', 'File Manager - Searching for filename: ' . $just_filename);
                
                // Search in upload directory and subfolders
                $found_file = $this->findFileInUploads($just_filename);
                
                if ($found_file) {
                    log_message('debug', 'File Manager - Found file at: ' . $found_file);
                    $file_path = $found_file;
                } else {
                    log_message('error', 'File Manager - File not found anywhere. Filename: ' . $just_filename);
                    $this->json_response(array(
                        'status' => 'error', 
                        'message' => 'File not found in uploads directory.',
                        'debug' => array(
                            'url' => $fileUrl,
                            'extracted_path' => str_replace(base_url(), '', $fileUrl),
                            'filename' => $just_filename,
                            'upload_path' => $this->upload_path
                        )
                    ));
                    return;
                }
            } else {
                log_message('debug', 'File Manager - File found at extracted path: ' . $file_path);
            }
        } else {
            $this->json_response(array('status' => 'error', 'message' => 'File not specified.'));
            return;
        }
        
        // Check file size (limit to 5MB for preview)
        $filesize = filesize($file_path);
        if ($filesize > 5242880) { // 5MB
            $this->json_response(array('status' => 'error', 'message' => 'File too large to preview (max 5MB). Please download instead.'));
            return;
        }
        
        // Get file extension
        $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        
        // Read file content
        $content = file_get_contents($file_path);
        
        if ($content === false) {
            $this->json_response(array('status' => 'error', 'message' => 'Failed to read file.'));
            return;
        }
        
        $this->json_response(array(
            'status' => 'success',
            'content' => $content,
            'filename' => basename($file_path),
            'extension' => $ext,
            'size' => $filesize
        ));
    }
    
    /**
     * Get file info (AJAX)
     */
    function getFileInfo() {
        if (!$this->loggedIn) {
            $this->json_response(array('status' => 'error', 'message' => 'Not logged in.'));
            return;
        }
        
        $filename = $this->input->post('file');
        
        if (!$filename) {
            $this->json_response(array('status' => 'error', 'message' => 'File not specified.'));
            return;
        }
        
        // First try to find the file in uploads directory (including subfolders)
        $file_path = $this->findFileInUploads($filename);
        
        if ($file_path === false) {
            // If not found, try validateFilePath as backup
            $file_path = $this->validateFilePath($filename);
        }
        
        if ($file_path === false || !file_exists($file_path)) {
            $this->json_response(array('status' => 'error', 'message' => 'File not found or access denied. File: ' . $filename));
            return;
        }
        
        $file_info = pathinfo($file_path);
        
        $info = array(
            'name' => basename($filename),
            'size' => $this->formatBytes(filesize($file_path)),
            'type' => strtoupper($file_info['extension']),
            'modified' => date('Y-m-d H:i:s', filemtime($file_path)),
            'path' => base_url($file_path)
        );
        
        $this->json_response(array('status' => 'success', 'data' => $info));
    }
    
    /**
     * Get file data for inline display (new approach)
     * Returns file content/data to be displayed in expanded row
     */
    function getFileData() {
        if (!$this->loggedIn) {
            $this->json_response(array('status' => 'error', 'message' => 'Not logged in.'));
            return;
        }
        
        $filename = $this->input->post('file');
        $fileUrl = $this->input->post('file_url');
        $fileType = $this->input->post('file_type');
        
        if (!$filename && !$fileUrl) {
            $this->json_response(array('status' => 'error', 'message' => 'File not specified.'));
            return;
        }
        
        // Get filename from URL if needed
        if (!$filename && $fileUrl) {
            $filename = basename($fileUrl);
        }
        
        // Find file in uploads directory
        $file_path = $this->findFileInUploads($filename);
        
        if ($file_path === false || !file_exists($file_path)) {
            $this->json_response(array('status' => 'error', 'message' => 'File not found: ' . $filename));
            return;
        }
        
        $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        $filesize = filesize($file_path);
        
        // Handle only PDF, Excel, CSV file types
        if ($ext === 'pdf') {
            // PDF files - read file and return as base64 (so viewer does not depend on a URL)
            if ($filesize > 10485760) { // 10MB safety limit for inline transfer
                $this->json_response(array('status' => 'error', 'message' => 'PDF too large to render inline (max 10MB).'));
                return;
            }
            $binary = @file_get_contents($file_path);
            if ($binary === false) {
                $this->json_response(array('status' => 'error', 'message' => 'Failed to read PDF file.'));
                return;
            }
            $base64 = base64_encode($binary);
            $this->json_response(array(
                'status' => 'success',
                'type' => 'pdf',
                'base64' => $base64,
                'data_url' => 'data:application/pdf;base64,' . $base64,
                'filename' => basename($filename),
                'extension' => $ext,
                'size' => $this->formatBytes($filesize)
            ));
        } elseif ($ext === 'csv') {
            // CSV files - read and parse
            if ($filesize > 5242880) { // 5MB limit
                $this->json_response(array('status' => 'error', 'message' => 'File too large to display (max 5MB).'));
                return;
            }
            
            $content = file_get_contents($file_path);
            $this->json_response(array(
                'status' => 'success',
                'type' => 'csv',
                'content' => $content,
                'filename' => basename($filename),
                'extension' => $ext,
                'size' => $this->formatBytes($filesize)
            ));
        } elseif (in_array($ext, array('xls', 'xlsx'))) {
            // Excel files - use existing getExcelContent logic
            require_once APPPATH . 'third_party/PHPExcel/PHPExcel.php';
            
            try {
                $objPHPExcel = PHPExcel_IOFactory::load($file_path);
                $worksheet = $objPHPExcel->getActiveSheet();
                
                $highestRow = $worksheet->getHighestRow();
                $highestColumn = $worksheet->getHighestColumn();
                $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
                
                // Limit rows for display
                $maxRows = 100; // Reduced for inline display
                $rowsToRead = min($highestRow, $maxRows);
                
                $data = array();
                
                for ($row = 1; $row <= $rowsToRead; $row++) {
                    $rowData = array();
                    for ($col = 0; $col < $highestColumnIndex; $col++) {
                        $cell = $worksheet->getCellByColumnAndRow($col, $row);
                        $value = $cell->getValue();
                        
                        if ($value instanceof PHPExcel_RichText) {
                            $value = $value->getPlainText();
                        }
                        
                        if (PHPExcel_Shared_Date::isDateTime($cell)) {
                            $value = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($value));
                        }
                        
                        $rowData[] = $value;
                    }
                    $data[] = $rowData;
                }
                
                $this->json_response(array(
                    'status' => 'success',
                    'type' => 'excel',
                    'data' => $data,
                    'rows' => $rowsToRead,
                    'total_rows' => $highestRow,
                    'columns' => $highestColumnIndex,
                    'limited' => $highestRow > $maxRows,
                    'filename' => basename($filename),
                    'extension' => $ext,
                    'size' => $this->formatBytes($filesize)
                ));
            } catch (Exception $e) {
                $this->json_response(array('status' => 'error', 'message' => 'Failed to read Excel file: ' . $e->getMessage()));
            }
        } else {
            // Unsupported file type
            $this->json_response(array(
                'status' => 'error',
                'message' => 'File type not supported for inline display. Please download the file.',
                'type' => 'unsupported'
            ));
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    // Generate ultra-short link (token mapped on disk) - internal use
    private function generateShareUrl($filename) {
        $map_dir = FCPATH . 'files/share_center/';
        if (!is_dir($map_dir)) { @mkdir($map_dir, 0755, true); }
        $token = substr(bin2hex(function_exists('openssl_random_pseudo_bytes') ? openssl_random_pseudo_bytes(4) : md5(uniqid('', true))), 0, 8);
        $path = $this->findFileInUploads($filename);
        if ($path === false) { $path = $this->upload_path . basename($filename); }
        @file_put_contents($map_dir . $token . '.txt', $path);
        return site_url('file_manager/t/' . $token);
    }

    /**
     * Create short share link for a file (no DB required)
     * Token contains filename and optional expiry, signed with HMAC
     */
    public function createShareLink() {
        if (!$this->loggedIn) {
            $this->json_response(array('status' => 'error', 'message' => 'Not logged in.'));
            return;
        }

        $filename = $this->input->post('file');
        if (!$filename) {
            $this->json_response(array('status' => 'error', 'message' => 'File not specified.'));
            return;
        }

        // Find file
        $file_path = $this->findFileInUploads($filename);
        if ($file_path === false || !file_exists($file_path)) {
            $this->json_response(array('status' => 'error', 'message' => 'File not found.'));
            return;
        }

        // Create tiny token mapping and return tiny URL
        $map_dir = FCPATH . 'files/share_center/';
        if (!is_dir($map_dir)) { @mkdir($map_dir, 0755, true); }
        $token = substr(bin2hex(function_exists('openssl_random_pseudo_bytes') ? openssl_random_pseudo_bytes(4) : md5(uniqid('', true))), 0, 8);
        @file_put_contents($map_dir . $token . '.txt', $file_path);
        $url = site_url('file_manager/t/' . $token);
        $this->json_response(array('status' => 'success', 'url' => $url));
    }

    /**
     * Resolve short link and serve or redirect to the file
     * /file_manager/s/{token}
     */
    public function s($token = '') {
        if (!$token) {
            show_404();
        }

        $parts = explode('.', $token);
        if (count($parts) !== 2) {
            show_404();
        }
        list($payload_b64, $sig) = $parts;
        $secret = isset($this->config->config['encryption_key']) ? $this->config->config['encryption_key'] : 'sma-secret';
        $expected = hash_hmac('sha256', $payload_b64, $secret);
        if (!hash_equals($expected, $sig)) {
            show_404();
        }

        $payload_json = base64_decode(strtr($payload_b64, '-_', '+/'));
        $data = json_decode($payload_json, true);
        if (!$data || !isset($data['f'])) {
            show_404();
        }
        // No expiry check (never expire)

        $filename = $data['f'];
        $file_path = $this->findFileInUploads($filename);
        if ($file_path === false || !file_exists($file_path)) {
            show_404();
        }

        // For simplicity, redirect to public URL
        redirect(base_url($file_path));
    }

    // Tiny resolver for ultra-short tokens - streams the file (avoids Apache perm issues)
    public function t($token = '') {
        if (!$token) { show_404(); }
        $map_dir = FCPATH . 'files/share_center/';
        $map_file = $map_dir . basename($token) . '.txt';
        if (!file_exists($map_file)) { show_404(); }
        $stored = trim(@file_get_contents($map_file));
        if (!$stored) { show_404(); }

        // Resolve absolute path
        $path = $stored;
        if (!file_exists($path)) {
            $abs = FCPATH . ltrim($stored, '/');
            if (file_exists($abs)) { $path = $abs; }
        }
        if (!file_exists($path)) { show_404(); }

        // Determine mime type
        $mime = 'application/octet-stream';
        if (function_exists('mime_content_type')) {
            $det = @mime_content_type($path);
            if ($det) { $mime = $det; }
        }
        $filename = basename($path);
        $size = filesize($path);

        // Clean output buffers
        while (ob_get_level() > 0) { @ob_end_clean(); }

        // Send headers and stream file as download
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . $size);
        header('Accept-Ranges: bytes');
        header('Cache-Control: public, max-age=86400');

        $fp = fopen($path, 'rb');
        if ($fp) {
            while (!feof($fp)) {
                echo fread($fp, 8192);
                flush();
            }
            fclose($fp);
            exit;
        }
        show_404();
    }
}

