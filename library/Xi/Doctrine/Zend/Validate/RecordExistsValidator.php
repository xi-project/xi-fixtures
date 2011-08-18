<?php
namespace Xi\Doctrine\Zend\Validate;

/**
 * @author     Mikko Hirvonen <mikko.hirvonen@brainalliance.com>
 */
class RecordExistsValidator extends AbstractExistsValidator
{
    /**
     * Is valid
     *
     * @param  string
     * @return boolean
     */
    public function isValid($value)
    {
        $valid = true;
        $this->_setValue($value);

        $result = $this->_query($value);

        if (!$result) {
            $valid = false;
            $this->_error(self::ERROR_NO_RECORD_FOUND);
        }

        return $valid;
    }
}
