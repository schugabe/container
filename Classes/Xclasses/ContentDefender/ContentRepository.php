<?php

declare(strict_types=1);

namespace B13\Container\Xclasses\ContentDefender;

/*
 * This file is part of TYPO3 CMS-based extension "container" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Container\Domain\Factory\ContainerFactory;
use B13\Container\Domain\Factory\Exception;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ContentRepository extends \IchHabRecht\ContentDefender\Repository\ContentRepository
{
    /**
     * @var ContainerFactory
     */
    protected $containerFactory;

    public function __construct(ContainerFactory $containerFactory = null)
    {
        $this->containerFactory = $containerFactory ?? GeneralUtility::makeInstance(ContainerFactory::class);
    }

    protected function fetchRecordsForColPos(array $record): array
    {
        $records = parent::fetchRecordsForColPos($record);
        $containerUid = $this->getContainerUidFromRecord($record);
        if ($containerUid !== null) {
            try {
                $container = $this->containerFactory->buildContainer($containerUid);
                $childrenInColPos = $container->getChildrenByColPos((int)$record['colPos']);
                $containerRecords = [];
                foreach ($childrenInColPos as $child) {
                    if (!empty($records[$child['uid']])) {
                        $containerRecords[$child['uid']] = $records[$child['uid']];
                    }
                }
                return $containerRecords;
            } catch (Exception $e) {
                // not a container
            }
        }
        return $records;
    }

    protected function getIdentifier(array $record): string
    {
        $identifier = parent::getIdentifier($record);
        $containerUid = $this->getContainerUidFromRecord($record);
        if ($containerUid !== null) {
            $identifier .= '/' . $containerUid;
        }
        return $identifier;
    }

    protected function getContainerUidFromRecord(array $record): ?int
    {
        if (!empty($record['tx_container_parent'][0]) && $record['tx_container_parent'][0] > 0) {
            return (int)$record['tx_container_parent'][0];
        }
        return null;
    }
}
