<?php


/**
 * Base interface for a transport agent.
 *
 * @package thrift.transport
 */
abstract class R2m_Transport {

    /**
     * Whether this transport is open.
     *
     * @return boolean true if open
     */
    public abstract function isOpen();

    /**
     * Open the transport for reading/writing
     *
     */
    public abstract function open();

    /**
     * Close the transport.
     */
    public abstract function close();

    /**
     * Read some data into the array.
     *
     * @param int    $len How much to read
     * @return string The data that has been read
     */
    public abstract function read($len);

    /**
     * Guarantees that the full amount of data is read.
     *
     * @return string The data, of exact length
     */
    public function readAll($len) {
        // return $this->read($len);
        $data = '';
        while (($got = strlen($data)) < $len) {
            $ret = $this->read($len - $got);
            if ($ret !== false) {
                $data .= $ret;
            } else {
                return false;
            }
        }
        return $data;
    }

    /**
     * Writes the given data out.
     *
     * @param string $buf  The data to write
     */
    public abstract function write($buf);

    /**
     * Flushes any pending data out of a buffer
     *
     */
    public function flush() {}
}
