<?php

$target  = $argv[1] ?? 'commit';
$checker = new UserGuideCheck();
$checker->run($target);

class UserGuideCheck
{
    public function run(string $target): void
    {
        switch ($target) {
            case 'commit':
                $this->checkIndexedFiles();
                break;

            case 'all':
                $this->checkAllFiles();
                break;

            default:
                echo 'Error: "' . $target . '" is invalid.' . "\n";

                exit(1);
        }
    }

    private function checkIndexedFiles(): void
    {
        $files = $this->getIndexedFiles();
        $this->checkTab($files);
    }

    private function getIndexedFiles(): array
    {
        exec('git diff --cached --name-only --diff-filter=ACMR HEAD | grep \\.rst$', $output);

        return $output;
    }

    private function checkTab(array $files): void
    {
        $errorFiles = [];

        foreach ($files as $file) {
            $contents = file_get_contents($file);

            if (strpos($contents, "\t") !== false) {
                $errorFiles[] = $file;
            }
        }

        if ($errorFiles !== []) {
            foreach ($errorFiles as $file) {
                echo "{$file}\n";
            }
            echo "The user guide contains tab(s). Please replace tabs with spaces before commit.\n";

            exit(1);
        }
    }

    private function checkAllFiles(): void
    {
        $files = $this->getAllFiles();
        $this->checkTab($files);
    }

    private function getAllFiles(): array
    {
        $dir = './user_guide_src/source';
        $ext = 'rst';

        $iter   = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
        $filter = static function (SplFileInfo $file) use ($ext) {
            return ! $file->isDir() && $file->getExtension() === $ext;
        };
        $iterator = new \CallbackFilterIterator($iter, $filter);

        $files = array_map(
            static function (SplFileInfo $file) {
                return $file->getPathname();
            },
            iterator_to_array($iterator, false)
        );

        return $files;
    }
}
