<?php
/**
 * Legacy mPDF 6 API shim — ElintOM18.00 PHP 8.5 upgrade.
 * Original MPDF 6.0 preserved as mpdf_legacy_6.php (not loaded).
 */
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'autoload.php';

class mPDF
{
    public $debug = false;
    public $autoScriptToLang = true;
    public $autoLangToFont = true;

    /** @var \Mpdf\Mpdf */
    protected $mpdf;

    public function __construct(
        $mode = 'utf-8',
        $format = 'A4',
        $default_font_size = 0,
        $default_font = '',
        $margin_left = 15,
        $margin_right = 15,
        $margin_top = 16,
        $margin_bottom = 16,
        $margin_header = 9,
        $margin_footer = 9,
        $orientation = 'P'
    ) {
        $pageFormat = $format;
        $orient = $orientation;
        if (is_string($format) && strpos($format, '-') !== false) {
            $parts = explode('-', $format, 2);
            $pageFormat = $parts[0];
            if (!empty($parts[1])) {
                $orient = $parts[1];
            }
        }

        $config = array(
            'mode' => $mode ?: 'utf-8',
            'format' => $pageFormat ?: 'A4',
            'orientation' => $orient ?: 'P',
            'margin_left' => (float) $margin_left,
            'margin_right' => (float) $margin_right,
            'margin_top' => (float) $margin_top,
            'margin_bottom' => (float) $margin_bottom,
            'margin_header' => (float) $margin_header,
            'margin_footer' => (float) $margin_footer,
            'tempDir' => sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'mpdf',
        );
        if ($default_font_size) {
            $config['default_font_size'] = (int) $default_font_size;
        }
        if ($default_font) {
            $config['default_font'] = $default_font;
        }

        if (!is_dir($config['tempDir'])) {
            @mkdir($config['tempDir'], 0777, true);
        }

        $this->mpdf = new \Mpdf\Mpdf($config);
        if ($this->autoScriptToLang) {
            $this->mpdf->autoScriptToLang = true;
        }
        if ($this->autoLangToFont) {
            $this->mpdf->autoLangToFont = true;
        }
    }

    public function WriteHTML($html, $mode = 0)
    {
        if ((int) $mode === 1) {
            $this->mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HEADER_CSS);
            return;
        }
        if ((int) $mode === 2) {
            $this->mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
            return;
        }
        $this->mpdf->WriteHTML($html);
    }

    public function SetTitle($title)
    {
        $this->mpdf->SetTitle($title);
    }

    public function SetAuthor($author)
    {
        $this->mpdf->SetAuthor($author);
    }

    public function SetCreator($creator)
    {
        $this->mpdf->SetCreator($creator);
    }

    public function SetDisplayMode($mode)
    {
        $this->mpdf->SetDisplayMode($mode);
    }

    public function SetHeader($header = '', $side = 'O', $write = false)
    {
        if ($header === '' || $header === null) {
            return;
        }
        $this->mpdf->SetHTMLHeader($header);
    }

    public function SetHTMLHeader($html, $side = 'O', $write = false)
    {
        $this->mpdf->SetHTMLHeader($html);
    }

    public function SetHTMLFooter($html, $side = 'O', $write = false)
    {
        $this->mpdf->SetHTMLFooter($html);
    }

    public function SetFooter($footer = '', $side = 'O', $write = false)
    {
        if ($footer === '' || $footer === null) {
            return;
        }
        $this->mpdf->SetHTMLFooter($footer);
    }

    public function AddPage($orientation = '', $condition = '', $resetpagenum = '', $pagenumstyle = '', $suppress = '', $mgl = '', $mgr = '', $mgt = '', $mgb = '', $mgh = '', $mgf = '', $ohname = '', $ehname = '', $ofname = '', $efname = '', $ohvalue = 0, $ehvalue = 0, $ofvalue = 0, $efvalue = 0, $pagesel = '', $newformat = '')
    {
        if ($orientation !== '' && $orientation !== null) {
            $this->mpdf->AddPage($orientation);
            return;
        }
        $this->mpdf->AddPage();
    }

    public function Output($name = '', $dest = '')
    {
        return $this->mpdf->Output($name, $dest);
    }

    public function __call($name, $arguments)
    {
        if (method_exists($this->mpdf, $name)) {
            return call_user_func_array(array($this->mpdf, $name), $arguments);
        }
        trigger_error('Call to undefined method mPDF::' . $name, E_USER_WARNING);
        return null;
    }

    public function __get($name)
    {
        if (property_exists($this->mpdf, $name)) {
            return $this->mpdf->$name;
        }
        return null;
    }

    public function __set($name, $value)
    {
        if (property_exists($this->mpdf, $name) || in_array($name, array('debug', 'autoScriptToLang', 'autoLangToFont'), true)) {
            if (property_exists($this, $name)) {
                $this->$name = $value;
            }
            if (property_exists($this->mpdf, $name)) {
                $this->mpdf->$name = $value;
            }
        }
    }
}
