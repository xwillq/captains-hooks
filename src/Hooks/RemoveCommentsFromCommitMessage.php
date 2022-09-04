<?php

namespace Xwillq\Hooks\Hooks;

use CaptainHook\App\Config;
use CaptainHook\App\Console\IO;
use CaptainHook\App\Hook\Action;
use CaptainHook\App\Hook\Constrained;
use CaptainHook\App\Hook\Restriction;
use CaptainHook\App\Hooks;
use SebastianFeldmann\Git\CommitMessage;
use SebastianFeldmann\Git\Repository;

class RemoveCommentsFromCommitMessage implements Action, Constrained
{
    /**
     * Returns the list of hooks where this action is applicable
     *
     * @return Restriction
     */
    public static function getRestriction(): Restriction
    {
        return Restriction::fromArray([Hooks::PRE_COMMIT, Hooks::PREPARE_COMMIT_MSG, Hooks::COMMIT_MSG]);
    }

    /**
     * Executes the action
     *
     * @param  Config  $config
     * @param  IO  $io
     * @param  Repository  $repository
     * @param  Config\Action  $action
     * @return void
     */
    public function execute(Config $config, IO $io, Repository $repository, Config\Action $action): void
    {
        $oldMsg = $repository->getCommitMsg();
        $repository->setCommitMsg(new CommitMessage(
            $oldMsg->getContent(),
            $oldMsg->getCommentCharacter(),
        ));
    }
}
