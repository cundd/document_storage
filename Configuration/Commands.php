<?php
declare(strict_types=1);

return [
    'document-storage'        => ['class' => \Cundd\DocumentStorage\Command\GeneralCommandController::class],
    'document-storage:list'   => ['class' => \Cundd\DocumentStorage\Command\ListCommandController::class],
    'document-storage:create' => ['class' => \Cundd\DocumentStorage\Command\CreateCommandController::class],
    'document-storage:read'   => ['class' => \Cundd\DocumentStorage\Command\ReadCommandController::class],
    'document-storage:delete' => ['class' => \Cundd\DocumentStorage\Command\DeleteCommandController::class],
    'document-storage:gc'     => ['class' => \Cundd\DocumentStorage\Command\GcCommandController::class],
];
