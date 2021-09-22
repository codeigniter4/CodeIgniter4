<?php

$checker = new UserGuideCheck();
$checker->run();

class UserGuideCheck
{
    public function run(): void
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
}
