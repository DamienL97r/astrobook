<?php

declare(strict_types=1);

/*
 * This file is part of the AstroBook project.
 * (c) David Pelletier-Ulrich <d@mztrix.me>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Dogstronauts\AstroBook\ApiPlatform\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\State\ProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Dogstronauts\AstroBook\Contracts\SoftDeleteInterface;

class SoftDeleteProcessor implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $decorated,
        private EntityManagerInterface $em,
    ) {
    }

    public function process($data, \ApiPlatform\Metadata\Operation $operation, array $uriVariables = [], array $context = [])
    {
        if (
            !$data instanceof SoftDeleteInterface
            || !$operation instanceof Delete
        ) {
            return $this->decorated->process($data, $operation, $uriVariables, $context);
        }

        $data->setDeletedAt(new \DateTimeImmutable());
        $this->em->flush();

        return $data;
    }
}
