<?php

/**
 * Sockets implementation of the TTransport interface.
 * @package thrift.transport
 */
class R2m_Socket extends R2m_Transport {

    private $_errMsg;

    /**
     * Handle to PHP socket
     * @var resource
     */
    private $handle_ = NULL;

    /**
     * Remote hostname
     * @var string
     */
    public $host_ = 'localhost';

    /**
     * Remote port
     * @var int
     */
    public $port_ = '9090';

    /**
     * Send timeout in seconds.
     * Combined with sendTimeoutUsec this is used for send timeouts.
     * @var int
     */
    private $sendTimeoutSec_ = 1;

    /**
     * Send timeout in microseconds.
     * Combined with sendTimeoutSec this is used for send timeouts.
     * @var int
     */
    private $sendTimeoutUsec_ = 1000000;

    /**
     * Recv timeout in seconds
     * Combined with recvTimeoutUsec this is used for recv timeouts.
     * @var int
     */
    private $recvTimeoutSec_ = 2;

    /**
     * Recv timeout in microseconds
     * Combined with recvTimeoutSec this is used for recv timeouts.
     * @var int
     */
    private $recvTimeoutUsec_ = 750000;

    /**
     * Persistent socket or plain?
     * @var bool
     */
    protected $persist_ = FALSE;

    /**
     * Debugging on?
     * @var bool
     */
    protected $debug_ = FALSE;

    /**
     * Debug handler
     * @var mixed
     */
    protected $debugHandler_ = NULL;

    /**
     * Socket constructor
     * @param string $host Remote hostname
     * @param int $port Remote port
     * @param bool $persist Whether to use a persistent socket
     * @param string $debugHandler Function to call for error logging
     */
    public function __construct(
      $host = 'localhost',
      $port = 9090,
      $persist = FALSE,
      $debugHandler = NULL
    ) {
        $this->host_ = $host;
        $this->port_ = $port;
        $this->persist_ = $persist;
        $this->debugHandler_ = $debugHandler ? $debugHandler : 'error_log';
    }

    /**
     * @param resource $handle
     * @return void
     */
    public function setHandle($handle) {
        $this->handle_ = $handle;
    }

    /**
     * Sets the send timeout.
     * @param int $timeout Timeout in milliseconds.
     */
    public function setSendTimeout($timeout) {
        $this->sendTimeoutSec_ = floor($timeout / 1000);
        $this->sendTimeoutUsec_ = ($timeout - ($this->sendTimeoutSec_ * 1000))
          * 1000;
    }

    /**
     * Sets the receive timeout.
     * @param int $timeout Timeout in milliseconds.
     */
    public function setRecvTimeout($timeout) {
        $this->recvTimeoutSec_ = floor($timeout / 1000);
        $this->recvTimeoutUsec_ = ($timeout - ($this->recvTimeoutSec_ * 1000))
          * 1000;
    }

    /**
     * Sets debugging output on or off
     * @param bool $debug
     */
    public function setDebug($debug) {
        $this->debug_ = $debug;
    }

    /**
     * Get the host that this socket is connected to
     * @return string host
     */
    public function getHost() {
        return $this->host_;
    }

    /**
     * Get the remote port that this socket is connected to
     * @return int port
     */
    public function getPort() {
        return $this->port_;
    }

    /**
     * Tests whether this is open
     * @return bool true if the socket is open
     */
    public function isOpen() {
        return is_resource($this->handle_);
    }

    /**
     * Connects the socket.
     */
    public function open() {
        if ($this->isOpen()) {
            $this->_errMsg = 'Socket already connected';
            return TRUE;
            //throw new TTransportException('Socket already connected', TTransportException::ALREADY_OPEN);
        }

        if (empty($this->host_)) {
            $this->_errMsg = 'Cannot open null host';
            return FALSE;
            //throw new TTransportException('Cannot open null host', TTransportException::NOT_OPEN);
        }

        if ($this->port_ <= 0) {
            $this->_errMsg = 'Cannot open without port';
            // throw new TTransportException('Cannot open without port', TTransportException::NOT_OPEN);
            return FALSE;
        }

        if ($this->persist_) {
            $handle = @pfsockopen(
              $this->host_,
              $this->port_,
              $errno,
              $errstr,
              $this->sendTimeoutSec_ + ($this->sendTimeoutUsec_ / 1000000)
            );
        } else {
            $handle = @fsockopen(
              $this->host_,
              $this->port_,
              $errno,
              $errstr,
              $this->sendTimeoutSec_ + ($this->sendTimeoutUsec_ / 1000000)
            );
        }

        $flag = ($handle !== $this->handle_);
        // var_dump("handle:$handle, this->handle_:{$this->handle_}, flag:{$flag}");
        $this->handle_ = $handle;

        // Connect failed?
        if ($this->handle_ === FALSE) {
            $error = 'TSocket: Could not connect to ' . $this->host_ . ':'
              . $this->port_ . ' (' . $errstr . ' [' . $errno . '])';
            if ($this->debug_) {
                call_user_func($this->debugHandler_, $error);
            }
            // throw new TException($error);

            $this->_errMsg = $error;
            return FALSE;
        }

        return $flag;
    }

    /**
     * Closes the socket.
     */
    public function close() {
//        if (!$this->persist_) {
            $flag = @fclose($this->handle_);
            $this->handle_ = NULL;
//        }

        return $flag;
    }

    /**
     * Read from the socket at most $len bytes.
     * This method will not wait for all the requested data, it will return as
     * soon as any data is received.
     * @param int $len Maximum number of bytes to read.
     * @return string Binary data
     */
    public function read($len) {
        $null = NULL;
        $read = array($this->handle_);
        $readable = @stream_select(
          $read, $null, $null, $this->recvTimeoutSec_, $this->recvTimeoutUsec_
        );

        if ($readable > 0) {
            $data = @stream_socket_recvfrom($this->handle_, $len);
            if ($data === FALSE) {
                $this->_errMsg = 'TSocket: Could not read ' . $len
                  . ' bytes from ' . $this->host_ . ':' . $this->port_;
            } elseif ($data == '' && feof($this->handle_)) {
                $this->_errMsg = 'TSocket read 0 bytes';
            } else {
                return $data;
            }
        } else if ($readable === 0) {
            $this->_errMsg =
              'TSocket: timed out reading ' . $len . ' bytes from ' .
              $this->host_ . ':' . $this->port_;
        } else {
            $this->_errMsg =
              'TSocket: Could not read ' . $len . ' bytes from ' .
              $this->host_ . ':' . $this->port_;
        }

        return false;
    }

    /**
     * Write to the socket.
     * @param string $buf The data to write
     * @return bool
     */
    public function write($buf) {
        $null = NULL;
        $write = array($this->handle_);

        // keep writing until all the data has been written
        while (strlen($buf) > 0) {
            // wait for stream to become available for writing
            $writable = @stream_select(
              $null, $write, $null, $this->sendTimeoutSec_,
              $this->sendTimeoutUsec_
            );
            if ($writable > 0) {
                // write buffer to stream
                $written = @stream_socket_sendto($this->handle_, $buf);
                if ($written === -1 || $written === FALSE) {
                    $this->_errMsg = 'TSocket: Could not write ' . strlen($buf) . ' bytes ' .
                      $this->host_ . ':' . $this->port_;

                    $this->close();
                    return false;
                }

                // determine how much of the buffer is left to write
                $buf = substr($buf, $written);
            } else if ($writable === 0) {
                $this->_errMsg = (
                  'TSocket: timed out writing ' . strlen($buf) . ' bytes from ' .
                  $this->host_ . ':' . $this->port_
                );

                $this->close();
                return false;
            } else {
                $this->_errMsg = (
                  'TSocket: Could not write ' . strlen($buf) . ' bytes ' .
                  $this->host_ . ':' . $this->port_
                );

                $this->close();
                return false;
            }
        }

        return true;
    }

    /**
     * Flush output to the socket.
     * Since read(), readAll() and write() operate on the sockets directly,
     * this is a no-op
     * If you wish to have flushable buffering behaviour, wrap this TSocket
     * in a TBufferedTransport.
     */
    public function flush() {
        // no-op
    }

    public function getErrorMsg() {
        return $this->_errMsg;
    }
}
