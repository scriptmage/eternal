<?php

namespace eternal\components;

class Mail extends \stdClass
{

    const EOL = "\r\n";

    private $_boundary = '';
    private $_boundaryBlockId = 1;
    protected $_error = '';
    protected $_subject = '';
    protected $_body = array('html' => '', 'txt' => '');
    protected $_replyTo = array();
    protected $_returnPath = array();
    protected $_from = array();
    protected $_to = array();
    protected $_cc = array();
    protected $_bcc = array();
    protected $_header = array();
    protected $_attachment = array();
    protected $_embedded = array();
    protected $_wrap = 78;
    public $charset = 'UTF-8';

    private function sanitize($string)
    {
        $string = $this->filter(filter_var($string, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES));
        return $string;
    }

    private function filter($string)
    {
        $string = trim(preg_replace('~(?:\n|\r|\t|%0A|%0D|%08|%09)+~i', '', $string));
        return $string;
    }

    private function add_address($type, $email, $name, $unique = FALSE)
    {
        if ($this->valid($email)) {
            $email = $this->filter($email);
            $name = $this->sanitize($name);
            if ($unique) {
                $this->{$type} = array();
            }
            $this->{$type}[$email] = $name;
            return TRUE;
        }
        return FALSE;
    }

    private function get_unique_id()
    {
        return md5(uniqid(time()));
    }

    private function attachment_data($file)
    {
        if (!file_exists($file)) {
            throw new Exception('File not found: ' . htmlspecialchars($file));
            return FALSE;
        }

        $filesize = filesize($file);
        if (($handle = fopen($file, "r")) === FALSE) {
            throw new Exception('File won\'t open: ' . htmlspecialchars($file));
            return FALSE;
        }

        $attachment = fread($handle, $filesize);
        fclose($handle);

        $mime = 'application/octet-stream';
        if (($finfo = finfo_open(FILEINFO_MIME_TYPE)) !== FALSE) {
            if (($strMime = finfo_file($finfo, $file)) !== FALSE) {
                $mime = $strMime;
            }
            finfo_close($finfo);
        }
        return array('content' => chunk_split(base64_encode($attachment)), 'mime' => $mime);
    }

    private function boundary($boundary = NULL)
    {
        if (!is_null($boundary)) {
            $this->_boundaryBlockId = 1;
            $this->_boundary = $boundary;
        }
        return sprintf('b%d_%s', $this->_boundaryBlockId, $this->_boundary);
    }

    private function boundary_begin($incBlockId = FALSE)
    {
        $line = self::EOL . self::EOL . '--' . $this->boundary() . self::EOL;
        if ($incBlockId) {
            $this->_boundaryBlockId++;
        }
        return $line;
    }

    private function boundary_end($decBlockId = FALSE)
    {
        $line = self::EOL . self::EOL . '--' . $this->boundary() . '--' . self::EOL;
        if ($decBlockId) {
            $this->_boundaryBlockId--;
        }
        return $line;
    }

    public function get_header()
    {
        $this->add_header('MIME-Version', '1.0');
        $this->add_header('X-Mailer', 'Eternal Framework Mailer 1.0');
        $this->add_header('From', $this->create_address($this->_from));
        $this->add_header('Sender', $this->create_address($this->_from));
//			$this->add_header('To', $this->create_address($this->to));
        $this->add_header('Cc', $this->create_address($this->_cc));
        $this->add_header('Bcc', $this->create_address($this->_bcc));
        if (empty($this->_replyTo)) {
            $this->add_header('Reply-To', $this->create_address($this->_from));
        }
        if (empty($this->_returnPath)) {
            $this->add_header('Return-Path', $this->create_address($this->_from));
        }
        return $this->_header;
    }

    public function create_address($data)
    {
        $addresses = '';
        foreach ($data as $email => $name) {
            if (!$this->valid($email)) {
                return FALSE;
            }

            if (empty($name)) {
                $addresses .= sprintf('%s;', $this->filter($email));
            } else {
                $addresses .= sprintf('%s <%s>;', $this->sanitize($name), $this->filter($email));
            }
        }
        return rtrim($addresses, ';');
    }

    public function add_header($param, $content)
    {
        if (empty($content) or empty($content)) {
            return FALSE;
        }
        $this->_header[$param] = sprintf('%s: %s', mb_strtolower($param, $this->charset), $content);
    }

    public function subject($subject)
    {
        $this->_subject = $this->sanitize($subject);
    }

    public function return_path($email = NULL)
    {
        if (is_null($email)) {
            return $this->_returnPath;
        }
        $this->add_address('return_path', $email, TRUE);
    }

    public function reply_to($email = NULL)
    {
        if (is_null($email)) {
            return $this->_replyTo;
        }
        $this->add_address('reply_to', $email, TRUE);
    }

    public function from($email = NULL, $name = '')
    {
        if (is_null($email)) {
            return $this->_from;
        }
        $this->add_address('from', $email, empty($name) ? 'Anonymous Root' : $name, TRUE);
    }

    public function valid($email)
    {
        return ($email == filter_var($email, FILTER_SANITIZE_EMAIL) and filter_var($email, FILTER_VALIDATE_EMAIL));
    }

    public function send($additionalParams = array())
    {
        if (empty($this->_to)) {
            throw new \RuntimeException('Unable to send, no To address has been set.');
            return FALSE;
        }

        $hasEmbedded = !empty($this->_embedded);
        $hasAttachment = !empty($this->_attachment);

        $this->add_header('Content-Type', 'multipart/alternative;boundary=' . $this->boundary($this->get_unique_id()));
        $message = $this->boundary_begin(TRUE);
        $message .= sprintf('Content-Type: multipart/alternative; boundary="%s"', $this->boundary());
        $message .= $this->boundary_begin();
        $message .= 'Content-Type: text/plain;charset="' . $this->charset . '"' . self::EOL;
        $message .= 'Content-Transfer-Encoding: 8bit' . self::EOL . self::EOL;
        $message .= $this->wrap($this->_body['txt']);

        if ($hasEmbedded) {
            $message .= $this->boundary_begin(TRUE);
            $message .= sprintf('Content-Type: multipart/related; boundary="%s"', $this->boundary());
        }

        $message .= $this->boundary_begin();
        $message .= 'Content-Type: text/html;charset="' . $this->charset . '"' . self::EOL;
        $message .= 'Content-Transfer-Encoding: 8bit' . self::EOL . self::EOL;

        if (empty($this->_body['html'])) {
            $message .= sprintf('<html><head><title></title></head><body>%s</body></html>', nl2br($this->_body['txt']));
        } else {
            $message .= $this->_body['html'];
        }

        if ($hasEmbedded) {
            foreach ($this->_embedded as $embedded) {
                $message .= $this->boundary_begin();
                $message .= 'Content-Type: ' . $embedded['data']['mime'] . '; name="' . $embedded['name'] . '"';
                $message .= self::EOL;
                $message .= 'Content-Transfer-Encoding: base64';
                $message .= self::EOL;
                $message .= 'Content-Disposition: inline; filename="' . $embedded['name'] . '"';
                $message .= self::EOL;
                $message .= 'Content-ID: <' . $embedded['cid'] . '>';
                $message .= self::EOL;
                $message .= self::EOL;
                $message .= $embedded['data']['content'];
            }
        }
        $message .= $this->boundary_end(TRUE);

        if ($hasEmbedded) {
            $message .= $this->boundary_end(TRUE);
        }

        if ($hasAttachment) {
            $this->add_header('Content-Type', 'multipart/mixed;boundary=' . $this->boundary());
            foreach ($this->_attachment as $attachment) {
                $message .= $this->boundary_begin();
                $message .= sprintf('Content-Type: %s; name="%s"', $attachment['data']['mime'], $attachment['name']);
                $message .= self::EOL;
                $message .= 'Content-Transfer-Encoding: base64';
                $message .= self::EOL;
                $message .= 'Content-Disposition: attachment; filename="' . $attachment['name'] . '"';
                $message .= self::EOL;
                $message .= self::EOL;
                $message .= $attachment['data']['content'];
            }
        }
        $message .= $this->boundary_end();

        if (($sended = @mail(
            $this->create_address($this->_to), 
            $this->_subject, 
            $message, 
            implode(self::EOL, $this->get_header()),
            implode(self::EOL, $additionalParams)
        )) === FALSE) {
            $error = error_get_last();
            $this->_error = $error['message'];
        }
        return $sended;
    }

    public function embed($file)
    {
        if (file_exists($file)) {
            $cid = $this->get_unique_id();
            $this->_embedded[] = array(
                'file' => $file,
                'name' => basename($file),
                'cid' => $cid,
                'data' => $this->attachment_data($file),
            );
            return sprintf('cid:%s', $cid);
        }
        throw new Exception('File not found: ' . htmlspecialchars(basename($file)));
        return FALSE;
    }

    public function & add_attachment($file, $filename = '')
    {
        if (file_exists($file)) {
            $filename = empty($filename) ? basename($file) : $filename;
            $this->_attachment[] = array(
                'file' => $file,
                'name' => $filename,
                'data' => $this->attachment_data($file),
            );
            return $this;
        }
        throw new Exception('File not found: ' . htmlspecialchars(basename($file)));
        return FALSE;
    }

    public function add_to($email, $name = '')
    {
        if (!$this->add_address('to', $email, $name)) {
            throw new Exception('Email address isn\'t valid: ' . htmlspecialchars($email));
        }
    }

    public function add_cc($email, $name = '')
    {
        if (!$this->add_address('cc', $email, $name)) {
            throw new Exception('Email address isn\'t valid: ' . htmlspecialchars($email));
        }
    }

    public function add_bcc($email, $name = '')
    {
        if (!$this->add_address('bcc', $email, $name)) {
            throw new Exception('Email address isn\'t valid: ' . htmlspecialchars($email));
        }
    }

    public function body($message, $type = 'txt')
    {
        if ($type == 'html') {
            $this->_body['html'] = $message;
        }
        $this->_body['txt'] = strip_tags($message);
    }

    public function wrap_limit($numChar = NULL)
    {
        if (is_null($numChar)) {
            return $this->_wrap;
        }
        $this->_wrap = (int) $numChar;
    }

    public function wrap($message)
    {
        return wordwrap($message, $this->_wrap);
    }

}
