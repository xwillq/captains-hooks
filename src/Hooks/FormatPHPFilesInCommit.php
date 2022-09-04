<?php

namespace Xwillq\Hooks\Hooks;

use CaptainHook\App\Config;
use CaptainHook\App\Console\IO;
use CaptainHook\App\Exception\ActionFailed;
use CaptainHook\App\Hook\Action;
use CaptainHook\App\Hook\Constrained;
use CaptainHook\App\Hook\Restriction;
use CaptainHook\App\Hooks;
use SebastianFeldmann\Cli\Processor\ProcOpen as Processor;
use SebastianFeldmann\Git\Repository;
use Xwillq\GitStageEditor\GitStagedFileEditor;

class FormatPHPFilesInCommit implements Action, Constrained
{
    /**
     * Returns the list of hooks where this action is applicable
     *
     * @return Restriction
     */
    public static function getRestriction(): Restriction
    {
        return Restriction::fromString(Hooks::PRE_COMMIT);
    }

    /**
     * Executes the action
     *
     * @param  Config  $config
     * @param  IO  $io
     * @param  Repository  $repository
     * @param  Config\Action  $action
     * @return void
     *
     * @throws \Exception
     */
    public function execute(Config $config, IO $io, Repository $repository, Config\Action $action): void
    {
        /** @var array<string> $excluded_files */
        $excluded_files = $action->getOptions()->get('excluded-files') ?: [];

        $changed_php_files = $this->getChangedFiles($repository, $excluded_files);
        if ($changed_php_files === []) {
            return;
        }

        /** @var string $formatter */
        $formatter = $action->getOptions()->get('formatter') ?: 'vendor/bin/pint {}';

        $index_editor = new GitStagedFileEditor($repository->getRoot());

        $processor = new Processor();
        $index_editor->execute(static function ($file, string $file_path) use ($formatter, $processor) {
            $result = $processor->run(str_replace('{}', $file_path, $formatter));
            if (! $result->isSuccessful()) {
                throw new ActionFailed('<error>Failed while formatting files</error>');
            }
        }, array_map(static fn (string $path) => "*/$path", $changed_php_files));
    }

    /**
     * @param  Repository  $repository
     * @param  array<string>  $excluded_files
     * @return array<string>
     *
     * @throws \RuntimeException
     */
    public function getChangedFiles(Repository $repository, array $excluded_files): array
    {
        /** @throws \RuntimeException */
        return array_filter(
            $repository->getIndexOperator()->getStagedFilesOfType('php', ['A', 'M']),
            static function (string $path) use ($excluded_files): bool {
                foreach ($excluded_files as $pattern) {
                    if ($path === $pattern) {
                        return false;
                    }
                    if (str_starts_with($pattern, '/')) {
                        $matched = preg_match($pattern, $path);
                        if ($matched === false) {
                            throw new \RuntimeException('Regex exception occurred');
                        }

                        if ($matched === 1) {
                            return true;
                        }
                    } elseif (fnmatch($pattern, $path)) {
                        return true;
                    }
                }

                return true;
            },
        );
    }
}
