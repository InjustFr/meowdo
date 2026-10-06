<?php

declare(strict_types=1);

namespace App\Application\Planning\EditProject;

use App\Application\Planning\ProjectView;
use App\Application\Transaction;
use App\Domain\Planning\Exception\DuplicateProjectName;
use App\Domain\Planning\ProjectRepository;

final readonly class EditProjectHandler
{
    public function __construct(
        private ProjectRepository $projects,
        private Transaction $transaction,
    ) {
    }

    public function __invoke(EditProject $command): ProjectView
    {
        $project = $this->projects->get($command->id);
        $homonym = $this->projects->named($command->name);
        if (null !== $homonym && !$homonym->id()->equals($project->id())) {
            throw new DuplicateProjectName(trim($command->name));
        }

        $project->rename($command->name);
        $project->recolor($command->color);
        $this->transaction->commit();

        return ProjectView::of($project);
    }
}
