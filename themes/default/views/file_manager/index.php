<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap.min.css"/>

<style>
    .upload-area {
        border: 3px dashed #ccc;
        border-radius: 8px;
        padding: 40px;
        text-align: center;
        background-color: #f9f9f9;
        margin-bottom: 20px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .upload-area:hover, .upload-area.dragover {
        border-color: #5bc0de;
        background-color: #e7f5f8;
    }
    .upload-area i {
        font-size: 48px;
        color: #ccc;
        margin-bottom: 15px;
    }
    .folder-card {
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 15px;
        margin-bottom: 15px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .folder-card:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border-color: #5bc0de;
    }
    .folder-card i {
        font-size: 32px;
        color: #f0ad4e;
    }
    .folder-badge {
        font-size: 12px;
        padding: 2px 8px;
    }
    
    /* File data modal styles */
    #fileDataModal .modal-dialog {
        width: 90%;
        max-width: 1200px;
    }
    
    #fileDataModal .modal-body { position: relative; }
    #file-data-content {
        max-height: 600px;
        overflow-y: auto;
    }
    /* Floating close button overlay (above PDF toolbars) */
    #pdf-floating-close {
        position: fixed;
        top: 70px; /* below modal header */
        right: 30px;
        z-index: 9999; /* above PDF viewer */
        opacity: 0.9;
    }
    
    #file-data-content iframe {
        border-radius: 4px;
    }
    
    /* Fullscreen modal styles */
    #fileDataModal.fullscreen-modal {
        padding: 0 !important;
    }
    
    #fileDataModal.fullscreen-modal .modal-dialog {
        width: 100%;
        height: 100%;
        margin: 0;
        max-width: none;
    }
    
    #fileDataModal.fullscreen-modal .modal-content {
        height: 100%;
        border: 0;
        border-radius: 0;
    }
    
    #fileDataModal.fullscreen-modal .modal-body {
        overflow-y: auto;
    }
    
    #fileDataModal.fullscreen-modal #file-data-content {
        max-height: calc(100vh - 120px);
    }
    
    #fileDataModal.fullscreen-modal #file-data-content iframe {
        height: calc(100vh - 180px) !important;
    }
    
    @media (max-width: 768px) {
        #fileDataModal .modal-dialog {
            width: 95%;
            margin: 10px auto;
        }
        
        #file-data-content {
            max-height: 400px;
        }
        
        #fullscreen-btn {
            font-size: 11px;
            padding: 4px 8px;
        }
        
        #header-close-btn {
            font-size: 11px;
            padding: 4px 8px;
        }
    }
</style>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa fa-folder-open"></i> File Manager</h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right" role="menu">
                        <li>
                            <a href="#" id="refresh-files">
                                <i class="fa fa-refresh"></i> Refresh
                            </a>
                        </li>
                        
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <?php if ($error) { ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <?= $error ?>
                    </div>
                <?php } ?>
                
                <?php if ($message) { ?>
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <?= $message ?>
                    </div>
                <?php } ?>

                <!-- Upload Area -->
                <div class="upload-area" id="uploadArea" style="display: none;">
                    <i class="fa fa-cloud-upload"></i>
                    <h4>Drag & Drop Files Here</h4>
                    <p>or click to browse files</p>
                    <input type="file" id="fileInput" multiple style="display: none;">
                    <button type="button" class="btn btn-primary" id="browseBtn" style="margin-top: 10px;">
                        <i class="fa fa-folder-open"></i> Browse Files
                    </button>
                </div>

                <!-- Filters Row -->
                <div class="row" style="margin-bottom: 15px;" style="display: none;">
                    <div class="col-sm-4" style="display: none;">
                        <div class="form-group">
                            <label>Filter by Folder:</label>
                            <select id="folder-filter" class="form-control">
                                <option value="all">All Folders</option>
                                <option value="root">Root Only</option>
                                <?php foreach ($folders as $folder): ?>
                                    <option value="<?= $folder ?>" <?= ($current_folder == $folder ? 'selected' : '') ?>>
                                        <?= $folder ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-4" style="display: none;">
                        <div class="form-group">
                            <label>Filter by Type:</label>
                            <select id="file-type-filter" class="form-control">
                                <option value="all">All Files</option>
                                <option value="pdf">PDF Files</option>
                                <option value="excel">Excel Files (XLS, XLSX)</option>
                                <option value="csv">CSV Files</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-4" style="display: none;">
                        <div class="form-group">
                            <label>&nbsp;</label><br>
                            <button class="btn btn-info btn-block" id="apply-filters">
                                <i class="fa fa-filter"></i> Apply Filters
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Folders Quick Access -->
                <?php if (!empty($folders)): ?>
                <div class="panel panel-default" style="margin-bottom: 20px;">
                    <div class="panel-heading">
                        <h4 class="panel-title"><i class="fa fa-folder"></i> Quick Access - Folders</h4>
                </div>
                    <div class="panel-body">
                        <div class="row">
                            <?php foreach ($folders as $folder): ?>
                                <div class="col-md-2 col-sm-3 col-xs-6">
                                    <div class="folder-card text-center" data-folder="<?= $folder ?>">
                                        <i class="fa fa-folder"></i>
                                        <p style="margin-top: 8px; margin-bottom: 0px;"><strong><?= $folder ?></strong></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Files Table -->
                <div class="table-responsive">
                    <table id="FilesTable" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th style="width: 60px;">Preview</th>
                                <th>File Name</th>
                                <th style="width: 80px;">Type</th>
                                <th style="width: 100px;">Size</th>
                                <th style="width: 150px;">Upload Date</th>
                                <th style="width: 180px;" class="text-center">Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- File Data Modal -->
<div class="modal fade" id="fileDataModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" title="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <button type="button" class="btn btn-sm btn-primary" id="fullscreen-btn" style="float: right; margin-right: 35px;" title="Toggle Fullscreen">
                    <i class="fa fa-expand"></i> Fullscreen
                </button>
                <button type="button" class="btn btn-sm btn-danger" id="print-btn" style="float: right; margin-right: 10px; display: none;" title="Print">
                    <i class="fa fa-print"></i> Print
                </button>
                <div class="btn-group" style="float: right; margin-right: 10px;">
                    <button type="button" class="btn btn-sm btn-info dropdown-toggle" data-toggle="dropdown" title="Share">
                        <i class="fa fa-share-alt"></i> Share <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu pull-right">
                        <li><a href="#" class="share-whatsapp" id="modal-share-whatsapp" data-file="" data-filename=""><i class="fa fa-whatsapp text-success"></i> WhatsApp</a></li>
                        <li><a href="#" class="share-sms" id="modal-share-sms" data-file="" data-filename=""><i class="fa fa-commenting text-info"></i> SMS</a></li>
                        <li><a href="#" class="share-email" id="modal-share-email" data-file="" data-filename=""><i class="fa fa-envelope text-primary"></i> Email</a></li>
                    </ul>
            </div>
               
                <h4 class="modal-title"><i class="fa fa-file"></i> <span id="modal-file-name"></span></h4>
            </div>
            <div class="modal-body" id="file-data-content" style="min-height: 300px;">
                <p style="text-align: center;"><i class="fa fa-spinner fa-spin fa-2x"></i><br>Loading file data...</p>
            </div>
            <!-- floating close button over content (shown for PDFs) -->
            <button type="button" class="btn btn-default btn-xs" id="pdf-floating-close" title="Close" style="display:none;">
                <i class="fa fa-times"></i>
            </button>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Confirm Delete</h4>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this file?</p>
                <p><strong id="delete-file-name"></strong></p>
                <p class="text-danger"><i class="fa fa-warning"></i> This action cannot be undone!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirm-delete-btn">
                    <i class="fa fa-trash-o"></i> Delete File
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Create Folder Modal removed -->

<!-- WhatsApp Share Modal -->
<div class="modal fade" id="whatsappModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><i class="fa fa-whatsapp text-success"></i> Share via WhatsApp</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Phone Number</label>
                    <div class="row">
                        <div class="col-xs-3">
                            <input type="text" class="form-control" id="whatsapp-country-code" placeholder="+91" value="+91">
                            <small class="help-block">Code</small>
                        </div>
                        <div class="col-xs-9">
                            <input type="text" class="form-control" id="whatsapp-phone" placeholder="0000000000">
                            <small class="help-block">Phone Number</small>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Message (Optional)</label>
                    <textarea class="form-control" id="whatsapp-message" rows="3"></textarea>
                </div>
                <div class="alert alert-info">
                    <strong>File:</strong> <span id="whatsapp-filename"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="send-whatsapp-btn">
                    <i class="fa fa-whatsapp"></i> Send
                </button>
            </div>
        </div>
    </div>
</div>

<!-- SMS Share Modal -->
<div class="modal fade" id="smsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><i class="fa fa-commenting text-info"></i> Share via SMS</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" class="form-control" id="sms-phone" placeholder="Enter phone number">
                </div>
                <div class="alert alert-info">
                    <strong>File:</strong> <span id="sms-filename"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-info" id="send-sms-btn">
                    <i class="fa fa-commenting"></i> Send SMS
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Email Share Modal -->
<div class="modal fade" id="emailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><i class="fa fa-envelope text-primary"></i> Share via Email</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" class="form-control" id="email-address" placeholder="Enter email address">
                </div>
                <div class="form-group">
                    <label>Subject</label>
                    <input type="text" class="form-control" id="email-subject" value="File Share">
                </div>
                <div class="form-group">
                    <label>Message</label>
                    <textarea class="form-control" id="email-message" rows="4"></textarea>
                </div>
                <div class="alert alert-info">
                    <strong>File:</strong> <span id="email-filename"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="send-email-btn">
                    <i class="fa fa-envelope"></i> Send Email
                </button>
            </div>
        </div>
    </div>
</div>

<!-- DataTables JS -->
<script type="text/javascript" src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap.min.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        var fileToDelete = '';
        var currentFileUrl = '';
        var currentFileName = '';
        var currentFolder = '<?= $current_folder ?>';
        
        // Get CSRF token
        var csrfName = '<?= $this->security->get_csrf_token_name() ?>';
        var csrfHash = '<?= $this->security->get_csrf_hash() ?>';
        
        // Setup AJAX to send CSRF token with every request
        $.ajaxSetup({
            data: function() {
                var data = {};
                data[csrfName] = csrfHash;
                return data;
            },
            beforeSend: function(xhr, settings) {
                if (settings.type === 'POST') {
                    // Update CSRF hash from response if available
                    if (typeof csrfHash !== 'undefined') {
                        if (settings.data && typeof settings.data === 'string') {
                            settings.data += '&' + csrfName + '=' + csrfHash;
                        }
                    }
                }
            },
            complete: function(xhr) {
                // Update CSRF token from response header
                var newToken = xhr.getResponseHeader('X-CSRF-TOKEN');
                if (newToken) {
                    csrfHash = newToken;
                }
            }
        });
        
        console.log('File Manager: Initializing with Server-Side DataTables...');
        
        // Check if DataTables is available
        if (typeof $.fn.DataTable === 'undefined') {
            console.error('DataTables library not loaded!');
            alert('Error: DataTables library not loaded. Please refresh the page.');
            return;
        }
        
        // Initialize DataTable (Server-side mode)
        var oTable = null;
        try {
            oTable = $('#FilesTable').DataTable({
                "processing": true,
                "serverSide": true,
                "deferRender": true, // Improve performance by deferring rendering
                "ajax": {
                    "url": "<?= site_url('file_manager/getFiles') ?>",
                    "type": "POST",
                    "data": function(d) {
                        // Add filter parameters
                        d.file_type = $('#file-type-filter').val();
                        d.folder_filter = $('#folder-filter').val();
                        // Add CSRF token
                        d[csrfName] = csrfHash;
                        
                        // Debug logging
                        console.log('DataTables Request:', {
                            start: d.start,
                            length: d.length,
                            search: d.search.value,
                            file_type: d.file_type,
                            folder_filter: d.folder_filter
                        });
                    },
                    "error": function(xhr, error, thrown) {
                        console.error('DataTables AJAX error:', error, thrown);
                        console.error('Response:', xhr.responseText);
                        console.error('Status:', xhr.status);
                        
                        // Show user-friendly error
                        alert('Error loading files. Please refresh the page.');
                    },
                    "dataSrc": function(json) {
                        // Debug logging
                        console.log('DataTables Response:', {
                            recordsTotal: json.recordsTotal,
                            recordsFiltered: json.recordsFiltered,
                            dataCount: json.data ? json.data.length : 0
                        });
                        
                        // Update CSRF token if provided in response
                        if (json.token) {
                            csrfHash = json.token;
                        }
                        
                        // Check for errors
                        if (json.error) {
                            console.error('Server error:', json.error);
                            alert('Error: ' + json.error);
                            return [];
                        }
                        
                        return json.data;
                    }
                },
                "columns": [
                    { "orderable": false, "width": "60px" },
                    { "orderable": true },
                    { "orderable": true, "width": "80px" },
                    { "orderable": true, "width": "100px" },
                    { "orderable": true, "width": "150px" },
                    { "orderable": false, "width": "180px" }
                ],
                "order": [[4, "desc"]], // Order by Upload Date (newest first)
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
            "pageLength": 25,
            "language": {
                    "processing": '<div style="margin: 20px;"><i class="fa fa-spinner fa-spin fa-3x fa-fw text-primary"></i><br><br><span class="text-muted">Loading files...</span></div>',
                    "emptyTable": "No files found. Upload files using the area above.",
                "info": "Showing _START_ to _END_ of _TOTAL_ files",
                "infoEmpty": "No files available",
                    "infoFiltered": "(filtered from _MAX_ total files)",
                    "zeroRecords": "No matching files found",
                    "loadingRecords": "Loading...",
                    "search": "Search files:"
                },
                "dom": '<"row"<"col-sm-6"l><"col-sm-6"f>>rt<"row"<"col-sm-5"i><"col-sm-7"p>>', // Layout definition
                "drawCallback": function(settings) {
                    // Re-initialize tooltips after table draw
                    $('.tip').tooltip();
                }
            });
        
            console.log('File Manager: DataTable initialized successfully', oTable);
        } catch (error) {
            console.error('Error initializing DataTable:', error);
            alert('Error initializing file table. Please refresh the page.');
            return;
        }

        // Apply filters
        $('#apply-filters').on('click', function() {
            console.log('Applying filters...');
            if (oTable) {
                oTable.ajax.reload();
            }
        });
        
        // Quick folder navigation
        $('.folder-card').on('click', function() {
            var folder = $(this).data('folder');
            console.log('Folder card clicked:', folder);
            $('#folder-filter').val(folder);
            console.log('Folder filter set to:', $('#folder-filter').val());
            if (oTable) {
                console.log('Reloading table for folder:', folder);
                oTable.ajax.reload();
            } else {
                console.error('oTable is not initialized!');
            }
        });
        
        // Folder filter change
        $('#folder-filter').on('change', function() {
            var selectedFolder = $(this).val();
            console.log('Folder filter changed to:', selectedFolder);
            if (oTable) {
                oTable.ajax.reload();
            } else {
                console.error('oTable is not initialized!');
            }
        });

        // Refresh button
        $('#refresh-files').on('click', function(e) {
            e.preventDefault();
            if (oTable) {
                oTable.ajax.reload();
            } else {
                location.reload();
            }
        });
        
        // Create Folder disabled (feature removed)

        // File Upload - Drag and Drop
        var uploadArea = $('#uploadArea')[0];
        var fileInput = $('#fileInput')[0];
        
        // Only set up upload handlers if elements exist
        if (uploadArea && fileInput) {
            $('#browseBtn').on('click', function() {
                if (fileInput) fileInput.click();
            });
            
            uploadArea.addEventListener('click', function() {
                if (fileInput) fileInput.click();
            });
            
            uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
                e.stopPropagation();
                $(this).addClass('dragover');
            });
            
            uploadArea.addEventListener('dragleave', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('dragover');
            });
            
            uploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('dragover');
                
                var files = e.dataTransfer.files;
                if (files.length > 0) {
                    uploadFiles(files);
                }
            });
            
            fileInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    uploadFiles(this.files);
                }
            });
        } else {
            console.warn('Upload area or file input not found on page');
        }
        
        function uploadFiles(files) {
            var formData = new FormData();
            
            for (var i = 0; i < files.length; i++) {
                formData.append('files[]', files[i]);
            }
            
            var currentFolderVal = $('#folder-filter').val();
            if (currentFolderVal && currentFolderVal !== 'all') {
                formData.append('folder', currentFolderVal);
            }
            
            // Add CSRF token to FormData
            formData.append(csrfName, csrfHash);
            
            // Show progress
            $('#uploadArea').html('<i class="fa fa-spinner fa-spin fa-3x"></i><h4>Uploading ' + files.length + ' file(s)...</h4>');
            
            $.ajax({
                url: '<?= site_url('file_manager/upload') ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    // Update CSRF token
                    if (response.token) {
                        csrfHash = response.token;
                    }
                    
                    if (response.status === 'success') {
                        alert(response.message);
                        if (oTable) {
                            oTable.ajax.reload();
                        } else {
            location.reload();
                        }
                    } else {
                        alert('Upload failed: ' + response.message);
                    }
                },
                error: function() {
                    alert('An error occurred during upload.');
                },
                complete: function() {
                    // Reset upload area
                    $('#uploadArea').html('<i class="fa fa-cloud-upload"></i><h4>Drag & Drop Files Here</h4><p>or click to browse files</p><button type="button" class="btn btn-primary" id="browseBtn" style="margin-top: 10px;"><i class="fa fa-folder-open"></i> Browse Files</button>');
                    fileInput.value = '';
                    
                    // Re-bind browse button
                    $('#browseBtn').on('click', function() {
                        fileInput.click();
                    });
                }
            });
        }

        // Share functionality
        $(document).on('click', '.share-whatsapp', function(e) {
            e.preventDefault();
            currentFileUrl = $(this).data('file');
            currentFileName = $(this).data('filename');
            
            $('#whatsapp-filename').text(currentFileName);
            $('#whatsapp-country-code').val('+91');
            $('#whatsapp-phone').val('');
            // Generate short URL before opening WhatsApp modal
            $.post('<?= site_url('file_manager/createShareLink') ?>', { file: currentFileName, '<?= $this->security->get_csrf_token_name(); ?>': csrfHash }, function(resp){
                if (resp && resp.status === 'success' && resp.url) {
                    currentFileUrl = resp.url;
                    if (resp.token) csrfHash = resp.token;
                }
                $('#whatsapp-message').val('Check out this file: ' + currentFileName + '\n\n' + currentFileUrl);
                $('#whatsappModal').modal('show');
            }, 'json');
        });

        $('#send-whatsapp-btn').on('click', function() {
            var countryCode = $('#whatsapp-country-code').val().trim();
            var phone = $('#whatsapp-phone').val().trim();
            var message = $('#whatsapp-message').val().trim();
            
            if (!phone) {
                alert('Please enter phone number');
                return;
            }
            
            // Clean numerics and compose E.164-like number
            countryCode = countryCode.replace(/[^0-9]/g, '');
            phone = phone.replace(/[^0-9]/g, '');
            var fullNumber = countryCode + phone;

            // Send via Cheerio API through backend (no wa.me window)
            $.ajax({
                url: '<?= site_url('whatsapp/send_file') ?>',
                type: 'POST',
                dataType: 'json',
                data: {
                    phone: fullNumber,
                    file: currentFileName,
                    message: message,
                    '<?= $this->security->get_csrf_token_name(); ?>': csrfHash
                },
                success: function(resp) {
                    if (resp && resp.status === 'success') {
                        alert('WhatsApp message sent successfully.');
                        $('#whatsappModal').modal('hide');
                    } else if (resp && resp.message) {
                        alert('WhatsApp send failed: ' + resp.message);
                    } else {
                        alert('WhatsApp send failed.');
                    }
                },
                error: function() {
                    alert('WhatsApp send error.');
                }
            });
        });

        // Share via SMS
        $(document).on('click', '.share-sms', function(e) {
            e.preventDefault();
            currentFileUrl = $(this).data('file');
            currentFileName = $(this).data('filename');
            
            $('#sms-filename').text(currentFileName);
            $('#sms-phone').val('');
            $.post('<?= site_url('file_manager/createShareLink') ?>', { file: currentFileName, '<?= $this->security->get_csrf_token_name(); ?>': csrfHash }, function(resp){
                if (resp && resp.status === 'success' && resp.url) {
                    currentFileUrl = resp.url;
                    if (resp.token) csrfHash = resp.token;
                }
                $('#smsModal').modal('show');
            }, 'json');
        });

        $('#send-sms-btn').on('click', function() {
            var phone = $('#sms-phone').val().trim();
            var message = '';
            
            if (!phone) {
                alert('Please enter phone number');
                return;
            }
            // message is optional; backend will compose with short link if empty
            
            $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending...');
            
            $.ajax({
                url: '<?= site_url('whatsapp/send_sms_file') ?>',
                type: 'POST',
                dataType: 'json',
                data: { 
                    phone: phone,
                    message: message,
                    file: currentFileName
                },
                success: function(response) {
                    if (response.status === 'success') {
                        alert('SMS sent successfully.');
            $('#smsModal').modal('hide');
                    } else {
                        alert('Error: ' + (response.message || 'Failed to send SMS'));
                    }
                },
                error: function() {
                    alert('An error occurred while sending SMS.');
                },
                complete: function() {
                    $('#send-sms-btn').prop('disabled', false).html('<i class="fa fa-commenting"></i> Send SMS');
                }
            });
        });

        // Share via Email
        $(document).on('click', '.share-email', function(e) {
            e.preventDefault();
            currentFileUrl = $(this).data('file');
            currentFileName = $(this).data('filename');
            
            $('#email-filename').text(currentFileName);
            $('#email-address').val('');
            $('#email-subject').val('File Share: ' + currentFileName);
            $.post('<?= site_url('file_manager/createShareLink') ?>', { file: currentFileName, '<?= $this->security->get_csrf_token_name(); ?>': csrfHash }, function(resp){
                if (resp && resp.status === 'success' && resp.url) {
                    currentFileUrl = resp.url;
                    if (resp.token) csrfHash = resp.token;
                }
                $('#email-message').val('Hi,\n\nPlease find the file below:\n\nFile: ' + currentFileName + '\nLink: ' + currentFileUrl + '\n\nBest Regards');
                $('#emailModal').modal('show');
            }, 'json');
        });

        $('#send-email-btn').on('click', function() {
            var email = $('#email-address').val().trim();
            var subject = $('#email-subject').val().trim();
            var message = $('#email-message').val().trim();
            
            if (!email) {
                alert('Please enter email address');
                return;
            }
            
            if (!subject) {
                alert('Please enter email subject');
                return;
            }
            
            if (!message) {
                alert('Please enter email message');
                return;
            }
            
            $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending...');
            
            $.ajax({
                url: '<?= site_url('file_manager/shareEmail') ?>',
                type: 'POST',
                dataType: 'json',
                data: { 
                    email: email,
                    subject: subject,
                    message: message,
                    file_url: currentFileUrl,
                    filename: currentFileName
                },
                success: function(response) {
                    if (response.status === 'success') {
                        alert(response.message);
            $('#emailModal').modal('hide');
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('An error occurred while sending email.');
                },
                complete: function() {
                    $('#send-email-btn').prop('disabled', false).html('<i class="fa fa-envelope"></i> Send Email');
                }
            });
        });

        // Fullscreen toggle for file data modal
        $('#fullscreen-btn').on('click', function() {
            var $modal = $('#fileDataModal');
            var $btn = $(this);
            var $icon = $btn.find('i');
            
            if ($modal.hasClass('fullscreen-modal')) {
                // Exit fullscreen
                $modal.removeClass('fullscreen-modal');
                $icon.removeClass('fa-compress').addClass('fa-expand');
                $btn.html('<i class="fa fa-expand"></i> Fullscreen');
            } else {
                // Enter fullscreen
                $modal.addClass('fullscreen-modal');
                $icon.removeClass('fa-expand').addClass('fa-compress');
                $btn.html('<i class="fa fa-compress"></i> Exit Fullscreen');
            }
        });
        
        // Reset fullscreen state when modal is closed
        $('#fileDataModal').on('hidden.bs.modal', function() {
            $(this).removeClass('fullscreen-modal');
            $('#fullscreen-btn').html('<i class="fa fa-expand"></i> Fullscreen');
            $('#print-btn').hide();
            $('#print-btn').removeData('print-type');
            $('#pdf-floating-close').hide();
        });
        
        // Floating PDF close button action
        $('#pdf-floating-close').on('click', function() {
            $('#fileDataModal').modal('hide');
        });
        
        // View file data in modal (click on file name to open popup)
        $(document).on('click', '.view-file-data', function(e) {
            e.preventDefault();
            console.log('View file data clicked!');
            
            var $link = $(this);
            var fileUrl = $link.data('file');
            var fileType = $link.data('type');
            var fileName = $link.data('name');
            
            console.log('File:', fileName, 'Type:', fileType);
            
            // Set modal title and update share links
            $('#modal-file-name').text(fileName + ' (' + fileType.toUpperCase() + ')');
            $('#modal-share-whatsapp').attr('data-file', fileUrl).attr('data-filename', fileName);
            $('#modal-share-sms').attr('data-file', fileUrl).attr('data-filename', fileName);
            $('#modal-share-email').attr('data-file', fileUrl).attr('data-filename', fileName);
            
            // Show modal with loading state
            $('#file-data-content').html('<p style="text-align: center; padding: 50px;"><i class="fa fa-spinner fa-spin fa-3x"></i><br><br>Loading file data...</p>');
            $('#fileDataModal').modal('show');
            // Ensure inline close is visible for all file types
            $('#pdf-inline-close').show();
            
            // Load file data via AJAX
                $.ajax({
                url: '<?= site_url('file_manager/getFileData') ?>',
                    type: 'POST',
                    data: {
                    file: fileName,
                        file_url: fileUrl,
                    file_type: fileType,
                    '<?= $this->security->get_csrf_token_name(); ?>': csrfHash
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.token) csrfHash = response.token;
                    
                    var content = '';
                        
                        if (response.status === 'success') {
                        // Toggle print button visibility based on type
                        if (response.type === 'excel' || response.type === 'csv') {
                            $('#print-btn').show();
                            $('#print-btn').data('print-type', response.type);
                        } else {
                            $('#print-btn').hide();
                            $('#print-btn').removeData('print-type');
                        }
                        
                        if (response.type === 'pdf') {
                            // Render PDF using data URL (no direct URL usage)
                            var dataUrl = response.data_url || ('data:application/pdf;base64,' + response.base64);
                            content = '';
                            content += '<div id="pdf-viewer-wrap" style="width: 100%; height: 520px; background: #fff; border: 1px solid #ddd; border-radius: 4px; overflow: hidden;">';
                            content +=   '<object data="' + dataUrl + '" type="application/pdf" style="width:100%; height:100%;">';
                            content +=     '<embed src="' + dataUrl + '" type="application/pdf" style="width:100%; height:100%;" />';
                            content +=     '<iframe src="' + dataUrl + '#toolbar=1&zoom=page-width" style="width:100%; height:100%; border:0;"></iframe>';
                            content +=     '<div style="padding: 20px; text-align:center;">';
                            content +=       '<p><i class="fa fa-file-pdf-o text-danger"></i> Unable to display PDF in this viewer.</p>';
                            content +=     '</div>';
                            content +=   '</object>';
                            content += '</div>';
                            content += '<p style="text-align: center; color: #666; margin-top: 10px;"><small>Size: ' + response.size + '</small></p>';
                            // Show inline close button for PDF view
                            $('#pdf-inline-close').css({display:'block'});
                        } else if (response.type === 'csv') {
                            // Parse and display CSV data
                            var lines = response.content.split('\n');
                            var maxRows = 100; // Limit for modal display
                            
                            content = '<div id="tabular-print-area" style="overflow-x: auto; max-height: 500px; overflow-y: auto; border: 1px solid #ddd; border-radius: 4px;">';
                            content += '<table class="table table-bordered table-striped table-condensed" id="tabular-print-table" style="margin: 0; font-size: 12px; background: white;">';
                            
                            var rowCount = 0;
                            for (var i = 0; i < lines.length && rowCount < maxRows; i++) {
                                var line = lines[i].trim();
                                if (line === '') continue;
                                
                                var cells = parseCSVLine(line);
                                content += '<tr>';
                                
                                for (var j = 0; j < cells.length; j++) {
                                    if (i === 0) {
                                        content += '<th style="background: #337ab7; color: white; padding: 8px; white-space: nowrap; position: sticky; top: 0; z-index: 10;">' + $('<div>').text(cells[j]).html() + '</th>';
                                    } else {
                                        content += '<td style="padding: 6px 8px; white-space: nowrap;">' + $('<div>').text(cells[j]).html() + '</td>';
                                    }
                                }
                                content += '</tr>';
                                rowCount++;
                            }
                            
                            content += '</table></div>';
                            content += '<div class="alert alert-info" style="margin-top: 15px; margin-bottom: 0;"><i class="fa fa-info-circle"></i> <strong>CSV Data</strong><br>Showing ' + rowCount + ' rows';
                            if (lines.length > maxRows) {
                                content += ' (limited to first ' + maxRows + ' rows, total: ' + lines.length + ' rows)';
                            }
                            content += ' | Size: ' + response.size + '</div>';
                            $('#pdf-inline-close').hide();
                        } else if (response.type === 'excel') {
                            // Display Excel data
                            var data = response.data;
                            
                            content = '<div id="tabular-print-area" style="overflow-x: auto; max-height: 500px; overflow-y: auto; border: 1px solid #ddd; border-radius: 4px;">';
                            content += '<table class="table table-bordered table-striped table-condensed" id="tabular-print-table" style="margin: 0; font-size: 12px; background: white;">';
                            
                            for (var i = 0; i < data.length; i++) {
                                content += '<tr>';
                                var row = data[i];
                                
                                for (var j = 0; j < row.length; j++) {
                                    if (i === 0) {
                                        content += '<th style="background: #217346; color: white; padding: 8px; white-space: nowrap; position: sticky; top: 0; z-index: 10;">' + $('<div>').text(row[j] || '').html() + '</th>';
                                    } else {
                                        content += '<td style="padding: 6px 8px; white-space: nowrap;">' + $('<div>').text(row[j] || '').html() + '</td>';
                                    }
                                }
                                content += '</tr>';
                            }
                            
                            content += '</table></div>';
                            content += '<div class="alert alert-success" style="margin-top: 15px; margin-bottom: 0;"><i class="fa fa-check-circle"></i> <strong>Excel Data</strong><br>Showing ' + response.rows + ' rows × ' + response.columns + ' columns';
                            if (response.limited) {
                                content += ' (limited to first 100 rows, total: ' + response.total_rows + ' rows)';
                            }
                            content += ' | Size: ' + response.size + '</div>';
                            $('#pdf-inline-close').hide();
                        } else {
                            content = '<div class="alert alert-warning"><i class="fa fa-info-circle"></i> File type not supported for display. Please download the file.</div>';
                        }
                    } else {
                        content = '<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> ' + response.message + '</div>';
                    }
                    
                    $('#file-data-content').html(content);
                    },
                    error: function(xhr, status, error) {
                    console.error('Error loading file data:', error);
                    $('#file-data-content').html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> Failed to load file data. Error: ' + error + '</div>');
                }
            });
        });

        // Print handler for CSV/Excel tabular content (same window)
        $('#print-btn').on('click', function() {
            var $table = $('#tabular-print-table');
            if ($table.length === 0) {
                alert('Nothing to print.');
                return;
            }
            // Create a hidden iframe for printing
            var iframeId = 'inline-print-iframe';
            var $existing = $('#' + iframeId);
            if ($existing.length) { $existing.remove(); }
            var $iframe = $('<iframe id="' + iframeId + '" style="position:absolute;left:-9999px;top:-9999px;width:0;height:0;border:0;"></iframe>');
            $('body').append($iframe);
            var doc = $iframe[0].contentWindow || $iframe[0].contentDocument;
            if (doc.document) doc = doc.document;
            var html = '';
            html += '<!DOCTYPE html><html><head><title>Print</title>';
            html += '<style>body{margin:16px;font-family:Arial,Helvetica,sans-serif} table{width:100%;border-collapse:collapse;font-size:12px} th,td{border:1px solid #333;padding:6px} th{background:#eee}</style>';
            html += '</head><body>';
            html += '<h4 style="margin:0 0 10px 0;">' + $('#modal-file-name').text() + '</h4>';
            html += '<table>' + $table.html() + '</table>';
            html += '</body></html>';
            doc.open();
            doc.write(html);
            doc.close();
            // Give the iframe content a tick to render, then print
            setTimeout(function(){
                var win = $iframe[0].contentWindow;
                win.focus();
                win.print();
                // Cleanup
                setTimeout(function(){ $iframe.remove(); }, 500);
            }, 200);
        });

        // Delete file
        $(document).on('click', '.delete-file', function(e) {
            e.preventDefault();
            
            fileToDelete = $(this).data('file');
            $('#delete-file-name').text(fileToDelete);
            $('#deleteModal').modal('show');
        });

        // Confirm delete
        $('#confirm-delete-btn').on('click', function() {
            if (!fileToDelete) return;
            
            $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Deleting...');
            
            $.ajax({
                url: '<?= site_url('file_manager/delete') ?>',
                type: 'POST',
                dataType: 'json',
                data: { file: fileToDelete },
                success: function(response) {
                    // Update CSRF token
                    if (response.token) {
                        csrfHash = response.token;
                    }
                    
                    if (response.status === 'success') {
                        $('#deleteModal').modal('hide');
                        alert(response.message);
                        if (oTable) {
                            oTable.ajax.reload();
                        } else {
                        location.reload();
                        }
                    } else {
                        alert('Error: ' + response.message);
                        $('#confirm-delete-btn').prop('disabled', false).html('<i class="fa fa-trash-o"></i> Delete File');
                    }
                },
                error: function() {
                    alert('An error occurred while deleting the file.');
                    $('#confirm-delete-btn').prop('disabled', false).html('<i class="fa fa-trash-o"></i> Delete File');
                },
                complete: function() {
                    fileToDelete = '';
                }
            });
        });

        // Reset modal on close
        $('#deleteModal').on('hidden.bs.modal', function() {
            fileToDelete = '';
        });
    });
    
    // Parse CSV line (handles quotes and commas within fields)
    function parseCSVLine(line) {
        var result = [];
        var current = '';
        var inQuotes = false;
        
        for (var i = 0; i < line.length; i++) {
            var char = line[i];
            
            if (char === '"') {
                inQuotes = !inQuotes;
            } else if (char === ',' && !inQuotes) {
                result.push(current.trim());
                current = '';
            } else {
                current += char;
            }
        }
        
        // Add last field
        if (current !== '') {
            result.push(current.trim());
        }
        
        return result;
    }
</script>

