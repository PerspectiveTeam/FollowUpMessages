<?php


namespace Perspective\FollowUpMessages\Helper\Logger;

use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Logger\Handler\Base;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Monolog\Logger;
use DateTime;

class Handler extends Base
{
    const ROOT = '/var/log/followup';

    const FILE_NAME = 'followup.log';

    /**
     * Date folder format.
     *
     * @var string
     */
    const FOLDER_DATE_FORMAT = 'd_m_Y';

    /**
     * {@inheritDoc}
     */
    protected $loggerType = Logger::INFO;

    /**
     * @var DateTime
     */
    protected $_date;

    public function __construct(
        TimezoneInterface $timezone,
        DriverInterface $filesystem,
        $filePath = null,
        $fileName = null
    )
    {
        $this->_date = $timezone->date();
        parent::__construct(
            $filesystem,
            $filePath,
            $this->getFilePath()
        );
    }

    /**
     * Get date instance.
     *
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        return $this->_date;
    }

    /**
     * Get date folder.
     *
     * @return string
     */
    protected function getDateFolderName(): string
    {
        return $this->getDate()->format(self::FOLDER_DATE_FORMAT);
    }

    /**
     * Get full file path,
     *
     * @return string
     */
    public function getFilePath(): string
    {
        return self::ROOT
            .'/'
            .$this->getDateFolderName()
            .'/'
            .self::FILE_NAME;
    }
}
