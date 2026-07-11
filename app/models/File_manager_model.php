<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class File_manager_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Get all files from a directory (including subfolders)
     * Simple logic: Only get PDF, Excel (XLS/XLSX), and CSV files
     * @param string $directory Path to directory
     * @param string $search_term Search term for filtering
     * @param string $file_type File type filter
     * @param string $folder_filter Folder filter (subfolder name)
     * @return array Array of file information
     */
    public function getFiles($directory, $search_term = '', $file_type = '', $folder_filter = 'all') {
        $files = array();
        
        // Check if directory exists
        if (!is_dir($directory)) {
            return $files;
        }
        
        // If specific folder selected, read only from that folder
        if ($folder_filter && $folder_filter !== 'all' && $folder_filter !== 'root') {
            $subfolder_path = $directory . $folder_filter . '/';
            if (is_dir($subfolder_path)) {
                $files = $this->readFilesFromDirectory($subfolder_path, $search_term, $file_type, $folder_filter);
            }
        } elseif ($folder_filter === 'root') {
            // Read only root level files
            $files = $this->readFilesFromDirectory($directory, $search_term, $file_type, 'root', false);
        } else {
            // Read from root directory
            $files = $this->readFilesFromDirectory($directory, $search_term, $file_type, 'root', false);
            
            // Read from all subfolders (only pdf, csv, excel folders)
            $allowed_folders = array('pdf', 'csv', 'excel');
            $dir = new DirectoryIterator($directory);
            foreach ($dir as $fileinfo) {
                if (!$fileinfo->isDot() && $fileinfo->isDir()) {
                    $subfolder = $fileinfo->getFilename();
                    $subfolder_lower = strtolower($subfolder);
                    
                    // Skip hidden folders and thumbs
                    if (substr($subfolder, 0, 1) === '.' || $subfolder === 'thumbs') {
                        continue;
                    }
                    
                    // Only read from pdf, csv, excel folders
                    if (!in_array($subfolder_lower, $allowed_folders)) {
                        continue;
                    }
                    
                    $subfolder_path = $directory . $subfolder . '/';
                    $subfolder_files = $this->readFilesFromDirectory($subfolder_path, $search_term, $file_type, $subfolder);
                    $files = array_merge($files, $subfolder_files);
                }
            }
        }
        
        // Sort files by date (newest first)
        usort($files, function($a, $b) {
            return $b['date'] - $a['date'];
        });
        
        return $files;
    }
    
    /**
     * Simple method to get only PDF, Excel, and CSV files
     * @param string $directory Path to directory
     * @return array Array of files (PDF, Excel, CSV only)
     */
    public function getUploadedFiles($directory) {
        $files = array();
        
        if (!is_dir($directory)) {
            return $files;
        }
        
        // Allowed extensions only
        $allowed_extensions = array('pdf', 'xls', 'xlsx', 'csv');
        
        // Read from root directory
        $this->scanDirectory($directory, $files, $allowed_extensions, 'root');
        
        // Read from all subfolders (only pdf, csv, excel folders)
        $allowed_folder_names = array('pdf', 'csv', 'excel');
        $dir = new DirectoryIterator($directory);
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot() && $fileinfo->isDir()) {
                $subfolder = $fileinfo->getFilename();
                $subfolder_lower = strtolower($subfolder);
                
                // Skip hidden folders and thumbs
                if (substr($subfolder, 0, 1) === '.' || $subfolder === 'thumbs') {
                    continue;
                }
                
                // Only read from pdf, csv, excel folders
                if (!in_array($subfolder_lower, $allowed_folder_names)) {
                    continue;
                }
                
                $subfolder_path = $directory . $subfolder . '/';
                $this->scanDirectory($subfolder_path, $files, $allowed_extensions, $subfolder);
            }
        }
        
        // Sort by date (newest first)
        usort($files, function($a, $b) {
            return $b['date'] - $a['date'];
        });
        
        return $files;
    }
    
    /**
     * Scan directory for allowed file types
     * @param string $directory Directory path
     * @param array &$files Files array (passed by reference)
     * @param array $allowed_extensions Allowed file extensions
     * @param string $folder Folder name
     */
    private function scanDirectory($directory, &$files, $allowed_extensions, $folder = '') {
        if (!is_dir($directory)) {
            return;
        }
        
        $dir = new DirectoryIterator($directory);
        
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot() && $fileinfo->isFile()) {
                $filename = $fileinfo->getFilename();
                $extension = strtolower($fileinfo->getExtension());
                
                // Skip hidden files and index files
                if (substr($filename, 0, 1) === '.' || $filename === 'index.html' || $filename === 'index.php') {
                    continue;
                }
                
                // Only allow PDF, Excel, CSV
                if (!in_array($extension, $allowed_extensions)) {
                    continue;
                }
                
                $files[] = array(
                    'name' => $filename,
                    'path' => $directory . $filename,
                    'size' => $fileinfo->getSize(),
                    'extension' => $extension,
                    'date' => $fileinfo->getMTime(),
                    'type' => $fileinfo->getType(),
                    'folder' => $folder
                );
            }
        }
    }
    
    /**
     * Read files from a specific directory
     * Simple logic: Only get PDF, Excel, and CSV files
     * @param string $directory Directory path
     * @param string $search_term Search term
     * @param string $file_type File type filter
     * @param string $folder Folder name (for display)
     * @param bool $recursive Whether to read recursively (default true)
     * @return array Files array
     */
    private function readFilesFromDirectory($directory, $search_term = '', $file_type = '', $folder = '', $recursive = true) {
        $files = array();
        
        if (!is_dir($directory)) {
            return $files;
        }
        
        // Only allow these file types
        $allowed_extensions = array('pdf', 'xls', 'xlsx', 'csv');
        
        $dir = new DirectoryIterator($directory);
        
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot() && $fileinfo->isFile()) {
                $filename = $fileinfo->getFilename();
                $extension = strtolower($fileinfo->getExtension());
                
                // Skip hidden files and index files
                if (substr($filename, 0, 1) === '.' || $filename === 'index.html' || $filename === 'index.php') {
                    continue;
                }
                
                // Only allow PDF, Excel, CSV files
                if (!in_array($extension, $allowed_extensions)) {
                    continue;
                }
                
                // Apply search filter
                if ($search_term && stripos($filename, $search_term) === false) {
                    continue;
                }
                
                // Apply file type filter
                if ($file_type && $file_type !== 'all') {
                    $type_match = false;
                    
                    switch ($file_type) {
                        case 'pdf':
                            $type_match = ($extension === 'pdf');
                            break;
                        case 'excel':
                            $type_match = in_array($extension, array('xls', 'xlsx'));
                            break;
                        case 'csv':
                            $type_match = ($extension === 'csv');
                            break;
                        default:
                            $type_match = true;
                    }
                    
                    if (!$type_match) {
                        continue;
                    }
                }
                
                $files[] = array(
                    'name' => $filename,
                    'path' => $directory . $filename,
                    'size' => $fileinfo->getSize(),
                    'extension' => $extension,
                    'date' => $fileinfo->getMTime(),
                    'type' => $fileinfo->getType(),
                    'folder' => $folder
                );
            }
        }
        
        return $files;
    }
    
    /**
     * Get list of subfolders
     * Only return pdf, csv, and excel folders
     * @param string $directory Directory path
     * @return array Array of folder names
     */
    public function getFolders($directory) {
        $folders = array();
        
        if (!is_dir($directory)) {
            return $folders;
        }
        
        // Only allow these specific folder names
        $allowed_folders = array('pdf', 'csv', 'excel');
        
        $dir = new DirectoryIterator($directory);
        
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot() && $fileinfo->isDir()) {
                $foldername = strtolower($fileinfo->getFilename());
                
                // Skip hidden folders and thumbs
                if (substr($foldername, 0, 1) === '.' || $foldername === 'thumbs') {
                    continue;
                }
                
                // Only allow pdf, csv, excel folders
                if (in_array($foldername, $allowed_folders)) {
                    $folders[] = $fileinfo->getFilename();
                }
            }
        }
        
        sort($folders);
        return $folders;
    }

    /**
     * Get file statistics
     * @param string $directory Path to directory
     * @return array Statistics array
     */
    public function getFileStats($directory) {
        $stats = array(
            'total_files' => 0,
            'total_size' => 0,
            'image_count' => 0,
            'pdf_count' => 0,
            'excel_count' => 0,
            'csv_count' => 0,
            'document_count' => 0,
            'archive_count' => 0,
            'other_count' => 0
        );
        
        if (!is_dir($directory)) {
            return $stats;
        }
        
        $dir = new DirectoryIterator($directory);
        
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot() && $fileinfo->isFile()) {
                $filename = $fileinfo->getFilename();
                
                // Skip hidden files and index files
                if (substr($filename, 0, 1) === '.' || $filename === 'index.html' || $filename === 'index.php') {
                    continue;
                }
                
                $stats['total_files']++;
                $stats['total_size'] += $fileinfo->getSize();
                
                $extension = strtolower($fileinfo->getExtension());
                
                if (in_array($extension, array('jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'))) {
                    $stats['image_count']++;
                } elseif ($extension === 'pdf') {
                    $stats['pdf_count']++;
                } elseif (in_array($extension, array('xls', 'xlsx'))) {
                    $stats['excel_count']++;
                } elseif ($extension === 'csv') {
                    $stats['csv_count']++;
                } elseif (in_array($extension, array('doc', 'docx', 'txt'))) {
                    $stats['document_count']++;
                } elseif (in_array($extension, array('zip', 'rar', '7z', 'tar', 'gz'))) {
                    $stats['archive_count']++;
                } else {
                    $stats['other_count']++;
                }
            }
        }
        
        return $stats;
    }

    /**
     * Check if file exists
     * @param string $directory Directory path
     * @param string $filename File name
     * @return bool
     */
    public function fileExists($directory, $filename) {
        return file_exists($directory . $filename);
    }

    /**
     * Get file info
     * @param string $directory Directory path
     * @param string $filename File name
     * @return array|false File information or false
     */
    public function getFileInfo($directory, $filename) {
        $filepath = $directory . $filename;
        
        if (!file_exists($filepath)) {
            return false;
        }
        
        $fileinfo = new SplFileInfo($filepath);
        
        return array(
            'name' => $filename,
            'path' => $filepath,
            'size' => $fileinfo->getSize(),
            'extension' => strtolower($fileinfo->getExtension()),
            'date' => $fileinfo->getMTime(),
            'type' => $fileinfo->getType()
        );
    }
}

