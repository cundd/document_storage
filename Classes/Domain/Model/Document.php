<?php
namespace Cundd\DocumentStorage\Domain\Model;


/***
 *
 * This file is part of the "Document Storage" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2019 Daniel Corn <info@cundd.net>
 *
 ***/
/**
 * Document
 */
class Document extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * Document identifier
     * 
     * @var string
     * @validate NotEmpty
     */
    protected $id = '';

    /**
     * Database identifier
     * 
     * @var string
     * @validate NotEmpty
     */
    protected $db = '';

    /**
     * JSON encoded data
     * 
     * @var string
     * @validate NotEmpty
     */
    protected $dataProtected = '';

    /**
     * Returns the id
     * 
     * @return string $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the id
     * 
     * @param string $id
     * @return void
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Returns the db
     * 
     * @return string $db
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * Sets the db
     * 
     * @param string $db
     * @return void
     */
    public function setDb($db)
    {
        $this->db = $db;
    }

    /**
     * Returns the dataProtected
     * 
     * @return string $dataProtected
     */
    public function getDataProtected()
    {
        return $this->dataProtected;
    }

    /**
     * Sets the dataProtected
     * 
     * @param string $dataProtected
     * @return void
     */
    public function setDataProtected($dataProtected)
    {
        $this->dataProtected = $dataProtected;
    }
}
