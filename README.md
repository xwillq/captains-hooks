# Captain's Hooks

My hooks for [Captain Hook](https://github.com/captainhookphp/captainhook).

## Installation

Install with Composer:

```shell
composer require --dev xwillq/captains-hooks
```

## Available hooks

### FormatPHPFilesInCommit

Runs code formatter before commit, only formats changes that were staged and
applies fixes to working tree. Uses Laravel [Pint](https://github.com/laravel/pint)
by default.

Can be used only as `pre-commit` hook. Configuration:

```json5
{
    "action": "\\Xwillq\\Hooks\\Hooks\\FormatPHPFilesInCommit",
    "options": {
        // Exclude files from formatting.
        "excluded-files": [
            // Specify file path.
            "src/FormatPHPFilesInCommit.php",
            // Patterns starting with / are treated as a regex.
            "/tests\/.*/",
            // Glob pattern.
            "config/*"
        ],
        // Command to execute. Placeholder `{}` gets replaced with path to file.
        // `vendor/bin/pint {}` is the default value.
        "formatter": "vendor/bin/pint {}"
    }
}
```

### FixPHPStormMergeMessage

When merging, PHPStorm adds list of conflicts to commit message body as comments.
This hook removes them.

Can be used only as `prepare-commit-msg` hook. Configuration:

```json5
{
    "action": "\\Xwillq\\Hooks\\Hooks\\FixPHPStormMergeMessage"
}
```

### RemoveCommentsFromCommitMessage

Removes comments from commit message. Can be used to sanitize message before
validation.

Can be used as `pre-commit`, `prepare-commit-msg` and `commit-msg` hook.
Configuration:

```json5
{
    "action": "\\Xwillq\\Hooks\\Hooks\\RemoveCommentsFromCommitMessage"
}
```
