<?php

class R2m_SwooleServer extends R2m_Transport {
    /**
     * @type swoole_server
     */
    private $_server = null;
    private $_fd = null;

    public function __construct(swoole_server $server, $fd) {
        $this->_server = $server;
        $this->_fd = $fd;
    }

    /**
     * Tests whether this is open
     * @return bool true if the socket is open
     */
    public function isOpen() {
        return !!$this->_server->exist($this->_fd);
    }

    /**
     * Connects the socket.
     */
    public function open() {
        return true;
    }

    /**
     * Closes the socket.
     */
    public function close() {
        $this->_server->close($this->_fd);
    }

    /**
     * Read from the socket at most $len bytes.
     * This method will not wait for all the requested data, it will return as
     * soon as any data is received.
     * @param int $len Maximum number of bytes to read.
     * @return string Binary data
     */
    public function read($len) {

    }

    /**
     * Write to the socket.
     * @param string $buf The data to write
     */
    public function write($buf) {
        $this->_server->send($this->_fd, $buf);
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
    }
}
