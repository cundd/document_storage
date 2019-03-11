<?php
declare(strict_types=1);

return [
    'document-storage:create' => ['class' => \Cundd\DocumentStorage\Command\CreateCommandController::class],
    'document-storage:read'   => ['class' => \Cundd\DocumentStorage\Command\ReadCommandController::class],
    'document-storage:delete' => ['class' => \Cundd\DocumentStorage\Command\DeleteCommandController::class],
];